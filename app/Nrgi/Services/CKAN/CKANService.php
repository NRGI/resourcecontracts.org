<?php namespace App\Nrgi\Services\CKAN;

use App\Nrgi\Services\Contract\ContractService;
use GuzzleHttp\Client;
use Exception;

/**
 * Class CKANService
 * @package App\Nrgi\Services\CKAN
 */
class CKANService
{
    /**
     * @var Client
     */
    protected $http;
    /**
     * @var ContractService
     */
    protected $contract;

    /**
     * @param Client          $http
     * @param ContractService $contract
     *
     * @internal param ContractService $contractService
     */
    public function __construct(Client $http, ContractService $contract)
    {
        $this->http     = $http;
        $this->contract = $contract;
    }

    public function callCkanApiPost($dataToCkan)
    {
        if ($dataToCkan["is_supporting_document"]) {
            $contract         = $this->contract->find($dataToCkan["contract_id"]);
            $parentContractId = $contract->getParentContract();
            $datasetName      = (string) $parentContractId;

            $this->postResourceToCkan($datasetName, $dataToCkan);
        } else {
            // this runs for parent document i.e if contract is not a supporting document
            $datasetName = (string) $dataToCkan["contract_id"];
            $this->postResourceToCkan($datasetName, $dataToCkan);
        }
    }

    public function postResourceToCkan($datasetName, $dataToCkan)
    {
        if ($this->datasetExists($datasetName)) {
            $data = $this->prepareResourceDataForCkan($datasetName, $dataToCkan);
            $this->createResourceInCkan($data);
        } else {
            try {
                $this->createDatasetInCkan($datasetName);
                $data = $this->prepareResourceDataForCkan($datasetName, $dataToCkan);
                $this->createResourceInCkan($data);
            } catch (Exception $e) {
                echo 'Error occurred during dataset creation: ', $e->getMessage(), "\n";
            }
        }
    }

    public function datasetExists($name)
    {
        $datasets = null;
        try {
            $datasets = $this->http->get('http://demo.ckan.org/api/3/action/package_show?id=nrgi-test-'.$name);
            $datasets = $datasets->json();
            $status   = ($datasets['success'] == 1) ? true : false;
        } catch (Exception $e) {
            $status = $datasets['success'] == 1 ? true : false;
        }

        return $status;
    }

    public function prepareResourceDataForCkan($datasetName, $dataToCkan)
    {
        $data = [
            "package_id"  => 'nrgi-test-'.$datasetName,
            "id"          => (string) $dataToCkan["contract_id"],
            "name"        => $dataToCkan["contract_name"],
            "format"      => "PDF",
            "url"         => $dataToCkan["file_url"],
            "license"     => $dataToCkan["license"],
            "description" => $dataToCkan["contract_name"],
        ];

        return $data;
    }

    public function createResourceInCkan($data)
    {
        $res = $this->http->post(
            'http://demo.ckan.org/api/action/resource_create',
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Authorization' => '2b89ee7d-44ee-4854-9931-a5276177163f',
                ],
                'body'    => json_encode($data),
            ]
        );

        return $res->json();
    }

    public function createDatasetInCkan($datasetName)
    {
        $data = [
            "name"  => 'nrgi-test-'.$datasetName,
            "title" => $datasetName,
        ];
        $res  = $this->http->post(
            'http://demo.ckan.org/api/action/package_create',
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Authorization' => '2b89ee7d-44ee-4854-9931-a5276177163f',
                ],
                'body'    => json_encode($data),
            ]
        );

        return $res;
    }

    public function callCkanApiDelete($dataToCkan)
    {
        if ($dataToCkan["is_supporting_document"]) {
            $contract         = $this->contract->find($dataToCkan["contract_id"]);
            $parentContractId = $contract->getParentContract();
            $packageId = (string) $parentContractId;
        } else {
            $packageId = (string) $dataToCkan["contract_id"];
        }

        $data = array(
            "package_id"  => "nrgi-test-" . $packageId,
            "id"          => (string) $dataToCkan["contract_id"]
        );

        $res = $this->http->post('http://demo.ckan.org/api/action/resource_delete',
                             [
                                 'headers'    =>  [
                                     'Content-Type' => 'application/json',
                                     'Accept' => 'application/json',
                                     'Authorization' => '2b89ee7d-44ee-4854-9931-a5276177163f'
                                 ],
                                 'body'       => json_encode($data)
                             ]);

        $this->deleteDatasetIfEmpty($packageId, $data);
        return $res;
    }

    public function deleteDatasetIfEmpty($packageId, $data)
    {
        $res = $this->http->get('http://demo.ckan.org/api/3/action/package_search?q=nrgi-test-' . '"'.$packageId.'"');
        $num_resources = (integer) $res->json()['result']['results'][0]['num_resources'];

        if ($num_resources == 0) {
            $data = array(
                "id"          => "nrgi-test-" . (string) $packageId
            );
            $res = $this->http->post('http://demo.ckan.org/api/action/package_delete',
                                     [
                                         'headers'    =>  [
                                             'Content-Type' => 'application/json',
                                             'Accept' => 'application/json',
                                             'Authorization' => '2b89ee7d-44ee-4854-9931-a5276177163f'
                                         ],
                                         'body'       => json_encode($data)
                                     ]);
            return $res;
        } else {
            return 1;
        }
    }
}