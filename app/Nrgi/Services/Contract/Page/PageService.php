<?php namespace App\Nrgi\Services\Contract\Page;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Filesystem\Filesystem;
use App\Nrgi\Entities\Contract\Pages\Pages;

/**
 * Class PageService
 * @package App\Nrgi\Services\Contract\Page
 */
class PageService
{
    /**
     * @var $contract
     */
    protected $contract;

    /**
     * @var
     */
    protected $fileSystem;

    /**
     * @param Contract   $contract
     * @param Filesystem $fileSystem
     */
    public function __construct(ContractService $contract, Filesystem $fileSystem)
    {
        $this->contract   = $contract;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param $contract
     * @param $directory
     * return bool
     */
    public function savePages($contractId, $pages)
    {
        $contract = $this->contract->find($contractId);

        return $contract->pages()->saveMany($pages);
    }

    /**
     * @param $directory
     * @return array
     */
    public function buildPages($directory)
    {
        $type  = 'text';
        $files = $this->fileSystem->files($directory . '/text');
        $pages = [];

        if (empty($files)) {
            $type  = 'pdf';
            $files = $this->fileSystem->files($directory . '/pages');
        }
        foreach ($files as $file) {
            $pageNo  = $this->getPageNo($file);
            $content = $pageNo;

            if ($type == 'text') {
                $content = $this->fileSystem->get($file);
            }

            $page          = new Pages();
            $page->page_no = $pageNo;
            $page->text    = $content;
            $pages[]       = $page;
        }

        return $pages;
    }

    /**
     * @param $file
     * @return int
     */
    public function getPageNo($file)
    {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $output   = explode("_", $fileName);

        return (int) $output[count($output) - 1];
    }
}
