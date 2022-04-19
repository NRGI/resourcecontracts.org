<?php namespace App\Nrgi\Services\Download;

use App\Nrgi\Services\User\UserService;
use App\Nrgi\Services\Contract\Annotation\AnnotationService;
use GuzzleHttp\Client;
use App\Nrgi\Services\Contract\ContractService;
use Maatwebsite\Excel\Excel;
use App\Nrgi\Entities\Contract\Contract;

/**
 * Class APIService
 * @package App\Http\Services
 */
class DownloadService
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var Excel
     */
    protected $excel;

    /**
     * @param Client            $client
     * @param ContractService   $contractService
     * @param AnnotationService $annotationService
     * @param Excel             $excel
     */
    public function __construct(Client $client, ContractService $contractService, AnnotationService $annotationService, Excel $excel, UserService $userService)
    {
        $this->client            = $client;
        $this->contractService   = $contractService;
        $this->annotationService = $annotationService;
        $this->userService       = $userService;
        $this->excel             = $excel;
    }

    /**
     * Download as CSV
     *
     * @param $contracts
     */
    public function downloadData($contracts)
    {
        set_time_limit(0);
        $web_url            = env('APP_DOMAIN');
        $users              = $this->userService->getAllUsersList();
        $annotationStatus   = $this->annotationService->getAllAnnotationStatus();
        $supportingDocs     = $this->contractService->getAllSupportingDocuments();
        $text_type          = [
                                1 => "Structured",
                                2 => "Needs Editing",
                                3 => "Needs Full Transcription",
                            ];
        $bucket_url         = substr(\Storage::disk('s3')
                                ->getDriver()
                                ->getAdapter()
                                ->getClient()
                                ->getObjectUrl(env('AWS_BUCKET'),'/'),0,-1);

        foreach ($contracts as $key => $contract) {
            $contracts[$key]['Resource']                        = join(';', json_decode($contract['Resource']));
            $contracts[$key]['Category']                        = join(';', json_decode($contract['Category']));
            $contracts[$key]['Contract Type']                   = join(';', json_decode($contract['Contract Type']));
            $contracts[$key]['Text Type']                       = $contract['Text Type'] ? $text_type[$contract['Text Type']] : '';
            $contracts[$key]['Show PDF Text']                   = $contracts[$key]['Show PDF Text'] == '0' ? 'No' : 'Yes';
            $contracts[$key]['Annotation Status']               = isset($annotationStatus[$contract["Contract ID"]])? $annotationStatus[$contract["Contract ID"]] : '';
            $contracts[$key]['Associated Documents']            = isset($supportingDocs[$contract["Contract ID"]]) ? $supportingDocs[$contract["Contract ID"]] : '';
            $contracts[$key]['Created by']                      = isset($users[$contract['Created by']]) ? $users[$contract['Created by']] : '';
            $contracts[$key]['Operator']                        = join(';', $this->getOperator(json_decode($contract['Operator']), 'operator'));
            $contracts[$key]['License Name']                    = join(';', $this->makeSemicolonSeparated(json_decode($contract["License Name"]),'license_name'));
            $contracts[$key]['License Identifier']              = join(';', $this->makeSemicolonSeparated(json_decode($contract["License Identifier"]),'license_identifier'));
            $contracts[$key]['Government Entity']               = join(';', $this->makeSemicolonSeparated(json_decode($contract['Government Entity']),'entity'));
            $contracts[$key]['Government Identifier']           = join(';', $this->makeSemicolonSeparated(json_decode($contract['Government Identifier']),'identifier'));
            $contracts[$key]['Company Name']                    = join(';', $this->makeSemicolonSeparated(json_decode($contract['Company Name']),'name'));
            $contracts[$key]['Jurisdiction of Incorporation']   = join(';', $this->makeSemicolonSeparated(json_decode($contract['Jurisdiction of Incorporation']),'jurisdiction_of_incorporation'));
            $contracts[$key]['Registration Agency']             = join(';', $this->makeSemicolonSeparated(json_decode($contract['Registration Agency']),'registration_agency'));
            $contracts[$key]['Company Number']                  = join(';', $this->makeSemicolonSeparated(json_decode($contract['Company Number']),'company_number'));
            $contracts[$key]['Company Address']                 = join(';', $this->makeSemicolonSeparated(json_decode($contract['Company Address']),'company_address'));
            $contracts[$key]['Participation Share']             = join(';', $this->makeSemicolonSeparated(json_decode($contract['Participation Share']),'participation_share'));
            $contracts[$key]['Corporate Grouping']              = join(';', $this->makeSemicolonSeparated(json_decode($contract['Corporate Grouping']),'parent_company'));
            $contracts[$key]['Open Corporates Link']            = join(';', $this->makeSemicolonSeparated(json_decode($contract['Open Corporates Link']),'open_corporates_id'));
            $contracts[$key]['Incorporation Date']              = join(';', $this->makeSemicolonSeparated(json_decode($contract['Incorporation Date']),'company_founding_date'));
            $publishingDateObj                                  = json_decode($contract['Publish Date'], true);
            $publishingDate                                     = isset($publishingDateObj['metadata']) && isset($publishingDateObj['metadata']['status']) && $publishingDateObj['metadata']['status'] === Contract::STATUS_PUBLISHED ? $publishingDateObj['metadata']['datetime'] : '';
            $contracts[$key]['Publish Date']                    = isset($publishingDate) ? $publishingDate : '';
            $contracts[$key]['RC Admin Link']                   = isset($web_url) && isset($contract['Contract ID']) ? $web_url.'/contract/'.$contract['Contract ID'] : '';
            $contracts[$key]['PDF URL']                         = $bucket_url.$contract['Contract ID'].'/'.$contract['PDF URL'];
        }

        $filename = "export" . date('Y-m-d');

        $this->excel->create(
            $filename,
            function ($csv) use (&$contracts) {
                $csv->sheet(
                    'sheetname',
                    function ($sheet) use (&$contracts) {
                        $sheet->fromArray($contracts);
                    }
                );
            }
        )->download('xls');        
    }

    /**
     * Format all the contracts data
     *
     * @param $contracts
     * @return array
     */
    private function formatCSVData($contracts)
    {
        $data = [];
        foreach ($contracts as $contract) {
            $data[] = $this->getCSVData($contract);
        }
    
        return $data;
    }

    /**
     * Make the array semicolon separated for multiple data
     *
     * @param $arrays
     * @param $key
     * @return array
     */
    private function makeSemicolonSeparated($arrays, $key)
    {
        $data = [];

        if ($arrays == null) {
            return $data;
        }

        foreach ($arrays as $array) {
            if (is_array($array) && array_key_exists($array, $key)) {
                array_push($data, $array[$key]);
            }

            if (is_object($array) && property_exists($array, $key)) {
                array_push($data, $array->$key);
            }
        }

        return $data;
    }

    /**
     * Return the operator
     *
     * @param $company
     * @return array
     */
    private function getOperator($company)
    {
        $data     = [];
        $operator = trans('operator');

        foreach ($company as $companyData) {
            if (isset($companyData->operator) && $companyData->operator) {
                array_push($data, $operator[$companyData->operator]);
            }
        }

        return $data;
    }

    /**
     * Return the Text Type for each contract
     *
     * @param $id
     * @return string
     */
    public function getTextType($id)
    {
        if ($id == 1) {
            return "Structured";
        } else {
            if ($id == 2) {
                return "Needs Editing";
            } else {
                if ($id == 3) {
                    return "Needs Full Transcription";
                }
            }
        }
    }

    /**
     * Get Supporting Documents for each contract.
     *
     * @param $id
     * @return array
     */
    public function getSupportingDoc($id)
    {
        $supportingDocs = $this->contractService->getSupportingDocuments($id);
        $supportingDoc  = [];
        foreach ($supportingDocs as $support) {
            array_push($supportingDoc, $support['id']);
        }

        return $supportingDoc;
    }

    /**
     * Get Show PDF Text Status
     *
     * @param $status
     * @return string
     */
    public function getShowPDFText($status)
    {
        if ($status == '0') {
            return "No";
        } else {
            return "Yes";
        }
    }
}
