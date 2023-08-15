<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Services\Download\DownloadService;
use App\Nrgi\Services\Contract\Page\PageService;


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
     * @var PageService
     */
    protected $pages;
    /**
     * @var AnnotationRepositoryInterface
     */
    protected $annotation;

    /**
     * @param ContractRepositoryInterface   $contract
     * @param CountryService                $countryService
     * @param AnnotationRepositoryInterface $annotations
     * @param DownloadService               $downloadCSV
     *
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        CountryService $countryService,
        AnnotationRepositoryInterface $annotations,
        DownloadService $downloadCSV,
        PageService $pages
    ) {
        $this->contract       = $contract;
        $this->countryService = $countryService;
        $this->annotation     = $annotations;
        $this->downloadCSV    = $downloadCSV;
        $this->pages           = $pages;
    }

    /**
     * Get all contract
     *
     * @param array $filters
     * @param int   $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function getAll(array $filters, $limit = 25)
    {
        if ($filters['type'] == "annotations" && $filters['status'] != '') {
            $annotations = $this->getContractByAnnotationStatus();
            $status      = isset($annotations[$filters['status']]) ? $annotations[$filters['status']] : [];

            return $this->contract->getContract($status, $limit);
        }

        if ($filters['download'] == 1) {
            $contracts = $this->contract->getAllDownload($filters);
            return $this->downloadCSV->downloadData($contracts);
        }

        $contracts = $this->contract->getAll($filters, $limit);
        if(isset($filters['count_pages'])) {
            return $this->getPageCountForAllContracts($contracts);
        }

        return $contracts;
    }

    /**
     * Returns child parent contracts
     *
     * @return array
     */
    public function getPageCountForAllContracts($contract_ids)
    {
        return $this->pages->getPageCountForAllContracts($contract_ids);
    }

    /**
     * Get Unique Countries
     *
     * @return array
     */
    public function getUniqueCountries($withcount = true)
    {
        $arr       = $this->contract->getUniqueCountries()->toArray();
        $countries = [];
        foreach ($arr as $key => $value) {
            $country = $this->countryService->getInfoByCode($value['countries']);
            if ($withcount) {
                $countries[$value['countries']] = sprintf('%s (%s)', $country['name'], $value['count']);
            } else {
                $countries[$value['countries']] = $country['name'];
            }
        }
        asort($countries);

        return $countries;
    }

    /**
     * Get Unique Years
     *
     * @return array
     */
    public function getUniqueYears($withcount = true)
    {
        $arr   = $this->contract->getUniqueYears()->toArray();
        $years = [];
        foreach ($arr as $key => $value) {
            if ($withcount) {
                $years[$value['years']] = sprintf('%s (%s)', $value['years'], $value['count']);
            } else {
                $years[$value['years']] = $value['years'];
            }

        }

        return $years;
    }

         /**
     * Get Unique Years
     *
     * @return array
     */
    public function getUniquePublishingYears()
    {
        $arr   = $this->contract->getUniquePublishingYears()->toArray();
        $years = [];
        foreach ($arr as $key => $value) {
            $years[$value['years']] = $value['years'];

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

        return array_filter($data);
    }

    /**
     * Get Contract Annotation status
     * @return array
     */
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
                    $statusRaw['published'][$key]->status,
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

    /**
     * Get Multiple Metadata Contract
     *
     * @param $filters
     * @param $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getMultipleMetadataContracts($filters, $limit)
    {
        if ($filters['word'] == "government_entity") {
            $filters['word'] = "government";
        }
        $contractId = $this->contract->getMultipleMetadataContract($filters['word']);
        $meta       = $contractId[0]->getmultiplemetadatacontract;

        if (strpos($meta, '=') == true) {
            $contractId = explode("=", $meta);
            $meta       = $contractId[1];
        }

        $contractId = str_replace(['{', '}'], ['', ''], $meta);
        $contractId = explode(",", $contractId);
        foreach ($contractId as $key => $id) {
            if (!is_numeric($id)) {
                unset($contractId[$key]);
            }
        }
        $contracts = $this->contract->getContractFilterByMetadata($filters, $limit, $contractId);

        return $contracts;
    }

}
