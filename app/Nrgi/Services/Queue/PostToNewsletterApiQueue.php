<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\Newsletter\NewsletterService;

/**
 * Queue for posting to Newsletter
 * Class PostToNewsletterApiQueue
 * @package App\Nrgi\Services\Queue
 */
class PostToNewsletterApiQueue
{
    /**
     * @var
     */
    public $newsletter;

    /**
     * PostToNewsletterApiQueue constructor.
     *
     * @param NewsletterService $newsletter
     */
    public function __construct(NewsletterService $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->newsletter->post($data);
        $job->delete();
    }
}
