<?php namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Localization;
use App\Nrgi\Mturk\Services\MTurkService;
use App\Nrgi\Services\Dashboard\DashboardService;
use Illuminate\Http\Response;

/**
 * Class DashboardController
 * @package App\Http\Controllers\Dashboard
 */
class DashboardController extends Controller
{

    const NO_OF_RECENT_CONTRACTS = 5;

    /**
     * @var DashboardService
     */
    protected $dashboard;

    protected $localization;
    /**
     * Create a new controller instance.
     * @param DashboardService $dashboard
     * @param Localization     $localization
     */
    public function __construct(DashboardService $dashboard, Localization $localization)
    {
        $this->middleware('auth');
        $this->dashboard    = $dashboard;
        $this->localization = $localization;
    }

    /**
     * Dashboard Home
     *
     * @param MTurkService $mTurk
     *
     * @return Response
     */
    public function index(MTurkService $mTurk)
    {

        $stats = [
            'balance'    => config('mturk.currencyCode').$mTurk->getBalance(),
            'total'      => $this->dashboard->countContractTotal(),
            'last_month' => $this->dashboard->countContractTotal('last_month'),
            'this_month' => $this->dashboard->countContractTotal('this_month'),
            'yesterday'  => $this->dashboard->countContractTotal('yesterday'),
            'today'      => $this->dashboard->countContractTotal('today'),
        ];

        list($metadata, $pdfText) = $this->dashboard->contractStatusCount();

        $annotation     = $this->dashboard->annotationStatusCount();
        $ocrStatusCount = $this->dashboard->getOcrStatusCount();
        $status         = compact('metadata', 'pdfText', 'annotation');

        $recent_contracts = $this->dashboard->recent(static::NO_OF_RECENT_CONTRACTS);

        return view('dashboard.index', compact('stats', 'recent_contracts', 'status', 'ocrStatusCount'));
    }

    /**
     * Dashboard Home
     *
     * @return string
     */
    public function version()
    {
        return '20th Jan,2021';
    }
}
