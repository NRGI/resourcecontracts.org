<?php namespace App\Nrgi\Services\Newsletter;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Guzzle\Http\Client;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

/**
 * Class NewsletterService
 * @package App\Nrgi\Services\Newsletter
 */
class NewsletterService
{
    /**
     * @param Client                      $http
     * @param ContractRepositoryInterface $contract
     * @param LoggerInterface             $logger
     */
    public function __construct(Client $http, ContractRepositoryInterface $contract, LoggerInterface $logger)
    {
        $this->http     = $http;
        $this->contract = $contract;
        $this->logger   = $logger;
    }

    /**
     * Post data about published contract to newsletter
     *
     * @param $data
     *
     * @return int
     */
    public function post($data)
    {
        $contract = $this->contract->findContract($data['contract_id']);

        if (!($contract->published_to_newsletter)) {
            $url     = getenv('NEWSLETTER_URL_PUBLISH');
            $options = [
                'http' => [
                    'method'  => 'POST',
                    'content' => json_encode($data),
                    'header'  => "Content-Type: application/json\r\n".
                        "Accept: application/json\r\n",
                ],
            ];

            try {
                $context  = stream_context_create($options);
                $result   = file_get_contents($url, false, $context);
                $response = json_decode($result);
                $this->updatePublishedData($contract);
                $this->logger->info("Contract of contrat id ".$data['contract_id']." is published to newsletter.");
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }

            return 1;
        } else {
            return 1;
        }
    }

    /**
     * Update status of published contract
     *
     * @param $contract
     *
     * @return int
     */
    public function updatePublishedData($contract)
    {
        $data['published_date']          = date('Y-m-d');
        $data['published_to_newsletter'] = 1;

        try {
            $contract->update($data);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return 1;
    }
}