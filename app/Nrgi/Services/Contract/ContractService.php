<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\Queue;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Contract
 */
class ContractService
{
    /**
     * Contract upload folder
     */
    const UPLOAD_FOLDER = 'data';

    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;
    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var Filesystem
     */
    protected $storage;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Contract on pipeline
     */
    const CONTRACT_QUEUE = 0;
    /**
     * Contract on Pending
     */
    const CONTRACT_PENDING = 1;
    /**
     * Contract Completed
     */
    const CONTRACT_COMPLETE = 2;
    /**
     * @var CountryService
     */
    protected $countryService;
    /**
     * @var Queue
     */
    protected $queue;


    /**
     * @param ContractRepositoryInterface $contract
     * @param Guard                       $auth
     * @param Storage                     $storage
     * @param Filesystem                  $filesystem
     * @param CountryService              $countryService
     * @param Queue                       $queue
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        Guard $auth,
        Storage $storage,
        Filesystem $filesystem,
        CountryService $countryService,
        Queue $queue
    ) {
        $this->contract       = $contract;
        $this->auth           = $auth;
        $this->storage        = $storage;
        $this->filesystem     = $filesystem;
        $this->countryService = $countryService;
        $this->queue          = $queue;
    }

    /**
     * Get all contract
     *
     * @param $filters
     * @return Contract
     */
    public function getAll($filters)
    {
        $contracts = $this->contract->getAll($filters);

        return $contracts;
    }

    /**
     * Get Contract By ID
     * @param $id
     * @return Contract
     */
    public function find($id)
    {
        return $this->contract->findContract($id);
    }

    /**
     * Get Contract With Pages by ID
     * @param $id
     * @return mixed
     */
    public function findWithPages($id)
    {
        return $this->contract->findContractWithPages($id);
    }


    /**
     * Upload Contract and save in database
     *
     * @param array $formData
     * @return \App\Nrgi\Entities\Contract\Contract|bool
     */
    public function saveContract(array $formData)
    {
        if ($file = $this->uploadContract($formData['file'])) {

            $metadata              = $this->processMetadata($formData);
            $metadata['file_size'] = $file['size'];
            $data                  = [
                'file'     => $file['name'],
                'filehash' => $file['hash'],
                'user_id'  => $this->auth->user()->id,
                'metadata' => $metadata
            ];
            $contract              = $this->contract->save($data);

            if ($contract) {
                $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $contract->id]);
            }

