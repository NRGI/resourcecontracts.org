<?php
namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\Factory as Storage;

/**
 * Class migration
 * @package App\Nrgi\Services\Contract
 */
class MigrationService
{
    const UPLOAD_FOLDER = 'data/temp';
    /**
     * @var string
     */
    protected $raw_data;
    /**
     * @var array
     */
    protected $annotation_title = [];
    /**
     * @var
     */
    protected $country;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @param CountryService              $country
     * @param ContractRepositoryInterface $contract
     * @param Queue                       $queue
     * @param Log                         $logger
     * @param Filesystem                  $filesystem
     * @param Storage                     $storage
     */
    public function __construct(CountryService $country, ContractRepositoryInterface $contract, Queue $queue, Log $logger, Filesystem $filesystem, Storage $storage)
    {
        $this->country    = $country;
        $this->logger     = $logger;
        $this->filesystem = $filesystem;
        $this->storage    = $storage;
        $this->contract   = $contract;
        $this->queue      = $queue;
    }

    /**
     * @param null $file
     */
    public function setData($file = null)
    {
        $this->raw_data = file_get_contents($file);
    }

    /**
     * @return object
     */
    public function data()
    {
        return json_decode($this->raw_data)->document;
    }

    /**
     * Run Migration
     *
     * @return array
     */
    public function run()
    {
        $default_keys = array_keys(array_merge($this->contractMapping(), $this->annotationTitleMaping()));
        $default      = [];

        foreach ($default_keys as $key => $value) {
            $default[$value] = '';
        }

        $available = array_merge(
            $this->filterData($this->data(), $this->contractMapping()),
            $this->extractMetadata($this->data()->annotations, $this->annotationTitleMaping())
        );

        $contract = array_merge($default, $available);

        if ($contract['pdf_url'] != '') {
            if ($pdf = $this->downloadPdf($contract['pdf_url'])) {
                if (!$this->isPdfExists($pdf)) {
                    $contract['file'] = $pdf;
                    $contract         = $this->getContractArray($contract);

                    return json_decode(json_encode($contract));
                }
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function contractMapping()
    {
        return [
            'language'         => 'language',
            'contract_name'    => 'title',
            'created_datetime' => 'created_at',
            'updated_datetime' => 'updated_at',
            'pdf_url'          => ['resources', 'pdf'],
            'signature_date_1' => ['data', 'Signature Date'],
            'signature_year_1' => ['data', 'Signature Year'],
            'resources'        => ['data', 'Resource'],
            'country'          => ['data', 'Countries']
        ];
    }

    /**
     * @return array
     */
    protected function annotationTitleMaping()
    {
        return [
            'company'                 => 'Local company name',
            'contract_identifier'     => 'Legal Enterprise Identifier',
            'project_title'           => 'Project title',
            'signature_date_2'        => 'Date of contract signature',
            'signature_year_2'        => 'Year of contract signature',
            'type_of_contract_1'      => 'Type of document / right (Concession, Lease, Production Sharing Agreement, Service Agreement, etc.)',
            'type_of_contract_2'      => 'Type of document / right (Concession, Lease, Production Sharing contract, Service contract, etc.)',
            'resources_1'             => 'Type of mining title associated with the contract',
            'resources_2'             => 'Type of resources',
            'resources_3'             => 'Type of resources (mineral type, crude oil, gas, etc.)',
            'resources_4'             => 'Type of resources (mineral type, crude oil, gas, timber, etc.) OR specific crops planned (ex: food crops, oil palm, etc.)',
            'resources_5'             => 'Type of resources (mineral type, crude oil, gas, timber, etc.) OR specific crops planned (ex: food crops, oil palm, etc.)',
            'license_concession_name' => 'Name and/or number of field, block or deposit'
        ];
    }

    /**
     * @param string $title
     * @return int|null|string
     */
    protected function getKeyIfValid($title = '')
    {
        foreach ($this->annotationTitleMaping() as $key => $value) {
            if (trim(strtolower($title)) == trim(strtolower($value))) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param $data
     * @param $grab
     * @return array
     */
    protected function filterData($data, $grab)
    {
        $return = [];
        foreach ($grab as $key => $map) {
            if (is_string($map) && property_exists($data, $map)) {
                $return[$key] = $data->$map;
                continue;
            }

            if (is_array($map)) {
                list($parent, $child) = $map;
                if (property_exists($data, $parent) && property_exists($data->$parent, $child)) {
                    $return[$key] = $data->$parent->$child;
                }
            }
        }

        return $return;
    }


    /**
     * @param $annotations
     * @return array
     */
    protected function extractMetadata($annotations)
    {
        $return = [];

        foreach ($annotations as $key => $value) {

            $title = explode('//', $value->title);

            if (isset($title[1])) {
                $this->annotation_title[] = $title[1];

                if ($key = $this->getKeyIfValid($title[1])) {
                    $return[$key] = $value->content;
                }
            }
        }

        //  $return = array_merge(array_keys($this->annotationTitleMaping(), $return));

        return $return;
    }

    /**
     * @return array
     */
    public function getTitles()
    {
        return $this->annotation_title;
    }

    /**
     * Get Contract Array
     *
     * @param $data
     * @return array
     */
    protected function getContractArray($data)
    {
        $contract = config('metadata.schema');

        $company_template = $contract['metadata']['company'][0];

        $contract['user_id']               = 1;
        $contract['file']                  = $data['file'];
        $contract['filehash']              = getFileHash($this->getMigrationFile($data['file']));
        $contract['metadata']['file_size'] = filesize($this->getMigrationFile($data['file']));

        $contract['created_datetime'] = $this->dateFormat($data['created_datetime']);
        $contract['updated_datetime'] = $this->dateFormat($data['updated_datetime']);

        $contract['metadata']['language']       = 'EN';
        $contract['metadata']['signature_date'] = $this->getSignatureDate([$data['signature_date_1'], $data['signature_date_2']]);
        $contract['metadata']['signature_year'] = $this->getSignatureYear([$data['signature_year_1'], $data['signature_year_2']]);

        $contract['metadata']['contract_name']                 = $data['contract_name'];
        $contract['metadata']['resources']                     = $this->getResources($data);
        $contract['metadata']['country']                       = $this->getCountry($data['country']);
        $contract['metadata']['contract_identifier']           = $data['contract_identifier'];
        $contract['metadata']['project_title']                 = $data['project_title'];
        $contract['metadata']['type_of_contract']              = $this->getTypeOfContract([$data['type_of_contract_1'], $data['type_of_contract_1']]);
        $contract['metadata']['concession'][0]['license_name'] = $data['license_concession_name'];

        $company_arr = array_map('trim', [$data['company']]);
        $companies   = [];

        foreach ($company_arr as $company) {
            $company_template['name'] = $company;
            $companies[]              = $company_template;
        }
        $contract['metadata']['company'] = $companies;

        return $contract;
    }

    /**
     * Get Formatted date
     *
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        $time = strtotime($date);

        if ($time != '') {
            return date('Y-m-d H:i:s', $time);
        }

        return '';
    }

    /**
     * Get Country code and name
     *
     * @param $country
     * @return array
     */
    protected function getCountry($country)
    {
        return $this->country->getCountryByName($country);
    }


    /**
     * Get Resources
     *
     * @param array $data
     * @return array
    \     */
    protected function getResources(array $data)
    {
        $resource_string = $data["resources"];
        $resource_array  = explode(',', $resource_string);
        $resource_array  = array_filter($resource_array + [$data["resources_1"], $data["resources_2"], $data["resources_3"], $data["resources_4"], $data["resources_5"]]);
        $valid_res       = [];
        $resource_list   = trans('codelist/resource');


        foreach ($resource_array as $resource) {
            if (array_key_exists($resource, $resource_list)) {
                $valid_res[] = $resource;
            }
        }

        return $valid_res;
    }

    /**
     * Get Type of Contracts
     *
     * @param array $type_of_contract
     * @return bool
     */
    protected function getTypeOfContract(array $type_of_contract)
    {
        $toc = array_map('trim', $type_of_contract);
        $toc = array_filter($toc);

        if (!empty($toc)) {
            return $toc[0];
        }

        return '';
    }

    /**
     * Get Signature Year
     *
     * @param array $signature_year
     * @return string
     */
    protected function getSignatureYear(array $signature_year)
    {
        $signature_year = array_map('trim', $signature_year);
        $signature_year = array_filter($signature_year);

        if (!empty($signature_year[0])) {
            return date('Y', strtotime($signature_year[0]));
        }

        return "";
    }

    /**
     * Get Signature Date
     *
     * @param array $signature_year
     * @return string
     */
    protected function getSignatureDate(array $signature_date)
    {
        $signature_date = array_map('trim', $signature_date);
        $signature_date = array_filter($signature_date);

        if (!empty($signature_date[0])) {
            return date('Y-m-d', strtotime($signature_date[0]));
        }

        return "";
    }

    /**
     * Check if pdf file exists
     *
     * @param $file
     * @return bool
     */
    protected function isPdfExists($file)
    {
        $file     = $this->getMigrationFile($file);
        $fileHash = getFileHash($file);

        if ($con = $this->contract->getContractByFileHash($fileHash)) {
            $this->filesystem->delete($file);

            return true;
        }

        return false;
    }


    /**
     * Upload Pdf to s3 and create contracts
     *
     * @return null|Contract
     */
    public function uploadPdfToS3AndCreateContracts($contract)
    {
        try {
            $this->storage->disk('s3')->put(
                $contract->file,
                $this->filesystem->get($this->getMigrationFile($contract->file))
            );
        } catch (Exception $e) {
            $this->logger->error(sprintf('File could not be uploaded : %s', $e->getMessage()));

            return false;
        }

        $data = [
            'file'     => $contract->file,
            'filehash' => $contract->filehash,
            'user_id'  => $contract->user_id,
            'metadata' => $contract->metadata,
        ];

        try {
            $con = $this->contract->save($data);

            $this->logger->activity('contract.log.save', ['contract' => $con->id], $con->id, $con->user_id);

            if ($con) {
                $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $con->id]);
            }

            $this->logger->info(
                'Contract successfully created.',
                ['Contract Title' => $con->title]
            );
            $this->filesystem->delete($this->getMigrationFile($contract->file));

            return $con;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            if ($this->storage->disk('s3')->exists($contract->file)) {
                $this->storage->disk('s3')->delete($contract->file);
            }
            $this->filesystem->delete($this->getMigrationFile($contract->file));

            return null;
        }

    }


    /**
     * Get Migration File
     *
     * @param string $fileName
     * @return string
     */
    public function getMigrationFile($fileName = '')
    {
        return sprintf('%s/%s', public_path(static::UPLOAD_FOLDER), $fileName);
    }

    /**
     * Download a Pdf File
     *
     * @param $pdf
     * @return null|string
     */
    protected function downloadPdf($pdf)
    {
        $pdf_name  = sha1(str_random()) . '.pdf';
        $temp_path = $this->getMigrationFile($pdf_name);

        try {
            copy($pdf, $temp_path);

            return $pdf_name;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }


}
