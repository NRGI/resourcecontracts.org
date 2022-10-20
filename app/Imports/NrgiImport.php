<?php

namespace App\Imports;
use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NrgiImport implements FromCollection, WithHeadingRow
{
    //code to support old processing of version 2.0 in rest of the app
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Collection|null
    */
    public function collection()
    {
    }
}
