<?php namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\ContractService;
use Aws\Lambda\LambdaClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface as Log;

/**
 * Class TranslationController
 * @package App\Http\Controllers\Contract
 */
class TranslationController extends Controller
{
    /**
     * @var ContractService
     */
    protected $contract;

    /**
     * @var Log
     */
    protected $logger;

    /**
     * @param ContractService $contract
     * @param Log             $logger
     */
    public function __construct(ContractService $contract, Log $logger)
    {
        $this->middleware('auth');
        $this->contract = $contract;
        $this->logger   = $logger;
    }

    /**
     * Invoke the translation Lambda for a contract.
     *
     * Sets the contract translation_status to IN_PROGRESS and fires the Lambda
     * asynchronously (InvocationType: Event). The Lambda is responsible for
     * updating translation_status and writing text_en / text_es / text_fr back
     * to contract_pages.
     *
     * @param int $id Contract ID
     * @return JsonResponse
     */
    public function invoke($id)
    {
        $contract = $this->contract->find($id);

        if (!$contract) {
            return response()->json(['result' => 'fail', 'message' => trans('contract.not_found')], 404);
        }

        $lambdaArn = env('TRANSLATION_LAMBDA_FUNCTION');

        if (empty($lambdaArn)) {
            $this->logger->error('TRANSLATION_LAMBDA_FUNCTION is not configured.');
            return response()->json(['result' => 'fail', 'message' => trans('contract.translation_not_configured')], 500);
        }

        try {
            $contract->translation_status = Contract::TRANSLATION_IN_PROGRESS;
            $contract->save();

            $lambda = LambdaClient::factory([
                'version' => 'latest',
                'region'  => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            $lambda->invoke([
                'FunctionName'   => $lambdaArn,
                'InvocationType' => 'Event',
                'Payload'        => json_encode(['contract_id' => (int) $id]),
            ]);

            $this->logger->info('Translation Lambda invoked', ['contract_id' => $id]);

            return response()->json([
                'result'  => 'success',
                'message' => trans('contract.translation_initiated'),
                'status'  => $contract->translation_status,
            ]);

        } catch (Exception $e) {
            $this->logger->error('Translation Lambda invocation failed', [
                'contract_id' => $id,
                'error'       => $e->getMessage(),
            ]);

            $contract->translation_status = Contract::TRANSLATION_FAILED;
            $contract->save();

            return response()->json(['result' => 'fail', 'message' => trans('contract.translation_failed'), 'error' => $e->getMessage()], 500);
        }
    }
}
