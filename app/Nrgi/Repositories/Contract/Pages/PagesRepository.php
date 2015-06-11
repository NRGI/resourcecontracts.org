<?php namespace App\Nrgi\Repositories\Contract\Pages;

use App\Nrgi\Entities\Contract\Pages\Pages;

/**
 * Class PagesRepository
 * @package App\Nrgi\Repositories\Pages
 */
class PagesRepository implements PagesRepositoryInterface
{
    /**
     * @var Pages
     */
    protected $pages;

    /**
     * @param Pages $pages
     */
    public function __construct(Pages $pages)
    {
        $this->pages = $pages;
    }

    /**
     * Get page Text
     * @param $contractID
     * @param $pageID
     * @return Pages
     */
    public function getText($contractID, $pageID)
    {
        return $this->pages->where('contract_id', $contractID)->where('page_no', $pageID)->first();
    }
}
