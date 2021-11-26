<?php namespace App\Console\Commands;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem;

/**
 * Bulk download of pdf text
 *
 * Class BulkDownloadText
 * @package App\Console\Commands
 */
class BulkDownloadText extends Command
{
    /**
     * Text storage folder
     */
    const REFINED_TEXT = "refined_text";
    /**
     * S3 object name where zip files stores
     */
    const S3_OBJECT_NAME = "download_text";
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var Filesystem
     */
    protected $filesystem;
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
     * @var array
     */
    protected $db = [];
    /**
     * @var array
     */
    protected $countries = ['TN', 'GN', 'SL'];

    /**
     * @param Storage    $storage
     * @param Filesystem $filesystem
     */
    public function __construct(Storage $storage, Filesystem $filesystem)
    {
        parent::__construct();
        $this->storage    = $storage;
        $this->filesystem = $filesystem;
        $this->db         = [
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'user'     => env('DB_USERNAME'),
            'database' => env('DB_DATABASE'),
            'password' => str_replace("&", "\&", env('DB_PASSWORD')),
        ];
    }

    /**
     * Execute bash file for all contracts , rc and olc
     */
    public function fire()
    {
        $this->extractText();
        $this->extractText('rc');
        $this->extractText('olc');

        foreach ($this->countries as $value) {
            $this->extractText($value);
        }
        $this->updateFilesInS3();
        $this->deleteFiles();
    }

    /**
     * Extract text file of all contracts
     *
     * @param string $type
     */
    public function extractText($type = 'all')
    {
        $param = [
            $this->db['host'],
            $this->db['port'],
            $this->db['user'],
            $this->db['database'],
            $this->db['password'],
            storage_path(),
            static::REFINED_TEXT,
        ];

        if ($type != 'all') {
            $param[] = $type;
        }

        $this->command($type, join(' ', $param));
        $this->info("File zipped");
    }

    /**
     * Upload file in s3
     */
    public function updateFilesInS3()
    {
        $s3folder = static::S3_OBJECT_NAME;
        $bucket   = env('AWS_BUCKET');

        $credentials = new Credentials(env('AWS_ACCESS_KEY_ID'), env('AWS_SECRET_ACCESS_KEY'));
        $s3 = new S3Client(
            [
                'version'=> '2006-03-01',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => $credentials
            ]
        );

        $s3->deleteMatchingObjects(
            $bucket,
            $s3folder.'/'
        );
        $this->info("Object - {$s3folder} deleted in s3");
        $s3->uploadDirectory(storage_path("download"), $bucket, "/".$s3folder);
        $this->info("File uploaded in s3 => ".$s3folder);
    }

    /**
     * Delete files
     *
     */
    public function deleteFiles()
    {
        $this->command('deleteFiles', storage_path());
    }

    /**
     * Execute shell command
     *
     * @param $type
     * @param $param
     */
    public function command($type, $param)
    {
        $bashFile = $this->getBashFileName($type);

        $path = __DIR__.'/BashScript';
        chdir($path);
        chmod(sprintf('%s/%s.sh', $path, $bashFile), 0777);
        $command = sprintf("./%s.sh %s", $bashFile, $param);
        echo shell_exec($command);
    }

    /**
     * Get Bash file name
     *
     * @param $type
     *
     * @return string
     */
    protected function getBashFileName($type)
    {
        if ($this->isCategory($type)) {
            return 'extract-category';
        } elseif ($this->isCountry($type)) {
            return 'extract-country';
        } elseif ($type == 'all') {
            return 'extract';
        }

        return $type;
    }

    /**
     * Determine if type is category
     *
     * @param $type
     *
     * @return bool
     */
    protected function isCategory($type)
    {
        return in_array($type, ['olc', 'rc']);
    }

    /**
     * Determine if type if country
     *
     * @param $type
     *
     * @return bool
     */
    protected function isCountry($type)
    {
        return in_array($type, $this->countries);
    }
}
