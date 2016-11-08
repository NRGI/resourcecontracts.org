<?php
namespace App\Nrgi\Services\Queue;

use GuzzleHttp\Client;
/**
 * Queue for posting to  CKAN API
 * @package App\Nrgi\Services\Queue
 */
class PostToCkanApiQueue
{

    public function __construct()
    {
    }

    /**
     * @var client
     */

    /**
     * @param $job
     * @param $dataToCkan
     */
    public function fire($job, $dataToCkan)
    {
        $client = new Client();
        $data = array(
            "package_id"        => "dataset-3",
            "id"                => (string) $dataToCkan["id"],
            "name"              => $dataToCkan["contract_name"],
            "format"            => "PDF",
            "url"               => $dataToCkan["file_url"],
            "license"           => $dataToCkan["license"],
            "description"       => $dataToCkan["contract_name"]
        );
        $res = $client->post('http://demo.ckan.org/api/action/resource_create',
               [
                   'headers'    =>  [
                       'Content-Type' => 'application/json',
                       'Accept' => 'application/json',
                       'Authorization' => '2b89ee7d-44ee-4854-9931-a5276177163f'
                   ],
                   'body'       => json_encode($data)
               ]);
        $job->delete();
    }
}
