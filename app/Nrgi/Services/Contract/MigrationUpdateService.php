<?php
namespace App\Nrgi\Services\Contract;

use Illuminate\Contracts\Logging\Log;
use Illuminate\Filesystem\Filesystem;
use Maatwebsite\Excel\Excel;

/**
 * Class migration Document Cloud contracts update from xls file
 * @package App\Nrgi\Services\Contract
 */
class MigrationUpdateService
{
    /**
     * @var Excel
     */
    protected $excel;

    /**
     * @param Excel $excel
     */
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }


    /**
     * Updates document cloud contract
     * @param $file
     */
    function update($file)
    {
        $contracts = $this->extractRecords($file);
        dd(count($contracts));

    }

    /**
     * Read and extract records from file
     *
     * @param $file
     * @return array
     */
    protected
    function extractRecords(
        $file
    ) {
        return $this->excel->load($file)->all()->toArray();
    }


}
