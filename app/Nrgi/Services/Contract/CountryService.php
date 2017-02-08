<?php namespace App\Nrgi\Services\Contract;

use Illuminate\Auth\Guard;

/**
 * Class CountryService
 * @package App\Nrgi\Services\Contract
 */
class CountryService
{
    /**
     * @var array
     */
    protected $countries = [];

    protected $auth;

    /**
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth      = $auth;
        $this->countries = trans('codelist/country');
    }

    /**
     * Get List of Countries
     * @return array
     */
    public function all()
    {
        if ($this->auth->user()->hasCountryRole()) {
            $this->countries = $this->getUserCountries();
        }

        return $this->countries;
    }

    /**
     * Get Country by information
     *
     * @param      $code
     *
     * @param null $locale
     *
     * @return string
     */
    public function getInfoByCode($code, $locale = null)
    {
        $countries = $this->countries;

        if (!is_null($locale)) {
            $countries = trans('codelist/country', [], null, $locale);
        }

        return isset($countries[$code]) ? ['code' => $code, 'name' => $countries[$code]] : '';
    }

    /**
     * gets user Countries
     *
     * @return array
     */
    public function getUserCountries()
    {
        $countries = [];
        foreach ($this->auth->user()->country as $code) {
            $countries[$code] = $this->countries[$code];;
        }

        return $countries;
    }

    /**
     * Get Country by name
     *
     * @param $countryName
     *
     * @return array
     * @internal param $country
     */
    public function getCountryByName($countryName)
    {
        $countries = $this->countries;

        $country = ['code' => '', 'name' => ''];

        foreach ($countries as $code => $name) {
            if (strtolower($countryName) == trim(strtolower($name))) {
                $country['code'] = $code;
                $country['name'] = $name;
            }
        }

        return $country;
    }

}
