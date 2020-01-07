<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
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
use Illuminate\Support\Facades\DB;
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
        $data        = $this->activityService->getPublishedInfo($id);
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

    public function backupMetadata()
    {
        $ocids      = [
            "'ocds-591adf-1393397662'",
            "'ocds-591adf-2587229116'",
            "'ocds-591adf-4292384119'",
            "'ocds-591adf-3262972300'",
            "'ocds-591adf-1331777791'",
            "'ocds-591adf-2549975919'",
            "'ocds-591adf-2543839071'",
            "'ocds-591adf-6872304960'",
            "'ocds-591adf-5861877010'",
            "'ocds-591adf-7493980687'",
            "'ocds-591adf-4571322578'",
            "'ocds-591adf-1990915353'",
            "'ocds-591adf-0213607157'",
            "'ocds-591adf-9645096819'",
            "'ocds-591adf-6543179654'",
            "'ocds-591adf-1835848694'",
            "'ocds-591adf-2346555490'",
            "'ocds-591adf-2532469644'",
            "'ocds-591adf-0866875345'",
            "'ocds-591adf-5531614911'",
            "'ocds-591adf-2853097771'",
            "'ocds-591adf-8710976138'",
            "'ocds-591adf-8885391601'",
            "'ocds-591adf-4818516752'",
            "'ocds-591adf-4023162861'",
            "'ocds-591adf-0027576505'",
            "'ocds-591adf-4016172435'",
            "'ocds-591adf-9582550876'",
            "'ocds-591adf-3613857557'",
            "'ocds-591adf-2931082215'",
            "'ocds-591adf-1144553332'",
            "'ocds-591adf-0608183813'",
            "'ocds-591adf-1353467417'",
            "'ocds-591adf-7590925822'",
            "'ocds-591adf-6952213166'",
            "'ocds-591adf-2048247319'",
            "'ocds-591adf-8179112735'",
            "'ocds-591adf-0775315324'",
            "'ocds-591adf-1812118383'",
            "'ocds-591adf-2247482343'",
            "'ocds-591adf-0472937784'",
            "'ocds-591adf-6113163715'",
            "'ocds-591adf-9653814550'",
            "'ocds-591adf-4844071708'",
            "'ocds-591adf-2380665573'",
            "'ocds-591adf-3967759096'",
            "'ocds-591adf-2132329479'",
            "'ocds-591adf-6635241984'",
            "'ocds-591adf-3309587999'",
            "'ocds-591adf-0227810038'",
            "'ocds-591adf-4777602941'",
            "'ocds-591adf-3251239997'",
            "'ocds-591adf-0905492397'",
            "'ocds-591adf-6141206684'",
            "'ocds-591adf-8410434628'",
            "'ocds-591adf-2129008362'",
            "'ocds-591adf-4100054818'",
            "'ocds-591adf-3390216177'",
            "'ocds-591adf-8333903733'",
            "'ocds-591adf-6379664048'",
            "'ocds-591adf-5744526094'",
            "'ocds-591adf-2617767522'",
            "'ocds-591adf-9691553720'",
            "'ocds-591adf-9845812582'",
            "'ocds-591adf-0538930488'",
            "'ocds-591adf-3492744232'",
            "'ocds-591adf-4891419551'",
            "'ocds-591adf-8156065501'",
            "'ocds-591adf-4516518284'",
            "'ocds-591adf-3670075937'",
            "'ocds-591adf-1966792682'",
            "'ocds-591adf-4299664636'",
            "'ocds-591adf-5523624675'",
            "'ocds-591adf-6235180849'",
            "'ocds-591adf-0964401878'",
            "'ocds-591adf-3613534944'",
            "'ocds-591adf-1265783068'",
            "'ocds-591adf-8586283189'",
            "'ocds-591adf-8856845352'",
            "'ocds-591adf-3440211455'",
            "'ocds-591adf-7546727432'",
            "'ocds-591adf-2742639589'",
            "'ocds-591adf-9515562347'",
            "'ocds-591adf-6116103360'",
            "'ocds-591adf-4394332202'",
            "'ocds-591adf-9756374456'",
            "'ocds-591adf-6180313215'",
            "'ocds-591adf-5357825509'",
            "'ocds-591adf-7650565205'",
            "'ocds-591adf-5442186007'",
            "'ocds-591adf-1953187130'",
            "'ocds-591adf-7492095230'",
            "'ocds-591adf-6662917447'",
            "'ocds-591adf-5265824533'",
            "'ocds-591adf-0304280379'",
            "'ocds-591adf-6176694140'",
            "'ocds-591adf-4266757473'",
            "'ocds-591adf-0771447862'",
            "'ocds-591adf-3523025073'",
            "'ocds-591adf-3697833438'",
            "'ocds-591adf-8293745677'",
            "'ocds-591adf-9224709687'",
            "'ocds-591adf-0131932458'",
            "'ocds-591adf-2228036438'",
            "'ocds-591adf-0639747840'",
            "'ocds-591adf-5133948327'",
            "'ocds-591adf-9173482252'",
            "'ocds-591adf-0677357016'",
            "'ocds-591adf-1215811760'",
            "'ocds-591adf-4256816203'",
            "'ocds-591adf-4627617881'",
            "'ocds-591adf-2512096783'",
            "'ocds-591adf-8175265085'",
            "'ocds-591adf-3045742924'",
            "'ocds-591adf-2985497670'",
            "'ocds-591adf-8844534381'",
            "'ocds-591adf-5030923648'",
            "'ocds-591adf-1594677361'",
            "'ocds-591adf-6290205772'",
            "'ocds-591adf-7459663089'",
            "'ocds-591adf-7037390087'",
            "'ocds-591adf-0452027420'",
            "'ocds-591adf-0304549855'",
            "'ocds-591adf-3108627390'",
            "'ocds-591adf-9575525455'",
            "'ocds-591adf-1015803430'",
            "'ocds-591adf-3222702771'",
            "'ocds-591adf-3282184821'",
            "'ocds-591adf-7403197571'",
            "'ocds-591adf-1834156729'",
            "'ocds-591adf-4778033815'",
            "'ocds-591adf-5877840504'",
            "'ocds-591adf-0073976086'",
            "'ocds-591adf-3082701403'",
            "'ocds-591adf-5062562123'",
            "'ocds-591adf-8212060329'",
            "'ocds-591adf-4251085025'",
            "'ocds-591adf-1468696395'",
            "'ocds-591adf-7480993358'",
            "'ocds-591adf-2840001526'",
            "'ocds-591adf-0096337208'",
            "'ocds-591adf-0624045737'",
            "'ocds-591adf-4684953346'",
            "'ocds-591adf-1890956908'",
            "'ocds-591adf-7015363530'",
            "'ocds-591adf-6070894490'",
            "'ocds-591adf-8733736834'",
            "'ocds-591adf-7033732387'",
            "'ocds-591adf-3508920677'",
            "'ocds-591adf-0768322156'",
            "'ocds-591adf-6452502161'",
            "'ocds-591adf-2219177832'",
            "'ocds-591adf-6304551722'",
            "'ocds-591adf-6127005758'",
            "'ocds-591adf-6899660250'",
            "'ocds-591adf-7753481070'",
            "'ocds-591adf-9311596646'",
            "'ocds-591adf-1544398979'",
            "'ocds-591adf-5972950624'",
            "'ocds-591adf-7785290402'",
            "'ocds-591adf-6998213818'",
            "'ocds-591adf-7183088348'",
            "'ocds-591adf-3266781739'",
            "'ocds-591adf-3124740910'",
            "'ocds-591adf-2132740097'",
            "'ocds-591adf-1017331670'",
            "'ocds-591adf-2364066400'",
            "'ocds-591adf-6314380103'",
            "'ocds-591adf-5838470097'",
            "'ocds-591adf-9479773023'",
            "'ocds-591adf-5805290587'",
            "'ocds-591adf-0462882243'",
            "'ocds-591adf-0752512707'",
            "'ocds-591adf-1551829611'",
            "'ocds-591adf-9928018094'",
            "'ocds-591adf-0181178415'",
            "'ocds-591adf-5325704581'",
            "'ocds-591adf-1398905143'",
            "'ocds-591adf-9988950132'",
            "'ocds-591adf-4397912652'",
            "'ocds-591adf-7838898920'",
            "'ocds-591adf-8497132181'",
            "'ocds-591adf-5268407026'",
            "'ocds-591adf-3960665000'",
            "'ocds-591adf-0851930818'",
            "'ocds-591adf-0418193876'",
            "'ocds-591adf-7151047842'",
            "'ocds-591adf-7202085028'",
            "'ocds-591adf-9188516395'",
            "'ocds-591adf-1677413274'",
            "'ocds-591adf-6801345335'",
            "'ocds-591adf-0218900327'",
            "'ocds-591adf-4380310584'",
            "'ocds-591adf-0271007010'",
            "'ocds-591adf-8841781775'",
            "'ocds-591adf-1022255942'",
            "'ocds-591adf-4257477346'",
            "'ocds-591adf-0114673626'",
            "'ocds-591adf-3173477058'",
            "'ocds-591adf-1661389950'",
            "'ocds-591adf-9922636059'",
            "'ocds-591adf-0971105244'",
            "'ocds-591adf-3001376476'",
            "'ocds-591adf-3922793692'",
            "'ocds-591adf-5545997817'",
            "'ocds-591adf-3232305854'",
            "'ocds-591adf-4426584943'",
            "'ocds-591adf-2878207711'",
            "'ocds-591adf-9762278042'",
            "'ocds-591adf-8331896069'",
            "'ocds-591adf-5741668866'",
            "'ocds-591adf-9517241316'",
            "'ocds-591adf-8615350441'",
            "'ocds-591adf-7184311755'",
            "'ocds-591adf-3508491732'",
            "'ocds-591adf-8326933932'",
            "'ocds-591adf-5545717486'",
            "'ocds-591adf-6664008036'",
            "'ocds-591adf-7882794324'",
            "'ocds-591adf-8228200502'",
            "'ocds-591adf-1820674929'",
            "'ocds-591adf-4691685152'",
            "'ocds-591adf-9160512674'",
            "'ocds-591adf-4096682799'",
            "'ocds-591adf-0420392779'",
            "'ocds-591adf-4375805114'",
            "'ocds-591adf-2882992072'",
            "'ocds-591adf-2740538018'",
            "'ocds-591adf-3757991515'",
            "'ocds-591adf-6112091133'",
            "'ocds-591adf-4472360738'",
            "'ocds-591adf-6265815339'",
            "'ocds-591adf-3900695508'",
            "'ocds-591adf-8755928578'",
            "'ocds-591adf-3710317476'",
            "'ocds-591adf-9352687684'",
            "'ocds-591adf-1648164093'",
            "'ocds-591adf-3691607772'",
            "'ocds-591adf-1495612293'",
            "'ocds-591adf-5996211567'",
            "'ocds-591adf-2311124573'",
            "'ocds-591adf-7912152192'",
            "'ocds-591adf-5063934735'",
            "'ocds-591adf-3014563630'",
            "'ocds-591adf-3316958152'",
            "'ocds-591adf-1624878521'",
            "'ocds-591adf-4399564244'",
            "'ocds-591adf-7309505757'",
            "'ocds-591adf-3906687826'",
            "'ocds-591adf-8301066061'",
            "'ocds-591adf-0461174209'",
            "'ocds-591adf-2944436063'",
            "'ocds-591adf-2064950212'",
            "'ocds-591adf-3407308771'",
            "'ocds-591adf-4109359446'",
            "'ocds-591adf-1806088707'",
            "'ocds-591adf-3315096248'",
            "'ocds-591adf-3723126760'",
            "'ocds-591adf-1108632570'",
            "'ocds-591adf-6743049102'",
            "'ocds-591adf-3314121968'",
            "'ocds-591adf-1465592851'",
            "'ocds-591adf-8841944380'",
            "'ocds-591adf-2245683705'",
            "'ocds-591adf-0420029369'",
            "'ocds-591adf-1195303977'",
            "'ocds-591adf-9776014613'",
            "'ocds-591adf-7478918284'",
            "'ocds-591adf-8715839071'",
            "'ocds-591adf-8192707206'",
            "'ocds-591adf-3632117373'",
            "'ocds-591adf-4627474417'",
            "'ocds-591adf-2409086627'",
            "'ocds-591adf-8237158457'",
            "'ocds-591adf-4444263100'",
            "'ocds-591adf-1740575512'",
            "'ocds-591adf-2014046591'",
            "'ocds-591adf-0292784834'",
            "'ocds-591adf-9620382408'",
            "'ocds-591adf-7021319128'",
            "'ocds-591adf-6297501032'",
            "'ocds-591adf-0951831892'",
            "'ocds-591adf-8944461550'",
            "'ocds-591adf-7950887599'",
            "'ocds-591adf-5792651943'",
            "'ocds-591adf-6781195714'",
            "'ocds-591adf-3525416018'",
            "'ocds-591adf-4204873445'",
            "'ocds-591adf-0628187731'",
            "'ocds-591adf-4815087699'",
            "'ocds-591adf-8649797663'",
            "'ocds-591adf-3212507685'",
            "'ocds-591adf-1440947345'",
            "'ocds-591adf-9640771314'",
            "'ocds-591adf-7832951874'",
            "'ocds-591adf-1619101625'",
            "'ocds-591adf-8560734374'",
            "'ocds-591adf-5901301414'",
            "'ocds-591adf-5848991193'",
            "'ocds-591adf-0240741237'",
            "'ocds-591adf-9306866843'",
            "'ocds-591adf-2590553572'",
            "'ocds-591adf-2901517891'",
            "'ocds-591adf-3433899670'",
            "'ocds-591adf-1878297445'",
            "'ocds-591adf-4977579802'",
            "'ocds-591adf-9771948110'",
            "'ocds-591adf-7451096540'",
            "'ocds-591adf-9736395382'",
            "'ocds-591adf-0014595575'",
            "'ocds-591adf-7534708827'",
            "'ocds-591adf-5685445366'",
            "'ocds-591adf-1717538716'",
            "'ocds-591adf-9499174502'",
            "'ocds-591adf-7120819444'",
            "'ocds-591adf-9686067103'",
            "'ocds-591adf-5424836511'",
            "'ocds-591adf-0902890112'",
            "'ocds-591adf-5586820610'",
            "'ocds-591adf-0228355919'",
            "'ocds-591adf-9052399699'",
            "'ocds-591adf-6537883697'",
            "'ocds-591adf-3831230606'",
            "'ocds-591adf-1949443621'",
            "'ocds-591adf-8863600732'",
            "'ocds-591adf-6064693869'",
            "'ocds-591adf-7303551872'",
            "'ocds-591adf-4709701176'",
            "'ocds-591adf-4566317163'",
            "'ocds-591adf-2438512631'",
            "'ocds-591adf-3261751421'",
            "'ocds-591adf-7159971800'",
            "'ocds-591adf-8100805971'",
            "'ocds-591adf-0939306330'",
            "'ocds-591adf-5906019396'",
            "'ocds-591adf-7757139298'",
            "'ocds-591adf-9214264709'",
            "'ocds-591adf-1600391941'",
            "'ocds-591adf-8383008052'",
            "'ocds-591adf-4388708715'",
            "'ocds-591adf-3232099165'",
            "'ocds-591adf-2843880701'",
            "'ocds-591adf-2009144457'",
            "'ocds-591adf-2182826308'",
            "'ocds-591adf-6899414447'",
            "'ocds-591adf-3558756842'",
            "'ocds-591adf-8653902484'",
            "'ocds-591adf-6688383797'",
            "'ocds-591adf-2899956853'",
            "'ocds-591adf-2009529955'",
            "'ocds-591adf-8260356369'",
            "'ocds-591adf-8486691222'",
            "'ocds-591adf-2548058701'",
            "'ocds-591adf-1707203194'",
            "'ocds-591adf-9201328103'",
            "'ocds-591adf-0701596709'",
            "'ocds-591adf-5573742588'",
            "'ocds-591adf-1774464193'",
            "'ocds-591adf-8101290201'",
            "'ocds-591adf-2262702985'",
            "'ocds-591adf-5642119190'",
            "'ocds-591adf-8580866972'",
            "'ocds-591adf-4037053965'",
            "'ocds-591adf-5958369981'",
            "'ocds-591adf-2887938326'",
            "'ocds-591adf-0736286219'",
            "'ocds-591adf-6800054916'",
            "'ocds-591adf-8052316867'",
            "'ocds-591adf-3988663064'",
            "'ocds-591adf-3612985124'",
            "'ocds-591adf-0855371527'",
            "'ocds-591adf-7468099467'",
            "'ocds-591adf-0921285528'",
            "'ocds-591adf-8820234307'",
            "'ocds-591adf-5003575897'",
            "'ocds-591adf-1653821228'",
            "'ocds-591adf-0725247752'",
            "'ocds-591adf-2269582291'",
            "'ocds-591adf-8033884916'",
            "'ocds-591adf-6904959112'",
            "'ocds-591adf-9016560520'",
            "'ocds-591adf-8606873912'",
            "'ocds-591adf-3240717431'",
            "'ocds-591adf-2768083178'",
            "'ocds-591adf-4483792440'",
            "'ocds-591adf-9669096564'",
            "'ocds-591adf-3437474455'",
            "'ocds-591adf-7227443979'",
            "'ocds-591adf-9115310768'",
            "'ocds-591adf-7212601903'",
            "'ocds-591adf-2927931073'",
            "'ocds-591adf-0925073922'",
            "'ocds-591adf-6237477089'",
            "'ocds-591adf-8078738904'",
            "'ocds-591adf-0616817020'",
            "'ocds-591adf-7087857453'",
            "'ocds-591adf-9642814017'",
            "'ocds-591adf-8001318248'",
            "'ocds-591adf-6822122534'",
            "'ocds-591adf-4170419959'",
            "'ocds-591adf-5307724746'",
            "'ocds-591adf-2774693704'",
            "'ocds-591adf-4278971256'",
            "'ocds-591adf-7040396335'",
            "'ocds-591adf-0628604071'",
            "'ocds-591adf-0621960018'",
            "'ocds-591adf-9399783777'",
            "'ocds-591adf-3303812608'",
            "'ocds-591adf-2396569119'",
            "'ocds-591adf-2461556641'",
            "'ocds-591adf-2401833289'",
            "'ocds-591adf-0484705559'",
            "'ocds-591adf-9145827406'",
            "'ocds-591adf-1601150827'",
            "'ocds-591adf-1880406409'",
            "'ocds-591adf-2707180369'",
            "'ocds-591adf-7382402375'",
            "'ocds-591adf-2372332812'",
            "'ocds-591adf-2159924071'",
            "'ocds-591adf-9201648630'",
            "'ocds-591adf-6749062117'",
            "'ocds-591adf-2458536233'",
            "'ocds-591adf-6187166467'",
            "'ocds-591adf-9593916956'",
            "'ocds-591adf-4258574838'",
            "'ocds-591adf-3032468364'",
            "'ocds-591adf-4449736548'",
            "'ocds-591adf-2096071243'",
            "'ocds-591adf-9112931197'",
            "'ocds-591adf-6562585363'",
            "'ocds-591adf-6677014369'",
            "'ocds-591adf-5738530463'",
            "'ocds-591adf-6179905611'",
            "'ocds-591adf-4942070282'",
            "'ocds-591adf-7718448031'",
            "'ocds-591adf-2771733988'",
            "'ocds-591adf-8002546923'",
            "'ocds-591adf-7941034659'",
            "'ocds-591adf-2656241484'",
            "'ocds-591adf-6207349867'",
            "'ocds-591adf-6387856838'",
            "'ocds-591adf-3970511656'",
            "'ocds-591adf-7284594451'",
            "'ocds-591adf-0048000024'",
            "'ocds-591adf-3533551205'",
            "'ocds-591adf-0773715511'",
            "'ocds-591adf-4214910059'",
            "'ocds-591adf-5942699559'",
            "'ocds-591adf-3624750089'",
            "'ocds-591adf-5458382622'",
            "'ocds-591adf-2288241575'",
            "'ocds-591adf-6181255605'",
            "'ocds-591adf-7411546128'",
            "'ocds-591adf-1038726743'",
            "'ocds-591adf-3071501652'",
            "'ocds-591adf-9373760375'",
            "'ocds-591adf-2032447165'",
            "'ocds-591adf-9572710160'",
            "'ocds-591adf-3843755960'",
            "'ocds-591adf-8938335769'",
            "'ocds-591adf-9753464836'",
            "'ocds-591adf-1900505079'",
            "'ocds-591adf-0640514048'",
            "'ocds-591adf-0083596491'",
            "'ocds-591adf-8082857980'",
            "'ocds-591adf-9564139780'",
            "'ocds-591adf-5963366284'",
            "'ocds-591adf-2327028479'",
            "'ocds-591adf-8486546892'",
            "'ocds-591adf-2393158296'",
            "'ocds-591adf-4379373179'",
            "'ocds-591adf-9194346883'",
            "'ocds-591adf-7705086445'",
            "'ocds-591adf-1314860465'",
            "'ocds-591adf-2939708932'",
            "'ocds-591adf-4966736928'",
            "'ocds-591adf-1424338090'",
            "'ocds-591adf-8564429744'",
            "'ocds-591adf-9581547683'",
            "'ocds-591adf-6626006637'",
            "'ocds-591adf-6696511613'",
            "'ocds-591adf-5233340063'",
            "'ocds-591adf-5444707467'",
            "'ocds-591adf-4357346394'",
            "'ocds-591adf-4197629132'",
            "'ocds-591adf-2593066531'",
            "'ocds-591adf-0633880291'",
            "'ocds-591adf-2792396017'",
            "'ocds-591adf-0056582197'",
            "'ocds-591adf-0781051765'",
            "'ocds-591adf-9208160921'",
            "'ocds-591adf-0115707939'",
            "'ocds-591adf-4283019703'",
            "'ocds-591adf-8380393006'",
            "'ocds-591adf-9203793811'",
            "'ocds-591adf-6155018199'",
            "'ocds-591adf-1623268944'",
            "'ocds-591adf-5976674032'",
            "'ocds-591adf-6484217517'",
            "'ocds-591adf-6012864853'",
            "'ocds-591adf-8368260751'",
            "'ocds-591adf-9844860754'",
            "'ocds-591adf-3824268091'",
            "'ocds-591adf-3498737512'",
            "'ocds-591adf-7265722663'",
            "'ocds-591adf-1713256223'",
            "'ocds-591adf-4141862922'",
            "'ocds-591adf-9308829862'",
            "'ocds-591adf-7888022700'",
            "'ocds-591adf-0617728514'",
            "'ocds-591adf-0060618096'",
            "'ocds-591adf-3741414378'",
            "'ocds-591adf-6645823668'",
            "'ocds-591adf-3363161868'",
            "'ocds-591adf-1946181466'",
            "'ocds-591adf-8908710232'",
            "'ocds-591adf-5792510988'",
            "'ocds-591adf-0431745646'",
            "'ocds-591adf-6975899734'",
            "'ocds-591adf-9119482338'",
            "'ocds-591adf-1394673190'",
            "'ocds-591adf-8549514639'",
            "'ocds-591adf-9975874698'",
            "'ocds-591adf-2917943809'",
            "'ocds-591adf-5185619420'",
            "'ocds-591adf-2689260212'",
            "'ocds-591adf-0421393552'",
            "'ocds-591adf-2719937589'",
            "'ocds-591adf-3751727488'",
            "'ocds-591adf-0742047974'",
            "'ocds-591adf-2437234184'",
            "'ocds-591adf-3538681265'",
            "'ocds-591adf-9492198891'",
            "'ocds-591adf-3156014237'",
            "'ocds-591adf-1737251309'",
            "'ocds-591adf-6091789645'",
            "'ocds-591adf-9374734729'",
            "'ocds-591adf-2181723079'",
            "'ocds-591adf-8757641437'",
            "'ocds-591adf-4013712466'",
            "'ocds-591adf-4621402481'",
            "'ocds-591adf-1333562032'",
            "'ocds-591adf-1265870384'",
            "'ocds-591adf-4659660417'",
            "'ocds-591adf-5502782183'",
            "'ocds-591adf-5920713915'",
            "'ocds-591adf-8808867910'",
            "'ocds-591adf-5307554205'",
            "'ocds-591adf-1658238869'",
            "'ocds-591adf-3848739629'",
            "'ocds-591adf-6822686270'",
            "'ocds-591adf-7195323379'",
            "'ocds-591adf-8951344827'",
            "'ocds-591adf-0669110404'",
            "'ocds-591adf-1599142675'",
            "'ocds-591adf-0075411163'",
            "'ocds-591adf-4949559443'",
            "'ocds-591adf-2108050504'",
            "'ocds-591adf-3398398402'",
            "'ocds-591adf-3291421710'",
            "'ocds-591adf-2074895725'",
            "'ocds-591adf-2761455866'",
            "'ocds-591adf-5929939263'",
            "'ocds-591adf-3025980849'",
            "'ocds-591adf-4744093490'",
            "'ocds-591adf-6600480891'",
            "'ocds-591adf-5773057513'",
            "'ocds-591adf-6886084452'",
            "'ocds-591adf-8376312952'",
            "'ocds-591adf-7068014216'",
            "'ocds-591adf-8152849352'",
            "'ocds-591adf-5579407168'",
            "'ocds-591adf-4789221573'",
            "'ocds-591adf-9855163235'",
            "'ocds-591adf-2610580215'",
            "'ocds-591adf-1730479724'",
            "'ocds-591adf-1319646931'",
            "'ocds-591adf-9614633875'",
            "'ocds-591adf-4126162216'",
            "'ocds-591adf-2173819973'",
            "'ocds-591adf-7769269550'",
            "'ocds-591adf-8785384336'",
            "'ocds-591adf-4824016790'",
            "'ocds-591adf-4478492664'",
            "'ocds-591adf-7496893655'",
            "'ocds-591adf-4292503746'",
            "'ocds-591adf-5838344155'",
            "'ocds-591adf-5432334519'",
            "'ocds-591adf-6328981421'",
            "'ocds-591adf-8254959256'",
            "'ocds-591adf-6884844688'",
            "'ocds-591adf-2175879797'",
            "'ocds-591adf-9914903289'",
            "'ocds-591adf-4290386059'",
            "'ocds-591adf-1454401801'",
            "'ocds-591adf-8090982720'",
            "'ocds-591adf-9181613139'",
            "'ocds-591adf-0749759512'",
            "'ocds-591adf-8621709918'",
            "'ocds-591adf-2556454498'",
            "'ocds-591adf-6051705587'",
            "'ocds-591adf-1503083799'",
            "'ocds-591adf-5344601220'",
            "'ocds-591adf-5828832149'",
            "'ocds-591adf-8501977034'",
            "'ocds-591adf-6541551918'",
            "'ocds-591adf-6477149327'",
            "'ocds-591adf-8597696414'",
            "'ocds-591adf-2146344132'",
            "'ocds-591adf-6098007922'",
            "'ocds-591adf-6955060610'",
            "'ocds-591adf-9528502089'",
            "'ocds-591adf-7427379098'",
            "'ocds-591adf-6173093934'",
            "'ocds-591adf-4427784387'",
            "'ocds-591adf-5527552623'",
            "'ocds-591adf-0237904720'",
            "'ocds-591adf-3510451430'",
            "'ocds-591adf-0567930699'",
            "'ocds-591adf-9380721319'",
            "'ocds-591adf-5890345809'",
            "'ocds-591adf-1362820655'",
            "'ocds-591adf-7331431087'",
            "'ocds-591adf-5841633425'",
            "'ocds-591adf-7208749619'",
            "'ocds-591adf-1414882346'",
            "'ocds-591adf-4342783405'",
            "'ocds-591adf-4764118287'",
            "'ocds-591adf-9279619856'",
            "'ocds-591adf-5317994994'",
            "'ocds-591adf-1974765349'",
            "'ocds-591adf-3149266819'",
            "'ocds-591adf-5006403470'",
            "'ocds-591adf-6223535439'",
            "'ocds-591adf-0330524527'",
            "'ocds-591adf-5770617196'",
            "'ocds-591adf-3711903879'",
            "'ocds-591adf-1331537774'",
            "'ocds-591adf-5573905643'",
            "'ocds-591adf-6464647576'",
            "'ocds-591adf-6801171062'",
            "'ocds-591adf-2666296340'",
            "'ocds-591adf-0329969692'",
            "'ocds-591adf-1569974362'",
            "'ocds-591adf-9883087113'",
            "'ocds-591adf-7457426940'",
            "'ocds-591adf-8829493091'",
            "'ocds-591adf-0683872141'",
            "'ocds-591adf-9769622045'",
            "'ocds-591adf-7280300169'",
            "'ocds-591adf-6495590428'",
            "'ocds-591adf-3808032469'",
            "'ocds-591adf-9606270401'",
            "'ocds-591adf-0241909737'",
            "'ocds-591adf-0624472233'",
            "'ocds-591adf-0304700055'",
            "'ocds-591adf-7759543712'",
            "'ocds-591adf-1502564676'",
            "'ocds-591adf-0864171697'",
            "'ocds-591adf-3073179050'",
            "'ocds-591adf-4125287033'",
            "'ocds-591adf-4679728293'",
            "'ocds-591adf-0859255815'",
            "'ocds-591adf-7856017364'",
            "'ocds-591adf-1536446241'",
            "'ocds-591adf-4320292802'",
            "'ocds-591adf-4645641333'",
            "'ocds-591adf-9678147772'",
            "'ocds-591adf-8619369763'",
            "'ocds-591adf-2653125767'",
            "'ocds-591adf-8994683757'",
            "'ocds-591adf-9585889698'",
            "'ocds-591adf-0432750635'",
            "'ocds-591adf-1646757982'",
            "'ocds-591adf-6901069067'",
            "'ocds-591adf-4123523800'",
            "'ocds-591adf-2914071981'",
            "'ocds-591adf-9306527580'",
            "'ocds-591adf-7568305856'",
            "'ocds-591adf-0885560786'",
            "'ocds-591adf-7146752013'",
            "'ocds-591adf-0429102665'",
            "'ocds-591adf-8091377248'",
            "'ocds-591adf-0195471540'",
            "'ocds-591adf-2354828177'",
            "'ocds-591adf-3419068261'",
            "'ocds-591adf-5374338853'",
            "'ocds-591adf-9113476125'",
            "'ocds-591adf-6884489067'",
            "'ocds-591adf-9611382739'",
            "'ocds-591adf-4525917050'",
            "'ocds-591adf-2015681219'",
            "'ocds-591adf-7433399809'",
            "'ocds-591adf-4284433444'",
            "'ocds-591adf-8913086018'",
            "'ocds-591adf-1594835240'",
            "'ocds-591adf-7876582028'",
            "'ocds-591adf-5604659447'",
            "'ocds-591adf-1406548730'",
            "'ocds-591adf-2720415109'",
            "'ocds-591adf-4901961894'",
            "'ocds-591adf-0024730431'",
            "'ocds-591adf-2236979409'",
            "'ocds-591adf-4837532873'",
            "'ocds-591adf-4256986882'",
            "'ocds-591adf-4484816003'",
            "'ocds-591adf-0818116201'",
            "'ocds-591adf-3986673209'",
            "'ocds-591adf-5187167862'",
            "'ocds-591adf-8309911494'",
            "'ocds-591adf-1071407854'",
            "'ocds-591adf-8350674630'",
            "'ocds-591adf-2565867185'",
            "'ocds-591adf-7097600866'",
            "'ocds-591adf-4975594142'",
            "'ocds-591adf-8700327622'",
            "'ocds-591adf-3234824350'",
            "'ocds-591adf-1596285788'",
            "'ocds-591adf-3417762881'",
            "'ocds-591adf-0475780199'",
            "'ocds-591adf-8521934512'",
            "'ocds-591adf-7121914031'",
            "'ocds-591adf-0469490625'",
            "'ocds-591adf-6554465329'",
            "'ocds-591adf-8581364151'",
            "'ocds-591adf-4180129773'",
            "'ocds-591adf-5465882683'",
            "'ocds-591adf-8476846603'",
            "'ocds-591adf-7097753035'",
            "'ocds-591adf-7558517637'",
            "'ocds-591adf-0405161541'",
            "'ocds-591adf-8086197830'",
            "'ocds-591adf-3966676342'",
            "'ocds-591adf-8860826385'",
            "'ocds-591adf-1562576878'",
            "'ocds-591adf-8465275052'",
            "'ocds-591adf-3911411402'",
            "'ocds-591adf-3885815417'",
            "'ocds-591adf-6766832315'",
            "'ocds-591adf-8429352461'",
            "'ocds-591adf-8288929525'",
            "'ocds-591adf-9746611143'",
            "'ocds-591adf-7620797249'",
            "'ocds-591adf-0636968741'",
            "'ocds-591adf-7827021309'",
            "'ocds-591adf-9784097494'",
            "'ocds-591adf-9646831370'",
            "'ocds-591adf-0859114956'",
            "'ocds-591adf-1037236487'",
            "'ocds-591adf-8693547378'",
            "'ocds-591adf-3271894222'",
            "'ocds-591adf-9273996970'",
            "'ocds-591adf-0358603254'",
            "'ocds-591adf-4712380251'",
            "'ocds-591adf-5834131316'",
            "'ocds-591adf-6159269475'",
            "'ocds-591adf-4532921009'",
            "'ocds-591adf-5185765046'",
            "'ocds-591adf-0745521051'",
            "'ocds-591adf-8459966788'",
            "'ocds-591adf-8120227611'",
            "'ocds-591adf-9251512174'",
            "'ocds-591adf-4811240028'",
            "'ocds-591adf-0130432161'",
            "'ocds-591adf-2594228695'",
            "'ocds-591adf-3687033972'",
            "'ocds-591adf-4326522813'",
            "'ocds-591adf-7338272103'",
            "'ocds-591adf-9310579850'",
            "'ocds-591adf-4132803471'",
            "'ocds-591adf-1458652954'",
            "'ocds-591adf-9513647951'",
            "'ocds-591adf-5462558434'",
            "'ocds-591adf-1912577274'",
            "'ocds-591adf-0589536288'",
            "'ocds-591adf-1165471583'",
            "'ocds-591adf-5025757871'",
            "'ocds-591adf-1212255351'",
            "'ocds-591adf-9586019222'",
            "'ocds-591adf-1337408934'",
            "'ocds-591adf-6683484306'",
            "'ocds-591adf-4444365858'",
            "'ocds-591adf-4302698707'",
            "'ocds-591adf-6186641258'",
            "'ocds-591adf-2098248333'",
            "'ocds-591adf-2999190496'",
            "'ocds-591adf-9349602762'",
            "'ocds-591adf-1049807701'",
            "'ocds-591adf-4313867617'",
            "'ocds-591adf-2464468553'",
            "'ocds-591adf-4854344543'",
            "'ocds-591adf-9679521107'",
            "'ocds-591adf-7022649559'",
            "'ocds-591adf-2060658975'",
            "'ocds-591adf-9795427622'",
            "'ocds-591adf-8402124061'",
            "'ocds-591adf-2514008710'",
            "'ocds-591adf-1819183362'",
            "'ocds-591adf-2238738028'",
            "'ocds-591adf-1373608183'",
            "'ocds-591adf-9128647513'",
            "'ocds-591adf-1133439999'",
            "'ocds-591adf-5999801820'",
            "'ocds-591adf-0989467575'",
            "'ocds-591adf-8446612750'",
            "'ocds-591adf-8506006251'",
            "'ocds-591adf-7038321305'",
            "'ocds-591adf-1251983902'",
            "'ocds-591adf-5021832004'",
            "'ocds-591adf-8062182641'",
            "'ocds-591adf-4777905844'",
            "'ocds-591adf-2817125596'",
            "'ocds-591adf-9552222925'",
            "'ocds-591adf-3556799773'",
            "'ocds-591adf-4828324785'",
            "'ocds-591adf-1630698631'",
            "'ocds-591adf-0179644427'",
            "'ocds-591adf-7671284346'",
            "'ocds-591adf-3544719155'",
            "'ocds-591adf-3883099279'",
            "'ocds-591adf-2514725846'",
            "'ocds-591adf-3522869491'",
            "'ocds-591adf-9797336341'",
            "'ocds-591adf-6223009974'",
            "'ocds-591adf-6045242845'",
            "'ocds-591adf-0940672274'",
            "'ocds-591adf-1019283522'",
            "'ocds-591adf-3479988382'",
            "'ocds-591adf-3119260709'",
            "'ocds-591adf-6442841974'",
            "'ocds-591adf-7013278443'",
            "'ocds-591adf-5128040886'",
            "'ocds-591adf-8797519591'",
            "'ocds-591adf-9932168781'",
            "'ocds-591adf-6550040376'",
            "'ocds-591adf-1707765023'",
            "'ocds-591adf-3298436391'",
            "'ocds-591adf-6647835520'",
            "'ocds-591adf-5520433530'",
            "'ocds-591adf-5935767031'",
            "'ocds-591adf-3135230141'",
            "'ocds-591adf-2903171387'",
            "'ocds-591adf-8691193848'",
            "'ocds-591adf-8116673375'",
            "'ocds-591adf-4046187347'",
            "'ocds-591adf-9487119280'",
            "'ocds-591adf-9274016935'",
            "'ocds-591adf-7027592904'",
            "'ocds-591adf-5549008607'",
            "'ocds-591adf-3993686006'",
            "'ocds-591adf-5606276477'",
            "'ocds-591adf-3682822226'",
            "'ocds-591adf-7492015340'",
            "'ocds-591adf-0865738708'",
            "'ocds-591adf-7742398126'",
            "'ocds-591adf-2673437920'",
            "'ocds-591adf-3987260466'",
            "'ocds-591adf-5576431527'",
            "'ocds-591adf-0143682044'",
            "'ocds-591adf-4091289998'",
            "'ocds-591adf-0682153656'",
            "'ocds-591adf-3098471462'",
            "'ocds-591adf-8293568781'",
            "'ocds-591adf-8398776486'",
            "'ocds-591adf-0020404818'",
            "'ocds-591adf-2547744131'",
            "'ocds-591adf-4847279287'",
            "'ocds-591adf-1195181754'",
            "'ocds-591adf-1790554736'",
            "'ocds-591adf-3664745125'",
            "'ocds-591adf-9584199744'",
            "'ocds-591adf-0839745741'",
            "'ocds-591adf-0593665649'",
            "'ocds-591adf-5301138756'",
            "'ocds-591adf-4332004799'",
            "'ocds-591adf-4002079239'",
            "'ocds-591adf-2623317081'",
            "'ocds-591adf-3484002062'",
            "'ocds-591adf-8615583226'",
            "'ocds-591adf-0132823891'",
            "'ocds-591adf-6580442576'",
            "'ocds-591adf-3888035061'",
            "'ocds-591adf-7143693099'",
            "'ocds-591adf-8201633114'",
            "'ocds-591adf-8767626914'",
            "'ocds-591adf-7235685315'",
            "'ocds-591adf-6234023717'",
            "'ocds-591adf-2517060866'",
            "'ocds-591adf-4707202416'",
            "'ocds-591adf-6782493746'",
            "'ocds-591adf-5864859645'",
            "'ocds-591adf-3090927242'",
            "'ocds-591adf-1883627976'",
            "'ocds-591adf-4261917359'",
            "'ocds-591adf-1548046121'",
            "'ocds-591adf-5978990122'",
            "'ocds-591adf-8606020946'",
            "'ocds-591adf-3967385753'",
            "'ocds-591adf-5706330973'",
            "'ocds-591adf-6062199430'",
            "'ocds-591adf-5527410295'",
            "'ocds-591adf-0302852534'",
            "'ocds-591adf-8525037580'",
            "'ocds-591adf-9907878295'",
            "'ocds-591adf-7945637258'",
            "'ocds-591adf-2960921626'",
            "'ocds-591adf-7890264225'",
            "'ocds-591adf-7725261062'",
            "'ocds-591adf-0569891533'",
            "'ocds-591adf-1706855956'",
            "'ocds-591adf-5298783412'",
            "'ocds-591adf-0663604054'",
            "'ocds-591adf-8131023957'",
            "'ocds-591adf-4539146649'",
            "'ocds-591adf-6266688800'",
            "'ocds-591adf-1063902547'",
            "'ocds-591adf-7892747625'",
            "'ocds-591adf-0332165904'",
            "'ocds-591adf-2026389074'",
            "'ocds-591adf-3657924895'",
            "'ocds-591adf-0485696360'",
            "'ocds-591adf-7857417842'",
            "'ocds-591adf-9297888733'",
            "'ocds-591adf-9076523020'",
            "'ocds-591adf-0509648377'",
            "'ocds-591adf-3122930756'",
            "'ocds-591adf-8591077493'",
            "'ocds-591adf-2756428993'",
            "'ocds-591adf-1131282147'",
            "'ocds-591adf-1329005460'",
            "'ocds-591adf-5552522826'",
            "'ocds-591adf-1831066994'",
            "'ocds-591adf-2475739747'",
            "'ocds-591adf-9132322916'",
            "'ocds-591adf-3098431036'",
            "'ocds-591adf-0558326609'",
            "'ocds-591adf-0366276821'",
            "'ocds-591adf-9915360032'",
            "'ocds-591adf-2376664043'",
            "'ocds-591adf-7095286648'",
            "'ocds-591adf-9520348483'",
            "'ocds-591adf-1854369267'",
            "'ocds-591adf-1407263449'",
            "'ocds-591adf-5794245182'",
            "'ocds-591adf-1591977575'",
            "'ocds-591adf-4054477057'",
            "'ocds-591adf-7520190920'",
            "'ocds-591adf-8332508387'",
            "'ocds-591adf-1837020943'",
            "'ocds-591adf-1311917983'",
            "'ocds-591adf-5871784501'",
            "'ocds-591adf-5498680840'",
            "'ocds-591adf-8871774498'",
            "'ocds-591adf-3746700873'",
            "'ocds-591adf-1933643437'",
            "'ocds-591adf-3686024332'",
            "'ocds-591adf-8879661565'",
            "'ocds-591adf-6117173522'",
            "'ocds-591adf-5013169899'",
            "'ocds-591adf-6203332348'",
            "'ocds-591adf-3390255274'",
            "'ocds-591adf-1105353909'",
            "'ocds-591adf-3333132780'",
            "'ocds-591adf-7861998816'",
            "'ocds-591adf-4671536002'",
            "'ocds-591adf-7757161861'",
            "'ocds-591adf-6530662916'",
            "'ocds-591adf-3735615231'",
            "'ocds-591adf-0200818704'",
            "'ocds-591adf-6306553654'",
            "'ocds-591adf-5340385696'",
            "'ocds-591adf-8316012521'",
            "'ocds-591adf-9047594161'",
            "'ocds-591adf-9032374631'",
            "'ocds-591adf-2742549604'",
            "'ocds-591adf-4148272684'",
            "'ocds-591adf-7735907829'",
            "'ocds-591adf-6727520965'",
            "'ocds-591adf-4117471747'",
            "'ocds-591adf-3077783190'",
            "'ocds-591adf-0798060544'",
            "'ocds-591adf-7412498175'",
            "'ocds-591adf-4589954022'",
            "'ocds-591adf-9244547960'",
            "'ocds-591adf-8050719857'",
            "'ocds-591adf-6140683299'",
            "'ocds-591adf-7065597933'",
            "'ocds-591adf-5371335298'",
            "'ocds-591adf-2844229205'",
            "'ocds-591adf-5207837052'",
            "'ocds-591adf-7995387830'",
            "'ocds-591adf-2544048781'",
            "'ocds-591adf-3888978338'",
            "'ocds-591adf-7095844293'",
            "'ocds-591adf-2708910949'",
            "'ocds-591adf-4045279348'",
            "'ocds-591adf-3409067814'",
            "'ocds-591adf-7686604098'",
            "'ocds-591adf-3225181084'",
            "'ocds-591adf-8983838627'",
            "'ocds-591adf-4520801060'",
            "'ocds-591adf-6981945420'",
            "'ocds-591adf-8067495913'",
            "'ocds-591adf-4096114571'",
            "'ocds-591adf-2173315125'",
            "'ocds-591adf-2017855315'",
            "'ocds-591adf-4781400162'",
            "'ocds-591adf-1066265145'",
            "'ocds-591adf-2047322093'",
            "'ocds-591adf-8227735872'",
            "'ocds-591adf-6882835030'",
            "'ocds-591adf-2311498575'",
            "'ocds-591adf-4547748931'",
            "'ocds-591adf-6156982970'",
            "'ocds-591adf-7994492346'",
            "'ocds-591adf-7914711855'",
            "'ocds-591adf-5010898926'",
            "'ocds-591adf-1406058658'",
            "'ocds-591adf-0825996055'",
            "'ocds-591adf-5249980075'",
            "'ocds-591adf-3634074347'",
            "'ocds-591adf-6166504626'",
            "'ocds-591adf-9059582840'",
            "'ocds-591adf-7108863953'",
            "'ocds-591adf-7614421030'",
            "'ocds-591adf-8661619152'",
            "'ocds-591adf-3625584241'",
            "'ocds-591adf-0958706053'",
            "'ocds-591adf-9882572605'",
            "'ocds-591adf-4325879058'",
            "'ocds-591adf-7381425813'",
            "'ocds-591adf-4146254774'",
            "'ocds-591adf-3673363917'",
            "'ocds-591adf-1396308637'",
            "'ocds-591adf-1866175104'",
            "'ocds-591adf-0615688394'",
            "'ocds-591adf-7833840785'",
            "'ocds-591adf-7027266627'",
            "'ocds-591adf-8972728921'",
            "'ocds-591adf-8012481551'",
            "'ocds-591adf-1449749838'",
            "'ocds-591adf-3072801258'",
            "'ocds-591adf-6037819767'",
            "'ocds-591adf-8574782887'",
            "'ocds-591adf-3548977769'",
            "'ocds-591adf-0674820397'",
            "'ocds-591adf-7507177853'",
            "'ocds-591adf-5430893395'",
            "'ocds-591adf-3620192564'",
            "'ocds-591adf-4393949430'",
            "'ocds-591adf-9545015215'",
            "'ocds-591adf-5039016379'",
            "'ocds-591adf-2204850785'",
            "'ocds-591adf-0133006529'",
            "'ocds-591adf-0666759231'",
            "'ocds-591adf-3329485576'",
            "'ocds-591adf-8050122320'",
            "'ocds-591adf-2082925163'",
            "'ocds-591adf-7815278440'",
            "'ocds-591adf-7195121254'",
            "'ocds-591adf-0436269922'",
            "'ocds-591adf-5449066506'",
            "'ocds-591adf-5425826978'",
            "'ocds-591adf-3656382132'",
            "'ocds-591adf-5950867230'",
            "'ocds-591adf-0580884808'",
            "'ocds-591adf-2242549464'",
            "'ocds-591adf-2863343726'",
            "'ocds-591adf-9203487160'",
            "'ocds-591adf-2690958446'",
            "'ocds-591adf-3535909942'",
            "'ocds-591adf-0186155272'",
            "'ocds-591adf-7201039544'",
            "'ocds-591adf-7424645481'",
            "'ocds-591adf-7790937159'",
            "'ocds-591adf-9943940123'",
            "'ocds-591adf-5260130992'",
            "'ocds-591adf-7154941803'",
            "'ocds-591adf-8671556535'",
            "'ocds-591adf-6715069482'",
            "'ocds-591adf-2551039783'",
            "'ocds-591adf-0737922432'",
            "'ocds-591adf-3731111257'",
            "'ocds-591adf-7265674473'",
            "'ocds-591adf-3018985128'",
            "'ocds-591adf-1908638552'",
            "'ocds-591adf-3461265117'",
            "'ocds-591adf-1218777149'",
            "'ocds-591adf-2280130974'",
            "'ocds-591adf-8712575395'",
            "'ocds-591adf-1418218158'",
            "'ocds-591adf-4056017602'",
            "'ocds-591adf-7564355785'",
            "'ocds-591adf-5574561739'",
            "'ocds-591adf-1222059267'",
            "'ocds-591adf-9628620990'",
            "'ocds-591adf-1868196691'",
            "'ocds-591adf-8243911455'",
            "'ocds-591adf-5211219418'",
            "'ocds-591adf-8508068535'",
            "'ocds-591adf-3165370578'",
            "'ocds-591adf-2771299739'",
            "'ocds-591adf-2490786662'",
            "'ocds-591adf-3351387852'",
            "'ocds-591adf-6887180447'",
            "'ocds-591adf-3038447635'",
            "'ocds-591adf-2883843670'",
            "'ocds-591adf-5329544429'",
            "'ocds-591adf-4202565266'",
            "'ocds-591adf-1048610439'",
            "'ocds-591adf-4331087136'",
            "'ocds-591adf-1914033414'",
            "'ocds-591adf-0672361505'",
            "'ocds-591adf-6618669801'",
            "'ocds-591adf-3582640703'",
            "'ocds-591adf-9490650951'",
            "'ocds-591adf-4394856997'",
            "'ocds-591adf-7466392063'",
            "'ocds-591adf-5049785461'",
            "'ocds-591adf-2977107799'",
            "'ocds-591adf-5466703598'",
            "'ocds-591adf-5131065064'",
            "'ocds-591adf-2040789626'",
            "'ocds-591adf-3064372588'",
            "'ocds-591adf-3987071271'",
            "'ocds-591adf-1257853071'",
            "'ocds-591adf-8916749906'",
            "'ocds-591adf-8642732999'",
            "'ocds-591adf-0179041608'",
            "'ocds-591adf-0982191402'",
            "'ocds-591adf-6802401911'",
            "'ocds-591adf-9240147546'",
            "'ocds-591adf-0588923601'",
            "'ocds-591adf-2949159236'",
            "'ocds-591adf-2534404074'",
            "'ocds-591adf-2394038626'",
            "'ocds-591adf-5670442428'",
            "'ocds-591adf-1344520384'",
            "'ocds-591adf-7225608504'",
            "'ocds-591adf-0620475055'",
            "'ocds-591adf-0132736861'",
            "'ocds-591adf-0436742749'",
            "'ocds-591adf-2862910055'",
            "'ocds-591adf-3332230439'",
            "'ocds-591adf-4661870534'",
            "'ocds-591adf-7373772636'",
            "'ocds-591adf-7668743788'",
            "'ocds-591adf-2767980529'",
            "'ocds-591adf-3469180455'",
            "'ocds-591adf-7019279020'",
            "'ocds-591adf-2666101950'",
            "'ocds-591adf-2018725841'",
            "'ocds-591adf-5901007417'",
            "'ocds-591adf-4292786006'",
            "'ocds-591adf-9671561394'",
            "'ocds-591adf-0856116931'",
            "'ocds-591adf-4530655807'",
            "'ocds-591adf-8168589938'",
            "'ocds-591adf-7862766066'",
            "'ocds-591adf-7082676166'",
            "'ocds-591adf-1240639543'",
            "'ocds-591adf-1959450407'",
            "'ocds-591adf-7317531109'",
            "'ocds-591adf-4515839887'",
            "'ocds-591adf-8204510927'",
            "'ocds-591adf-5611369110'",
            "'ocds-591adf-0070222386'",
            "'ocds-591adf-5888157546'",
            "'ocds-591adf-7711595368'",
            "'ocds-591adf-2807774921'",
            "'ocds-591adf-7158316162'",
            "'ocds-591adf-6793116734'",
            "'ocds-591adf-7847639074'",
            "'ocds-591adf-8934817155'",
            "'ocds-591adf-9168209192'",
            "'ocds-591adf-4957567520'",
            "'ocds-591adf-1399550295'",
            "'ocds-591adf-2947803650'",
            "'ocds-591adf-5648230406'",
            "'ocds-591adf-9099026006'",
            "'ocds-591adf-2371985819'",
            "'ocds-591adf-4402690469'",
            "'ocds-591adf-8951550494'",
            "'ocds-591adf-3110313528'",
            "'ocds-591adf-7119806732'",
            "'ocds-591adf-2076816277'",
            "'ocds-591adf-6669092902'",
            "'ocds-591adf-0987434231'",
            "'ocds-591adf-3053805650'",
            "'ocds-591adf-9205170350'",
            "'ocds-591adf-8803822988'",
            "'ocds-591adf-2045508917'",
            "'ocds-591adf-3857833819'",
            "'ocds-591adf-3498512231'",
            "'ocds-591adf-7082927540'",
            "'ocds-591adf-9826942661'",
            "'ocds-591adf-4628168337'",
            "'ocds-591adf-1772757144'",
            "'ocds-591adf-0229286985'",
            "'ocds-591adf-9992750712'",
            "'ocds-591adf-2849121033'",
            "'ocds-591adf-2187423240'",
            "'ocds-591adf-6064437287'",
            "'ocds-591adf-5449890166'",
            "'ocds-591adf-8065650563'",
            "'ocds-591adf-6932850782'",
            "'ocds-591adf-1934720031'",
            "'ocds-591adf-0806975065'",
            "'ocds-591adf-5507758701'",
            "'ocds-591adf-9666004480'",
            "'ocds-591adf-3838178041'",
            "'ocds-591adf-7561219297'",
            "'ocds-591adf-4413175528'",
            "'ocds-591adf-6713867467'",
            "'ocds-591adf-6598130484'",
            "'ocds-591adf-6781259404'",
            "'ocds-591adf-4350636426'",
            "'ocds-591adf-7842827037'",
            "'ocds-591adf-2799343658'",
            "'ocds-591adf-0933270364'",
            "'ocds-591adf-1512044250'",
            "'ocds-591adf-8022638698'",
            "'ocds-591adf-0633235157'",
            "'ocds-591adf-4095844006'",
            "'ocds-591adf-5643845425'",
            "'ocds-591adf-9296734498'",
            "'ocds-591adf-6883838365'",
            "'ocds-591adf-1204180158'",
            "'ocds-591adf-3444971816'",
            "'ocds-591adf-8124967501'",
            "'ocds-591adf-3562649159'",
            "'ocds-591adf-8670893486'",
            "'ocds-591adf-0220455611'",
            "'ocds-591adf-4349392806'",
            "'ocds-591adf-7873211703'",
            "'ocds-591adf-1294469678'",
            "'ocds-591adf-6508189086'",
            "'ocds-591adf-2936586867'",
            "'ocds-591adf-1334869872'",
            "'ocds-591adf-6741641672'",
            "'ocds-591adf-0558400392'",
            "'ocds-591adf-2226373839'",
            "'ocds-591adf-7595843241'",
            "'ocds-591adf-5766259282'",
            "'ocds-591adf-2948121076'",
            "'ocds-591adf-7879070681'",
            "'ocds-591adf-4194579285'",
            "'ocds-591adf-1190485504'",
            "'ocds-591adf-2224148824'",
            "'ocds-591adf-4197748386'",
            "'ocds-591adf-6064388516'",
            "'ocds-591adf-1162006744'",
            "'ocds-591adf-6177380897'",
            "'ocds-591adf-9493847967'",
            "'ocds-591adf-5686606559'",
            "'ocds-591adf-8961020108'",
            "'ocds-591adf-7830746256'",
            "'ocds-591adf-1462370491'",
            "'ocds-591adf-6995105149'",
            "'ocds-591adf-7812949494'",
            "'ocds-591adf-6617860739'",
            "'ocds-591adf-1640052929'",
            "'ocds-591adf-4012564940'",
            "'ocds-591adf-6709215946'",
            "'ocds-591adf-6337376196'",
            "'ocds-591adf-3106914004'",
            "'ocds-591adf-3994139620'",
            "'ocds-591adf-8894996401'",
            "'ocds-591adf-8355847897'",
            "'ocds-591adf-5375757628'",
            "'ocds-591adf-2982358524'",
            "'ocds-591adf-0758573675'",
            "'ocds-591adf-9592875630'",
            "'ocds-591adf-7951823582'",
            "'ocds-591adf-1566200141'",
            "'ocds-591adf-9901918410'",
            "'ocds-591adf-6516952791'",
            "'ocds-591adf-1176824767'",
            "'ocds-591adf-5974071589'",
            "'ocds-591adf-2447793220'",
            "'ocds-591adf-3157792476'",
            "'ocds-591adf-1215008141'",
            "'ocds-591adf-3884407445'",
            "'ocds-591adf-3318813222'",
            "'ocds-591adf-8663619824'",
            "'ocds-591adf-6074368783'",
            "'ocds-591adf-3212388081'",
            "'ocds-591adf-4528038323'",
            "'ocds-591adf-8469575691'",
            "'ocds-591adf-4961667430'",
            "'ocds-591adf-0880142436'",
            "'ocds-591adf-3555599039'",
            "'ocds-591adf-4489318008'",
            "'ocds-591adf-8491015438'",
            "'ocds-591adf-9640020397'",
            "'ocds-591adf-8414414067'",
            "'ocds-591adf-8158976892'",
            "'ocds-591adf-9068210832'",
            "'ocds-591adf-9286326979'",
            "'ocds-591adf-0596575586'",
            "'ocds-591adf-0067521657'",
            "'ocds-591adf-3298339851'",
            "'ocds-591adf-0189763827'",
            "'ocds-591adf-3784178125'",
            "'ocds-591adf-9664217559'",
            "'ocds-591adf-4805466589'",
            "'ocds-591adf-2513873144'",
            "'ocds-591adf-4753829826'",
            "'ocds-591adf-8981515207'",
            "'ocds-591adf-2334311214'",
            "'ocds-591adf-9627607790'",
            "'ocds-591adf-1952003166'",
            "'ocds-591adf-6909938165'",
            "'ocds-591adf-5790191118'",
            "'ocds-591adf-7565412350'",
            "'ocds-591adf-7138107365'",
            "'ocds-591adf-7882465105'",
            "'ocds-591adf-8992041901'",
            "'ocds-591adf-5839767241'",
            "'ocds-591adf-1166689018'",
            "'ocds-591adf-6099566460'",
            "'ocds-591adf-4448493850'",
            "'ocds-591adf-7993880250'",
            "'ocds-591adf-2688570333'",
            "'ocds-591adf-8362504240'",
            "'ocds-591adf-2930796937'",
            "'ocds-591adf-0452980787'",
            "'ocds-591adf-5477046563'",
            "'ocds-591adf-7074027256'",
            "'ocds-591adf-7768716171'",
            "'ocds-591adf-4616542302'",
            "'ocds-591adf-7104921871'",
            "'ocds-591adf-6716589315'",
            "'ocds-591adf-6476275683'",
            "'ocds-591adf-3157711052'",
            "'ocds-591adf-7302954640'",
            "'ocds-591adf-1761043738'",
            "'ocds-591adf-0301330279'",
            "'ocds-591adf-5502075847'",
            "'ocds-591adf-2747761504'",
            "'ocds-591adf-3972422713'",
            "'ocds-591adf-5947912794'",
            "'ocds-591adf-6647672546'",
            "'ocds-591adf-2834590888'",
            "'ocds-591adf-5277394261'",
            "'ocds-591adf-7237200728'",
            "'ocds-591adf-3874532072'",
            "'ocds-591adf-3845912103'",
            "'ocds-591adf-8678868440'",
            "'ocds-591adf-5920721951'",
            "'ocds-591adf-0198796310'",
            "'ocds-591adf-6343053440'",
            "'ocds-591adf-7122812153'",
            "'ocds-591adf-1296215998'",
            "'ocds-591adf-5050631778'",
            "'ocds-591adf-6619573571'",
            "'ocds-591adf-5735524086'",
            "'ocds-591adf-7986289626'",
            "'ocds-591adf-8258374587'",
            "'ocds-591adf-6209593883'",
            "'ocds-591adf-0918711075'",
            "'ocds-591adf-5002996926'",
            "'ocds-591adf-8347903490'",
            "'ocds-591adf-5617783958'",
            "'ocds-591adf-4370598028'",
            "'ocds-591adf-4582194938'",
            "'ocds-591adf-3339136956'",
            "'ocds-591adf-9405332913'",
            "'ocds-591adf-3096170926'",
            "'ocds-591adf-7968305907'",
            "'ocds-591adf-9524747307'",
            "'ocds-591adf-9803917004'",
            "'ocds-591adf-5173064622'",
            "'ocds-591adf-1189831873'",
            "'ocds-591adf-9235697275'",
            "'ocds-591adf-7734540457'",
            "'ocds-591adf-8374292499'",
            "'ocds-591adf-6931884027'",
            "'ocds-591adf-5143717407'",
            "'ocds-591adf-1738863533'",
            "'ocds-591adf-4868375289'",
            "'ocds-591adf-9005998459'",
            "'ocds-591adf-8305944156'",
            "'ocds-591adf-3081289934'",
            "'ocds-591adf-6791296927'",
            "'ocds-591adf-4453903916'",
            "'ocds-591adf-7216315066'",
            "'ocds-591adf-1540020834'",
            "'ocds-591adf-4424676733'",
            "'ocds-591adf-0282645522'",
            "'ocds-591adf-4654148810'",
            "'ocds-591adf-2016664905'",
            "'ocds-591adf-6870000871'",
            "'ocds-591adf-3171785702'",
            "'ocds-591adf-7831399968'",
            "'ocds-591adf-8771416580'",
            "'ocds-591adf-6395827387'",
            "'ocds-591adf-5523323502'",
            "'ocds-591adf-9048890358'",
            "'ocds-591adf-1370335501'",
            "'ocds-591adf-9247577876'",
            "'ocds-591adf-6860122385'",
            "'ocds-591adf-6442097844'",
            "'ocds-591adf-5534548736'",
            "'ocds-591adf-0263135156'",
            "'ocds-591adf-4153161855'",
            "'ocds-591adf-6885577456'",
            "'ocds-591adf-0999271614'",
            "'ocds-591adf-3791594481'",
            "'ocds-591adf-0174649252'",
            "'ocds-591adf-2623185299'",
            "'ocds-591adf-5731033659'",
            "'ocds-591adf-4055296582'",
            "'ocds-591adf-3344561588'",
            "'ocds-591adf-3122094392'",
            "'ocds-591adf-7940214801'",
            "'ocds-591adf-8754224127'",
            "'ocds-591adf-6639553481'",
            "'ocds-591adf-1071350127'",
            "'ocds-591adf-3223591068'",
            "'ocds-591adf-6115191223'",
            "'ocds-591adf-6075316030'",
            "'ocds-591adf-4207666738'",
            "'ocds-591adf-9593858126'",
            "'ocds-591adf-0374172483'",
            "'ocds-591adf-1800799116'",
            "'ocds-591adf-2676416271'",
            "'ocds-591adf-1212784561'",
            "'ocds-591adf-3434080334'",
            "'ocds-591adf-8344502322'",
            "'ocds-591adf-3822580249'",
            "'ocds-591adf-1304252678'",
            "'ocds-591adf-5019959439'",
            "'ocds-591adf-4335802553'",
            "'ocds-591adf-3896093794'",
            "'ocds-591adf-1718452998'",
            "'ocds-591adf-6264516286'",
            "'ocds-591adf-0281884724'",
            "'ocds-591adf-3762562951'",
            "'ocds-591adf-5515805540'",
            "'ocds-591adf-5231394526'",
            "'ocds-591adf-2104563521'",
            "'ocds-591adf-0290550345'",
            "'ocds-591adf-3222625916'",
            "'ocds-591adf-3060927282'",
            "'ocds-591adf-5859317465'",
            "'ocds-591adf-3604158543'",
            "'ocds-591adf-0907900772'",
            "'ocds-591adf-4772500162'",
            "'ocds-591adf-6465961214'",
            "'ocds-591adf-6351654111'",
            "'ocds-591adf-0585175110'",
            "'ocds-591adf-2272060363'",
            "'ocds-591adf-8992507217'",
            "'ocds-591adf-8935235821'",
            "'ocds-591adf-6921063233'",
            "'ocds-591adf-6366754885'",
        ];
        $ocids      = implode(",", $ocids);
        $select_sql = "select id,metadata from contracts where metadata->>'open_contracting_id' in ($ocids) ";

        $contracts   = DB::select($select_sql);
        $metadata_bk = [];

        foreach ($contracts as $contract) {
            $id               = $contract->id;
            $metadata_bk[$id] = $contract->metadata;
        }

        file_put_contents('metadata_bk.json', json_encode($metadata_bk));
    }

    public function restoreMetadata()
    {
        $metadata = json_decode(file_get_contents('metadata_bk.json'), true);

        foreach ($metadata as $contract_id => $metadatum) {
            $update_sql = "update contracts set metadata='$metadatum' where id=$contract_id";
            DB::statement($update_sql);
        }
    }

    public function getLicenseTitleOcids()
    {
        return [
            'ocds-591adf-2901517891' => 'Aynak Copper',
            'ocds-591adf-3433899670' => 'Western Garmak Coal Project',
            'ocds-591adf-1878297445' => 'Amu Darya Basin EPSC',
            'ocds-591adf-4977579802' => 'Aynak Copper',
            'ocds-591adf-9771948110' => 'Qara Zaghan Gold Project',
            'ocds-591adf-7451096540' => 'Aynak Copper',
            'ocds-591adf-9736395382' => 'Herat Cement',
            'ocds-591adf-6872304960' => 'PSC',
            'ocds-591adf-7493980687' => 'PSC',
            'ocds-591adf-4571322578' => 'PSC',
            'ocds-591adf-0014595575' => 'Production Sharing Contract between Sociedade Nacional de Combustiveis de Angola Empresa Publica - (Sonangol, E.P.) and CIE Angola Block 20 LTD., Sonangol Pesquisa e Producao, S.A., BP Exploration Angola (Kwanza Benguela) Limited, China Sonangol International Holding Limited in the Area of Block 20/11',
            'ocds-591adf-7534708827' => 'JPDA 06-105',
            'ocds-591adf-5685445366' => 'Timor Sea Designated Authority for the Joint Petroleum Development Area - Production Sharing Contract JPDA 03-13',
            'ocds-591adf-1717538716' => 'JPDA 06-101(A)',
            'ocds-591adf-9499174502' => 'JPDA 06-103',
            'ocds-591adf-7120819444' => 'JPDA 06-102',
            'ocds-591adf-9686067103' => 'Ndian River',
            'ocds-591adf-5424836511' => 'Kombe-Nsepe',
            'ocds-591adf-0902890112' => 'Da Shi Qiao Gas Pipeline Project',
            'ocds-591adf-5586820610' => 'Put-30',
            'ocds-591adf-0228355919' => 'Tiple',
            'ocds-591adf-9052399699' => 'Fenix block',
            'ocds-591adf-6537883697' => 'SN 9',
            'ocds-591adf-3831230606' => 'Esperanza',
            'ocds-591adf-1949443621' => 'La Paloma',
            'ocds-591adf-8863600732' => 'Puntero',
            'ocds-591adf-6064693869' => 'Antares',
            'ocds-591adf-7303551872' => 'Block Alea 1848-A',
            'ocds-591adf-4709701176' => 'Merecure',
            'ocds-591adf-4566317163' => 'Putumayo Basin',
            'ocds-591adf-2438512631' => 'Putumayo Basin',
            'ocds-591adf-3261751421' => 'Platanillo Block',
            'ocds-591adf-7159971800' => 'VIM 21',
            'ocds-591adf-8100805971' => 'VIM 5',
            'ocds-591adf-0939306330' => 'Block Alea 1947-C',
            'ocds-591adf-5906019396' => 'Niscota Block',
            'ocds-591adf-7757139298' => 'Midas',
            'ocds-591adf-9214264709' => 'Put-12',
            'ocds-591adf-1600391941' => 'Mer tres profonde nord',
            'ocds-591adf-8383008052' => 'Haute Mer C',
            'ocds-591adf-4388708715' => 'Kouakouala',
            'ocds-591adf-3232099165' => 'Mer tres profonde sud',
            'ocds-591adf-2843880701' => "Société d'Exploitation Minière de MUSOSHI SA",
            'ocds-591adf-2009144457' => "Société d'Exploitation Minière de MUSOSHI SA",
            'ocds-591adf-8856845352' => 'Pueblo Viejo',
            'ocds-591adf-3440211455' => 'Pueblo Viejo',
            'ocds-591adf-7546727432' => 'Pueblo Viejo',
            'ocds-591adf-3523025073' => 'West Cape Three Points Block 2',
            'ocds-591adf-3697833438' => 'South Deepwater Tano Block',
            'ocds-591adf-2182826308' => 'South West Saltpond',
            'ocds-591adf-6899414447' => 'Offshore Cape Three Points South',
            'ocds-591adf-3558756842' => 'Offshore South West Tano',
            'ocds-591adf-8653902484' => 'Deepwater Cape Three Points West Offshore',
            'ocds-591adf-6688383797' => 'East Cape Three Points',
            'ocds-591adf-2899956853' => 'West Cape Three Points Block 2',
            'ocds-591adf-2009529955' => 'Expanded Shallow Water Tano',
            'ocds-591adf-8260356369' => 'Shallow Water Cape Three Point',
            'ocds-591adf-8486691222' => 'East Keta Offshore',
            'ocds-591adf-2548058701' => 'South West Saltpond',
            'ocds-591adf-1707203194' => 'Expanded Shallow Water Tano',
            'ocds-591adf-9201328103' => 'Shallow Water Cape Three Point',
            'ocds-591adf-0701596709' => 'Central Tano Block',
            'ocds-591adf-5573742588' => 'Offshore South West Tano',
            'ocds-591adf-9224709687' => 'Offshore Cape Three Points Basin',
            'ocds-591adf-1774464193' => 'West Cape Three Points Block',
            'ocds-591adf-8101290201' => 'East Keta Offshore',
            'ocds-591adf-2262702985' => 'South Deepwater Tano Block',
            'ocds-591adf-5642119190' => 'Deepwater Tano',
            'ocds-591adf-8580866972' => 'Onshore Offshore Keta Delta Block',
            'ocds-591adf-4037053965' => 'Offshore Cape Three Points Basin',
            'ocds-591adf-5958369981' => 'West Cape Three Points Block',
            'ocds-591adf-2887938326' => 'Cape Three Points Block 4',
            'ocds-591adf-0736286219' => 'Central Tano Block',
            'ocds-591adf-9173482252' => 'Bel Air Mining',
            'ocds-591adf-6800054916' => 'Kabata',
            'ocds-591adf-8052316867' => 'Friguia',
            'ocds-591adf-3988663064' => 'COBAD',
            'ocds-591adf-3612985124' => 'SAG',
            'ocds-591adf-0855371527' => 'GAC',
            'ocds-591adf-7468099467' => 'CGB',
            'ocds-591adf-0921285528' => 'GAC',
            'ocds-591adf-8820234307' => 'SAG',
            'ocds-591adf-5003575897' => 'AMC',
            'ocds-591adf-1653821228' => 'SMFG',
            'ocds-591adf-0725247752' => 'SMFG',
            'ocds-591adf-2269582291' => 'GAC',
            'ocds-591adf-8033884916' => 'AMC',
            'ocds-591adf-6904959112' => 'CBG, GAC',
            'ocds-591adf-9016560520' => 'AMC',
            'ocds-591adf-8606873912' => 'CDM',
            'ocds-591adf-3240717431' => 'Friguia',
            'ocds-591adf-2768083178' => 'Lefa',
            'ocds-591adf-4483792440' => 'GAC',
            'ocds-591adf-9669096564' => 'GAC',
            'ocds-591adf-3437474455' => 'SEMAFO',
            'ocds-591adf-7227443979' => 'Raffinerie d’Alumine de Débélé et de la Mine de Bauxite de Garafiri',
            'ocds-591adf-9115310768' => 'SAG',
            'ocds-591adf-7212601903' => 'COBAD',
            'ocds-591adf-2927931073' => 'AMC',
            'ocds-591adf-0925073922' => 'Simandou 3&4',
            'ocds-591adf-6237477089' => 'Simandou 3&4',
            'ocds-591adf-8078738904' => 'Friguia',
            'ocds-591adf-0616817020' => 'GAC',
            'ocds-591adf-7087857453' => 'AMC',
            'ocds-591adf-9642814017' => 'IMD - Convention Annulee',
            'ocds-591adf-8001318248' => 'COBAD',
            'ocds-591adf-6822122534' => 'Friguia',
            'ocds-591adf-4170419959' => 'Friguia',
            'ocds-591adf-5307724746' => 'Friguia',
            'ocds-591adf-2774693704' => 'GAC',
            'ocds-591adf-4278971256' => 'CBG',
            'ocds-591adf-7040396335' => 'Lefa',
            'ocds-591adf-0628604071' => 'GAC',
            'ocds-591adf-0621960018' => 'CBK',
            'ocds-591adf-9399783777' => 'Friguia',
            'ocds-591adf-3303812608' => 'CBK',
            'ocds-591adf-2396569119' => 'Simandou 3&4',
            'ocds-591adf-2461556641' => 'SBDT',
            'ocds-591adf-2401833289' => 'Lefa',
            'ocds-591adf-0484705559' => 'CBG',
            'ocds-591adf-9145827406' => 'SAG',
            'ocds-591adf-1601150827' => 'Kalia',
            'ocds-591adf-1880406409' => 'Trik',
            'ocds-591adf-2707180369' => 'SAG',
            'ocds-591adf-7382402375' => 'Simandou 1&3',
            'ocds-591adf-2372332812' => 'Friguia',
            'ocds-591adf-2159924071' => 'SMFG',
            'ocds-591adf-9201648630' => 'Lefa',
            'ocds-591adf-6749062117' => 'AMC',
            'ocds-591adf-2458536233' => 'CBK',
            'ocds-591adf-6187166467' => 'Simandou',
            'ocds-591adf-9593916956' => 'Kalia',
            'ocds-591adf-4258574838' => 'Bel Air Mining',
            'ocds-591adf-3032468364' => 'CBK',
            'ocds-591adf-4449736548' => 'Friguia',
            'ocds-591adf-2096071243' => 'CBK',
            'ocds-591adf-9112931197' => 'CBK',
            'ocds-591adf-6562585363' => 'CBG',
            'ocds-591adf-6677014369' => 'SMFG',
            'ocds-591adf-5738530463' => 'Simandou',
            'ocds-591adf-6179905611' => 'Kalia',
            'ocds-591adf-4942070282' => 'Telimele - Convention Annulee',
            'ocds-591adf-7718448031' => 'GAC',
            'ocds-591adf-2771733988' => 'CPI',
            'ocds-591adf-8002546923' => 'Bel Air Mining',
            'ocds-591adf-0677357016' => 'Kanuku',
            'ocds-591adf-1215811760' => 'Kanuku',
            'ocds-591adf-7941034659' => 'Roraima',
            'ocds-591adf-4256816203' => 'Mahaica-Mahaicony',
            'ocds-591adf-3045742924' => 'Bawean',
            'ocds-591adf-7202085028' => 'Block CI-602',
            'ocds-591adf-9188516395' => 'Block CI-603',
            'ocds-591adf-1677413274' => 'Block CI-707',
            'ocds-591adf-6801345335' => 'Block CI-526',
            'ocds-591adf-0218900327' => 'Block CI-708',
            'ocds-591adf-2656241484' => 'Block 11A',
            'ocds-591adf-1022255942' => 'Block L27',
            'ocds-591adf-4257477346' => 'Block 9',
            'ocds-591adf-0114673626' => 'Block 4',
            'ocds-591adf-6207349867' => 'Western Cluster Project',
            'ocds-591adf-6387856838' => 'Bong Project',
            'ocds-591adf-3970511656' => 'Block C13',
            'ocds-591adf-8615350441' => 'Bloc 25',
            'ocds-591adf-7284594451' => 'Block C12',
            'ocds-591adf-9160512674' => 'Block C6',
            'ocds-591adf-0048000024' => 'Block C8',
            'ocds-591adf-3533551205' => 'Ébano',
            'ocds-591adf-0773715511' => 'Altamira',
            'ocds-591adf-4214910059' => 'Amatitlán',
            'ocds-591adf-5942699559' => 'Ébano-Pánuco-Cacalilao (contiene el área contractual Altamira)',
            'ocds-591adf-3624750089' => 'San Andrés',
            'ocds-591adf-5458382622' => 'Pitepec',
            'ocds-591adf-2288241575' => 'Humapa',
            'ocds-591adf-6181255605' => 'Soledad',
            'ocds-591adf-7411546128' => 'Tierra Blanca',
            'ocds-591adf-1038726743' => 'Miahuapan',
            'ocds-591adf-3071501652' => 'Pánuco',
            'ocds-591adf-9373760375' => 'Carrizo',
            'ocds-591adf-2032447165' => 'Miquetla',
            'ocds-591adf-9572710160' => 'Arenque',
            'ocds-591adf-3843755960' => 'Lote 88',
            'ocds-591adf-1624878521' => 'Lote 126',
            'ocds-591adf-4399564244' => 'Lote 95',
            'ocds-591adf-3906687826' => 'Lote 161',
            'ocds-591adf-0461174209' => 'Lote 127',
            'ocds-591adf-2944436063' => 'Proyecto de contrato the Licencia para la Exploración y Explotación de Hidrocarburos en el lote 188',
            'ocds-591adf-3407308771' => 'Lote 137',
            'ocds-591adf-4109359446' => 'Lote 19',
            'ocds-591adf-1806088707' => 'Lote 135',
            'ocds-591adf-3315096248' => 'Lote 138',
            'ocds-591adf-3723126760' => 'Lote 31C',
            'ocds-591adf-1108632570' => 'Lote 39',
            'ocds-591adf-6743049102' => 'Lote 58',
            'ocds-591adf-3314121968' => 'Lote 144',
            'ocds-591adf-2245683705' => 'Lote 145',
            'ocds-591adf-1195303977' => 'Lote 131',
            'ocds-591adf-9776014613' => 'Lote 57',
            'ocds-591adf-8938335769' => 'Lote 133',
            'ocds-591adf-8715839071' => 'Lote 31-E',
            'ocds-591adf-8192707206' => 'Lote 56',
            'ocds-591adf-2409086627' => 'Lote 8',
            'ocds-591adf-8237158457' => 'Lote 76',
            'ocds-591adf-9753464836' => 'Lote 95',
            'ocds-591adf-0292784834' => 'Lote 156',
            'ocds-591adf-9620382408' => 'Lote 183',
            'ocds-591adf-1900505079' => 'Rio Tuba Nickel Project',
            'ocds-591adf-6297501032' => 'Malampaya Deep Water Gas-to-Power Project',
            'ocds-591adf-0640514048' => 'Tagana-an Nickel Project',
            'ocds-591adf-0083596491' => 'Adlay Mining Project',
            'ocds-591adf-8082857980' => 'Guinabon Nickel Project',
            'ocds-591adf-9564139780' => 'Cagdianao Nickel Project',
            'ocds-591adf-5963366284' => 'Guinabon Nickel Project',
            'ocds-591adf-2327028479' => 'Paracale Gold Project',
            'ocds-591adf-8486546892' => 'Siana Gold Project',
            'ocds-591adf-2393158296' => 'Masbate Gold Project',
            'ocds-591adf-4379373179' => 'Toledo Copper Project     ',
            'ocds-591adf-9194346883' => 'Elluvial Chromite Mining and Concentration Project',
            'ocds-591adf-7705086445' => 'Leyte Magnetite Project',
            'ocds-591adf-1314860465' => 'Masbate Gold Project',
            'ocds-591adf-2939708932' => 'Siana Gold Project',
            'ocds-591adf-4966736928' => 'Tandawa Nickel Project',
            'ocds-591adf-1424338090' => 'Berong Nickel Project',
            'ocds-591adf-8564429744' => 'Tagana-an Nickel Project',
            'ocds-591adf-9581547683' => 'Cantilan Nickel Project',
            'ocds-591adf-6626006637' => 'Co‐o Gold Project',
            'ocds-591adf-6696511613' => 'Nonoc Nickel Project',
            'ocds-591adf-5233340063' => 'Toronto and Pulot Nickel Projects',
            'ocds-591adf-5444707467' => 'Libertad Gas Field',
            'ocds-591adf-4357346394' => 'Libertad Gas Field',
            'ocds-591adf-4197629132' => 'Taganito Nickel Project',
            'ocds-591adf-2593066531' => 'Apex Maco Operation',
            'ocds-591adf-0633880291' => 'Taganito Nickel Project',
            'ocds-591adf-2792396017' => 'Didipio Project',
            'ocds-591adf-0056582197' => 'Rapu Rapu Polymetallic Project',
            'ocds-591adf-0781051765' => 'Tubay Nickel-Cobalt Mining Project',
            'ocds-591adf-9208160921' => 'Dinagat Chromite Project',
            'ocds-591adf-0115707939' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-4283019703' => 'Masbate Gold Project',
            'ocds-591adf-8380393006' => 'Urbiztondo Nickel Project',
            'ocds-591adf-9203793811' => 'Toronto and Pulot Nickel Projects',
            'ocds-591adf-6155018199' => 'Siana Gold Project',
            'ocds-591adf-1623268944' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-5976674032' => 'Tandawa Nickel Project',
            'ocds-591adf-6484217517' => 'Victoria Gold Project',
            'ocds-591adf-6012864853' => 'Dahican Nickel Project',
            'ocds-591adf-8368260751' => 'Bel-at Nickel Project',
            'ocds-591adf-9844860754' => 'Homonhon Chromite Project',
            'ocds-591adf-3824268091' => 'Carrascal Nickel Project',
            'ocds-591adf-3498737512' => 'Masbate Gold Project',
            'ocds-591adf-7265722663' => 'Rio Tuba Nickel Project',
            'ocds-591adf-1713256223' => 'Siana Gold Project',
            'ocds-591adf-4141862922' => 'Bel-at Nickel Project',
            'ocds-591adf-9308829862' => 'Leyte Magnetite Project',
            'ocds-591adf-7888022700' => 'HY Nickel-Chromite Project',
            'ocds-591adf-0617728514' => 'Camachin Iron Ore Mining Project',
            'ocds-591adf-0060618096' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-3741414378' => 'Toledo Copper Project',
            'ocds-591adf-6645823668' => 'Canatuan Mining Project',
            'ocds-591adf-3363161868' => 'Sta. Cruz Candelaria Project',
            'ocds-591adf-1946181466' => 'Carrascal Nickel Project',
            'ocds-591adf-8908710232' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-5792510988' => 'Cagdianao Nickel Project',
            'ocds-591adf-0431745646' => 'Homonhon Chromite Project',
            'ocds-591adf-6975899734' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-9119482338' => 'Sta. Cruz Candelaria Project   ',
            'ocds-591adf-1394673190' => 'Canatuan Mining Project',
            'ocds-591adf-8549514639' => 'Victoria Gold Project',
            'ocds-591adf-9975874698' => 'HY Nickel-Chromite Project',
            'ocds-591adf-2917943809' => 'Dinagat Chromite Project',
            'ocds-591adf-5185619420' => 'Apex Maco Operation',
            'ocds-591adf-2689260212' => 'Malampaya Deep Water Gas-to-Power Project',
            'ocds-591adf-0421393552' => 'Tubay Nickel-Cobalt Mining Project',
            'ocds-591adf-2719937589' => 'Co‐o Gold Project',
            'ocds-591adf-3751727488' => 'Berong Nickel Project',
            'ocds-591adf-0742047974' => 'Toledo Copper Project ',
            'ocds-591adf-2437234184' => 'Cagdianao Nickel Project',
            'ocds-591adf-3538681265' => 'Padcal Copper-Gold Operation',
            'ocds-591adf-9492198891' => 'Toledo Copper Project',
            'ocds-591adf-3156014237' => 'Nonoc Nickel Project',
            'ocds-591adf-1737251309' => 'Masbate Gold Project',
            'ocds-591adf-6091789645' => 'Libertad Gas Field',
            'ocds-591adf-9374734729' => 'Rapu Rapu Polymetallic Project',
            'ocds-591adf-2181723079' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-8757641437' => 'Rapu-Rapu Polymetallic Project',
            'ocds-591adf-4013712466' => 'Dahican Nickel Project',
            'ocds-591adf-4621402481' => 'Padcal Copper-Gold Operation',
            'ocds-591adf-1333562032' => 'Urbiztondo Nickel Project',
            'ocds-591adf-1265870384' => 'Masbate Gold Project',
            'ocds-591adf-4659660417' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-5502782183' => 'Adlay Mining Project',
            'ocds-591adf-5920713915' => 'Elluvial Chromite Mining and Concentration Project',
            'ocds-591adf-8808867910' => 'Rapu-Rapu Polymetallic Project',
            'ocds-591adf-5307554205' => 'Cantilan Nickel Project',
            'ocds-591adf-1658238869' => 'Paracale Gold Project',
            'ocds-591adf-3848739629' => 'Sta. Cruz Nickel Project',
            'ocds-591adf-6822686270' => 'Cagdianao Nickel Project',
            'ocds-591adf-7195323379' => 'Block 5',
            'ocds-591adf-8951344827' => 'Rufisque Offshore Profond block',
            'ocds-591adf-0669110404' => 'Koidu Kimberlite Project',
            'ocds-591adf-1599142675' => 'Dharoor-Valley',
            'ocds-591adf-0628187731' => 'Dharoor Valley',
            'ocds-591adf-3212507685' => 'Songo Songo gas to electricity project',
            'ocds-591adf-0075411163' => 'Songo Songo Gas to Electricity Project',
            'ocds-591adf-4949559443' => 'JPDA 06-101(A)',
            'ocds-591adf-2108050504' => 'Timor Sea Designated Authority for the Joint Petroleum Development Area - Production Sharing Contract JPDA 03-13',
            'ocds-591adf-3398398402' => 'S-06-01',
            'ocds-591adf-3291421710' => 'JPDA 06-102',
            'ocds-591adf-2074895725' => 'S-06-02',
            'ocds-591adf-2761455866' => 'S-06-04',
            'ocds-591adf-5929939263' => 'S-06-06',
            'ocds-591adf-3025980849' => 'S-06-03',
            'ocds-591adf-4744093490' => 'JPDA 06-105',
            'ocds-591adf-6600480891' => 'JPDA 06-103',
            'ocds-591adf-5773057513' => 'S-06-05',
            'ocds-591adf-6886084452' => 'Borj El Khadra',
            'ocds-591adf-8376312952' => 'Sfax Kerkennah',
            'ocds-591adf-7068014216' => 'Le Kef',
            'ocds-591adf-8152849352' => 'Amilcar',
            'ocds-591adf-5579407168' => 'Amilcar',
            'ocds-591adf-4789221573' => 'Golf de Gabes',
            'ocds-591adf-9855163235' => 'Borj El Khadra Sud',
            'ocds-591adf-2610580215' => 'Medenine',
            'ocds-591adf-1730479724' => 'Douz',
            'ocds-591adf-1319646931' => 'Amilcar',
            'ocds-591adf-9614633875' => 'Borj El Khadra Sud',
            'ocds-591adf-4126162216' => 'Borj El Khadra Sud',
            'ocds-591adf-2173819973' => 'Kerkouane',
            'ocds-591adf-7769269550' => 'Amilcar',
            'ocds-591adf-8785384336' => 'Nord Medenine',
            'ocds-591adf-4824016790' => 'Makther',
            'ocds-591adf-4478492664' => 'Sud Remada',
            'ocds-591adf-7496893655' => 'Bargou',
            'ocds-591adf-4292503746' => 'Bargou',
            'ocds-591adf-5838344155' => 'Amilcar',
            'ocds-591adf-5432334519' => 'Nord Medenine',
            'ocds-591adf-6328981421' => 'Makther',
            'ocds-591adf-8254959256' => 'El Jem',
            'ocds-591adf-6884844688' => 'Amilcar',
            'ocds-591adf-2175879797' => '7th of November / Joint Oil Block',
            'ocds-591adf-9914903289' => 'Borj El Khadra',
            'ocds-591adf-4290386059' => 'Marin Centre-Oriental',
            'ocds-591adf-1454401801' => 'Ksar Hadada',
            'ocds-591adf-8090982720' => 'Zarat',
            'ocds-591adf-9181613139' => 'Zaafrane',
            'ocds-591adf-0749759512' => 'El Fahs',
            'ocds-591adf-8621709918' => 'Zarat',
            'ocds-591adf-2556454498' => 'Permis SFAX Offshore',
            'ocds-591adf-6051705587' => 'Douz',
            'ocds-591adf-1503083799' => 'Bargou',
            'ocds-591adf-5344601220' => 'Borj El Khadra',
            'ocds-591adf-5828832149' => 'Cap Bon Golfe de Hammamet',
            'ocds-591adf-8501977034' => 'Chorbane',
            'ocds-591adf-6541551918' => 'Cap-Bon',
            'ocds-591adf-6477149327' => 'Convention Gabes Jerba Ben Gardane',
            'ocds-591adf-8597696414' => 'Douz',
            'ocds-591adf-2146344132' => 'El Fahs',
            'ocds-591adf-6098007922' => 'Jelma',
            'ocds-591adf-6955060610' => 'Jenein Centre',
            'ocds-591adf-9528502089' => 'Jenein Sud',
            'ocds-591adf-7427379098' => 'Borj El Khadra Sud',
            'ocds-591adf-6173093934' => 'Borj El Khadra Sud',
            'ocds-591adf-4427784387' => 'Kairouan Nord',
            'ocds-591adf-5527552623' => 'Kerkouane',
            'ocds-591adf-0237904720' => 'Mahdia',
            'ocds-591adf-3510451430' => 'Marin Centre-Oriental',
            'ocds-591adf-0567930699' => 'Nord des Chotts',
            'ocds-591adf-9380721319' => 'Medenine',
            'ocds-591adf-1619101625' => 'Convention Chaal',
            'ocds-591adf-5890345809' => 'Sfax Kerkennah',
            'ocds-591adf-1362820655' => 'Permis du Sud',
            'ocds-591adf-7331431087' => 'Nord Medenine',
            'ocds-591adf-5841633425' => 'Zaafrane',
            'ocds-591adf-7208749619' => 'Zaafrane',
            'ocds-591adf-1414882346' => 'Zarat',
            'ocds-591adf-4342783405' => 'Zarat',
            'ocds-591adf-4764118287' => 'Zarat',
            'ocds-591adf-9279619856' => 'Borj El Khadra',
            'ocds-591adf-5317994994' => 'Cap Bon Marin',
            'ocds-591adf-1974765349' => 'Bir Aouine',
            'ocds-591adf-3149266819' => 'Centre Nord',
            'ocds-591adf-5006403470' => 'Convention Gabes Jerba Ben Gardane',
            'ocds-591adf-6223535439' => 'Convention Kerkenah Ouest',
            'ocds-591adf-0330524527' => 'Djebel Oust',
            'ocds-591adf-5770617196' => 'El Fahs',
            'ocds-591adf-3711903879' => 'Douz',
            'ocds-591adf-1331537774' => 'El Fahs',
            'ocds-591adf-5573905643' => 'El Jem',
            'ocds-591adf-6464647576' => 'Jelma',
            'ocds-591adf-6801171062' => 'El Jem',
            'ocds-591adf-2666296340' => 'Grombalia',
            'ocds-591adf-0329969692' => 'Jelma',
            'ocds-591adf-1569974362' => 'Jenein Centre',
            'ocds-591adf-9883087113' => 'Kaboudia',
            'ocds-591adf-7457426940' => 'Ksar Hadada',
            'ocds-591adf-8829493091' => 'Kaboudia',
            'ocds-591adf-0683872141' => 'Le Kef',
            'ocds-591adf-9769622045' => 'Kebili',
            'ocds-591adf-7280300169' => 'Makther',
            'ocds-591adf-6495590428' => 'Marin Centre-Oriental',
            'ocds-591adf-3808032469' => 'Makther',
            'ocds-591adf-9606270401' => 'Zarzis',
            'ocds-591adf-0241909737' => 'Nord des Chotts',
            'ocds-591adf-0624472233' => 'Permis Araifa',
            'ocds-591adf-0304700055' => 'Permis Araifa',
            'ocds-591adf-7759543712' => 'Sfax Kerkennah',
            'ocds-591adf-1502564676' => 'Permis du Sud',
            'ocds-591adf-0864171697' => 'Permis du Sud',
            'ocds-591adf-3073179050' => 'Zaafrane',
            'ocds-591adf-4125287033' => 'Zaafrane',
            'ocds-591adf-4679728293' => 'Permis SFAX Offshore',
            'ocds-591adf-0859255815' => 'Zarat',
            'ocds-591adf-7856017364' => 'Zarat',
            'ocds-591adf-1536446241' => 'Zarat',
            'ocds-591adf-4320292802' => 'Nord Medenine',
            'ocds-591adf-4645641333' => 'Hasdrubal',
            'ocds-591adf-9678147772' => 'Permis du Sud',
            'ocds-591adf-8619369763' => 'Zaafrane',
            'ocds-591adf-2653125767' => 'Zarat',
            'ocds-591adf-8994683757' => 'Douz',
            'ocds-591adf-9585889698' => 'Kebili',
            'ocds-591adf-0432750635' => 'Amilcar',
            'ocds-591adf-1646757982' => 'Borj El Khadra',
            'ocds-591adf-6901069067' => 'Zarat',
            'ocds-591adf-4123523800' => 'Nord Medenine',
            'ocds-591adf-2914071981' => 'Bargou',
            'ocds-591adf-9306527580' => 'Anaguid',
            'ocds-591adf-7568305856' => 'Borj El Khadra',
            'ocds-591adf-0885560786' => 'Borj El Khadra',
            'ocds-591adf-7146752013' => 'Zarat',
            'ocds-591adf-0429102665' => 'Zarat',
            'ocds-591adf-8091377248' => 'Bir Aouine',
            'ocds-591adf-0195471540' => 'Borj El Khadra',
            'ocds-591adf-2354828177' => 'Jenein Nord',
            'ocds-591adf-3419068261' => 'Permis SFAX Offshore',
            'ocds-591adf-5374338853' => 'El Borma',
            'ocds-591adf-9113476125' => 'Douz',
            'ocds-591adf-6884489067' => 'Kerkouane',
            'ocds-591adf-9611382739' => 'Sfax Kerkennah',
            'ocds-591adf-8560734374' => 'Permis SFAX Offshore',
            'ocds-591adf-4525917050' => 'Kebili',
            'ocds-591adf-2015681219' => 'Borj El Khadra',
            'ocds-591adf-7433399809' => 'Borj El Khadra',
            'ocds-591adf-4284433444' => 'Sfax Kerkennah',
            'ocds-591adf-8913086018' => 'Cap Bon Golfe de Hammamet',
            'ocds-591adf-1594835240' => 'Kaboudia',
            'ocds-591adf-7876582028' => 'Borj El Khadra Sud',
            'ocds-591adf-5604659447' => 'Mahdia',
            'ocds-591adf-1406548730' => 'Jenein Centre',
            'ocds-591adf-2720415109' => 'Amilcar',
            'ocds-591adf-4901961894' => 'Zarat',
            'ocds-591adf-0024730431' => 'Zarat',
            'ocds-591adf-2236979409' => 'Bouhajla',
            'ocds-591adf-4837532873' => 'Cap Bon Golfe de Hammamet',
            'ocds-591adf-4256986882' => 'Cap Bon Marin',
            'ocds-591adf-4484816003' => 'Enfidha',
            'ocds-591adf-0818116201' => 'Marin Centre-Oriental',
            'ocds-591adf-3986673209' => 'Zarzis',
            'ocds-591adf-5187167862' => 'Marin Golfe de Hammemet',
            'ocds-591adf-8309911494' => 'Cap-Bon',
            'ocds-591adf-1071407854' => 'Nord Medenine',
            'ocds-591adf-8350674630' => 'Nord des Chotts',
            'ocds-591adf-2565867185' => 'Bouhajla',
            'ocds-591adf-7097600866' => 'Convention Gabes Jerba Ben Gardane',
            'ocds-591adf-4975594142' => 'Permis du Sud',
            'ocds-591adf-8700327622' => 'Jenein Sud',
            'ocds-591adf-3234824350' => 'Amilcar',
            'ocds-591adf-1596285788' => 'Anaguid',
            'ocds-591adf-3417762881' => 'Anaguid',
            'ocds-591adf-0475780199' => 'Kerkouane',
            'ocds-591adf-8521934512' => 'Permis Araifa',
            'ocds-591adf-7121914031' => 'Chorbane',
            'ocds-591adf-0469490625' => 'Douz',
            'ocds-591adf-6554465329' => 'Amilcar',
            'ocds-591adf-8581364151' => 'Amilcar',
            'ocds-591adf-4180129773' => 'Amilcar',
            'ocds-591adf-5901301414' => 'Permis-Chaal',
            'ocds-591adf-5465882683' => 'Borj El Khadra',
            'ocds-591adf-8476846603' => 'Borj El Khadra',
            'ocds-591adf-7097753035' => 'Borj El Khadra',
            'ocds-591adf-7558517637' => 'Borj El Khadra',
            'ocds-591adf-0405161541' => 'Bouhajla',
            'ocds-591adf-8086197830' => 'Cap Bon Golfe de Hammamet',
            'ocds-591adf-3966676342' => 'Borj El Khadra',
            'ocds-591adf-8860826385' => 'Centre Nord',
            'ocds-591adf-1562576878' => 'Convention Chaal',
            'ocds-591adf-8465275052' => 'Enfidha',
            'ocds-591adf-3911411402' => 'El Borma',
            'ocds-591adf-3885815417' => 'Makther',
            'ocds-591adf-6766832315' => 'Marin Golfe de Hammemet',
            'ocds-591adf-8429352461' => 'Kairouan Nord',
            'ocds-591adf-8288929525' => 'Kerkouane',
            'ocds-591adf-9746611143' => 'Sfax Kerkennah',
            'ocds-591adf-7620797249' => 'Hammamet Offshore',
            'ocds-591adf-0636968741' => 'Jenein Sud',
            'ocds-591adf-7827021309' => 'Permis Araifa',
            'ocds-591adf-9784097494' => 'Sud Remada',
            'ocds-591adf-9646831370' => 'Permis SFAX Offshore',
            'ocds-591adf-0859114956' => 'Nord Medenine',
            'ocds-591adf-1037236487' => 'Nord Medenine',
            'ocds-591adf-8693547378' => 'Zarat',
            'ocds-591adf-3271894222' => 'Permis SFAX Offshore',
            'ocds-591adf-9273996970' => 'Djebel Oust',
            'ocds-591adf-5848991193' => 'Permis SFAX Offshore',
            'ocds-591adf-0358603254' => 'Jenein Sud',
            'ocds-591adf-4712380251' => 'Jenein Sud',
            'ocds-591adf-5834131316' => 'Permis SFAX Offshore',
            'ocds-591adf-6159269475' => 'Hammamet Offshore',
            'ocds-591adf-4532921009' => 'Kerkouane',
            'ocds-591adf-5185765046' => 'Zarat',
            'ocds-591adf-0745521051' => 'Anaguid',
            'ocds-591adf-8459966788' => 'El Fahs',
            'ocds-591adf-8120227611' => 'Jelma',
            'ocds-591adf-9251512174' => 'Douz',
            'ocds-591adf-4811240028' => 'Zarat',
            'ocds-591adf-0130432161' => 'Convention Kerkenah Ouest',
            'ocds-591adf-2594228695' => 'Douz',
            'ocds-591adf-3687033972' => 'Ksar Hadada',
            'ocds-591adf-4326522813' => 'Le Kef',
            'ocds-591adf-7338272103' => 'Mahdia',
            'ocds-591adf-9310579850' => 'Sud Remada',
            'ocds-591adf-4132803471' => 'Sud Remada',
            'ocds-591adf-1458652954' => 'Hammamet Grands Fonds',
            'ocds-591adf-9513647951' => 'Grombalia',
            'ocds-591adf-5462558434' => 'El Jem',
            'ocds-591adf-1912577274' => 'El Fahs',
            'ocds-591adf-0589536288' => 'Hammamet Grands Fonds',
            'ocds-591adf-1165471583' => 'Kebili',
            'ocds-591adf-5025757871' => 'Jenein Centre',
            'ocds-591adf-1212255351' => 'Hammamet Offshore',
            'ocds-591adf-9586019222' => 'Jenein Sud',
            'ocds-591adf-1337408934' => 'Jenein Sud',
            'ocds-591adf-6683484306' => 'Kebili',
            'ocds-591adf-4444365858' => 'Borj El Khadra Sud',
            'ocds-591adf-4302698707' => 'Kerkouane',
            'ocds-591adf-6186641258' => 'Convention Chaal',
            'ocds-591adf-2098248333' => 'Nord des Chotts',
            'ocds-591adf-2999190496' => 'Zarat',
            'ocds-591adf-9349602762' => 'Zarat',
            'ocds-591adf-1049807701' => '7th of November / Joint Oil Block',
            'ocds-591adf-4313867617' => 'Douz',
            'ocds-591adf-2464468553' => 'Borj El Khadra',
            'ocds-591adf-4854344543' => 'Amilcar',
            'ocds-591adf-9679521107' => 'Amilcar',
            'ocds-591adf-7022649559' => 'Amilcar',
            'ocds-591adf-2060658975' => 'Amilcar',
            'ocds-591adf-9795427622' => 'Borj El Khadra',
            'ocds-591adf-8402124061' => 'Bouhajla',
            'ocds-591adf-2514008710' => 'Borj El Khadra',
            'ocds-591adf-1819183362' => 'Golf de Gabes',
            'ocds-591adf-2238738028' => 'Jenein Nord',
            'ocds-591adf-1373608183' => 'Kerkouane',
            'ocds-591adf-9128647513' => 'Kaboudia',
            'ocds-591adf-1133439999' => 'Le Kef',
            'ocds-591adf-5999801820' => 'Ksar Hadada',
            'ocds-591adf-0989467575' => 'Makther',
            'ocds-591adf-8446612750' => 'Mahdia',
            'ocds-591adf-8506006251' => 'Chorbane',
            'ocds-591adf-7038321305' => 'Chorbane',
            'ocds-591adf-1251983902' => 'Convention Gabes Jerba Ben Gardane',
            'ocds-591adf-5021832004' => 'Permis du Sud',
            'ocds-591adf-8062182641' => 'Permis SFAX Offshore',
            'ocds-591adf-4777905844' => 'Permis SFAX Offshore',
            'ocds-591adf-2817125596' => 'Zarat',
            'ocds-591adf-9552222925' => 'Area 1',
            'ocds-591adf-9306866843' => 'Bugruvativsk Field',
            'ocds-591adf-3556799773' => 'Bugruvativsk Field',
            'ocds-591adf-4828324785' => 'Kitumba Copper Project',
            'ocds-591adf-1630698631' => 'Borj El Khadra Sud',
        ];
    }

    public function getLicenseIdentifierOcids()
    {
        return [
            'ocds-591adf-0179644427'=>'Block W',
            'ocds-591adf-7671284346'=>'Block EG-24',
            'ocds-591adf-3544719155'=>'Block S',
            'ocds-591adf-3883099279'=>'Block EG-21',
            'ocds-591adf-3533551205'=>'424102883',
            'ocds-591adf-0773715511'=>'Contrato 424102854 ALTAMIRA',
            'ocds-591adf-4214910059'=>'424104804 AMATITLAN',
            'ocds-591adf-3624750089'=>'Contrato San Andres 424102855',
            'ocds-591adf-5458382622'=>'424104803 PITEPEC',
            'ocds-591adf-2288241575'=>'424103812 Humapa',
            'ocds-591adf-6181255605'=>'424103814 Soledad',
            'ocds-591adf-7411546128'=>'424102856',
            'ocds-591adf-1038726743'=>'424104805 MIAHUAPAN',
            'ocds-591adf-3071501652'=>'Contrato PANUCO 424102853',
            'ocds-591adf-9373760375'=>'425021852 CARRIZO',
            'ocds-591adf-2032447165'=>'424103813 Miquetla',
            'ocds-591adf-9572710160'=>'424102889 Ebano',
            'ocds-591adf-3843755960'=>'License contract for exploration and exploitation of hydrocarbons Lote 88',
            'ocds-591adf-1624878521'=>'License contract for exploration and exploitation of hydrocarbons Lote 126',
            'ocds-591adf-4399564244'=>'License contract for exploration and exploitation of hydrocarbons Lote 95',
            'ocds-591adf-3906687826'=>'License contract for exploration and exploitation of hydrocarbons Lote 161',
            'ocds-591adf-0461174209'=>'License contract for exploration and exploitation of hydrocarbons Lote 127',
            'ocds-591adf-3407308771'=>'License contract for exploration and exploitation of hydrocarbons Lote 137',
            'ocds-591adf-4109359446'=>'License',
            'ocds-591adf-1806088707'=>'License contract for exploration and exploitation of hydrocarbons Lote 135',
            'ocds-591adf-3315096248'=>'License contract for exploration and exploitation of hydrocarbons Lote 138',
            'ocds-591adf-3723126760'=>'License contract for exploration and exploitation of hydrocarbons Lote 31C',
            'ocds-591adf-6743049102'=>'License contract for exploration and exploitation of hydrocarbons Lote 58',
            'ocds-591adf-3314121968'=>'License contract for exploration and exploitation of hydrocarbons Lote 144',
            'ocds-591adf-2245683705'=>'License contract for exploration and exploitation of hydrocarbons Lote 145',
            'ocds-591adf-1195303977'=>'License contract for exploration and exploitation of hydrocarbons Lote 131',
            'ocds-591adf-8938335769'=>'License contract for exploration and exploitation of hydrocarbons Lote 133',
            'ocds-591adf-8715839071'=>'License contract for exploration and exploitation of hydrocarbons Lote 31E',
            'ocds-591adf-8192707206'=>'License contract for exploration and exploitation of hydrocarbons Lote 56',
            'ocds-591adf-9753464836'=>'License contract for exploration and exploitation of hydrocarbons Lote 95',
            'ocds-591adf-0292784834'=>'License contract for exploration and exploitation of hydrocarbons Lote 156',
            'ocds-591adf-9620382408'=>'License contract for exploration and exploitation of hydrocarbons Lote 183',
            'ocds-591adf-1900505079'=>'ph_Rio-Tuba-Nickel-Project',
            'ocds-591adf-6297501032'=>'ph_Malampaya-Deep-Water-Gas-to-Power-Project',
            'ocds-591adf-0640514048'=>'ph_Tagana-an-Nickel-Project',
            'ocds-591adf-0083596491'=>'ph_Adlay-Mining-Project',
            'ocds-591adf-8082857980'=>'ph_Guinabon-Nickel-Project',
            'ocds-591adf-9564139780'=>'ph_Cagdianao-Nickel-Project',
            'ocds-591adf-5963366284'=>'ph_Guinabon-Nickel-Project',
            'ocds-591adf-2327028479'=>'ph_Paracale-Gold-Project',
            'ocds-591adf-8486546892'=>'ph_Siana-Gold-Project',
            'ocds-591adf-2393158296'=>'ph_Masbate-Gold-Project',
            'ocds-591adf-4379373179'=>'ph_Toledo-Copper-Project',
            'ocds-591adf-9194346883'=>'ph_Elluvial-Chromite-Mining-and-Concentration-Project',
            'ocds-591adf-7705086445'=>'ph_Leyte-Magnetite-Project',
            'ocds-591adf-1314860465'=>'ph_Masbate-Gold-Project',
            'ocds-591adf-2939708932'=>'ph_Siana-Gold-Project',
            'ocds-591adf-4966736928'=>'ph_Tandawa-Nickel-Project',
            'ocds-591adf-1424338090'=>'ph_Berong-Nickel-Project',
            'ocds-591adf-8564429744'=>'ph_Tagana-an-Nickel-Project',
            'ocds-591adf-9581547683'=>'ph_Cantilan-Nickel-Project',
            'ocds-591adf-6626006637'=>'ph_Co-O-Gold-Project',
            'ocds-591adf-6696511613'=>'ph_Nonoc-Nickel-Project',
            'ocds-591adf-5233340063'=>'ph_Toronto-and-Pulot-Nickel-Projects',
            'ocds-591adf-5444707467'=>'ph_Libertad-Gas-Field',
            'ocds-591adf-4357346394'=>'ph_Libertad-Gas-Field',
            'ocds-591adf-4197629132'=>'ph_Taganito-Nickel-Project',
            'ocds-591adf-2593066531'=>'ph_Apex-Maco-Operation',
            'ocds-591adf-0633880291'=>'ph_Taganito-Nickel-Project',
            'ocds-591adf-2792396017'=>'ph_Didipio-Project',
            'ocds-591adf-0056582197'=>'ph_Rapu-Rapu-Polymetallic-Project',
            'ocds-591adf-0781051765'=>'ph_Tubay-Nickel-Cobalt-Mining-Project',
            'ocds-591adf-9208160921'=>'ph_Dinagat-Chromite-Project',
            'ocds-591adf-0115707939'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-4283019703'=>'ph_Masbate-Gold-Project',
            'ocds-591adf-8380393006'=>'ph_Urbiztondo-Nickel-Project',
            'ocds-591adf-9203793811'=>'ph_Toronto-and-Pulot-Nickel-Projects',
            'ocds-591adf-6155018199'=>'ph_Siana-Gold-Project',
            'ocds-591adf-1623268944'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-5976674032'=>'ph_Tandawa-Nickel-Project',
            'ocds-591adf-6484217517'=>'ph_Victoria-Gold-Project',
            'ocds-591adf-6012864853'=>'ph_Dahican-Nickel-Project',
            'ocds-591adf-8368260751'=>'ph_Bel-at-Nickel-Project',
            'ocds-591adf-9844860754'=>'ph_Homonhon-Chromite-Project',
            'ocds-591adf-3824268091'=>'ph_Carrascal-Nickel-Project',
            'ocds-591adf-3498737512'=>'ph_Masbate-Gold-Project',
            'ocds-591adf-7265722663'=>'ph_Rio-Tuba-Nickel-Project',
            'ocds-591adf-1713256223'=>'ph_Siana-Gold-Project',
            'ocds-591adf-4141862922'=>'ph_Bel-at-Nickel-Project',
            'ocds-591adf-9308829862'=>'ph_Leyte-Magnetite-Project',
            'ocds-591adf-7888022700'=>'ph_HY-Nickel-Chromite-Project',
            'ocds-591adf-0617728514'=>'ph_Camachin-Iron-Ore-Mining-Project',
            'ocds-591adf-0060618096'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-3741414378'=>'ph_Toledo-Copper-Project',
            'ocds-591adf-6645823668'=>'ph_Canatuan-Mining-Project',
            'ocds-591adf-3363161868'=>'ph_Sta-Cruz-Candelaria-Project',
            'ocds-591adf-1946181466'=>'ph_Carrascal-Nickel-Project',
            'ocds-591adf-8908710232'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-5792510988'=>'ph_Cagdianao-Nickel-Project',
            'ocds-591adf-0431745646'=>'ph_Homonhon-Chromite-Project',
            'ocds-591adf-6975899734'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-9119482338'=>'ph_Sta-Cruz-Candelaria-Project',
            'ocds-591adf-1394673190'=>'ph_Canatuan-Mining-Project',
            'ocds-591adf-8549514639'=>'ph_Victoria-Gold-Project',
            'ocds-591adf-9975874698'=>'ph_HY-Nickel-Chromite-Project',
            'ocds-591adf-2917943809'=>'ph_Dinagat-Chromite-Project',
            'ocds-591adf-5185619420'=>'ph_Apex-Maco-Operation',
            'ocds-591adf-2689260212'=>'ph_Malampaya-Deep-Water-Gas-to-Power-Project',
            'ocds-591adf-0421393552'=>'ph_Tubay-Nickel-Cobalt-Mining-Project',
            'ocds-591adf-2719937589'=>'ph_Co-O-Gold-Project',
            'ocds-591adf-3751727488'=>'ph_Berong-Nickel-Project',
            'ocds-591adf-0742047974'=>'ph_Toledo-Copper-Project',
            'ocds-591adf-2437234184'=>'ph_Cagdianao-Nickel-Project',
            'ocds-591adf-3538681265'=>'ph_Padcal-Copper-Gold-Operation',
            'ocds-591adf-9492198891'=>'ph_Toledo-Copper-Project',
            'ocds-591adf-3156014237'=>'ph_Nonoc-Nickel-Project',
            'ocds-591adf-1737251309'=>'ph_Masbate-Gold-Project',
            'ocds-591adf-6091789645'=>'ph_Libertad-Gas-Field',
            'ocds-591adf-9374734729'=>'ph_Rapu-Rapu-Polymetallic-Project',
            'ocds-591adf-2181723079'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-8757641437'=>'ph_Rapu-Rapu-Polymetallic-Project',
            'ocds-591adf-4013712466'=>'ph_Dahican-Nickel-Project',
            'ocds-591adf-4621402481'=>'ph_Padcal-Copper-Gold-Operation',
            'ocds-591adf-1333562032'=>'ph_Urbiztondo-Nickel-Project',
            'ocds-591adf-1265870384'=>'ph_Masbate-Gold-Project',
            'ocds-591adf-4659660417'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-5502782183'=>'ph_Adlay-Mining-Project',
            'ocds-591adf-5920713915'=>'ph_Elluvial-Chromite-Mining-and-Concentration-Project',
            'ocds-591adf-8808867910'=>'ph_Rapu-Rapu-Polymetallic-Project',
            'ocds-591adf-5307554205'=>'ph_Cantilan-Nickel-Project',
            'ocds-591adf-1658238869'=>'ph_Paracale-Gold-Project',
            'ocds-591adf-3848739629'=>'ph_Sta-Cruz-Nickel-Project',
            'ocds-591adf-6822686270'=>'ph_Cagdianao-Nickel-Project',
            'ocds-591adf-2514725846'=>'B3',
            'ocds-591adf-3522869491'=>'El Hana',
            'ocds-591adf-9797336341'=>'Mestaoua',
            'ocds-591adf-6223009974'=>'El Kneis',
            'ocds-591adf-6045242845'=>'Zamlet El Beidha Oued Mestaoua',
            'ocds-591adf-0940672274'=>'Jebel Djerissa',
            'ocds-591adf-1019283522'=>'Henchir Hassen',
            'ocds-591adf-3479988382'=>'Sabkhet El Mehabel Nord',
            'ocds-591adf-3119260709'=>'Mima/Sabkhet El Adhibate',
            'ocds-591adf-6442841974'=>'Sabkhet El Melah de Zarzis',
            'ocds-591adf-7013278443'=>'Bir el Afou',
            'ocds-591adf-5128040886'=>'Oued El Gabel',
            'ocds-591adf-8797519591'=>'Oued El Ghar',
            'ocds-591adf-9932168781'=>'El Badr',
            'ocds-591adf-6550040376'=>'El Abbassia',
            'ocds-591adf-1707765023'=>'El Benia',
            'ocds-591adf-3298436391'=>'Jebel Hachana',
            'ocds-591adf-6647835520'=>'Bir Mguebla',
            'ocds-591adf-5520433530'=>'Convention COTUSAL 1949',
            'ocds-591adf-5935767031'=>'Sabkhet el Gharra',
            'ocds-591adf-3135230141'=>'El Adouli',
            'ocds-591adf-2903171387'=>'Chott El Jerid',
            'ocds-591adf-8691193848'=>'Henchir Majdoub',
            'ocds-591adf-8116673375'=>'Jebel Hameima',
            'ocds-591adf-4046187347'=>'Bled El Adla',
            'ocds-591adf-9487119280'=>'Jebel Rkaiz El Beidha',
            'ocds-591adf-9274016935'=>'Nour',
            'ocds-591adf-7027592904'=>'El Ittihad',
            'ocds-591adf-5549008607'=>'Zamlet Khechem Mohamed',
            'ocds-591adf-3993686006'=>'Salakta',
            'ocds-591adf-5606276477'=>'Oued Ech Chogga',
            'ocds-591adf-3682822226'=>'Oued Sabat',
            'ocds-591adf-7492015340'=>'Merbeh Chtioua',
            'ocds-591adf-0865738708'=>'Henchir Jebbes El Ghrifet',
            'ocds-591adf-7742398126'=>'Kef Abdallah',
            'ocds-591adf-2673437920'=>'Jbel Essif',
            'ocds-591adf-3987260466'=>'Bir El Jedid',
            'ocds-591adf-5576431527'=>'Kodiat el Koucha',
            'ocds-591adf-0143682044'=>'Hassi El Gypse-Mestaoua',
            'ocds-591adf-4091289998'=>'Jebel Houfia',
            'ocds-591adf-0682153656'=>'Sidi Salah',
            'ocds-591adf-3098471462'=>'Oued El Bakbaka',
            'ocds-591adf-8293568781'=>'Sabkhet Oum El Khialate',
            'ocds-591adf-8398776486'=>'Sidi El Hani',
            'ocds-591adf-0020404818'=>'Henchir El Jebbes',
        ];
    }

    public function updateLicenseTitle()
    {
        $ocids = $this->getLicenseTitleOcids();

        foreach ($ocids as $ocid => $license_name) {
            $contract   = $this->contract->findContractByOpenContractingId($ocid);
            $metadata   = json_decode($contract->metadata, true);
            $concession = $metadata['concession'];

            if (!(is_array($concession))) {
                $concession = json_decode($concession, true);
            }
            $concession['license_name'] = $license_name;
            $metadata['concession']     = $concession;
            $metadata                   = json_encode($metadata);
            $contract_id                = $contract->id;
            $update_sql                 = "update contracts set metadata='$metadata' where id=$contract_id";
            $contract                   = DB::statement($update_sql);
        }
    }

    public function updateLicenseIdentifier()
    {
        $ocids = $this->getLicenseIdentifierOcids();

        foreach ($ocids as $ocid => $license_name) {
            $contract   = $this->contract->findContractByOpenContractingId($ocid);
            $metadata   = json_decode($contract->metadata, true);
            $concession = $metadata['concession'];

            if (!(is_array($concession))) {
                $concession = json_decode($concession, true);
            }
            $concession['license_identifier'] = $license_name;
            $metadata['concession']           = $concession;
            $metadata                         = json_encode($metadata);
            $contract_id                      = $contract->id;
            $update_sql                       = "update contracts set metadata='$metadata' where id=$contract_id";
            $contract                         = DB::statement($update_sql);
        }
    }

    public function updateMetadata()
    {
        $this->updateLicenseTitle();
        $this->updateLicenseIdentifier();
    }
}
