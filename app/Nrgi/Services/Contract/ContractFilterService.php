<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\AnnotationRepositoryInterface;
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
     * @var AnnotationRepositoryInterface
     */
    private $annotations;

    /**
     * @param ContractRepositoryInterface   $contract
     * @param CountryService                $countryService
     * @param AnnotationRepositoryInterface $annotations
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        CountryService $countryService,
        AnnotationRepositoryInterface $annotations
    ) {
        $this->contract       = $contract;
        $this->countryService = $countryService;
        $this->annotation     = $annotations;
    }

    /**
     * Get all contract
     *
     * @param array $filters
     * @param int   $limit
     * @return Contract
     */
    public function getAll(array $filters, $limit = 25)
    {

        if ($filters['type'] == "annotations" && $filters['status'] != '') {
            $annotations = $this->getContractByAnnotationStatus();
            $status      = isset($annotations[$filters['status']]) ? $annotations[$filters['status']] : [];
            $contracts   = $this->contract->getContract($status, $limit);

            return $contracts;
        }

        $contracts = $this->contract->getAll($filters, $limit);

        return $contracts;
    }

    /**
     * Get Unique Countries
     *
     * @return array
     */
    public function getUniqueCountries()
    {
        $arr       = $this->contract->getUniqueCountries()->toArray();
        $countries = [];
        foreach ($arr as $key => $value) {
            $country                        = $this->countryService->getInfoByCode($value['countries']);
            $countries[$value['countries']] = sprintf('%s (%s)', $country['name'], $value['count']);
        }
        asort($countries);

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
        $resources = $this->contract->getUniqueResources();

        $data = [];
        foreach ($resources as $re) {
            $data[$re['resource']] = $re['resource'];
        }
        asort($data);

        return $data;
    }

    public function getContractByAnnotationStatus()
    {
        $draft     = $this->annotation->getStatusCountByType(Annotation::DRAFT);
        $completed = $this->annotation->getStatusCountByType(Annotation::COMPLETED);
        $rejected  = $this->annotation->getStatusCountByType(Annotation::REJECTED);
        $published = $this->annotation->getStatusCountByType(Annotation::PUBLISHED);

        $statusRaw = compact('draft', 'completed', 'rejected', 'published');
        $contract  = [];
        foreach ($statusRaw['draft'] as $key => $value) {
            $status              = $this->annotation->checkStatus(
                [
                    $value->status,
                    $statusRaw['completed'][$key]->status,
                    $statusRaw['rejected'][$key]->status,
                    $statusRaw['published'][$key]->status
                ]
            );
            $status              = empty($status) ? 'processing' : $status;
            $contract[$status][] = $value->id;
        }
        $default = [
            'draft'      => 0,
            'completed'  => 0,
            'rejected'   => 0,
            'published'  => 0,
            'processing' => 0,
        ];

        return array_merge($default, $contract);
    }
}
