<?php

namespace App\Imports;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class NrgiContractsImport implements WithMultipleSheets
{
    //code to support old processing of version 2.0 in rest of the app
    public function sheets(): array
    {
        return [
            0 => new NrgiImport(),
            1 => new NrgiImport(),
        ];
    }
}
