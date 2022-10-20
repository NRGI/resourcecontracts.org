<?php namespace App\Nrgi\Services\Contract\Discussion;

use App\Nrgi\Repositories\Contract\Discussion\DiscussionRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface as Log;
use App\Nrgi\Log\NrgiLogService;


/**
 * Class DiscussionService
 * @package App\Nrgi\Services\Contract\Discussion
 */
class DiscussionService
{
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var DiscussionRepositoryInterface
     */
    protected $discussion;
    /**
     * @var NrgiLogService
     */
    protected $nrgiLogService;

    /**
     * @param DiscussionRepositoryInterface $discussion
     * @param Log                           $logger
     * @param LNrgiLogServiceog             $nrgiLogService
     */
    public function __construct(DiscussionRepositoryInterface $discussion, Log $logger, NrgiLogService $nrgiLogService)
    {
        $this->logger     = $logger;
        $this->discussion = $discussion;
        $this->nrgiLogService = $nrgiLogService;
    }

    /**
     * Save Contract Discussion
     *
     * @param        $contract_id
     * @param        $data
     *
     * @return bool
     */
    public function save($contract_id, array $data)
    {
        try {
            $this->discussion->save($contract_id, $data);
            $this->logger->info(
                'Discussion successfully saved.',
                [
                    'Contract id' => $contract_id,
                    'Message'     => $data['message'],
                    'Key'         => $data['key'],
                    'type'        => $data['type'],
                ]
            );

            $this->nrgiLogService->activity('contract.log.discussion.save', $data, $contract_id);

            return true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * Get all discussion
     *
     * @param $contract_id
     * @param $key
     * @param $type
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($contract_id, $key, $type)
    {
        $messages = $this->discussion->get($contract_id, $key, $type);

        foreach ($messages as &$msg) {
            $msg->createdDate = $msg->createdDate('F m, d  \a\t h:i A');
        }

        return $messages;
    }

    /**
     * Get Contract Discussion count
     *
     * @param $contract_id
     *
     * @return array
     */
    public function getCount($contract_id)
    {
        $counts = $this->discussion->getCount($contract_id);
        $dis    = [];

        if (!empty($counts)) {
            foreach ($counts as $count) {
                $dis[$count['key']] = $count['count'];
            }
        }

        return $dis;
    }

    /**
     * Delete Contract Discussion
     *
     * @param $keys
     */
    public function deleteContractDiscussion($contract_id, array $keys)
    {
        foreach ($keys as $meta_key => $key) {
            if (empty($key)) {
                continue;
            }

            $ks = array_map('trim', explode(',', $key));
            asort($ks);

            foreach ($ks as $i) {
                $schema = config('metadata.schema.metadata.'.$meta_key);
                $meta   = $schema[0];

                foreach ($meta as $k => $v) {
                    $key = $k.'-'.$i;
                    $this->discussion->delete($contract_id, $key);
                    $this->discussion->update($contract_id, $k.'-'.($i + 1), ['key' => $key]);
                }
            }
        }
    }

    /**
     * Get Resolved discussion status
     *
     * @param $contract_id
     *
     * @return array
     */
    public function getResolved($contract_id)
    {
        $rows   = $this->discussion->getResolved($contract_id);
        $status = [];

        foreach ($rows as $row) {
            $status[$row->key] = $row->status;
        }

        return $status;
    }

}