            return $contract;
        }

        return false;
    }


    protected function processMetadata($formData)
    {
        $data = [
            "language"                  => isset($formData["language"]) ? $formData["language"] : '',
            "country"                   => isset($formData['country']) ? $this->countryService->getInfoByCode(
                $formData['country']
            ) : '',
            "resource"                  => isset($formData['resource']) ? $formData['resource'] : '',
            "government_entity"         => isset($formData['government_entity']) ? $formData['government_entity'] : '',
            "government_identifier"     => isset($formData['government_identifier']) ? $formData['government_identifier'] : '',
            "type_of_contract"          => isset($formData['type_of_contract']) ? $formData['type_of_contract'] : '',
            "signature_date"            => isset($formData['signature_date']) ? $formData['signature_date'] : '',
            "signature_year"            => (isset($formData['signature_date']) && $formData['signature_date'] != '') ? date(
                'Y',
                strtotime($formData['signature_date'])
            ) : '',
            "document_type"             => isset($formData['document_type']) ? $formData['document_type'] : '',
            "translation_from_original" => isset($formData['translation_from_original']) ? $formData['translation_from_original'] : '',
            "translation_parent"        => isset($formData['translation_parent']) ? $formData['translation_parent'] : '',
            "company"                   => isset($formData['company']) ? $formData['company'] : '',
            "license_name"              => isset($formData['license_name']) ? $formData['license_name'] : '',
            "license_identifier"        => isset($formData['license_identifier']) ? $formData['license_identifier'] : '',
            "project_title"             => isset($formData['project_title']) ? $formData['project_title'] : '',
            "project_identifier"        => isset($formData['project_identifier']) ? $formData['project_identifier'] : '',
            "Source_url"                => isset($formData['Source_url']) ? $formData['Source_url'] : '',
            "date_retrieval"            => isset($formData['date_retrieval']) ? $formData['date_retrieval'] : '',
            "category"                  => isset($formData['category']) ? $formData['category'] : '',
        ];

        return $data;

    }

    /**
     * Update Contract
     * @param array $formData
     * @return bool
     */
    public function updateContract($contractID, array $formData)
    {
        $contract              = $this->contract->findContract($contractID);
        $file_size             = $contract->metadata->file_size;
        $metadata              = $this->processMetadata($formData);
        $metadata['file_size'] = $file_size;
        $contract->metadata    = $metadata;

        return $contract->save();
    }

    /**
     * Upload contract file
     *
     * @param UploadedFile $file
     * @return array
     */
    protected function uploadContract(UploadedFile $file)
    {
        if ($file->isValid()) {
            $fileName    = $file->getClientOriginalName();
            $file_type   = $file->getClientOriginalExtension();
            $newFileName = sprintf("%s.%s", $this->hashFileName($fileName), $file_type);
            $data        = $this->storage->disk('s3')->put(
                $newFileName,
                $this->filesystem->get($file)
            );

            if ($data) {
                return [
                    'name' => $newFileName,
                    'size' => $file->getSize(),
                    'hash' => $this->hashFileName($newFileName),
                ];
            }

        }

        return false;
    }

    /**
     * Delete Contract
     *
     * @param $id
     * @return bool
     */
    function deleteContract($id)
    {
        $contract = $this->contract->findContract($id);

        if ($this->contract->delete($contract->id)) {
            return $this->deleteFileFromS3($contract->file);
        }

        return false;
    }

    /**
     * Delete File from aws s3
     *
     * @param $file
     * @return bool
     * @throws Exception
     */
    protected function deleteFileFromS3($file)
    {
        if (!$this->storage->disk('s3')->exists($file)) {
            throw new Exception('File not found');
        }

        return $this->storage->disk('s3')->delete($file);
    }

    /**
     * Hash the file name
     *
     * @param string $name
     * @return string
     */
    protected function hashFileName($name)
    {
        return sha1(microtime() . $name); //$this->storage->disk('s3')->get($name);
    }

    /**
     * Get aws s3 file url
     *
     * @param string $fileName
     * @return string
     */
    public function getS3FileURL($fileName = '')
    {
        return $this->storage->disk('s3')
                             ->getDriver()
                             ->getAdapter()
                             ->getClient()
                             ->getObjectUrl(env('AWS_BUCKET'), $fileName);
    }

    /**
     * Get Contract Status by ContractID
     *
     * @param $contractId
     * @return int
     */
    public function getStatus($contractID)
    {
        $path = public_path(self::UPLOAD_FOLDER . '/' . $contractID);

        if ($this->filesystem->exists($path)) {
            try {
                $status = $this->filesystem->get(sprintf('%s/status.txt', $path));
                $status = (integer) trim($status);

                return $status === 1 ? self::CONTRACT_COMPLETE : self::CONTRACT_PENDING;
            } catch (FileNotFoundException $e) {
                return self::CONTRACT_PENDING;
            }
        }

        return self::CONTRACT_QUEUE;
    }

    /**
     * Save Page text
     *
     * @param $id
     * @param $page
     * @param $text
     * @return int
     */
    public function savePageText($id, $page, $text)
    {
        $path = public_path(self::UPLOAD_FOLDER . '/' . $id . '/' . $page . '.txt');
        echo $text;

        return $this->filesystem->put($path, $text);
    }

    /**
     * Save Pdf Output Type
     * @param $contractID
     * @param $textType
     * @return Contract/bool
     */
    public function saveTextType($contractID, $textType)
    {
        $contract           = $this->contract->findContract($contractID);
        $contract->textType = $textType;
        if ($contract->save()) {
            return $contract;
        }

        return false;
    }

}
