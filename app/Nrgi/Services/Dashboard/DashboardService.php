<?php namespace App\Nrgi\Services\Dashboard;

use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\AnnotationRepositoryInterface;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Carbon\Carbon;

/**
 * Class DashboardService
 * @package App\Nrgi\Services\Dashboard
 */
class DashboardService
{
    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;
    /**
     * @var AnnotationRepositoryInterface
     */
    protected $annotation;
    /**
     * @var Carbon
     */
    protected $carbon;

    /**
     * @param ContractRepositoryInterface   $contract
     * @param AnnotationRepositoryInterface $annotation
     * @param Carbon                        $carbon
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        AnnotationRepositoryInterface $annotation,
        Carbon $carbon
    ) {
        $this->contract   = $contract;
        $this->annotation = $annotation;
        $this->carbon     = $carbon;
    }

    /**
     * Count total Contracts
     *
     * @param string $date
     * @return contract
     */
    public function countContractTotal($date = '')
    {
        switch ($date) {
            case 'today':
                $dateFormat = $this->carbon->today()->toDateString();
                break;
            case 'yesterday':
                $dateFormat = $this->carbon->yesterday()->toDateString();
                break;
            case 'this_month':
                $dateFormat = [$this->carbon->firstOfMonth()->toDateString(), $this->carbon->now()->toDateString()];
                break;
            case 'last_month':
                $dateFormat = [
                    $this->carbon->now()->subMonth(1)->firstOfMonth()->toDateString(),
                    $this->carbon->now()->subMonth(1)->endOfMonth()->toDateString()
                ];
                break;
            default:
                $dateFormat = '';
                break;
        }

        return $this->contract->countTotal($dateFormat);
    }

    /**
     * Get Recent Contracts
     *
     * @param $no
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recent($no)
    {
        return $this->contract->recent($no);
    }

    /**
     * Get Contract count by status
     *
     * @param $statusType
     * @return array
     */
    public function contractStatusCount()
    {
        $default     = [
            'draft'     => 0,
            'completed' => 0,
            'rejected'  => 0,
            'published' => 0,
        ];
        $metadataRaw = $this->contract->statusCount('metadata_status');
        $metadata    = [];
        foreach ($metadataRaw as $meta) {
            $metadata[$meta['status']] = $meta['count'];
        }
        $metadata = array_merge($default, $metadata);

        $pdfTextRaw = $this->contract->statusCount('text_status');
        $pdfText    = [];

        foreach ($pdfTextRaw as $text) {
            if (is_null($text['status'])) {
                $pdfText['processing'] = $text['count'];
            } else {
                $pdfText[$text['status']] = $text['count'];
            }
        }

        return [$metadata, $pdfText];
    }

    /**
     * Get Annotation status Count
     */
    public function annotationStatusCount()
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
                    $statusRaw['published'][$key]->status
                ]
            );
            $status              = empty($status) ? 'processing' : $status;
            $contract[$status][] = $value->id;
        }
        $count = [];
        foreach ($contract as $status => $ids) {
            $count[$status] = count($ids);
        }

        $default = [
            'draft'      => 0,
            'completed'  => 0,
            'rejected'   => 0,
            'published'  => 0,
            'processing' => 0,
        ];

        return array_merge($default, $count);
    }

    /**
     * Return the status count of OCR status
     *
     * @return array
     */
    public function getOcrStatusCount()
    {
        $ocrDefault = [
            'acceptable'    => 0,
            'editing'       => 0,
            'transcription' => 0,
            'non'           => 0
        ];

        $ocrRaw = $this->contract->statusCount('textType');

        $ocrStatusCount = [];
        foreach ($ocrRaw as $ocr) {
            if ($ocr['status'] == "") {
                $ocr['status'] = "non";
            }
            if ($ocr['status'] == 1) {
                $ocr['status'] = "acceptable";
            }
            if ($ocr['status'] == 2) {
                $ocr['status'] = "editing";
            }
            if ($ocr['status'] == 3) {
                $ocr['status'] = "transcription";
            }
            $ocrStatusCount[$ocr['status']] = $ocr['count'];
        }

        return array_merge($ocrDefault, $ocrStatusCount);
    }
}