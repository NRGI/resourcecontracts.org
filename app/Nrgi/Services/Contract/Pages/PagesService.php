<?php namespace App\Nrgi\Services\Contract\Pages;

use App\Nrgi\Repositories\Contract\Pages\PagesRepositoryInterface;
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
     * @param PagesRepositoryInterface $pages
     */
    public function __construct(PagesRepositoryInterface $pages)
    {
        $this->pages = $pages;
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

            return $page->save();
        }

        throw new ModelNotFoundException();
    }
}
