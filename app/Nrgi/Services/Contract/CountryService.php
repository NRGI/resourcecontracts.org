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

    function __construct()
    {
        $this->countries = config('nrgi.country');
    }

    /**
     * Get List of Countries
     */
    public function lists()
    {
        $data = array();

        foreach ($this->countries as $id => $country) {
            $data[$id] = $country['name'];
        }

        return $data;
    }
}
