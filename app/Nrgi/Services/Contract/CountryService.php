<?php namespace App\Nrgi\Services\Contract;

/**
 * Class CountryService
 * @package App\Nrgi\Services\Contract
 */
class CountryService
{
    /**
     * @var array
     */
    protected $countries = array();

    public function __construct()
    {
        $this->countries = trans('codelist/country');
    }

    /**
     * Get List of Countries
     * @return array
     */
    public function all()
    {
        return $this->countries;
    }

    /**
     * Get Country by information
     * @param $code
     * @return string
     */
    public function getInfoByCode($code)
    {
        $countries = $this->countries;

        return isset($countries[$code]) ? ['code' => $code, 'name' => $countries[$code]] : '';
    }
}
