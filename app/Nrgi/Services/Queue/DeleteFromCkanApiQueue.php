<?php
namespace App\Nrgi\Services\Queue;

use GuzzleHttp\Client;
/**
 * Queue for posting to  CKAN API
 * @package App\Nrgi\Services\Queue
 */
class DeleteFromCkanApiQueue
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
            "package_id"  => "dataset-3",
            "id"          => (string) $dataToCkan["id"]
        );
        $res = $client->post('http://demo.ckan.org/api/action/resource_delete',
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
