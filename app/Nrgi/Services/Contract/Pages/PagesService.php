<?php namespace App\Nrgi\Services\Contract\Pages;

use App\Nrgi\Repositories\Contract\Pages\PagesRepositoryInterface;
use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class PagesService
 */
class PagesService
{
    /**
     * @var PagesRepositoryInterface
     */
    protected $pages;
    /**
     * @var Log
     */
    protected $logger;

    /**
     * @param PagesRepositoryInterface $pages
     * @param Log                      $logger
     */
    public function __construct(PagesRepositoryInterface $pages, Log $logger)
    {
        $this->pages  = $pages;
        $this->logger = $logger;
    }

    /**
     * Get Page Text
     * @param $contractID
     * @param $pageID
     * @return \App\Nrgi\Entities\Contract\Pages\Pages
     */
    public function getText($contractID, $pageID)
    {
        if ($pages = $this->pages->getText($contractID, $pageID)) {
            return $pages;
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
            $this->pages->updateOrCreate($page_detail);

            if ($log) {
                $this->logger->activity(
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
        return $this->pages->fullTextSearch($contract_id, $query);
    }

    /**
     * Check is pages exists for a contract
     * @param $contract_id
     * @return bool
     */
    public function exists($contract_id)
    {
        return $this->pages->getTotalPages($contract_id) > 0;
    }
}
