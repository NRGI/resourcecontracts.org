<?php namespace App\Nrgi\Services\CKAN;

use App\Nrgi\Services\Contract\ContractService;
use Guzzle\Http\Client;
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
     * @param Client                        $http
     * @param ContractService               $contract
     */
    public function __construct(Client $http, ContractService $contract)
    {
        $this->http       = $http;
        $this->contract   = $contract;
    }

    public function callCkanApi($data)
    {
        $dataToCkan = $data;

        if ($dataToCkan["is_supporting_document"])
        {
            //$parentContractId = null;
        } else
        {
            $datasetName = (string) $dataToCkan["contract_id"];
            //ok
            if($this->datasetExists($datasetName))
            {
                $data = $this->prepareResourceDataForCkan($datasetName, $dataToCkan);
                //ok
                $res = $this->createResourceInCkan($data);
                dd($res);
            } else
            {
                try
                {
                    $this->createDatasetInCkan($datasetName);
                    $data = $this->prepareResourceDataForCkan($datasetName, $dataToCkan);
                    $this->createResourceInCkan($data);
                } catch (Exception $e)
                {
                    echo 'Error occurred during dataset creation: ',  $e->getMessage(), "\n";
                }
            }
        }
    }

    public function datasetExists($name)
    {
        return 1;
    }

    public function prepareResourceDataForCkan($datasetName, $dataToCkan)
    {
        $data = array(
            "package_id"        => 'nrgi-' . $datasetName,
            "id"                => (string) $dataToCkan["contract_id"],
            "name"              => $dataToCkan["contract_name"],
            "format"            => "PDF",
            "url"               => $dataToCkan["file_url"],
            "license"           => $dataToCkan["license"],
            "description"       => $dataToCkan["contract_name"]
        );
        return $data;
    }

    public function createResourceInCkan($data)
    {
        //ok
        $client = new Client();
        $res = $client->post('http://demo.ckan.org/api/action/resource_create',
                             [
                                 'headers'    =>  [
                                     'Content-Type' => 'application/json',
                                     'Accept' => 'application/json',
                                     'Authorization' => '2b89ee7d-44ee-4854-9931-a5276177163f'
                                 ],
                                 'body'       => json_encode($data)
                             ]);
        return $res;
    }

    public function createDatasetInCkan($datasetName)
    {
        $data = array(
            "name"              => 'nrgi-' . $datasetName,
            "title"             => $datasetName
        );
        $res = $this->http->post('http://demo.ckan.org/api/action/package_create',
                                 [
                                     'headers'    =>  [
                                         'Content-Type' => 'application/json',
                                         'Accept' => 'application/json',
                                         'Authorization' => '2b89ee7d-44ee-4854-9931-a5276177163f'
                                     ],
                                     'body'       => json_encode($data)
                                 ]);
        return $res;
    }
}