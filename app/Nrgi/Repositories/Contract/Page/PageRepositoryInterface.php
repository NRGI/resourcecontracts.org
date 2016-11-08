<?php namespace App\Nrgi\Repositories\Contract\Page;

use App\Nrgi\Entities\Contract\Page\Page;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface PageRepositoryInterface
 * @package App\Nrgi\Repositories\Page
 */
interface PageRepositoryInterface
{
    /**
     * Get page Text
     * @param $contractID
     * @param $page_no
     * @return Page
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
     * Get Total Page
     * @param $contractID
     * @return Int
     */
    public function getTotalPage($contractID);

    /**
     * Update or create page
     *
     * @param array $pageDetail
     * @return mixed
     */
    public function updateOrCreate(array $pageDetail);

    /**
     * Get all text by contract id
     *
     * @param $contractID
     * @return Collection
     */
    public function getAllText($contractID);

    /**
     * Get all the text
     * @return array
     */
    public function contractText();

}
