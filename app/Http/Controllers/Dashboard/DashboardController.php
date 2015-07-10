<?php namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Dashboard\DashboardService;
use Aws\S3\S3Client;
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
    protected $contract;

    /**
     * Create a new controller instance.
     * @param DashboardService $dashboard
     */
    public function __construct(DashboardService $dashboard)
    {
        $this->middleware('auth');
        $this->dashboard = $dashboard;
    }

    /**
     * Dashboard Home
     *
     * @return Response
     */
    public function index()
    {
        $stats = [
            'total'      => $this->dashboard->countContractTotal(),
            'last_month' => $this->dashboard->countContractTotal('last_month'),
            'this_month' => $this->dashboard->countContractTotal('this_month'),
            'yesterday'  => $this->dashboard->countContractTotal('yesterday'),
            'today'      => $this->dashboard->countContractTotal('today'),
        ];

        list($metadata, $pdfText) = $this->dashboard->contractStatusCount();

        $annotation = $this->dashboard->annotationStatusCount();

        $status = compact('metadata', 'pdfText', 'annotation');

        $recent_contracts = $this->dashboard->recent(static::NO_OF_RECENT_CONTRACTS);

        return view('dashboard.index', compact('stats', 'recent_contracts', 'status'));
    }
}
