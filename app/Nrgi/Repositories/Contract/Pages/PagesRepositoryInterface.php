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
     * @param $pageID
     * @return Pages
     */
    public function getText($contractID, $pageID);

    /**
     * Get result of Full text search
     * @param $contract_id
     * @param $query
     * @return Collection
     */
    public function fullTextSearch($contract_id, $query);
}
