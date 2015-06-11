<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
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
     * @param ContractRepositoryInterface $contract
     * @param Guard                       $auth
     * @param Storage                     $storage
     * @param Filesystem                  $filesystem
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        Guard $auth,
        Storage $storage,
        Filesystem $filesystem
    ) {
        $this->contract   = $contract;
        $this->auth       = $auth;
        $this->storage    = $storage;
        $this->filesystem = $filesystem;
    }

    /**
     * Get all contract
     *
     * @return Contract
     */
    public function getAll()
    {
        $contracts = $this->contract->getAll();

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
     * Upload Contract and save in database
     *
     * @param array $formData
     * @return \App\Nrgi\Entities\Contract\Contract|bool
     */
    public function saveContract(array $formData)
    {
        if ($file = $this->uploadContract($formData['file'])) {
            $data = [
                'file'     => $file['name'],
                'filehash' => $file['hash'],
                'user_id'  => $this->auth->user()->id,
                'metadata' =>
                    [
                        "language"             => isset($formData["language"]) ?$formData["language"]: '',
                        "country"              => isset($formData['country']) ?$formData['country']: '',
                        "resource"             => isset($formData['resource']) ?$formData['resource']: '',
                        "government_entity"    => isset($formData['government_entity']) ?$formData['government_entity']: '',
                        "type_of_mining_title" => isset($formData['type_of_mining_title']) ?$formData['type_of_mining_title']: '',
                        "signature_date"       => isset($formData['signature_date']) ?$formData['signature_date']: '',
                        "contract_term"        => isset($formData['contract_term']) ?$formData['contract_term']: '',
                        "company"              => isset($formData['company']) ?$formData['company']: '',
                        "license_name"         => isset($formData['license_name']) ?$formData['license_name']: '',
                        "license_identifier"   => isset($formData['license_identifier']) ?$formData['license_identifier']: '',
                        "license_source_url"   => isset($formData['license_source_url']) ?$formData['license_source_url']: '',
                        "license_type"         => isset($formData['license_type']) ?$formData['license_type']: '',
                        "project_title"        => isset($formData['project_title']) ?$formData['project_title']: '',
                        "project_identifier"   => isset($formData['project_identifier']) ?$formData['project_identifier']: '',
                        "date_granted"         => isset($formData['date_granted']) ?$formData['date_granted']: '',
                        "date_ratification"    => isset($formData['date_ratification']) ?$formData['date_ratification']: '',
                        "Source_url"           => isset($formData['Source_url']) ?$formData['Source_url']: '',
                        "date_retrieval"       => isset($formData['date_retrieval']) ?$formData['date_retrieval']: '',
                        "category"             => isset($formData['category']) ?$formData['category']: '',
                        'file_size'            => $file['size']
                    ]
            ];

            return $this->contract->save($data);
        }

        return false;
    }

    /**
     * Update Contract
     * @param array $formData
     * @return bool
     */
    public function updateContract($contractID, array $formData)
    {
        $contract           = $this->contract->findContract($contractID);
        $file_size          = $contract->metadata->file_size;
        $contract->metadata =
            [
                "language"             => $formData["language"],
                "country"              => $formData['country'],
                "resource"             => $formData['resource'],
                "government_entity"    => $formData['government_entity'],
                "type_of_mining_title" => $formData['type_of_mining_title'],
                "signature_date"       => $formData['signature_date'],
                "contract_term"        => $formData['contract_term'],
                "company"              => $formData['company'],
                "license_name"         => $formData['license_name'],
                "license_identifier"   => $formData['license_identifier'],
                "license_source_url"   => $formData['license_source_url'],
                "license_type"         => $formData['license_type'],
                "project_title"        => $formData['project_title'],
                "project_identifier"   => $formData['project_identifier'],
                "date_granted"         => $formData['date_granted'],
                "date_ratification"    => $formData['date_ratification'],
                "Source_url"           => $formData['Source_url'],
                "date_retrieval"       => $formData['date_retrieval'],
                "location"             => $formData['location'],
                'file_size'            => $file_size
            ];

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
}
