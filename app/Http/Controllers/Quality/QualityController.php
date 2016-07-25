<?php namespace App\Http\Controllers\Quality;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\ContractFilterService;
use App\Nrgi\Services\Quality\QualityService;
use Illuminate\Http\Request;

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
     * @var ContractFilterService
     */
    public $contractFilter;

    /**
     * @param QualityService        $quality
     * @param ContractFilterService $contractFilter
     */
    public function __construct(QualityService $quality, ContractFilterService $contractFilter)
    {
        $this->quality        = $quality;
        $this->contractFilter = $contractFilter;
    }

    /**
     * Get quality of contract and annotations
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = $request->only('year', 'country', 'category', 'resource');
        $data    = [
            'metadata'    => $this->quality->getMetadataQuality($filters),
            'annotations' => $this->quality->getAnnotationsQuality($filters),
            'total'       => $this->quality->getTotalContractCount($filters)
        ];

        $years     = $this->contractFilter->getUniqueYears(false);
        $countries = $this->contractFilter->getUniqueCountries(false);
        $resources = $this->contractFilter->getUniqueResources();

        return view('quality.index', compact('data', 'countries', 'resources', 'years','filters'));
    }


}

