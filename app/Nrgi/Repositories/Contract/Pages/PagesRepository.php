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
        $this->db    = $db;
    }

    /**
     * Get page Text
     * @param $contractID
     * @param $page_no
     * @return Pages
     */
    public function getText($contractID, $page_no)
    {
        return $this->pages->where('contract_id', $contractID)->where('page_no', $page_no)->first();
    }

    /**
     * Get result of Full text search
     * @param $contract_id
     * @param $query
     * @return array
     */
    public function fullTextSearch($contract_id, $query)
    {
        return $this->pages->select(
            $this->db->raw("contract_id, page_no, ts_headline(text, plainto_tsquery('" . $query . "')) as text")
        )
                           ->whereRaw("to_tsvector(text) @@ plainto_tsquery('" . $query . "')")
                           ->orderBy(
                               $this->db->raw("ts_rank(to_tsvector(text), plainto_tsquery('" . $query . "'))"),
                               'DESC'
                           )
                           ->where('contract_id', $contract_id)
                           ->get()->toArray();
    }

    /**
     * Get Total Pages
     * @param $contractID
     * @return Int
     */
    public function getTotalPages($contractID)
    {
        return $this->pages->where('contract_id', $contractID)->count();
    }

    /**
     * Update or create page
     *
     * @param array $pageDetail
     * @return mixed
     */
    public function updateOrCreate(array $pageDetail)
    {
        $page       = $this->pages->firstOrNew(['contract_id' => $pageDetail['contract_id'], 'page_no' => $pageDetail['page_no']]);
        $page->text = $pageDetail['text'];

        return $page->save();
    }
}
