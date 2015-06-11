<?php namespace App\Nrgi\Repositories\Contract\Pages;
/**
 * Interface PagesRepositoryInterface
 * @package App\Nrgi\Repositories\Pages
 */
interface PagesRepositoryInterface
{
    /**
     * Get page Text
     * @param $contractID
     * @param $pageID
     * @return Pages
     */
    public function getText($contractID, $pageID);

}
