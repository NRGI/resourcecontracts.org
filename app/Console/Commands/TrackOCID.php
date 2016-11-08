<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem;

/**
 * Class TrackOCID
 * @package App\Console\Commands
 */
class TrackOCID extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:trackocid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OCID in csv.';
    /**
     * @var Storage
     */
    private $storage;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Create a new command instance.
     *
     * @param Storage    $storage
     * @param Filesystem $filesystem
     */
    public function __construct(Storage $storage, Filesystem $filesystem)
    {
        parent::__construct();
        $this->storage = $storage;
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @param Contract $contract
     *
     */
    public function fire(Contract $contract)
    {
        $contracts    = $contract->all();
        $contracArray = [];

        foreach ($contracts as $contract) {
            $contracArray[] = [
                'id'                  => $contract->id,
                'name'                => $contract->title,
                'category'            => $contract->metadata->category[0],
                'country_code'        => $contract->metadata->country->code,
                'ocid'                => $contract->metadata->open_contracting_id,
                'metadata_status'     => $contract->metadata_status,
                'last_updated_at'     => $contract->last_updated_datetime,
                'supporting_document' => $contract->metadata->is_supporting_document,
            ];
        }
        $this->generateAndUploadCSV($contracArray);
    }

    /**
     * Generate And upload CSV in S3
     *
     * @param $contracArray
     */
    protected function generateAndUploadCSV($contracArray)
    {
        $csvFile = storage_path('oci.csv');
        $s3path  = sprintf('ocid/%s.csv', date('Y-m-d-H-i-s'));

        $file    = fopen($csvFile, "w");

        foreach ($contracArray as $contract) {
            fputcsv($file, $contract);
        }
        fclose($file);

        $this->storage->disk('s3')->put(
            $s3path,
            $this->filesystem->get($csvFile)
        );

        $this->filesystem->delete($csvFile);
    }
}
