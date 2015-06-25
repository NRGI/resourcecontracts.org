<?php namespace App\Nrgi\Repositories\Contract\Pages;

use App\Nrgi\Entities\Contract\Pages\Pages;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;

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
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param Pages           $pages
     * @param DatabaseManager $db
     */
    public function __construct(Pages $pages, DatabaseManager $db)
    {
        $this->pages = $pages;
        $this->db = $db;
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

    /**
     * Get result of Full text search
     * @param $contract_id
     * @param $query
     * @return array
     */
    public function fullTextSearch($contract_id, $query)
    {
        return $this->pages->select($this->db->raw("contract_id, page_no, ts_headline(text, plainto_tsquery('".$query."')) as text"))
                  ->whereRaw("to_tsvector(text) @@ plainto_tsquery('".$query."')")
                  ->orderBy($this->db->raw("ts_rank(to_tsvector(text), plainto_tsquery('".$query."'))"), 'DESC')
                  ->where('contract_id', $contract_id)
                  ->get()->toArray();
    }
}
