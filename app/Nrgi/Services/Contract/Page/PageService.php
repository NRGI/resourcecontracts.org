<?php namespace App\Nrgi\Services\Contract\Page;

use App\Nrgi\Entities\Contract\Page\Page;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Repositories\Contract\Page\PageRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface as Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem;
use App\Nrgi\Log\NrgiLogService;

/**
 * Class PageService
 * @package App\Nrgi\Services\Contract\Page
 */
class PageService
{
    /**
     * @var $contract
     */
    protected $contract;
    /**
     * @var
     */
    protected $fileSystem;
    /**
     * @var PageRepositoryInterface
     */
    protected $page;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var NrgiLogService
     */
    protected $nrgiLogService;

    /**
     * @param ContractRepositoryInterface $contract
     * @param PageRepositoryInterface     $page
     * @param Filesystem                  $fileSystem
     * @param Log                         $logger
     * @param NrgiLogService              $nrgiLogService
     */
    public function __construct(ContractRepositoryInterface $contract, PageRepositoryInterface $page, Filesystem $fileSystem, Log $logger, NrgiLogService $nrgiLogService)
    {
        $this->fileSystem = $fileSystem;
        $this->contract   = $contract;
        $this->page       = $page;
        $this->logger     = $logger;
        $this->nrgiLogService     = $nrgiLogService;
    }

    /**
     * Save pages
     *
     * @param $contractId
     * @param $page
     * @return array
     */
    public function savePages($contractId, $page)
    {
        $contract = $this->contract->findContract($contractId);

        return $contract->pages()->saveMany($page);
    }

    /**
     * Build Pages
     *
     * @param $directory
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function buildPages($directory)
    {
        $files = $this->fileSystem->files($directory . '/text');
        $pages  = [];
        foreach ($files as $file) {
            $content       = $this->fileSystem->get($file);
            $pageNo        = $this->getPageNo($file);
            $page          = new Page();
            $page->page_no = $pageNo;
            $page->text    = $content;
            $pages[]       = $page;
        }

        return $pages;
    }

    /**
     * Get Page Number
     *
     * @param $file
     * @return int
     */
    public function getPageNo($file)
    {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $output   = explode("_", $fileName);

        return (int) $output[count($output) - 1];
    }

    /**
     * Get Page Text
     *
     * @param $contractID
     * @param $pageID
     * @return Page
     */
    public function getText($contractID, $pageID)
    {
        if ($page = $this->page->getText($contractID, $pageID)) {
            return $page;
        }

        throw new ModelNotFoundException();
    }

    /**
     * Get All Text
     *
     * @param $contractID
     * @return Page
     */
    public function getAllText($contractID)
    {
        if ($page = $this->page->getAllText($contractID)) {
            return $page;
        }

        throw new ModelNotFoundException();
    }

    /**
     * Save Page Text
     * @param      $contractID
     * @param      $page_no
     * @param      $text
     * @param bool $log
     * @return bool
     */
    public function saveText($contractID, $page_no, $text, $log = true)
    {
        $page_detail = [
            'contract_id' => $contractID,
            'page_no'     => $page_no,
            'text'        => $text
        ];
        try {
            $this->page->updateOrCreate($page_detail);

            if ($log) {
                $this->nrgiLogService->activity(
                    'contract.log.save_page',
                    ['page' => $page_no],
                    $contractID
                );
            }

            $this->logger->info(
                "Page text updated",
                [
                    'Contract id' => $contractID,
                    'Page id '    => $page_no,
                ]
            );

            return true;

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * Get Result of full text search
     *
     * @param $contract_id
     * @param $query
     * @return array
     */
    public function fullTextSearch($contract_id, $query)
    {
        return $this->page->fullTextSearch($contract_id, $query);
    }
    /**
     * Get Total number of pages for all contracts
     *
     * @return array
     */
    public function getPageCountForAllContracts()
    {
        return $this->page->getPageCountForAllContracts();
    }

    /**
     * Check is page exists for a contract
     *
     * @param $contract_id
     * @return bool
     */
    public function exists($contract_id)
    {
        return $this->page->getTotalPage($contract_id) > 0;
    }
}
