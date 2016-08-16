<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem;
use Maatwebsite\Excel\Excel;

/**
 * Class TrackOCID
 * @package App\Console\Commands
 */
class TrackOCID extends Command
{
    public $analyzedArray;
    public $header;
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
     * @var Excel
     */
    private $excel;

    /**
     * Create a new command instance.
     *
     * @param Storage    $storage
     * @param Filesystem $filesystem
     * @param Excel      $excel
     */
    public function __construct(Storage $storage, Filesystem $filesystem, Excel $excel)
    {
        parent::__construct();
        $this->storage       = $storage;
        $this->filesystem    = $filesystem;
        $this->analyzedArray = [];
        $this->header        = [];
        $this->excel         = $excel;
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {
        //$this->extractOCIDListToJson();
        $this->analysisOfOCID();
    }

    /**
     * Extract OCID list to Json file.
     *
     */
    protected function extractOCIDListToJson()
    {
        $s3    = $this->storage->disk('s3');
        $files = $s3->files('ocid');

        $fields = [
            'id',
            'name',
            'category',
            'country',
            'ocid',
            'is_published',
            'last_updated_date',
            'is_associated_doc',
        ];
        if (!is_dir(public_path('ocid'))) {
            mkdir(public_path('ocid'), 0777);
        }

        foreach ($files as $file) {
            $records  = [];
            $date     = str_replace(['ocid/', '.csv'], ['', ''], $file);
            $filename = sprintf('ocid/%s.json', $date);
            if (!file_exists(public_path($filename))) {
                $handle = fopen('https://s3-us-west-2.amazonaws.com/rc-stage/' . $file, 'r');

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $row = [];
                    foreach ($data as $k => $v) {
                        $row[$fields[$k]] = $v;
                    }
                    $records[] = $row;
                }
                file_put_contents(public_path($filename), json_encode($records));
                $this->info($date . ' completed');
            } else {
                $this->info($date . ' skip');
            }
        }
    }

    /**
     * Generate OCID list
     *
     */
    protected function generate()
    {
        $contracts     = Contract::all();
        $contractArray = [];

        foreach ($contracts as $contract) {
            $contractArray[] = [
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
        $this->generateAndUploadCSV($contractArray);
    }

    /**
     * Generate And upload CSV in S3
     *
     * @param $contractArray
     */
    protected function generateAndUploadCSV($contractArray)
    {
        $csvFile = storage_path('oci.csv');
        $s3path  = sprintf('ocid/%s.csv', date('Y-m-d-H-i-s'));

        $file = fopen($csvFile, "w");

        foreach ($contractArray as $contract) {
            fputcsv($file, $contract);
        }
        fclose($file);

        $this->storage->disk('s3')->put(
            $s3path,
            $this->filesystem->get($csvFile)
        );

        $this->filesystem->delete($csvFile);
    }

    private function analysisOfOCID()
    {
        $this->header = [];
        $files        = $this->filesystem->allFiles(public_path('ocid'));

        $fileArray = [];
        foreach ($files as $file) {
            $filename = $file->getRelativePathname();
            $filename = explode('.', $filename);

            $fileArray[$filename[0]] = json_decode($file->getContents());
        }

        ksort($fileArray);

        $mainArray = [];
        foreach ($fileArray as $ar => $value) {
            foreach ($value as $v) {
                $mainArray[$v->id][$ar] = $v;
            }
        }
        $analysis = [];
        foreach ($mainArray as $id => $ana) {
            $base = [];
            foreach ($ana as $key => $a) {
                $reason = '-';

                if (empty($base)) {
                    $base                = $a;
                    $reason              = $a->ocid;
                    $analysis[$id][$key] = $reason;
                    continue;
                }
                if ($base->ocid != $a->ocid) {
                    $reason = $a->ocid;

                    if ($base->category != $a->category) {
                        $reason .= sprintf('(   category change from %s to %s)', $base->category, $a->category);
                    }

                    if ($base->country != $a->country) {
                        $reason .= sprintf('(   country change from %s to %s)', $base->country, $a->country);
                    }

                    if ($base->is_associated_doc != $a->is_associated_doc) {
                        $reason .= sprintf('(   is_associated_doc change from %s to %s)', $base->is_associated_doc, $a->is_associated_doc);
                    }

                    if ($reason == $a->ocid && $a->is_associated_doc) {
                        $reason .= " (Parent doc change)";
                    }
                }
                $analysis[$id][$key] = $reason;
                $base                = $a;
            }
        }


        $data   = [];
        $header = [];


        foreach ($analysis as $id => $date) {
            if (empty($header)) {
               $header = array_merge(['id'] ,array_keys($date));
            }

            $idA = [$id];

            $v = [$id];
            foreach ($header as $head) {
                if($head == 'id') {continue;}
                $v[] = isset($date[$head]) ? $date[$head] : '-';
            }

            $data[] = $v;
        }



        $data = array_merge([$header], $data);



       file_put_contents(public_path('analysis.html'), json_encode($data));


        $this->excel->create(
            'ocid_analysis',
            function ($csv) use (&$data) {
                $csv->sheet(
                    'sheetname',
                    function ($sheet) use (&$data) {
                        $sheet->fromArray($data);
                    }
                );
            }
        )->save('xls', public_path());


    }


}