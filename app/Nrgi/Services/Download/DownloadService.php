<?php namespace App\Nrgi\Services\Download;

use App\Nrgi\Entities\User\User;
use App\Nrgi\Services\Contract\Annotation\AnnotationService;
use GuzzleHttp\Client;
use App\Nrgi\Services\Contract\ContractService;
use Maatwebsite\Excel\Excel;

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
    public function __construct(Client $client, ContractService $contractService, AnnotationService $annotationService, Excel $excel)
    {
        $this->client            = $client;
        $this->contractService   = $contractService;
        $this->annotationService = $annotationService;
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
        $users = User::pluck('name','id')->toArray();
        $text_type = [
                        1 => "Structured",
                        2 => "Needs Editing",
                        3 => "Needs Full Transcription",
                    ];
       
        foreach ($contracts as $key => $contract) {
            $contracts[$key]['Resource']                        = join(';', json_decode($contract['Resource']));
            $contracts[$key]['Category']                        = join(';', json_decode($contract['Category']));
            $contracts[$key]['Contract Type']                   = join(';', json_decode($contract['Contract Type']));
            $contracts[$key]['Annotation Status']               = $this->annotationService->getStatus($contract["Contract Id"]);
            $contracts[$key]['Text Type']                       = $contract['Text Type'] ? $text_type[$contract['Text Type']] : '';
            $contracts[$key]['Show PDF Text']                   = $contracts[$key]['Show PDF Text'] == '0' ? 'No' : 'Yes';
            $contracts[$key]['Associated Documents']            = join(';', $this->getSupportingDoc($contract["Contract Id"]));
            $contracts[$key]['License Name']                    = join(';', $this->makeSemicolonSeparated(json_decode($contract["License Name"]),'license_name'));
            $contracts[$key]['License Identifier']              = join(';', $this->makeSemicolonSeparated(json_decode($contract["License Identifier"]),'license_identifier'));
            $contracts[$key]['Created by']                      = isset($users[$contract['Created by']]) ? $users[$contract['Created by']] : '';
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
            $contracts[$key]['Operator']                        = join(';', $this->getOperator(json_decode($contract['Operator']), 'operator'));
        }


        //load testing
        sleep(60);
        $data = [];
        foreach ($contracts as $key => $c) {
            sleep(.001);
            $c['test'] = 'memory';
            $c['test2'] = 'memory';
            $data[$key][1] = $key%2 == 0 ? $key+1 : $key;
            $data[$key][3] = $key*$key;
            $data[$key][2] = $c;
        }
        //end of load testing
        
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
     * Return the format of csv
     *
     * @param       $contract
     * @return array
     *
     *     */
    private function getCSVData($contract)
    {
        $created_by = isset($contract->created_user()->first()->name) ? $contract->created_user()->first()->name : '';
        $company_type = is_array($contract->metadata->type_of_contract) ? $contract->metadata->type_of_contract : [];
        return [
            'Contract ID'                   => $contract->id,
            'OCID'                          => $contract->metadata->open_contracting_id,
            'Category'                      => join(';', $contract->metadata->category),
            'Contract Name'                 => $contract->metadata->contract_name,
            'Contract Identifier'           => $contract->metadata->contract_identifier,
            'Language'                      => $contract->metadata->language,
            'Country Name'                  => $contract->metadata->country->name,
            'Resource'                      => join(';', $contract->metadata->resource),
            'Contract Type'                 => join(';', $company_type),
            'Signature Date'                => $contract->metadata->signature_date,
            'Document Type'                 => $contract->metadata->document_type,
            'Government Entity'             => join(';', $this->makeSemicolonSeparated($contract->metadata->government_entity, 'entity')),
            'Government Identifier'         => join(';', $this->makeSemicolonSeparated($contract->metadata->government_entity, 'identifier')),
            'Company Name'                  => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'name')),
            'Jurisdiction of Incorporation' => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'jurisdiction_of_incorporation')),
            'Registration Agency'           => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'registration_agency')),
            'Company Number'                => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'company_number')),
            'Company Address'               => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'company_address')),
            'Participation Share'           => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'participation_share')),
            'Corporate Grouping'            => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'parent_company')),
            'Open Corporates Link'          => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'open_corporate_id')),
            'Incorporation Date'            => join(';', $this->makeSemicolonSeparated($contract->metadata->company, 'company_founding_date')),
            'Operator'                      => join(';', $this->getOperator($contract->metadata->company, 'operator')),
            'Project Title'                 => $contract->metadata->project_title,
            'Project Identifier'            => $contract->metadata->project_identifier,
            'License Name'                  => join(';', $this->makeSemicolonSeparated($contract->metadata->concession, 'license_name')),
            'License Identifier'            => join(';', $this->makeSemicolonSeparated($contract->metadata->concession, 'license_identifier')),
            'Source Url'                    => $contract->metadata->source_url,
            'Disclosure Mode'               => $contract->metadata->disclosure_mode,
            'Retrieval Date'                => $contract->metadata->date_retrieval,
            'Pdf Url'                       => $contract->metadata->file_url,
            'Associated Documents'          => join(';', $this->getSupportingDoc($contract->id)),
            'Pdf Type'                      => $contract->pdf_structure,
            'Show Pdf Text'                 => $this->getShowPDFText($contract->metadata->show_pdf_text),
            'Text Type'                     => $this->getTextType($contract->textType),
            'Metadata Status'               => $contract->metadata_status,
            'Annotation Status'             => $this->annotationService->getStatus($contract->id),
            'Pdf Text Status'               => $contract->text_status,
            'Created by'                    => $created_by,
            'Created on'                    => $contract->created_datetime,
        ];
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
