<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Illuminate\Contracts\Filesystem\Factory as Storage;

class ImportService
{
    const UPLOAD_FOLDER = 'data/temp';
    const PIPELINE      = 0;
    const PROCESSING    = 1;
    const COMPLETED     = 2;
    const FAILED        = 3;

    const CREATE_PENDING    = 0;
    const CREATE_PROCESSING = 1;
    const CREATE_COMPLETED  = 2;
    const CREATE_FAILED     = 3;

    /**
     * @var Excel
     */
    protected $excel;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var Queue
     */
    protected $queue;
    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var CountryService
     */
    protected $country;

    /*
     * Valid Keys
     */
    protected $keys = [
        "contract",
        "title",
        "company",
        "pdf_url",
        "country",
        "concessionlicense_name",
        "signature_date",
        "language",
        "disclosure_mode"
    ];

    /**
     * @param ContractRepositoryInterface $contract
     * @param Excel                       $excel
     * @param Storage                     $storage
     * @param Filesystem                  $filesystem
     * @param Guard                       $auth
     * @param Log                         $logger
     * @param Queue                       $queue
     * @param CountryService              $country
     */
    public function __construct(ContractRepositoryInterface $contract, Excel $excel, Storage $storage, Filesystem $filesystem, Guard $auth, Log $logger, Queue $queue, CountryService $country)
    {
        $this->excel      = $excel;
        $this->filesystem = $filesystem;
        $this->auth       = $auth;
        $this->logger     = $logger;
        $this->queue      = $queue;
        $this->contract   = $contract;
        $this->storage    = $storage;
        $this->country    = $country;
    }

    /**
     * Import CSV File
     *
     * @param Request $request
     * @return bool|string
     * @throws Exception
     */
    public function import(Request $request)
    {
        $import_key = md5(microtime());

        try {
            $originalFileName = $request->file('file')->getClientOriginalName();
            $fileName         = $originalFileName;
            $request->file('file')->move($this->getFilePath($import_key), $fileName);
        } catch (\Exception $d) {
            $this->logger->error('File could not be uploaded');
            throw new Exception('File could not be uploaded.');
        }
        try {
            $contracts = $this->extractRecords($this->getFilePath($import_key, $fileName));
        } catch (Exception $e) {
            $this->logger->error('Import Error :' . $e->getMessage());
            $this->deleteImportFolder($import_key);
            throw new Exception('Could not extract data from file.');
        }

        if (empty($contracts)) {
            $this->deleteImportFolder($import_key);
            throw new Exception('Could not found any contract to import.');
        }

        if (!$this->isValidFormat($contracts)) {
            $this->deleteImportFolder($import_key);
            throw new Exception('File is not in valid format. Please check sample csv file for the valid format.');
        }

        $this->exportToJson($import_key, $contracts, $originalFileName);

        $this->queue->push(
            'App\Nrgi\Services\Queue\ContractDownloadQueue',
            ['import_key' => $import_key],
            'contract_download'
        );

        return $import_key;
    }

    /**
     * Read and extract records from file
     *
     * @param $file
     * @return array
     */
    protected function extractRecords($file)
    {
        return $this->excel->load($file)->all()->toArray();
    }

    /**
     * Save contracts array to json file
     *
     * @param $import_key
     * @param $results
     */
    protected function exportToJson($import_key, $results)
    {
        $data = [];

        foreach ($results as $key => $result) {
            $contract       = $this->getFormattedJsonData($result);
            $contract['id'] = $key + 1;
            $data[]         = $contract;
        }

        $this->updateJson($import_key, $data);
    }

    /**
     * Format the csv data to Contract array
     *
     * @param $results
     * @return array
     */
    protected function getFormattedJsonData(array $results)
    {
        $contract                              = config('metadata.schema');
        $company_template                      = $contract['metadata']['company'][0];
        $contract['metadata']['contract_name'] = $results['contract'];

        $company_arr = explode(',', $results['company']);
        $company_arr = array_map('trim', $company_arr);
        $companies   = [];

        foreach ($company_arr as $company) {
            $company_template['name'] = $company;
            $companies[]              = $company_template;
        }

        $contract['pdf_url'] = $results['pdf_url'];

        $contract['download_status']  = static::PIPELINE;
        $contract['download_remarks'] = '';

        $contract['create_status']  = '';
        $contract['create_remarks'] = '';

        $contract['user_id']                                   = $this->auth->id();
        $contract['metadata']['resource']                      = ['Hydrocarbons'];
        $contract['metadata']['project_title']                 = $results['title'];
        $contract['metadata']['company']                       = $companies;
        $contract['metadata']['disclosure_mode']               = $results['disclosure_mode'];
        $contract['metadata']['concession'][0]['license_name'] = $results['concessionlicense_name'];
        $contract['metadata']['country']                       = $this->getCountry($results['country']);
        $contract['metadata']['signature_date']                = $this->dateFormat($results['signature_date']);
        $contract['metadata']['language']                      = $this->getLanguage($results['language']);
        $contract['metadata']['category']                      = [];
        $metadata['metadata']['show_pdf_text']                 = 1;

        return $contract;
    }

