<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\SupportingContract\SupportingContract;
use App\Nrgi\Mturk\Services\ActivityService;
use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\Contract\Discussion\DiscussionService;
use App\Nrgi\Services\Contract\Page\PageService;
use App\Nrgi\Services\Language\LanguageService;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\Lang;
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
     * @var ActivityService
     */
    public $activityService;
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
     * @var AnnotationRepositoryInterface
     */
    protected $annotation;
    /**
     * @var LanguageService
     */
    protected $lang;
    /**
     * @var Carbon
     */
    protected $carbon;

    /**
     * @param ContractRepositoryInterface   $contract
     *
     * @param Guard                         $auth
     * @param Storage                       $storage
     * @param Filesystem                    $filesystem
     * @param CountryService                $countryService
     * @param Queue                         $queue
     * @param CommentService                $comment
     * @param DiscussionService             $discussion
     * @param DatabaseManager               $database
     * @param Log                           $logger
     * @param DatabaseManager               $db
     * @param PageService                   $pages
     * @param WordGenerator                 $word
     * @param ActivityService               $activityService
     * @param AnnotationRepositoryInterface $annotation
     * @param LanguageService               $lang
     * @param Carbon                        $carbon
     */
    public function __construct(
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
        DatabaseManager $db,
        PageService $pages,
        WordGenerator $word,
        ActivityService $activityService,
        AnnotationRepositoryInterface $annotation,
        LanguageService $lang,
        Carbon $carbon
    ) {
        $this->contract        = $contract;
        $this->auth            = $auth;
        $this->storage         = $storage;
        $this->filesystem      = $filesystem;
        $this->countryService  = $countryService;
        $this->queue           = $queue;
        $this->database        = $database;
        $this->comment         = $comment;
        $this->logger          = $logger;
        $this->pages           = $pages;
        $this->word            = $word;
        $this->discussion      = $discussion;
        $this->db              = $db;
        $this->activityService = $activityService;
        $this->annotation      = $annotation;
        $this->lang            = $lang;
        $this->carbon          = $carbon;
    }

    /**
     * Get Contract By ID
     *
     * @param $id
     *
     * @return Contract
     */
    public function find($id)
    {
        try {
            return $this->contract->findContract($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Find : Contract not found.', ['Contract ID' => $id]);
        } catch (Exception $e) {
            $this->logger->error('Find : '.$e->getMessage());
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
    public function findWithPages($id)
    {
        try {
            return $this->contract->findContractWithPages($id);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('findWithPages : Contract not found.', ['Contract ID' => $id]);
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
    public function findWithTasks($id, $status = null, $approved = null)
    {
        try {
            return $this->contract->findContractWithTasks($id, $status, $approved);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('findWithTasks : Contract not found.', ['Contract ID' => $id]);
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
    public function getMTurkContracts(array $filter = [], $perPage = null)
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
     *
     * @return Contract
     *
     */
    public function findWithAnnotations($id)
    {
        try {
            $contract = $this->contract->findContractWithAnnotations($id);

            return $contract;
        } catch (ModelNotFoundException $e) {
            $this->logger->error('findWithAnnotations : Contract not found.', ['Contract ID' => $id]);
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
    public function manageAnnotationRelation(Collection $annotations)
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
    public function saveContract(array $formData)
    {
        if ($file = $this->uploadContract($formData['file'])) {
            try {
                $metadata                        = $this->processMetadata($formData);
                $metadata['file_size']           = $file['size'];
                $metadata['open_contracting_id'] = $this->contract->generateOCID();
                $data                            = [
                    'file'     => $file['name'],
                    'filehash' => $file['hash'],
                    'user_id'  => $this->auth->id(),
                    'metadata' => $metadata,
                ];
                $contract                        = $this->contract->save($data);

                if (isset($metadata['is_supporting_document']) && $metadata['is_supporting_document'] == '1' && isset($formData['translated_from'])) {
                    $contract->syncSupportingContracts($formData['translated_from']);
                }
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
     * update translated contract
     *
     * @param       $contractID
     * @param array $formData
     *
     * @return bool
     */
    public function updateContractTrans($contractID, array $formData)
    {
        $data = array_only(
            $formData,
            [
                'contract_name',
                'company',
                'project_title',
                'project_identifier',
                'concession',
                'disclosure_mode_text',
                'contract_note',
            ]
        );

        if (isset($formData['disclosure_mode'])) {
            $data['disclosure_mode'] = $formData['disclosure_mode'];
        }

        $contract                           = $this->contract->findContract($contractID);
        $metadata_trans                     = json_decode(json_encode($contract->metadata_trans), true);
        $metadata_trans[$formData['trans']] = $data;
        $contract->metadata_trans           = $metadata_trans;
        $contract->updated_by               = $this->auth->id();
        $contract->metadata_status          = Contract::STATUS_DRAFT;
        $contract->save();

        return true;
    }

    /**
     * Update Contract
     *
     * @param       $contractID
     * @param array $formData
     *
     * @return bool
     */
    public function updateContract($contractID, array $formData)
    {
        if (isset($formData['trans']) && $formData['trans'] != $this->lang->defaultLang(
            ) && $this->lang->isValidTranslationLang($formData['trans'])
        ) {
            return $this->updateContractTrans($contractID, $formData);
        }

        try {
            $contract = $this->contract->findContract($contractID);
        } catch (Exception $e) {
            $this->logger->error('updateContract : '.$e->getMessage(), ['Contract ID' => $contractID]);

            return false;
        }
        $metadata                        = $this->processMetadata($formData);
        $metadata['file_size']           = $contract->metadata->file_size;
        $metadata['open_contracting_id'] = $contract->metadata->open_contracting_id;
        if (isset($formData['file']) && $file = $this->uploadContract($formData['file'])) {
            $contract->file        = $file['name'];
            $contract->filehash    = $file['hash'];
            $metadata['file_size'] = $file['size'];
            $contract->pages()->delete();
            $this->deleteContractFileAndFolder($contract);
            $contract->save();
            $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $contract->id]);
            $this->logger->info('Contract pdf re-uploaded', ['Contract ID' => $contractID]);
            $this->logger->activity('contract.log.pdfupdate', ['contract' => $contract->title], $contract->id);
        }
        $contract->metadata        = $metadata;
        $contract->updated_by      = $this->auth->id();
        $contract->metadata_status = Contract::STATUS_DRAFT;

        $supporting_contract_model = new SupportingContract();
        $supporting_contract       = $supporting_contract_model->where('supporting_contract_id', '=', $contractID)->get()->first();

        if (!empty($supporting_contract)
            && $supporting_contract->supporting_contract_id == $contractID
            && $supporting_contract->contract_id != (int)$formData['translated_from'])
        {
            $this->queue->push(
                'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                ['contract_id' => $supporting_contract->contract_id, 'type' => 'metadata'],
                'elastic_search'
            );
        }

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
     * Delete Contract
     *
     * @param $id
     *
     * @return bool
     */
    public function deleteContract($id)
    {
        try {
            $contract = $this->contract->findContract($id);
        } catch (Exception $e) {
            $this->logger->error('DeleteContract : Contract not found.', ['Contract ID' => $id]);

            return false;
        }

        if ($this->contract->delete($contract->id)) {
            $this->logger->info('Contract successfully deleted.', ['Contract Id' => $id]);
            $this->logger->activity(
                'contract.log.delete',
                ['contract' => $contract->title, 'id' => $contract->id],
                null
            );
            $this->queue->push(
                'App\Nrgi\Services\Queue\DeleteToElasticSearchQueue',
                ['contract_id' => $id],
                'elastic_search'
            );
            $this->db->beginTransaction();
            try {
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
     * Get Contract Status by ContractID
     *
     * @param $contractID
     *
     * @return int
     */
    public function getStatus($contractID)
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
    public function savePageText($id, $page, $text)
    {
        $path = public_path(self::UPLOAD_FOLDER.'/'.$id.'/'.$page.'.txt');

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
     * Update status with message
     *
     * @param $contract_id
     * @param $status
     * @param $message
     * @param $type
     *
     * @return bool
     */
    public function updateStatusWithComment($contract_id, $status, $message, $type)
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
    public function updateStatus($id, $status, $type)
    {
        try {
            $contract = $this->contract->findContract($id);

        } catch (ModelNotFoundException $e) {
            $this->logger->error('Update Status : Contract not found', ['contract id' => $id]);

            return false;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        if ($contract->isEditableStatus($status)) {
            $status_key            = sprintf('%s_status', $type);
            $old_status            = $contract->$status_key;
            $contract->$status_key = $status;
            if ($status == Contract::STATUS_UNPUBLISHED) {
                $contract->$status_key = ($old_status == Contract::STATUS_PUBLISHED) ? 'completed' : $old_status;
            }

            $contract->save();

            if ($status == Contract::STATUS_PUBLISHED) {
                $this->queue->push(
                    'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                    ['contract_id' => $id, 'type' => $type],
                    'elastic_search'
                );
            }

            if ($status == Contract::STATUS_UNPUBLISHED) {
                $this->logger->info("Contract status updated", ['type' => $type]);
                $this->queue->push(
                    'App\Nrgi\Services\Queue\DeleteElementQueue',
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
     * @param $filehash
     *
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
     * @param null $id
     *
     * @return array
     */
    public function getList($id = null)
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
    function moveS3File($file, $moveTo)
    {
        try {
            $this->storage->disk('s3')->move($file, $moveTo);
            $this->logger->info(sprintf('%s move to %s', $file, $moveTo));

            return true;
        } catch (Exception $e) {
            $this->logger->error('Could not move pdf file : '.$e->getMessage());

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
    public function updateWordFile($contract_id)
    {
        $text = [];

        if ($contract = $this->contract->findContractWithPages($contract_id)) {
            foreach ($contract->pages->sortBy('page_no') as $key => $page) {
                $text [] = $page->text;
            }
        }

        $filename     = explode('.', $contract->file);
        $filename     = $filename[0];
        $wordFileName = $filename.'.txt';

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
                'Word file could not  be update : '.$e->getMessage(),
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
    public function getProcessCompleted()
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
    public function getSupportingDocuments($id)
    {
        $contractsId = $this->getAssociatedContractsId($id);
        if (empty($contractsId)) {
            return [];
        }
        $contracts = $this->getcontracts($contractsId);

        return $contracts;
    }

    public function getParentDocument($child_contract_id)
    {
        $parentContract = $this->contract->getParentDocument($child_contract_id);

        if (empty($parentContract)) {
            return [];
        }

        $parentContract = $parentContract->toArray();

        try {
            $parentContract = $this->contract->findContract($parentContract['contract_id']);
        } catch (ModelNotFoundException $e) {
            $this->logger->error('Find : Parent contract not found.', ['Contract ID' => $parentContract['contract_id']]);
            return [];
        }

        if (empty($parentContract)) {
            return [];
        }

        return ['id' => $parentContract->id, 'open_contracting_id' => $parentContract->metadata->open_contracting_id];
    }

    /**
     * Get the contract's id and name
     *
     * @param $id
     *
     * @return array
     */
    public function getcontracts($id)
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
    public function updateFileName($contract)
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
    public function getTextFromS3($contract_id, $file)
    {
        $filename = explode('.', $file);
        $filename = $filename[0];

        try {
            return $this->storage->disk('s3')->get($contract_id.'/'.$filename.'.txt');
        } catch (Exception $e) {
            $this->logger->error('File not found:'.$e->getMessage());

            return null;
        }
    }

    /**
     * Unpublish Contract
     *
     * @param $id
     * @param $elementStatus
     *
     * @return bool
     */
    public function unPublishContract($id, $elementStatus)
    {
        try {
            $contract = $this->contract->findContract($id);
        } catch (Exception $e) {
            $this->logger->error('Unpublish Contract : Contract not found.', ['Contract ID' => $id]);

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

            $this->logger->activity(
                'contract.log.status',
                [
                    'type'       => 'metadata',
                    'old_status' => $elementStatus['metadata_status'],
                    'new_status' => 'unpublished',
                ],
                $id
            );
            $this->logger->activity(
                'contract.log.status',
                ['type' => 'text', 'old_status' => $elementStatus['text_status'], 'new_status' => 'unpublished'],
                $id
            );
            $this->logger->activity(
                'contract.log.status',
                [
                    'type'       => 'annotation',
                    'old_status' => $elementStatus['annotation_status'],
                    'new_status' => 'unpublished',
                ],
                $id
            );

            $contract->metadata_status = ($elementStatus['metadata_status'] == "published") ? Contract::STATUS_COMPLETED : $elementStatus['metadata_status'];
            $contract->text_status     = ($elementStatus['text_status'] == "published") ? Contract::STATUS_COMPLETED : $elementStatus['text_status'];
            $contract->save();

            $annStatus = ($elementStatus['annotation_status'] == "published") ? Annotation::COMPLETED : $elementStatus['annotation_status'];
            $this->annotation->updateStatus($annStatus, $id);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function parentContracts()
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
    public function getAssociatedContracts($contract)
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
    public function getCompanyNames()
    {
        $companyName  = [];
        $company_name = $this->contract->getCompanyName();

        foreach ($company_name as $name => $val) {
            $companyName[] = $val['company_name'];
        }

        return ($companyName);
    }

    /**
     * Rename contract for given contracts
     *
     * @param array $filter
     *
     * @return array
     */
    public function getContractRenameList(array $filter)
    {
        $report    = [];
        $contracts = $this->contract->getAll($filter, $limit = null);
        if (empty($contracts)) {
            return [];
        }

        try {

            foreach ($contracts as $contract) {
                $con                          = $contract->metadata;
                $report[$contract->id]['old'] = $contract->metadata->contract_name;
                $report[$contract->id]['id']  = $contract->id;
                $report[$contract->id]['new'] = $this->refineContract($con, $contract->id);
            }

            return $report;

        } catch (Exception $e) {
            $this->logger->error('getContractRenameList :'.$e->getMessage());

            return [];
        }
    }

    /**
     * Get license for contract
     *
     * @param $licenses
     *
     * @return array
     *
     */
    public function getLicense($licenses)
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
     * @param        $typeOfContract
     *
     * @param string $lang
     *
     * @return string
     */
    public function getTypeOfContract($typeOfContract, $lang = 'en')
    {
        $trans_toc = [];
        foreach ($typeOfContract as $type) {
            $type = trim($type);

            if (empty($type)) {
                continue;
            }

            if ($this->lang->defaultLang() == $lang) {
                $abb_toc = config('abbreviation_toc');
                $type    = isset($abb_toc[$type]) ? $abb_toc[$type] : $type;
            } elseif (Lang::has(sprintf('codelist/contract_type.%s', $type), $lang)) {
                $type = trans('codelist/contract_type.'.$type, [], null, $lang);
            }

            $trans_toc[] = $type;
        }

        return join(', ', $trans_toc);
    }

    /**
     * Translates the document type
     *
     * @param        $documentType
     * @param string $lang
     *
     * @return string
     */
    public function getDocumentType($documentType, $lang = 'en')
    {
        $documentType = trim($documentType);

        if (!empty($documentType) && Lang::has(sprintf('codelist/documentType.%s', $documentType), $lang)) {
            $documentType = trans('codelist/documentType.'.$documentType, [], null, $lang);
        }

        return $documentType;
    }

    /**
     * get companyName for given contract
     *
     * @param $companyName
     *
     * @return array
     */
    public function getCompany($companyName)
    {
        $cn = [];
        foreach ($companyName as $comp) {

            if (!empty($comp->name)) {

                array_push($cn, trim($comp->name));
            }
        }
        $cn = join(', ', array_filter($cn));

        return $cn;
    }

    /**
     * Update Contract Name
     *
     * @param $contracts
     *
     * @return boolean
     */
    public function renameContracts($contracts)
    {
        foreach ($contracts as $con) {
            try {
                $contract                  = $this->contract->findContract($con->id);
                $metadata                  = json_decode(json_encode($contract->metadata), true);
                $metadata['contract_name'] = $con->new;
                $contract->metadata        = $metadata;
                $contract->save();
            } catch (Exception $e) {
                $this->logger->error('rename contracts : '.$e->getMessage());
            }
        }

        return true;
    }

    /**
     * Get contract name
     *
     * @param $contracts
     *
     * @return string
     */
    public function getContractName($contracts)
    {
        $con  = json_decode(json_encode($contracts), false);
        $id   = isset($con->contract_id) ? $con->contract_id : null;
        $lang = 'en';

        if (!is_null($id) && isset($con->trans)) {
            $lang = $con->trans;
            $con  = $this->getContractForTranslation($con, $id);
        }

        return $this->refineContract($con, $id, $lang);
    }

    /**
     * Get Contract For translation
     *
     * @param $con
     * @param $id
     *
     * @return object
     */
    public function getContractForTranslation($con, $id)
    {
        $data     = array_only(
            (array) $con,
            [
                'contract_name',
                'company',
                'project_title',
                'project_identifier',
                'concession',
                'disclosure_mode_text',
                'contract_note',
            ]
        );
        $contract = $this->find($id);
        $contract->setLang($this->lang->current_translation());
        $metadata = json_decode(json_encode($contract->metadata), true);

        return (object) array_replace_recursive($metadata, $data);
    }

    /**
     * Refine Contract name
     *
     * @param        $contract
     *
     * @param null   $id
     * @param string $lang
     *
     * @return string
     */
    public function refineContract($contract, $id = null, $lang = 'en')
    {
        $cn = $ln = $tc = $sy = $nn = $a = null;

        if (isset($contract->company)) {
            $cn = $this->getCompany($contract->company);
        }

        if (isset($contract->concession)) {
            $ln = $this->getLicense($contract->concession);
        }

        if (!empty($contract->type_of_contract)) {
            $tc = $this->getTypeOfContract($contract->type_of_contract, $lang);
        } else {
            $tc = $this->getDocumentType($contract->document_type, $lang);
        }

        if (!empty($contract->signature_year)) {
            $sy = trim($contract->signature_year);
        }

        $a             = [$cn, $ln, $tc, $sy];
        $contract_name = join(', ', array_filter($a));
        $count         = $this->getContractNameCount($contract_name, $id);

        if ($count > 0) {
            return $contract_name = $contract_name.', '.str_pad($count, 3, 0, STR_PAD_LEFT);
        } else {
            return $contract_name;
        }
    }

    /**
     * Find if the contract name is unique
     *
     * @param $contractName
     *
     * @param $id
     *
     * @return bool
     */
    public function getContractNameCount($contractName, $id = null)
    {
        $count = 0;

        if (is_null($id)) {
            return $count;
        }

        $contracts = $this->contract->getContractByName($contractName, $id);
        if ($contracts) {

            foreach ($contracts as $contract) {
                $contractNamePad = $contract->metadata->contract_name;
                $lastNum         = explode(' ', $contract->metadata->contract_name);
                $lastNum         = end($lastNum);

                if (is_numeric($lastNum) && strlen($lastNum) == 3) {
                    $contractNamePad = substr($contract->metadata->contract_name, 0, -5);
                }

                if ($contractNamePad == $contractName) {
                    $count++;
                }
            }

            if ($count > 0) {
                $lastNum = explode(' ', $contract->metadata->contract_name);
                $lastNum = end($lastNum);
                $count   = (is_numeric($lastNum) && strlen($lastNum) == 3) ? $lastNum + 1 : $count;
            }
        }

        return $count;
    }

    /**
     * Get published information of metadata,text and annotation
     *
     * @param $id
     *
     * @return array
     */
    public function getPublishedInformation($id)
    {
        $data        = $this->activityService->getLatestPublishedInfo($id);
        $information = [];

        foreach ($data as $element => $info) {
            $information[$element] = [
                'created_at' => isset($info->created_at) ? $info->created_at->format('D M d, Y h:i A') : '',
                'user_name'  => isset($info->user->name) ? $info->user->name : '',
            ];
        }

        return $information;

    }

    /**
     * Get download Text files
     * @return array
     */
    public function getDownloadTextFiles()
    {
        $object         = 'download_text/';
        $files          = $this->storage->disk('s3')->files($object);
        $download_files = [];
        if (!empty($files)) {
            foreach ($files as $file) {
                $path                     = $file;
                $file                     = explode($object, $file);
                $file                     = explode('-', $file[1]);
                $size                     = getFileSize(str_replace('.zip', '', $file[2]));
                $download_files[$file[0]] = [
                    'path' => str_replace($object, '', $path),
                    'date' => str_replace('_', '-', $file[1]),
                    'size' => $size,
                ];
            }
            $order = [
                'All' => $download_files['all'],
                'RC'  => $download_files['rc'],
                'OLC' => $download_files['olc'],
            ];

            $countries = array_except($download_files, ['all', 'rc', 'olc']);
            ksort($countries);
            $countriesArr = [];
            foreach ($countries as $key => &$cn) {
                $countriesArr[ucfirst(trans('codelist/country.'.$key, [], null, 'en'))] = $cn;
            }
            $order          = $order + $countriesArr;
            $download_files = $order;
        }

        return $download_files;
    }

    /**
     * Download Contract Text zip
     *
     * @param $file
     */
    public function bulkTextDownload($file)
    {
        $zipUrl   = getS3FileURL('download_text/'.$file);
        $filename = explode('-', $file);
        $filename = 'contract_text_'.$filename[1].'.zip';
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        readfile($zipUrl);
        exit;
    }

    /**
     * Upload contract file
     *
     * @param UploadedFile $file
     *
     * @return array
     */
    protected function uploadContract(UploadedFile $file)
    {
        if ($file->isValid()) {
            $fileName    = $file->getClientOriginalName();
            $file_type   = $file->getClientOriginalExtension();
            $newFileName = sprintf("%s.%s", sha1($fileName.time()), $file_type);
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
    protected function processMetadata($formData)
    {
        if (isset($formData['type_of_contract']) && in_array('Other', $formData['type_of_contract'])) {
            unset($formData['type_of_contract'][array_search('Other', $formData['type_of_contract'])]);
        }

        $formData['date_retrieval'] = $this->formatDate($formData['date_retrieval']);
        $formData['signature_date'] = $this->formatDate($formData['signature_date']);

        foreach ($formData['company'] as &$company) {
            $company['company_founding_date'] = $this->formatDate($company['company_founding_date']);
        }

        $formData['company']           = $this->removeKeys($formData['company']);
        $formData['country']           = $this->countryService->getInfoByCode($formData['country'], 'en');
        $formData['resource']          = (!empty($formData['resource'])) ? $formData['resource'] : [];
        $formData['category']          = (!empty($formData['category'])) ? $formData['category'] : [];
        $formData['type_of_contract']  = (isset($formData['type_of_contract'])) ? $this->removeKeys(
            $formData['type_of_contract']
        ) : [];
        $formData['concession']        = $this->removeKeys($formData['concession']);
        $formData['government_entity'] = $this->removeKeys($formData['government_entity']);
        $formData['show_pdf_text']     = isset($formData['show_pdf_text']) ? $formData['show_pdf_text'] : Contract::SHOW_PDF_TEXT;;
        $formData['is_contract_signed'] = isset($formData['is_contract_signed']) ? $formData['is_contract_signed'] : 0;
        $data                           = array_only(
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
                'is_contract_signed',
                'disclosure_mode_text',
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
    protected function removeKeys($items)
    {
        $i = [];

        foreach ($items as $item) {
            $i[] = $item;
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
    protected function deleteFileFromS3($file)
    {
        if (!$this->storage->disk('s3')->exists($file)) {
            throw new FileNotFoundException(sprintf(' % not found', $file));
        }

        return $this->storage->disk('s3')->delete($file);
    }

    /**
     * Delete contract file and Folder in S#
     *
     * @param $contract
     *
     * @throws FileNotFoundException
     */
    protected function deleteContractFileAndFolder($contract)
    {
        $this->storage->disk('s3')->deleteDirectory($contract->id);
    }

    /**
     * Return array of supporting documents
     *
     * @return array
     */
    private function getSupportingContractsId()
    {
        $contractsId = [];
        $supportings = $this->contract->getAllSupportingContracts();

        foreach ($supportings as $supporting) {
            array_push($contractsId, $supporting["supporting"]);
        }

        return $contractsId;
    }

    /**
     * Get associated contracts id
     *
     * @param $id
     *
     * @return array
     */
    private function getAssociatedContractsId($id)
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
     * Format Date
     *
     * @param $date
     *
     * @return string
     */
    private function formatDate($date)
    {
        if (empty($date)) {
            return $date;
        }

        return $this->carbon->createFromFormat('Y-m-d', $date)->format('Y-m-d');
    }

    /**
     * Returns parent child contracts
     *
     * @return array
     */
    public function getParentChild()
    {
        $parent_child_contracts = $this->contract->getParentChild();
        $parent_array           = [];

        foreach ($parent_child_contracts as $parent_child_contract) {
            $parent_child_contract               = (array) $parent_child_contract;
            $parent_contract_id                  = $parent_child_contract['parent_contract_id'];
            $parent_array[$parent_contract_id][] = [
                'id'                  => $parent_child_contract['child_contract_id'],
                'open_contracting_id' => $parent_child_contract['child_open_contracting_id'],
            ];
        }

        return $parent_array;
    }

    /**
     * Returns child parent contracts
     *
     * @return array
     */
    public function getChildParent()
    {
        $child_parent_contracts = $this->contract->getChildParent();
        $child_array            = [];

        foreach ($child_parent_contracts as $child_parent_contract) {
            $child_parent_contract            = (array) $child_parent_contract;
            $parent_contract_id               = $child_parent_contract['child_contract_id'];
            $child_array[$parent_contract_id] = [
                'id'                  => $child_parent_contract['parent_contract_id'],
                'open_contracting_id' => $child_parent_contract['parent_open_contracting_id'],
            ];
        }

        return $child_array;
    }
}
