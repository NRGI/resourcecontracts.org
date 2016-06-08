<?php namespace App\Console\Commands;

use App\Nrgi\Repositories\Contract\Page\PageRepository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;
use ZipArchive;

/*
 * Bulk download of pdf text
 */

class BulkdownloadText extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:bulktext';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the bulk text.';
    /**
     * @var PageRepository
     */
    public $page;
    /**
     * @var Filesystem
     */
    public $file;
    /*
     * Folder name
     */
    public $fileName = "bulktext.txt";

    /*
     * Folder for to store text and zip file
     */
    public $folder = "bulktext";

    /*
     * Zip file name
     */
    public $zipFileName;

    /**
     * @param PageRepository $page
     * @param Filesystem     $file
     */
    public function __construct(PageRepository $page, Filesystem $file)
    {
        parent::__construct();
        $this->page        = $page;
        $this->file        = $file;
        $this->zipFileName = $this->getZipFileName();
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {

        $this->info(sprintf('Start time %s ', date('H:i:s')));
        $start = microtime(true);
        $pages = $this->page->contractText();
        $text  = $this->concatPages($pages, $start);
        $this->makeFile($text);
        $url = $this->upLoadZipFile();
        $end = microtime(true);
        $this->info(sprintf('End  time %s ', date('H:i:s')));
        $this->info(sprintf('Total  time %s ', $end - $start));
        $this->info(sprintf('Url for bulk file %s ', $url));
    }

    /**
     * Concat text
     * @param $pages
     */
    public function concatPages($pages, $start)
    {
        $text = '';
        foreach ($pages as $page) {
            $sanitizedText = $this->sanitizeText($page['text']);
            $text .= " " . $sanitizedText;
        }
        $this->info("Text sanitized");

        return $text;
    }

    /**
     * Make file and zip it
     * @param $text
     */
    public function makeFile($text)
    {
        if (!is_dir(public_path() . '/' . $this->folder)) {
            mkdir(public_path() . '/' . $this->folder, 0777, true);
        }

        $fileW = fopen(public_path() . '/' . $this->folder . '/' . $this->fileName, "w") or die("Unable to open file!");
        fwrite($fileW, $text);
        fclose($fileW);
        $this->info("Raw file made");
        $zip = new ZipArchive();
        $zip->open(public_path() . '/' . $this->folder . '/' . $this->zipFileName, ZipArchive::CREATE);
        $zip->addFile(public_path() . '/' . $this->folder . '/' . $this->fileName, $this->fileName);
        $zip->close();
        $this->info("File zipped");

    }


    /**
     * Upload the zipped file to s3
     * @return string
     */
    private function upLoadZipFile()
    {
        $this->info("Uploading file to s3");
        $this->file->disk('s3')->put('/bulktext/' . $this->zipFileName, file_get_contents(public_path() . '/' . $this->folder . '/' . $this->zipFileName));
        $file = 'http://s3-us-west-2.amazonaws.com/' . env('AWS_BUCKET') . '/bulktext/' . $this->zipFileName;
        $this->rrmdir(public_path() . '/' . $this->folder);

        return $file;
    }

    /**
     * Remove directory
     * @param $dir
     */
    public function rrmdir($dir)
    {
        $this->info("Removing directory");
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Sanitize text
     * @param $text
     * @return string
     */
    public function sanitizeText($text)
    {
        return str_replace(["\r\n", "\r", "\n", "\f"], "", strip_tags($text));
    }

    /**
     * Get Zip file name
     * @return string
     */
    public function getZipFileName()
    {
        $date = date('Y_m_d_H_i_s');

        return "contract_texts_" . $date . ".zip";
    }


}
