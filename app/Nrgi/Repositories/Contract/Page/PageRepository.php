<?php namespace App\Nrgi\Repositories\Contract\Page;

use App\Nrgi\Entities\Contract\Page\Page;
use Illuminate\Database\DatabaseManager;

/**
 * Class PageRepository
 * @method void where()
 * @method void select()
 * @package App\Nrgi\Repositories\Page
 */
class PageRepository implements PageRepositoryInterface
{
    /**
     * @var Page
     */
    protected $page;
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param Page            $page
     * @param DatabaseManager $db
     */
    public function __construct(Page $page, DatabaseManager $db)
    {
        $this->page = $page;
        $this->db   = $db;
    }

    /**
     * Get page Text
     * @param $contractID
     * @param $page_no
     * @return Page
     */
    public function getText($contractID, $page_no)
    {
        return $this->page->where('contract_id', $contractID)->where('page_no', $page_no)->first();
    }

    public function getAllText($contractID)
    {
        return $this->page->where('contract_id', $contractID)->get();
    }

    /**
     * Get result of Full text search
     * @param $contract_id
     * @param $query
     * @return array
     */
    public function fullTextSearch($contract_id, $query)
    {
        return $this->page->select(
            $this->db->raw("contract_id, page_no, ts_headline(text, plainto_tsquery('" . $query . "')) as text")
        )
                          ->whereRaw("to_tsvector(text) @@ plainto_tsquery('" . $query . "')")
                          ->orderBy('page_no', 'ASC')
                          ->where('contract_id', $contract_id)
                          ->get()->toArray();
    }

    /**
     * Get Total Page
     * @param $contractID
     * @return Int
     */
    public function getTotalPage($contractID)
    {
        return $this->page->where('contract_id', $contractID)->count();
    }

      /**
     * Get Total Page
     * @param $contractID
     * @return Int
     */
    public function getPageCountForAllContracts($contract_ids)
    {
        return $this->page->whereIn('contract_id', $contract_ids)->count();
    }

    /**
     * Update or create page
     *
     * @param array $pageDetail
     * @return mixed
     */
    public function updateOrCreate(array $pageDetail)
    {
        $page       = $this->page->firstOrNew(['contract_id' => $pageDetail['contract_id'], 'page_no' => $pageDetail['page_no']]);
        $page->text = $pageDetail['text'];
        return $page->save();
    }


    /**
     * Get all the text
     * @return array
     */
    public function contractText()
    {
        return $this->page->select('text')->get()->toArray();
    }
}
