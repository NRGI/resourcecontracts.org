<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\Contract\Discussion\DiscussionService;
use App\Nrgi\Services\Contract\Page\PageService;
use Exception;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem;
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
     * @var PageService
     */
    protected $pages;
    /**
     * @var WordGenerator
     */
    protected $word;
    /**
     * @var DiscussionService
     */
    protected $discussion;
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param ContractRepositoryInterface $contract
     *
     * @param Guard                       $auth
     * @param Storage                     $storage
     * @param Filesystem                  $filesystem
     * @param CountryService              $countryService
     * @param Queue                       $queue
     * @param CommentService              $comment
     * @param DiscussionService           $discussion
     * @param DatabaseManager             $database
     * @param Log                         $logger
     * @param PageService                 $pages
     * @param WordGenerator               $word
     * @param DatabaseManager             $db
     */
    public function __construct (
        ContractRepositoryInterface $contract,
        Guard $auth,
        Storage $storage,
        Filesystem $filesystem,
        CountryService $countryService,
        Queue $queue,
        CommentService $comment,
        DiscussionService $discussion,
        DatabaseManager $database,
        Log $logger,
        WordGenerator $word,
        DatabaseManager $db,
        PageService $pages,
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
        $this->discussion     = $discussion;
        $this->db             = $db;
    }

    /**
     * Get Contract By ID
     *
     * @param $id
     *
     * @return Contract
     */
    public function find ($id)
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
     *
     * @return Contract
     */
    public function findWithPages ($id)
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
     * @param $status
     * @param $approved
     *
     * @return Contract
     */
    public function findWithTasks ($id, $status = null, $approved = null)
    {
        try {
            return $this->contract->findContractWithTasks($id, $status, $approved);
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
     * @param array $filter
     * @param int   $perPage
     *
     * @return Collection|null
     */
    public function getMTurkContracts (array $filter = [], $perPage = null)
    {
        try {
            return $this->contract->getMTurkContracts($filter, $perPage);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Get Contract With Annotations by ID
     *
     * @param      $id
     * @param bool $withRelation
     *
     * @return Contract
     */
    public function findWithAnnotations ($id, $withRelation = false)
    {
        try {
            $contract = $this->contract->findContractWithAnnotations($id);

            return $contract;
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Contract not found.', ['Contract ID' => $id]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Re-arrange annotation with parent-child relation
     *
     * @param Collection $annotations
     *
     * @return mixed
     */
    public function manageAnnotationRelation (Collection $annotations)
    {
        foreach ($annotations as $key => &$annotation) {
            $child = [];
            foreach ($annotations as $k => $anno) {
                if (isset($anno->annotation->parent) && $anno->annotation->parent == $annotation->id) {
                    $child[] = $anno;
                    unset($annotations[$k]);
                }
            }
            $annotation->childs = $child;
        }

        return $annotations;
    }

    /**
     * Upload Contract and save in database
     *
     * @param array $formData
     *
     * @return Contract|bool
     */
    public function saveContract (array $formData)
    {
        if ($file = $this->uploadContract($formData['file'])) {
            $metadata                        = $this->processMetadata($formData);
            $metadata['file_size']           = $file['size'];
            $metadata['open_contracting_id'] = getContractIdentifier(
                $metadata['category'][0],
                $metadata['country']['code']
            );
            $data                            = [
                'file'     => $file['name'],
                'filehash' => $file['hash'],
                'user_id'  => $this->auth->id(),
                'metadata' => $metadata,
            ];
            try {
                $contract = $this->contract->save($data);

                if (isset($metadata['is_supporting_document']) && $metadata['is_supporting_document'] == '1' && isset($formData['translated_from'])) {
                    $contract->syncSupportingContracts($formData['translated_from']);
                }
                $this->contract->updateOCID($contract);
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
     * Upload contract file
     *
     * @param UploadedFile $file
     *
     * @return array
     */
    protected function uploadContract (UploadedFile $file)
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
                    'hash' => getFileHash($file->getPathName()),
                ];
            }
        }

        return false;
    }

    /**
     * Process meta data
     *
     * @param $formData
     *
     * @return array
     */
    protected function processMetadata ($formData)
    {
        if (isset($formData['type_of_contract']) && in_array('Other', $formData['type_of_contract'])) {
            unset($formData['type_of_contract'][array_search('Other', $formData['type_of_contract'])]);
        }

        $formData['country']           = $this->countryService->getInfoByCode($formData['country']);
        $formData['resource']          = (!empty($formData['resource'])) ? $formData['resource'] : [];
        $formData['category']          = (!empty($formData['category'])) ? $formData['category'] : [];
        $formData['company']           = $this->removeKeys($formData['company']);
        $formData['type_of_contract']  = (isset($formData['type_of_contract'])) ? $this->removeKeys(
            $formData['type_of_contract']
        ) : [];
        $formData['concession']        = $this->removeKeys($formData['concession']);
        $formData['government_entity'] = $this->removeKeys($formData['government_entity']);
        $formData['show_pdf_text']     = isset($formData['show_pdf_text']) ? $formData['show_pdf_text'] : Contract::SHOW_PDF_TEXT;;
        $data = array_only(
            $formData,
            [
                "contract_name",
                "contract_identifier",
                "contract_note",
                "deal_number",
                "matrix_page",
                "language",
                "country",
                "resource",
                "government_entity",
                "type_of_contract",
                "signature_date",
                "document_type",
                "company",
                "concession",
                "project_title",
                "project_identifier",
                "source_url",
                "date_retrieval",
                "category",
                "signature_year",
                "disclosure_mode",
                "open_contracting_id",
                'is_supporting_document',
                'show_pdf_text',
                'pages_missing',
                'annexes_missing',
            ]
        );

        return trimArray($data);
    }

    /**
     * Remove Keys From Array
     *
     * @param $items
     *
     * @return array
     */
    protected function removeKeys ($items)
    {
        $i = [];

        foreach ($items as $items) {
            $i[] = $items;
        }

        return $i;
    }

    /**
     * Delete File from aws s3
     *
     * @param $file
     *
     * @return bool
     * @throws Exception
     */
    protected function deleteFileFromS3 ($file)
    {
        if (!$this->storage->disk('s3')->exists($file)) {
            throw new FileNotFoundException(sprintf(' % not found', $file));
        }

        return $this->storage->disk('s3')->delete($file);
    }

    /**
     * Update Contract
     *
     * @param       $contractID
     * @param array $formData
     *
     * @return bool
     */
    public function updateContract ($contractID, array $formData)
    {
        try {

            $contract     = $this->contract->findContract($contractID);
            $oldIsSupport = $contract->metadata->is_supporting_document;
            $newIsSupport = $formData['is_supporting_document'];
        } catch (Exception $e) {
            $this->logger->error('Contract not found', ['Contract ID' => $contractID]);

            return false;
        }
        $file_size             = $contract->metadata->file_size;
        $metadata              = $this->processMetadata($formData);
        $metadata['file_size'] = $file_size;
        if (isset($formData['file']) && $file = $this->uploadContract($formData['file'])) {
            $contract->file        = $file['name'];
            $contract->filehash    = $file['hash'];
            $metadata['file_size'] = $file['size'];
            $contract->pages()->delete();
            $this->deleteContractFileAndFolder($contract);
            $contract->save();
            $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $contract->id]);
            $this->logger->info('Contract pdf reuploaded', ['Contract ID' => $contractID]);

            $this->logger->activity('contract.log.pdfupdate', ['contract' => $contract->title], $contract->id);
        }
        $metadata['open_contracting_id'] = $this->getOpenContractingId($contract->metadata, $metadata);
        $contract->metadata              = $metadata;
        $contract->updated_by            = $this->auth->id();
        $contract->metadata_status       = Contract::STATUS_DRAFT;

        try {
            if (!$contract->save()) {
                return false;
            }

            $this->discussion->deleteContractDiscussion($contract->id, $formData['delete']);

            if (isset($metadata['is_supporting_document']) && $metadata['is_supporting_document'] == '1' && isset($formData['translated_from'])) {
                $contract->syncSupportingContracts($formData['translated_from']);
            }
            if (isset($metadata['is_supporting_document']) && $metadata['is_supporting_document'] == '0') {
                $this->contract->removeAsSupportingContract($contract->id);
            }
            if ($oldIsSupport != $newIsSupport) {
                $this->contract->updateOCID($contract);
            }

            $this->logger->info('Contract successfully updated', ['Contract ID' => $contractID]);
            $associatedContracts = (isset($formData['supporting_document']) && !empty($formData['supporting_document'])) ? $formData['supporting_document'] : [];
            if (!empty($associatedContracts)) {
                $this->updateOCIDOnEdit($associatedContracts);
            }
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
     * Get Updated Open Contracting ID
     *
     * @param $old_metadata
     * @param $new_metadata
     *
     * @return mixed
     */
    protected function getOpenContractingId ($old_metadata, $new_metadata)
    {
        $category = $old_metadata->category;

        if (!isset($new_metadata['category'][0])) {
            return isset($old_metadata->open_contracting_id) ? $old_metadata->open_contracting_id : '';
        }

        $old_identifier = isset($category[0]) ? $category[0] : '';
        $new_identifier = $new_metadata['category'][0];
        $old_iso        = $old_metadata->country->code;
        $new_iso        = $new_metadata['country']['code'];

        if (!isset($old_metadata->open_contracting_id) || $old_metadata->open_contracting_id == '') {
            return getContractIdentifier($new_metadata['category'][0], $new_metadata['country']['code']);
        }

        $opcid = $old_metadata->open_contracting_id;

        if ($old_identifier != $new_identifier) {
            $opcid = str_replace(
                mb_substr(strtoupper($old_identifier), 0, 2),
                mb_substr(strtoupper($new_identifier), 0, 2),
                $opcid
            );
        }

        if ($old_iso != $new_iso) {
            $opcid = str_replace(mb_substr(strtoupper($old_iso), 0, 2), mb_substr(strtoupper($new_iso), 0, 2), $opcid);
        }

        return $opcid;
    }

    /**
     * Delete Contract
     *
     * @param $id
     *
     * @return bool
     */
    public function deleteContract ($id)
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
            $this->db->beginTransaction();
            try {
                if ($this->updateOCIDOfSupportingContracts($id)) {
                    $this->logger->info('OCID updated for associated contracts.', ['Contract Id' => $id]);
                }
                if ($this->contract->deleteSupportingContract($id)) {
                    $this->logger->info('Parent deleted.', ['Contract Id' => $id]);
                }
            } catch (Exception $e) {
                $this->db->rollback();
                $this->logger->error($e->getMessage(), ['Contract Id' => $id]);
            }
            $this->db->commit();

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
     * Delete contract file and Folder in S#
     *
     * @param $contract
     *
     * @throws FileNotFoundException
     */
    protected function deleteContractFileAndFolder ($contract)
    {
        $this->storage->disk('s3')->deleteDirectory($contract->id);
    }

    /**
     * Get Contract Status by ContractID
     *
     * @param $contractID
     *
     * @return int
     */
    public function getStatus ($contractID)
    {
        $contract = $this->contract->findContract($contractID);

        if ($contract->pdf_process_status == Contract::PROCESSING_COMPLETE && !$this->pages->exists($contractID)) {
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
     *
     * @return int
     */
    public function savePageText ($id, $page, $text)
    {
        $path = public_path(self::UPLOAD_FOLDER . '/' . $id . '/' . $page . '.txt');

        return $this->filesystem->put($path, $text);
    }

    /**
     * Save Pdf Output Type
     *
     * @param $contractID
     * @param $textType
     *
     * @return Contract|bool
     */
    public function saveTextType ($contractID, $textType)
    {
        $contract           = $this->contract->findContract($contractID);
        $contract->textType = $textType;
        if ($contract->save()) {
            return $contract;
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
     *
     * @return bool
     */
    public function updateStatusWithComment ($contract_id, $status, $message, $type)
    {
        $this->database->beginTransaction();

        if ($this->updateStatus($contract_id, $status, $type) && $this->comment->save(
                $contract_id,
                $message,
                $type,
                $status
            )
        ) {
            $this->database->commit();

            return true;
        }
        $this->database->rollback();

        return false;
    }

    /**
     * Update Contract status
     *
     * @param $id
     * @param $status
     * @param $type
     *
     * @return bool
     */
    public function updateStatus ($id, $status, $type)
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
                    'New Status'  => $status,
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * Check for unique file hash
     *
     * @param $file
     *
     * @return bool|Contract
     */
    public function getContractIfFileHashExist ($filehash)
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
    public function getList ($id = null)
    {
        $supportingContract = $this->getSupportingContractsId();
        if ($id != null) {
            array_push($supportingContract, $id);
        }

        $contracts = $this->contract->getContractsWithoutSupporting($supportingContract);
        $data      = [];
        foreach ($contracts as $k => $v) {
            $data[$v['id']] = $v['metadata']->contract_name;
        }

        return $data;
    }

    /**
     * Move File on S3
     *
     * @param $file
     * @param $moveTo
     *
     * @return bool
     */
    function moveS3File ($file, $moveTo)
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
     *
     * @return string
     */
    public function updateWordFile ($contract_id)
    {
        $text = [];

        if ($contract = $this->contract->findContractWithPages($contract_id)) {
            foreach ($contract->pages->sortBy('page_no') as $key => $page) {
                $text [] = $page->text;
            }
        }

        $filename     = explode('.', $contract->file);
        $filename     = $filename[0];
        $wordFileName = $filename . '.txt';

        try {
            $file_path = $this->word->create($text, $wordFileName);
            $this->storage->disk('s3')->put(
                sprintf('%s/%s', $contract_id, $wordFileName),
                $this->filesystem->get($file_path)
            );
            $this->filesystem->delete($file_path);
            $this->logger->info('Word file updated', ['Contract id' => $contract_id]);

            return true;
        } catch (Exception $e) {
            $this->logger->error(
                'Word file could not  be update : ' . $e->getMessage(),
                ['Contract id' => $contract_id]
            );

            return false;
        }
    }

    /**
     * Get Contract with process completed
     *
     * @return Collection|null
     */
    public function getProcessCompleted ()
    {
        try {
            return $this->contract->getContractWithPdfProcessingStatus(Contract::PROCESSING_COMPLETE);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }

    /**
     * Get the supporting Contracts
     *
     * @param $id
     *
     * @return array
     */
    public function getSupportingDocuments ($id)
    {
        $contractsId = $this->getAssociatedContractsId($id);
        if (empty($contractsId)) {
            return [];
        }
        $contracts = $this->getcontracts($contractsId);

        return $contracts;
    }

    /**
     * Get the contract's id and name
     *
     * @param $id
     *
     * @return array
     */
    public function getcontracts ($id)
    {
        $contracts = $this->contract->getSupportingContracts((array) $id);

        return $contracts;
    }

    /**
     * updates filename of contract
     *
     * @param $contract
     *
     * @return bool
     */
    public function updateFileName ($contract)
    {
        $newFileName    = sprintf("%s-%s", $contract->id, $contract->Slug);
        $contract->file = "$newFileName.pdf";
        if ($contract->save()) {
            return $contract;
        }

        return false;
    }

    /**
     * Get Contract Text from AWS S3
     *
     * @param $contract_id
     * @param $file
     *
     * @return null|string
     */
    public function getTextFromS3 ($contract_id, $file)
    {
        $filename = explode('.', $file);
        $filename = $filename[0];

        try {
            return $this->storage->disk('s3')->get($contract_id . '/' . $filename . '.txt');
        } catch (Exception $e) {
            $this->logger->error('File not found:' . $e->getMessage());

            return null;
        }
    }

    /**
     * Unpublish Contract
     *
     * @param $id
     *
     * @return bool
     */
    public function unPublishContract ($id)
    {
        try {
            $contract = $this->contract->findContract($id);
        } catch (Exception $e) {
            $this->logger->error('Contract not found.', ['Contract ID' => $id]);

            return false;
        }
        if ($this->queue->push(
            'App\Nrgi\Services\Queue\DeleteToElasticSearchQueue',
            ['contract_id' => $id],
            'elastic_search'
        )
        ) {
            $this->logger->info('Contract successfully deleted.', ['Contract Id' => $id]);
            $this->logger->activity('contract.log.unpublish', ['contract' => $contract->title], $id);
            $contract->metadata_status = Contract::STATUS_DRAFT;
            $contract->text_status     = Contract::STATUS_DRAFT;
            $contract->save();

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function parentContracts ()
    {
        $contracts = $this->contract->getParentContracts();

        return $contracts;
    }

    /**
     * Get Associated Contracts
     *
     * @param $contract
     *
     * @return array
     */
    public function getAssociatedContracts ($contract)
    {
        $associatedContracts = [];
        $parent              = $contract->getParentContract();
        $contract_id         = '';
        if ($parent) {
            $parent = $this->find($parent);
            if ($parent) {
                $associatedContracts[] = [
                    'parent'   => true,
                    'contract' => ['id' => $parent->id, 'contract_name' => $parent->title],
                ];
                $contract_id           = $parent->id;
            }
        } else {
            $contract_id = $contract->id;
        }
        $aContracts = !empty($contract_id) ? $this->getSupportingDocuments($contract_id) : [];
        foreach ($aContracts as $key => $aContract) {
            if ($contract->id != $aContract['id']) {
                $associatedContracts[] = ['parent' => false, 'contract' => $aContract];
            }
        }

        return $associatedContracts;
    }


    /**
     * Get Company Name
     *
     * @return array
     */
    public function getCompanyNames ()
    {
        $companyName  = [];
        $company_name = $this->contract->getCompanyName();

        foreach ($company_name as $name => $val) {
            $companyName[] = $val['company_name'];
        }

        return ($companyName);
    }

    /**
     * Return array of supporting documents
     *
     * @return array
     */
    private function getSupportingContractsId ()
    {
        $contractsId = [];
        $supportings = $this->contract->getAllSupportingContracts();

        foreach ($supportings as $supporting) {
            array_push($contractsId, $supporting["supporting"]);
        }

        return $contractsId;
    }

    /**
     * Update OCID of Associated on delete of parent contract
     *
     * @param $id
     *
     * @return bool
     */
    private function updateOCIDOfSupportingContracts ($id)
    {
        $contracts = $this->getAssociatedContractsId($id);
        if (empty($contracts)) {
            return false;
        }
        foreach ($contracts as $associatedId) {
            $contract = $this->contract->findContract($associatedId);
            try {
                $ocid                               = getContractIdentifier(
                    $contract->metadata->category[0],
                    $contract->metadata->country->code
                );
                $metadata                           = json_decode(json_encode($contract->metadata), true);
                $metadata['open_contracting_id']    = $ocid;
                $metadata['is_supporting_document'] = 0;
                $contract->metadata                 = $metadata;
                $contract->save();
            } catch (Exception $e) {
                $this->logger->error(
                    sprintf('Associated contract OCID could not be updated. %s', $e->getMessage()),
                    ['Contract ID' => $associatedId]
                );
            }

        }

        return true;
    }

    /**
     * Get associated contracts id
     *
     * @param $id
     *
     * @return array
     */
    private function getAssociatedContractsId ($id)
    {
        $supportingContracts = $this->contract->getSupportingDocument($id);
        if (empty($supportingContracts)) {
            return [];
        }
        $contractsId = [];
        foreach ($supportingContracts as $contractId) {
            array_push($contractsId, $contractId['supporting_contract_id']);
        }

        return $contractsId;
    }

    /**
     * Update OCID on edit
     *
     * @param $associatedContracts
     *
     * @return bool
     */
    private function updateOCIDOnEdit ($associatedContracts)
    {
        foreach ($associatedContracts as $id) {
            try {
                $contract = $this->contract->findContract($id);
                $this->contract->updateOCID($contract);
            } catch (Exception $e) {
                $this->logger->error(
                    sprintf('Associated contract OCID could not be updated. %s', $e->getMessage()),
                    ['Contract ID' => $id]
                );
            }
        }

        return true;
    }

    /**
     * Rename contract for given contracts
     *
     * @param array $filter
     *
     * @return array
     */
    public function getContractRenameList (array $filter)
    {
        $report    = [];
        $contracts = $this->contract->getAll($filter, $limit = null);

        if (empty($contracts)) {
            return [];
        }

        try {
            $cn = $ln = $tc = $sy = $nn = $a = null;

            foreach ($contracts as $contract) {
                $con                          = $contract->metadata;
                $report[$contract->id]['old'] = $contract->metadata->contract_name;
                $report[$contract->id]['id']  = $contract->id;

                if (isset($con->company)) {
                    $cn = $this->getCompany($con->company);
                }

                if (isset($con->concession)) {
                    $ln = $this->getLicense($con->concession);
                }

                if (!empty($con->type_of_contract)) {
                    $tc = $this->getTypeOfContract($con->type_of_contract);
                } else {
                    $tc = trim($con->document_type);
                }
                if (!empty($con->signature_year)) {
                    $sy = trim($con->signature_year);
                }

                if (!empty($contract->id)) {
                    $nn = formatIdRorName($contract->id);
                }

                $a                            = [$cn, $ln, $tc, $sy, $nn];
                $report[$contract->id]['new'] = join(', ', array_filter($a));
            }

            return $report;

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
    }

    /**
     * get license for contract
     *
     * @param $license
     *
     * @return array
     */
    public function getLicense ($licenses)
    {
        $license = [];
        foreach ($licenses as $l) {

            if (!empty($l->license_name)) {

                array_push($license, trim($l->license_name));
            } else {
                array_push($license, trim($l->license_identifier));
            }
        }
        $license = join('-', array_filter($license));

        return $license;
    }

    /**
     * get type of contract for given contracts
     *
     * @param $typeOfContract
     *
     * @return array
     */
    public function getTypeOfContract ($typeOfContract)
    {
        $tocs = [];
        foreach ($typeOfContract as $toc) {
            if (!empty($toc)) {
                array_push($tocs, trim($toc));
            }
        }
        $tocs = join('-', array_filter($tocs));

        return $tocs;
    }

    /**
     * get companyName for given contract
     *
     * @param $companyName
     *
     * @return array
     */
    public function getCompany ($companyName)
    {
        $cn = [];
        foreach ($companyName as $comp) {

            if (!empty($comp->name)) {

                array_push($cn, trim($comp->name));
            }
        }
        $cn = join('-', array_filter($cn));

        return $cn;
    }

    /**
     * Update Contract Name
     *
     * @param $contracts
     *
     * @return boolean
     */
    public function renameContracts ($contracts)
    {
        foreach ($contracts as $con) {
            try {
                $contract                  = $this->contract->findContract($con->id);
                $metadata                  = json_decode(json_encode($contract->metadata), true);
                $metadata['contract_name'] = $con->new;
                $contract->metadata        = $metadata;
                $contract->save();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return true;
    }
}
