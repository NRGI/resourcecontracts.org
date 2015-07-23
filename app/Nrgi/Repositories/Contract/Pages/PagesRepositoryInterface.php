<?php namespace App\Nrgi\Repositories\Contract\Pages;

use App\Nrgi\Entities\Contract\Pages\Pages;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface PagesRepositoryInterface
 * @package App\Nrgi\Repositories\Pages
 */
interface PagesRepositoryInterface
{
    /**
     * Get page Text
     * @param $contractID
     * @param $page_no
     * @return Pages
     */
    public function getText($contractID, $page_no);

    /**
     * Get result of Full text search
     * @param $contract_id
     * @param $query
     * @return array
     */
    public function fullTextSearch($contract_id, $query);

    /**
     * Get Total Pages
     * @param $contractID
     * @return Int
     */
    public function getTotalPages($conractID);

    /**
     * Update or create page
     *
     * @param array $pageDetail
     * @return mixed
     */
    public function updateOrCreate(array $pageDetail);

}
