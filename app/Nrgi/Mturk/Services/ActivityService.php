<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Mturk\Repositories\Activity\ActivityRepositoryInterface;
use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ActivityService
 * @package App\Nrgi\Services\ActivityLog
 */
class ActivityService
{
    /**
     * @var ActivityRepositoryInterface
     */
    protected $activity;

    /**
     * @param ActivityRepositoryInterface   $activity
     * @param AnnotationRepositoryInterface $annotation
     */
    public function __construct(ActivityRepositoryInterface $activity, AnnotationRepositoryInterface $annotation)
    {
        $this->activity   = $activity;
        $this->annotation = $annotation;
    }

    /**
     * Save activity
     *
     * @param       $message
     * @param array $params
     * @param null  $contract_id
     *
     * @param null  $page_no
     *
     * @return bool
     */
    public function save($message, $params = [], $contract_id = null, $page_no = null)
    {
        $activity            = [];
        $activity['message'] = $message;

        if (!empty($params)) {
            $activity['message_params'] = $params;
        }

        if (!is_null($contract_id)) {
            $activity['contract_id'] = $contract_id;
        }

        if (!is_null($page_no)) {
            $activity['page_no'] = $page_no;
        }

        return $this->activity->save($activity);
    }

    /**
     * Activities pagination
     *
     * @param     $filter
     * @param int $perPage
     *
     * @return Collection
     */
    public function getAll($filter, $perPage = 25)
    {
        return $this->activity->paginate($filter, $perPage);
    }

    /**
     * Get latest published information.
     *
     * @param $id
     *
     * @return array
     */
    public function getLatestPublishedInfo($id)
    {
        $elements = ["metadata", "text", "annotation"];
        $data     = [];
        foreach ($elements as $element) {
            $data[$element] = $this->activity->getLatestPublicationEvent($id, $element);
        }

        return $data;
    }

    /**
     * Element state to show in subsite
     *
     * @param $id
     *
     * @return array
     */
    public function getElementState($id)
    {
        $elements = ["metadata", "text", "annotation"];
        $data     = [];
        foreach ($elements as $element) {
            $type           = $this->activity->getElementState($id, $element);
            $data[$element] = 'unpublished';
            if (isset($type->message_params['new_status']) && $type->message_params['new_status'] == 'published') {
                $data[$element] = 'published';

                if($element=='metadata') {
                    // This is used by the recent documents feature that requires the first date of publication instead
                    // of the latest.
                    $first_publication = $this->activity->getFirstPublicationEvent($id, $element);
                    $data['metadata_published_at'] = $first_publication->created_at;
                }
            } else {
                if ($element == 'annotation') {
                    $data[$element] = $this->annotation->getStatus($id);
                }
            }
        }

        return $data;
    }

    /**
     * Returns published date for contracts
     *
     * @param bool $recent
     *
     * @return array
     */
    public function getPublishedContracts($recent = false)
    {
        $recent_published_contracts = $this->activity->getPublishedContracts($recent)->toArray();

        return array_reduce($recent_published_contracts, function ($contract, $recent_published_contract) {
            $contract[$recent_published_contract['contract_id']] = $recent_published_contract['published_at'];

            return $contract;
        });
    }
}