    /**
     * Download Pdfs
     *
     * @param $import_key
     */
    public function download($import_key)
    {
        $contracts = $this->getJsonData($import_key);

        foreach ($contracts as $contract) {
            $this->updateContractJsonByID($import_key, $contract->id, ['download_status' => static::PROCESSING]);

            if (empty($contract->pdf_url)) {
                $this->updateContractJsonByID($import_key, $contract->id, ['download_remarks' => trans('Pdf file url is required'), 'download_status' => static::FAILED]);
                continue;
            }

            $file = $this->downloadPdf($import_key, $contract->pdf_url);

            if (is_null($file)) {
                $this->updateContractJsonByID($import_key, $contract->id, ['download_remarks' => trans('File could not be downloaded'), 'download_status' => static::FAILED]);
                continue;
            }

            $fileHash = getFileHash($this->getFilePath($import_key, $file));

            if ($con = $this->contract->getContractByFileHash($fileHash)) {
                $title = sprintf('<a href="%s" target="_blank">%s</a>', route('contract.show', $con->id), str_limit($con->title, 25));
                $this->updateContractJsonByID(
                    $import_key,
                    $contract->id,
                    ['download_remarks' => trans('contract.import.exist', ['link' => $title]), 'file' => $file, 'download_status' => static::FAILED]
                );
                $this->deleteFile($import_key, $file);
                continue;
            }

            $filePath = $this->getFilePath($import_key, $file);

            $this->updateContractJsonByID(
                $import_key,
                $contract->id,
                ['file' => $file, 'filehash' => $fileHash, 'file_size' => filesize($filePath), 'download_status' => static::COMPLETED]
            );

        }
    }

    /**
     * Update Contract Json file
     *
     * @param       $key
     * @param       $id
     * @param array $updateData
     * @param int   $step
     * @return bool
     */
    protected function updateContractJsonByID($key, $id, array $updateData, $step = 1)
    {
        $contracts = $this->getJsonData($key);

        foreach ($contracts as &$contract) {

            if ($contract->id == $id) {

                foreach ($updateData as $ckey => $value) {

                    if (property_exists($contract, $ckey)) {
                        $contract->$ckey = $value;
                    }

                    if (property_exists($contract->metadata, $ckey)) {
                        $contract->metadata->$ckey = $value;
                    }
                }

                break;
            }

        }

        return $this->updateJson($key, $contracts, $step);
    }

    /**
     * Download a Pdf File
     *
     * @param $key
     * @param $pdf
     * @return null|string
     */
    protected function downloadPdf($key, $pdf)
    {
        $pdf_name  = sha1(str_random()) . '.pdf';
        $temp_path = $this->getFilePath($key, $pdf_name);

        try {
            copy($pdf, $temp_path);

            return $pdf_name;
        } catch (\Exception $e) {
            $this->logger->error('PDF Download Error:' . $e->getMessage());

            return null;
        }
    }

    /**
     * Save all Contracts
     *
     * @param $key
     * @param $ids
     * @return bool
     */
    public function saveContracts($key, $ids)
    {
        if (empty($ids)) {
            return false;
        }

        $all_data  = $this->getJsonData($key);
        $contracts = [];

        foreach ($all_data as $data) {
            if (in_array($data->id, $ids) && $data->download_status == static::COMPLETED) {
                $data->create_status = static::CREATE_PENDING;
                $contracts[]         = $data;
            }
        }

        $this->updateJson($key, $contracts, 2);

        $this->queue->push(
            'App\Nrgi\Services\Queue\UploadBulkPdf',
            ['key' => $key],
            'contract_bulk_create'
        );

        return true;
    }

