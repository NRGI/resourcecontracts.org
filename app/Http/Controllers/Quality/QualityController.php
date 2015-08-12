<?php namespace App\Http\Controllers\Quality;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Nrgi\Services\Quality\QualityService;

/**
 * Check the quality of Metadata,Text and Annotations
 *
 * Class QualityController
 * @package App\Http\Controllers\Quality
 */
class QualityController extends Controller
{
    /**
     * @var QualityService
     */
    protected $quality;

    /**
     * @param QualityService $quality
     */
    public function __construct(QualityService $quality)
    {
        $this->quality = $quality;
    }

    /**
     * Get quality of contract and annotations
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = [
            'metadata'    => $this->quality->getMetadataQuality(),
            'annotations' => $this->quality->getAnnotationsQuality(),
            'total'       => $this->quality->getTotalContractCount()
        ];

        return view('quality.index', compact('data'));
    }


}

