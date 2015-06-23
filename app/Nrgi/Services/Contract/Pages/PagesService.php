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
        $this->pages = $pages;
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
     * @param $contractID
     * @param $pageID
     * @return bool
     */
    public function saveText($contractID, $pageID, $text)
    {
        if ($page = $this->pages->getText($contractID, $pageID)) {
            $page->text = $text;

            try {
                $page->save();

                $this->logger->activity(
                    'contract.log.save_page',
                    ['page' => $pageID],
                    $contractID
                );

                $this->logger->info(
                    "Page text updated",
                    [
                        'Contract id' => $contractID,
                        'Page id '    => $pageID,
                    ]
                );
                return true;

            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        throw new ModelNotFoundException();
    }
}
