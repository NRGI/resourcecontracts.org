<?php namespace App\Nrgi\Services\Newsletter;

use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class NewsletterService
 * @package App\Nrgi\Services\Newsletter
 */
class NewsletterService
{
    /**
     * @param ContractRepositoryInterface $contract
     * @param LoggerInterface             $logger
     */
    public function __construct(ContractRepositoryInterface $contract, LoggerInterface $logger)
    {
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
        $this->postToNewsletter($contract, $data);

        return 1;
    }

    /**
     * Update status of published contract
     *
     * @param $contract
     *
     * @param $action
     *
     * @return int
     */
    public function updatePublishedData($contract, $action)
    {
        $data['published_date']          = date('Y-m-d');
        $data['published_to_newsletter'] = ($action == "publish") ? 1 : 0;

        try {
            $contract->update($data);

            return 1;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return 0;
        }
    }

    /**
     * Checks if contract is published today
     *
     * @param $contract
     *
     * @return bool
     */
    private function isPublishedToday($contract)
    {
        $currentDate = date('Y-m-d');

        return $currentDate == $contract->published_date;
    }

    /**
     * Posts to Newsletter
     *
     * @param     $contract
     * @param     $data
     *
     * @return int
     */
    public function postToNewsletter($contract, $data)
    {
        $publish = (($data['action'] == 'delete' && $this->isPublishedToday($contract)) ? 0 : $contract->published_to_newsletter);

        if (!$publish) {
            $url     = getenv('NEWSLETTER_URL').'/'.$data['action'];
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
                $this->updatePublishedData($contract, $data['action']);
                $this->logger->info(
                    "Contract of contract id ".$data['contract_id']." is ".$data['action']."(e)d to/from newsletter."
                );

                return 1;
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());

                return 0;
            }
        }

        return 1;
    }
}
