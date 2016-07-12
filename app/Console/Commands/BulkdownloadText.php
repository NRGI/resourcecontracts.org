<?php namespace App\Console\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem;
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
    protected $description = 'Download All pdf Text in zip file.';
    /**
     * @var
     */
    public $storage;
    /**
     * @var
     */
    public $filesystem;

    const RAWTEXT     = "rawtext";
    const REFINEDTEXT = "refinedtext";
    const S3FOLDER    = "dumptext";

    /**
     * @param Storage    $storage
     * @param Filesystem $filesystem
     */
    public function __construct(Storage $storage, Filesystem $filesystem)
    {
        parent::__construct();
        $this->storage    = $storage;
        $this->filesystem = $filesystem;
    }


    /**
     * Execute bash file
     */
    public function fire()
    {
        $host        = env('DB_HOST');
        $port        = env('DB_PORT');
        $user        = env('DB_USERNAME');
        $database    = env('DB_DATABASE');
        $storagepath = storage_path();
        $password    = str_replace("&", "\&", env('DB_PASSWORD'));
        $path        = __DIR__ . '/BashScript';
        $date        = date('Y_m_d');
        $filename    = "contract_text_" . $date . ".zip";
        $rawText     = self::RAWTEXT;
        $refinedText = self::REFINEDTEXT;
        chdir($path);
        chmod($path . '/extract.sh', 0777);
        shell_exec("./extract.sh $host $port $user $database $storagepath $password $date $filename $rawText $refinedText");
        $this->info("File zipped");
        $this->uploadZipFile($storagepath, $filename, $date);

    }


    /**
     * Upload file in s3
     * @param $filename
     */
    public function uploadZipFile($storagepath, $filename, $date)
    {
        $client = S3Client::factory(
            [
                'key'    => env('AWS_KEY'),
                'secret' => env('AWS_SECRET'),
                'region' => env('AWS_REGION'),
            ]
        );

        $client->uploadDirectory($storagepath . "/" . $date . "/", env('AWS_BUCKET'), "/" . self::S3FOLDER);
        $this->info("File uploaded in s3");
        $this->filesystem->deleteDirectory($storagepath . '/' . self::RAWTEXT);
        $this->filesystem->deleteDirectory($storagepath . '/' . self::REFINEDTEXT);
        $this->filesystem->deleteDirectory($storagepath . '/' . $date);
        $this->info("File deleted from local");

    }


}
