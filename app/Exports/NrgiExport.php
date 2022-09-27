<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NrgiExport implements FromArray, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $contracts;
    protected $headingsArr = [
        'Contract ID',
        'OCID',
        "Category",
        'Contract Name',
        'Contract Identifier',
        "Resource",
        "Language",
        "Country",
        "Resource",
        "Contract Type",
        "Signature Date",
        "Document Type",
        "Government Entity",
        "Government Identifier",
        "Company Name",
        "Jurisdiction of Incorporation",
        "Registration Agency",
        "Company Number",
        "Company Address",
        "Participation Share",
        "Corporate Grouping",
        "Open Corporates Link",
        "Incorporation Date",
        "Operator",
        "Project Title",
        "Project Identifier",
        "License Name",
        "License Identifier",
        "Source URL",
        "Disclosure Mode",
        "Retrieval Date",
        "Publish Date",
        "PDF URL",
        "Associated Documents",
        "PDF Type",
        "Text Type",
        "Show PDF Text",
        "Metadata Status",
        "Annotation Status",
        "PDF Text Status",
        "Created by",
        "Created on",
        "RC Admin Link"
    ];

    public function __construct(array $contracts)
    {
        $this->contracts = $contracts;
    }

    public function array(): array
    {
        return $this->contracts;
    }

    public function styles(Worksheet $sheet)
    {
        return [
        // Style the first row as bold text.
        1    => ['font' => ['bold' => true]],
        ];
    }

    public function map($contract): array {
        $arr = [];
        foreach ($this -> headingsArr as $value) 
        {
            array_push($arr, $contract[$value]);
        }
        return $arr;
    }

    public function headings(): array 
    {
        return  $this -> headingsArr;
    }
}
