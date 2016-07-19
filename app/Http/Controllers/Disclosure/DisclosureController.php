<?php namespace App\Http\Controllers\Disclosure;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\CountryService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Class DisclosureController
 *
 * @package App\Http\Controllers\Disclosure
 */
class DisclosureController extends Controller
{

    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;

    /**
     * @var CountryService
     */
    protected $country;

    /**
     * DisclosureController constructor.
     *
     * @param ContractRepositoryInterface $contract
     * @param CountryService              $country
     */
    public function __construct(ContractRepositoryInterface $contract, CountryService $country)
    {
        $this->contract = $contract;
        $this->country  = $country;
    }

    /**
     * Display a listing of the disclosure mode on contract.
     * @return \Illuminate\View\View
     * @internal param Guard $auth
     *
     */
    public function index()
    {
        $government = $this->contract->getDisclosureModeCount('Government');
        $company    = $this->contract->getDisclosureModeCount('Company');
        $unknown    = $this->contract->getUnknownDisclosureModeCount();
        $country    = $this->country->all();
        foreach ($country as $code => $name) {
            $disclosureMode[$code] = [
                'government' => isset($government[$code]) ? $government[$code] : 0,
                'company'    => isset($company[$code]) ? $company[$code] : 0,
                'unknown'    => isset($unknown[$code]) ? $unknown[$code] : 0,
            ];
        }

        return view('disclosure.index', compact('disclosureMode'));
    }

}