    /**
     * Upload Pdf to s3 and create contracts
     *
     * @param $key
     */
    public function uploadPdfToS3AndCreateContracts($key)
    {
        $contracts = $this->getJsonData($key);

        foreach ($contracts as $contract) {

            $this->updateContractJsonByID($key, $contract->id, ['create_status' => static::CREATE_PROCESSING], 2);

            try {
                $this->storage->disk('s3')->put(
                    $contract->file,
                    $this->filesystem->get($this->getFilePath($key, $contract->file))
                );
            } catch (Exception $e) {
                $this->logger->error(sprintf('File could not be uploaded : %s', $e->getMessage()));

                continue;
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
                $this->updateContractJsonByID($key, $contract->id, ['create_status' => static::CREATE_COMPLETED], 2);

                if ($con) {
                    $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $con->id]);
                }

                $this->logger->info(
                    'Contract successfully created.',
                    ['Contract Title' => $con->title]
                );
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                if ($this->storage->disk('s3')->exists($contract->file)) {
                    $this->storage->disk('s3')->delete($contract->file);
                }
                $this->updateContractJsonByID($key, $contract->id, ['create_remarks' => trans('contract.save_fail'), 'create_status' => static::CREATE_FAILED], 2);
            }
            $this->deleteFile($key, $contract->file);
        }
    }

    /**
     * Get Contract Json
     *
     * @param      $key
     * @param bool $contractOnly
     * @return object|null
     */
    public function getJsonData($key, $contractOnly = true)
    {
        try {
            $filename = $key . '.json';
            $data     = $this->filesystem->get($this->getFilePath($key, $filename));
            $data     = json_decode($data);

            if ($contractOnly) {
                return $data->contracts;
            }

            return $data;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }

    /**
     * Update Contract Json
     *
     * @param     $key
     * @param     $contracts
     * @param int $step
     * @return bool
     */
    protected function updateJson($key, $contracts, $step = 1)
    {
        $data_string = json_encode(['step' => $step, 'contracts' => $contracts]);
        $fileName    = $key . '.json';
        $filePath    = $this->getFilePath($key, $fileName);

        return $this->filesystem->put($filePath, $data_string);
    }

    /**
     * Get File
     *
     * @param string $key
     * @param string $fileName
     * @return string
     */
    public function getFilePath($key = '', $fileName = '')
    {
        if ($key == '' && $fileName == '') {
            return public_path(static::UPLOAD_FOLDER);
        }

        if ($key != '' && $fileName != '') {
            return sprintf('%s/%s/%s', public_path(static::UPLOAD_FOLDER), $key, $fileName);
        }

        if ($key != '' && $fileName == '') {
            return sprintf('%s/%s', public_path(static::UPLOAD_FOLDER), $key);
        }
    }

    /**
     * Delete File from temp folder
     *
     * @param $key
     * @param $file
     * @return bool
     */
    protected function deleteFile($key, $file)
    {
        return $this->filesystem->delete($this->getFilePath($key, $file));
    }

    /**
     * Get Import Job by User
     *
     * @return array
     */
    public function getImportJobByUser()
    {
        $dirs     = $this->filesystem->directories($this->getFilePath());
        $user_key = [];

        if (empty($dirs)) {
            return $user_key;
        }

        foreach ($dirs as $dir) {

            $key  = last(explode('/', $dir));
            $json = $this->getJsonData($key, false);

            if (empty($json)) {
                continue;
            }

            $contracts = $json->contracts;

            if (isset($contracts[0]->user_id) && $contracts[0]->user_id == $this->auth->id()) {
                $user_key[] = [
                    'step'         => $json->step,
                    'key'          => $key,
                    'is_completed' => $this->isImportCompleted($key),
                    'file'         => $this->getOriginalFileName($key)
                ];
            }

        }

        return $user_key;
    }

    /**
     * Get Original Imported FileName
     *
     * @param $key
     * @return string|null
     */
    protected function getOriginalFileName($key)
    {
        $files = $this->filesystem->allFiles($this->getFilePath($key));

        foreach ($files as $file) {
            if (!in_array(pathinfo($file, PATHINFO_EXTENSION), ['pdf', 'json'])) {
                return ['name' => $file->getRelativePathname(), 'created_at' => filemtime($file)];
            }
        }

        return null;
    }

    /**
     * Get Language code
     *
     * @param $language
     * @return mixed
     */
    protected function getLanguage($lang)
    {
        $languages = trans('codelist/language')['major'] + trans('codelist/language')['minor'];

        foreach ($languages as $code => $name) {
            if ($name == $code || $name == $lang) {
                return $code;
            }
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
     * Delete Import Folder
     *
     * @param $key
     * @return bool
     */
    public function deleteImportFolder($key)
    {
        return $this->filesystem->deleteDirectory($this->getFilePath($key));
    }

    /**
     * check whether Import Completed or not
     *
     * @param $key
     * @return bool
     */
    public function isImportCompleted($key)
    {
        $contracts = $this->getJsonData($key);

        foreach ($contracts as $contract) {
            if ($contract->create_status < static::COMPLETED) {
                return false;
            }
        }

        return true;
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
            return date('Y-m-d', $time);
        }

        return '';
    }

    /**
     * Check for valid format
     *
     * @param $contracts
     * @return bool
     */
    protected function isValidFormat($contracts)
    {
        $titles = array_keys($contracts[0]);
        $diff   = array_diff($this->keys, $titles);

        if (count($diff) > 0) {
            return false;
        }

        return true;
    }


}
