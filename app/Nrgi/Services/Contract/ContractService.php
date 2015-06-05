<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Contract
 */
class ContractService
{
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
    private $filesystem;

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
                'file'     => $file['filePath'],
                'filehash' => $file['fileHash'],
                'user_id'  => $this->auth->user()->id,
                'metadata' =>
                    [
                        'project_title'  => $formData['project_title'],
                        'language'       => $formData['language'],
                        'country'        => $formData['country'],
                        'resource'       => $formData['resource'],
                        'signature_date' => $formData['signature_date'],
                        'signature_year' => $formData['signature_year'],
                        'type_of_mining' => $formData['type_of_mining'],
                        'contract_term'  => $formData['contract_term'],
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
        $contract->metadata =
            [
                'project_title'  => $formData['project_title'],
                'language'       => $formData['language'],
                'country'        => $formData['country'],
                'resource'       => $formData['resource'],
                'signature_date' => $formData['signature_date'],
                'signature_year' => $formData['signature_year'],
                'type_of_mining' => $formData['type_of_mining'],
                'contract_term'  => $formData['contract_term'],
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
                    'filePath' => $this->getS3FileURL($newFileName),
                    'fileHash' => $newFileName,
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
            return $this->deleteFileFromS3($contract->filehash);
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
        return sha1(microtime() . $name);
    }

    /**
     * Get aws s3 file url
     *
     * @param string $fileName
     * @return string
     */
    protected function getS3FileURL($fileName = '')
    {
        return $this->storage->disk('s3')
                             ->getDriver()
                             ->getAdapter()
                             ->getClient()
                             ->getObjectUrl(env('AWS_BUCKET'), $fileName);
    }
}
