<?php

namespace App\Nrgi\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class ContractImport implements ToCollection
{
    /**
     * @param array @$row
     *
     * @return array
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            return [
                "category"  => @$row[0],
                // "" => @$row[1],
                // "" => @$row[2],
                "document_url" => @$row[3],
                "contract_name" => @$row[4],
                "language"  => @$row[5],
                "country_code"  => @$row[6],
                "resource"  => @$row[7],
                "contract_type"  => @$row[8],
                "signature_date"  => @$row[9],
                "signature_year" => @$row[10],
                "contract_signed" => @$row[11],
                "document_type"  => @$row[12],
                "government_entity"  => @$row[13],
                "company_name"  => @$row[14],
                "jurisdiction_of_incorporation"  => @$row[15],
                "registration_agency"  => @$row[16],
                "company_number"  => @$row[17],
                "company_address"  => @$row[18],
                "participation_share"  => @$row[19],
                "corporate_grouping"  => @$row[20],
                "open_corporate_link"  => @$row[21],
                "incorporation_date"  => @$row[22],
                "operator"  => @$row[23],
                "project_title"  => @$row[24],
                "project_identifier"  => @$row[25],
                "license_name"  => @$row[26],
                "license_identifier"  => @$row[27],
                "disclosure_mode"  => @$row[28],
                "retrieval_date"  => @$row[29],
                "source_url"  => @$row[30],
                "matrix_page"  => @$row[31],
                "deal_number"  => @$row[32],
            ];
        }
        
     
    }
}