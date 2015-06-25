<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;

/**
 * Class ContractFilterService
 * @package App\Nrgi\Services\Contract
 */
class ContractFilterService
{
    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;
    /**
     * @var CountryService
     */
    protected $countryService;

    /**
     * @param ContractRepositoryInterface $contract
     * @param CountryService              $countryService
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        CountryService $countryService
    ) {
        $this->contract       = $contract;
        $this->countryService = $countryService;
    }

    /**
     * Get all contract
     *
     * @param $filters
     * @return Contract
     */
    public function getAll(array $filters)
    {
        $contracts = $this->contract->getAll($filters);

        return $contracts;
    }

    /**
     * Get Unique Countries
     *
     * @return array
     */
    public function getUniqueCountries()
    {
        $arr            = $this->contract->getUniqueCountries()->toArray();
        $countries      = [];
        $country_config = trans('codelist.country');
        foreach ($arr as $key => $value) {
            $country                        = $this->countryService->getInfoByCode($value['countries']);
            $countries[$value['countries']] = sprintf('%s (%s)', $country['name'], $value['count']);
        }

        return $countries;
    }

    /**
     * Get Unique Years
     *
     * @return array
     */
    public function getUniqueYears()
    {
        $arr   = $this->contract->getUniqueYears()->toArray();
        $years = [];
        foreach ($arr as $key => $value) {
            $years[$value['years']] = sprintf('%s (%s)', $value['years'], $value['count']);
        }

        return $years;
    }

    /**
     * Get Unique Resources
     *
     * @return array
     */
    public function getUniqueResources()
    {
        return trans('codelist/resource');
    }
}