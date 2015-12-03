<?php
namespace App\Http\Services;

use App\Nrgi\Entities\Contract\Contract;
use GuzzleHttp\Client;

use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\AnnotationService;

/**
 * Class APIService
 * @package App\Http\Services
 */
class DownloadService
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @param Client            $client
     * @param ContractService   $contractService
     * @param AnnotationService $annotationService
     */
    public function __construct(Client $client, ContractService $contractService, AnnotationService $annotationService)
    {
        $this->client            = $client;
        $this->contractService   = $contractService;
        $this->annotationService = $annotationService;
    }

    /**
     * Download as CSV
     * @param $contracts
     */
    public function downloadData($contracts)
    {
        $data = [];
        foreach ($contracts as $contract) {
            $metadata = $contract->metadata;
            $data[]   = $this->getCSVData($contract);

        }
        $heading = [
            'Contract ID',
            'OCID',
            'Category',
            'Contract Name',
            'Contract Identifier',
            'Language',
            'Country Name',
            'Resource',
            'Contract Type',
            'Signature Date',
            'Document Type',
            'Government Entity',
            'Government Identifier',
            'Company Name',
            'Jurisdiction of Incorporation',
            'Registration Agency',
            'Company Number',
            'Company Address',
            'Participation Share',
            'Corporate Grouping',
            'Open Corporates Link',
            'Incorporation Date',
            'Operator',
            'Project Title',
            'Project Identifier',
            'License Name',
            'License Identifier',
            'Source Url',
            'Disclosure Mode',
            'Retrieval Date',
            'Pdf Url',
            'Associated Documents',
            'Pdf Type',
            'Show Pdf Text',
            'Text Type',
            'Metadata Status',
            'Annotation Status',
            'Pdf Text Status',
            'Created by',
            'Created on'
        ];


        $filename = "export" . date('Y-m-d');
        header('Content-type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        $file = fopen('php://output', 'w');
        fputcsv($file, $heading);


        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
        die;


    }

    /**
     * Format all the contracts data
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
     * @param       $contract
     * @return array
     *
     *     */
    private function getCSVData($contract)
    {
        return [
            $contract->id,
            $contract->metadata->open_contracting_id,
            join(';', $contract->metadata->category),
            $contract->metadata->contract_name,
            $contract->metadata->contract_identifier,
            $contract->metadata->language,
            $contract->metadata->country->name,
            implode(';', $contract->metadata->resource),
            implode(';', $contract->metadata->type_of_contract),
            $contract->metadata->signature_date,
            $contract->metadata->document_type,
            implode(';', $this->makeSemicolonSeparated($contract->metadata->government_entity, 'entity')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->government_entity, 'identifier')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'name')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'jurisdiction_of_incorporation')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'registration_agency')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'company_number')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'company_address')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'participation_share')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'parent_company')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'open_corporate_id')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->company, 'company_founding_date')),
            implode(';', $this->getOperator($contract->metadata->company, 'operator')),
            $contract->metadata->project_title,
            $contract->metadata->project_identifier,
            implode(';', $this->makeSemicolonSeparated($contract->metadata->concession, 'license_name')),
            implode(';', $this->makeSemicolonSeparated($contract->metadata->concession, 'license_identifier')),
            $contract->metadata->source_url,
            $contract->metadata->disclosure_mode,
            $contract->metadata->date_retrieval,
            $contract->metadata->file_url,
            implode(';', $this->getSupportingDoc($contract->id)),
            $contract->pdf_structure,
            $this->getShowPDFText($contract->metadata->show_pdf_text),
            $this->getTextType($contract->textType),
            $contract->metadata_status,
            $this->annotationService->getStatus($contract->id),
            $contract->text_status,
            $contract->created_user()->first()->name,
            $contract->created_datetime,
        ];
    }

    /**
     * Make the array semicolon separated for multiple data
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
