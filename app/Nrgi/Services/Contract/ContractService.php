<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\Contract\Pages\PagesService;
use Exception;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\Queue;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ContractService
 * @package App\Nrgi\Services\Contract
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
     * @var CountryService
     */
    protected $countryService;
    /**
     * @var Queue
     */
    protected $queue;
    /**
     * @var Log
     */
    protected $logger;

    /**
     * @var PagesService
     */
    protected $pages;
    /**
     * @var WordGenerator
     */
    protected $word;

    /**
     * @param ContractRepositoryInterface $contract
     *
     * @param Guard                       $auth
     * @param Storage                     $storage
     * @param Filesystem                  $filesystem
     * @param CountryService              $countryService
     * @param Queue                       $queue
     * @param CommentService              $comment
     * @param DatabaseManager             $database
     * @param Log                         $logger
     * @param PagesService                $pages
     * @param WordGenerator               $word
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        Guard $auth,
        Storage $storage,
        Filesystem $filesystem,
        CountryService $countryService,
        Queue $queue,
        CommentService $comment,
        DatabaseManager $database,
        Log $logger,
        PagesService $pages,
        WordGenerator $word
    ) {
        $this->contract       = $contract;
        $this->auth           = $auth;
        $this->storage        = $storage;
        $this->filesystem     = $filesystem;
        $this->countryService = $countryService;
        $this->queue          = $queue;
        $this->database       = $database;
        $this->comment        = $comment;
        $this->logger         = $logger;
        $this->pages          = $pages;
        $this->word           = $word;
    }

    /**
     * Get Contract By ID
     *
     * @param $id
     * @return Contract
     */
    public function find($id)
    {
        try {
            return $this->contract->findContract($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Contract not found.', ['Contract ID' => $id]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Get Contract With Pages by ID
     *
     * @param $id
     * @return Contract
     */
    public function findWithPages($id)
    {
        try {
            return $this->contract->findContractWithPages($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Contract not found.', ['Contract ID' => $id]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Get Contract With Tasks
     *
     * @param $id
     * @return Contract
     */
    public function findWithTasks($id)
    {
        try {
            return $this->contract->findContractWithTasks($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Contract not found.', ['Contract ID' => $id]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Get Contracts Having MTurk tasks
     *
     * @return Collection|null
     */
    public function getMTurkContracts()
    {
        try {
            return $this->contract->getMTurkContracts();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Get Contract With Annotations by ID
     *
     * @param $id
     * @return Contract
     */
    public function findWithAnnotations($id)
    {
        try {
            return $this->contract->findContractWithAnnotations($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Contract not found.', ['Contract ID' => $id]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Upload Contract and save in database
     *
     * @param array $formData
     * @return Contract|bool
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
                'metadata' => $metadata,
            ];
            try {
                $contract = $this->contract->save($data);
                $this->logger->activity('contract.log.save', ['contract' => $contract->title], $contract->id);

                $this->logger->info(
                    'Contract successfully created.',
                    ['Contract Title' => $contract->title]
                );
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                $this->deleteFileFromS3($file['name']);

                return false;
            }

            if ($contract) {
                $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $contract->id]);
            }

            return $contract;
        }

        return false;
    }

    /**
     * Process meta data
     *
     * @param $formData
     * @return array
     */
    protected function processMetadata($formData)
    {
        $formData['signature_year'] = (!empty($formData['signature_date'])) ? date(
            'Y',
            strtotime(
                $formData['signature_date']
            )
        ) : '';

        $formData['country']  = $this->countryService->getInfoByCode($formData['country']);
        $formData['resource'] = (!empty($formData['resource'])) ? $formData['resource'] : [];
        $formData['category'] = (!empty($formData['category'])) ? $formData['category'] : [];

        return array_only(
            $formData,
            [
                "contract_name",
                "contract_identifier",
                "language",
                "country",
                "resource",
                "government_entity",
                "government_identifier",
                "type_of_contract",
                "signature_date",
                "document_type",
                "translation_from_original",
                "translation_parent",
                "company",
                "concession",
                "project_title",
                "project_identifier",
                "source_url",
                "date_retrieval",
                "category",
                "signature_year",
                "participation_share",
                "disclosure_mode"
            ]
        );
    }

    /**
     * Update Contract
     *
     * @param array $formData
     * @return bool
     */
    public function updateContract($contractID, array $formData)
    {
        try {
            $contract = $this->contract->findContract($contractID);
        } catch (Exception $e) {
            $this->logger->error('Contract not found', ['Contract ID' => $contractID]);

            return false;
        }

        $file_size                 = $contract->metadata->file_size;
        $metadata                  = $this->processMetadata($formData);
        $metadata['file_size']     = $file_size;
        $contract->metadata        = $metadata;
        $contract->updated_by      = $this->auth->user()->id;
        $contract->metadata_status = Contract::STATUS_DRAFT;

        try {
            $contract->save();
            $this->logger->info('Contract successfully updated', ['Contract ID' => $contractID]);

            $this->logger->activity('contract.log.update', ['contract' => $contract->title], $contract->id);

            return true;
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Contract could not be updated. %s', $e->getMessage()),
                ['Contract ID' => $contractID]
            );

            return false;
        }
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
            $newFileName = sprintf("%s.%s", sha1($fileName . time()), $file_type);
            try {
                $data = $this->storage->disk('s3')->put(
                    $newFileName,
                    $this->filesystem->get($file)
                );
            } catch (Exception $e) {
                $this->logger->error(sprintf('File could not be uploaded : %s', $e->getMessage()));

                return false;
            }

            if ($data) {
                return [
                    'name' => $newFileName,
                    'size' => $file->getSize(),
                    'hash' => getFileHash($file->getPathName())
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
    public function deleteContract($id)
    {
        try {
            $contract = $this->contract->findContract($id);
        } catch (Exception $e) {
            $this->logger->error('Contract not found.', ['Contract ID' => $id]);

            return false;
        }

        if ($this->contract->delete($contract->id)) {
            $this->logger->info('Contract successfully deleted.', ['Contract Id' => $id]);
            $this->logger->activity('contract.log.delete', ['contract' => $contract->title], null);
            $this->queue->push(
                'App\Nrgi\Services\Queue\DeleteToElasticSearchQueue',
                ['contract_id' => $id],
                'elastic_search'
            );
            try {
                $this->deleteContractFileAndFolder($contract);
                $this->logger->info(
                    'contract file and folder deleted',
                    ['Contract Id' => $id, 'file' => $contract->file]
                );

                return true;
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), ['Contract Id' => $id, 'file' => $contract->file]);

                return false;
            }
        }

        $this->logger->error('Contract could not be deleted', ['Contract Id' => $id]);

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
            throw new FileNotFoundException(sprintf(' % not found', $file));
        }

        return $this->storage->disk('s3')->delete($file);
    }

    /**
     * Get Contract Status by ContractID
     *
     * @param $contractId
     * @return int
     */
    public function getStatus($contractID)
    {
        $contract = $this->contract->findContract($contractID);

        if ($contract->pdf_process_status == Contract::PROCESSING_COMPLETE and !$this->pages->exists($contractID)) {
            return Contract::PROCESSING_FAILED;
        }

        return $contract->pdf_process_status;
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

        return $this->filesystem->put($path, $text);
    }

    /**
     * Save Pdf Output Type
     *
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

    /**
     * Update Contract status
     *
     * @param $id
     * @param $status
     * @param $type
     * @return bool
     */
    public function updateStatus($id, $status, $type)
    {
        try {
            $contract = $this->contract->findContract($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Contract not found', ['contract id' => $id]);

            return false;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        if ($contract->isEditableStatus($status)) {
            $status_key            = sprintf('%s_status', $type);
            $old_status            = $contract->$status_key;
            $contract->$status_key = $status;
            $contract->save();

            if ($status == Contract::STATUS_PUBLISHED) {
                $this->queue->push(
                    'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                    ['contract_id' => $id, 'type' => $type],
                    'elastic_search'
                );
            }
            $this->logger->activity(
                'contract.log.status',
                ['type' => $type, 'old_status' => $old_status, 'new_status' => $status],
                $contract->id
            );
            $this->logger->info(
                "Contract status updated",
                [
                    'Contract id' => $contract->id,
                    'Status type' => $type,
                    'Old status'  => $old_status,
                    'New Status'  => $status
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * Update status with message
     *
     * @param $contract_id
     * @param $status
     * @param $message
     * @param $type
     * @return bool
     */
    public function updateStatusWithComment($contract_id, $status, $message, $type)
    {
        $this->database->beginTransaction();

        if ($this->updateStatus($contract_id, $status, $type) and $this->comment->save($contract_id, $message, $type, $status)) {
            $this->database->commit();

            return true;
        }
        $this->database->rollback();

        return false;
    }

    /**
     * Check for unique file hash
     *
     * @param $file
     * @return bool|Contract
     */
    public function getContractIfFileHashExist($filehash)
    {
        try {
            if ($file = $this->contract->getContractByFileHash($filehash)) {
                return $file;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * Get contract list
     *
     * @return array
     */
    public function getList()
    {
        $contracts = $this->contract->getList()->toArray();
        $data      = [];
        foreach ($contracts as $k => $v) {
            $data[$v['id']] = $v['metadata']->contract_name;
        }

        return $data;
    }

    /**
     * Delete contract file and Folder in S#
     *
     * @param $contract
     * @throws FileNotFoundException
     */
    protected function deleteContractFileAndFolder($contract)
    {
        $this->storage->disk('s3')->deleteDirectory($contract->id);
    }

    /**
     * Move File on S3
     *
     * @param $file
     * @param $moveTo
     * @return bool
     */
    function moveS3File($file, $moveTo)
    {
        try {
            $this->storage->disk('s3')->move($file, $moveTo);
            $this->logger->info(sprintf('%s move to %s', $file, $moveTo));

            return true;
        } catch (Exception $e) {
            $this->logger->error('Could not move pdf file : ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Update Contract word file
     *
     * @param $contract_id
     * @return string
     */
    public function updateWordFile($contract_id)
    {
        $text = [];

        if ($contract = $this->contract->findContractWithPages($contract_id)) {
            foreach ($contract->pages->sortBy('page_no') as $key => $page) {
                $text [] = $page->text;
            }
        }

        list($filename, $ext) = explode('.', $contract->file);
        $wordFileName = $filename . '.docx';

        try {
            $file_path = $this->word->create($text, $wordFileName);
            $this->storage->disk('s3')->put(sprintf('%s/%s', $contract_id, $wordFileName), $this->filesystem->get($file_path));
            $this->filesystem->delete($file_path);
            $this->logger->info('Word file updated', ['Contract id' => $contract_id]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Word file could not  be update : ' . $e->getMessage(), ['Contract id' => $contract_id]);

            return false;
        }
    }

    /**
     * Get Contract with process completed
     *
     * @return Collection|null
     */
    public function getProcessCompleted()
    {
        try {
            return $this->contract->getContractWithPdfProcessingStatus(Contract::PROCESSING_COMPLETE);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }
}
