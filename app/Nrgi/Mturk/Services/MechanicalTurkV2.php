<?php

namespace App\Nrgi\Mturk\Services;

use Carbon\Carbon;

/**
 * Class MechanicalTurkV2
 */
class MechanicalTurkV2
{
    /**
     * @var HeaderBlackList
     */
    protected $headerBlacklist = [
        'cache-control'         => true,
        'content-type'          => true,
        'content-length'        => true,
        'expect'                => true,
        'max-forwards'          => true,
        'pragma'                => true,
        'range'                 => true,
        'te'                    => true,
        'if-match'              => true,
        'if-none-match'         => true,
        'if-modified-since'     => true,
        'if-unmodified-since'   => true,
        'if-range'              => true,
        'accept'                => true,
        'authorization'         => true,
        'proxy-authorization'   => true,
        'from'                  => true,
        'referer'               => true,
        'user-agent'            => true,
        'x-amzn-trace-id'       => true,
        'aws-sdk-invocation-id' => true,
        'aws-sdk-retry'         => true,
    ];

    // This metadata is copied from auto-generated sdk-root/src/data/mturk-requester/2017-01-17/api-2.json
    protected $metadata = [
        'apiVersion'          => '2017-01-17',
        'endpointPrefix'      => 'mturk-requester',
        'jsonVersion'         => '1.1',
        'protocol'            => 'json',
        'serviceAbbreviation' => 'Amazon MTurk',
        'serviceFullName'     => 'Amazon Mechanical Turk',
        'serviceId'           => 'MTurk',
        'signatureVersion'    => 'v4',
        'targetPrefix'        => 'MTurkRequesterServiceV20170117',
        'uid'                 => 'mturk-requester-2017-01-17',
    ];

    /// AWS API keys
    protected $mturk_access_key_id;
    protected $mturk_secret_access_key;

    // AWS region and Host Name (Host names are different for each AWS region)
    // As an example these are set to us-east-1 (US Standard)
    protected $host_name;
    protected $end_point;
    protected $mturk_region;
    protected $aws_service_name;

    // UTC timestamp and date
    protected $timestamp = '';
    protected $date = '';

    // HTTP request headers as key & value
    protected $request_headers = array();

    protected $content = '';
    protected $defaults;

    /**
     * Sets AWS keys, endpoint, and sandbox mode.
     *
     * MechanicalTurkV2 constructor.
     * @throws MTurkException
     */
    public function __construct()
    {
        if (config('mturk.credentials.AWS_ROOT_ACCESS_KEY_ID') === false ||
            config('mturk.credentials.AWS_ROOT_SECRET_ACCESS_KEY') === false
        ) {
            throw new MTurkException('AWS Root account keys must be set as environment variables.');
        }
        $this->mturk_access_key_id     = config('mturk.credentials.MTURK_ROOT_ACCESS_KEY_ID');
        $this->mturk_secret_access_key = config('mturk.credentials.MTURK_ROOT_SECRET_ACCESS_KEY');
        $this->host_name                   = 'mturk-requester.us-east-1.amazonaws.com';
        $this->end_point                   = 'https://'.$this->host_name;
        $this->mturk_region                = 'us-east-1';

        if (!empty(env('MTURK_REGION'))) {
            $this->mturk_region = env('MTURK_REGION');
        }

        $this->aws_service_name = $this->metadata['endpointPrefix'];

        // UTC timestamp and date
        $this->timestamp = gmdate('Ymd\THis\Z');
        $this->date      = gmdate('Ymd');

        if ($this->isSandbox()) {
            $this->setSandboxMode();
        }
    }

    /**
     * Sandbox or Production
     *
     * @return bool
     */
    protected function isSandbox()
    {
        return config('mturk.sandbox_mode');
    }

    /**
     * Sets the API in Sandbox Mode.
     * All API calls will go to the sandbox Amazon Mechanical Turk site and will use sandbox default config parameters.
     *
     * @return void
     */
    public function setSandboxMode()
    {
        $this->host_name = 'mturk-requester-sandbox.'.$this->mturk_region.'.amazonaws.com';
        $this->end_point = 'https://'.$this->host_name;
        $this->defaults  = array_merge(config('mturk.defaults.production'), config('mturk.defaults.sandbox'));
    }


    /**
     * Sets request headers
     *
     * @return array
     */
    public function setRequestHeaders()
    {
        // HTTP request headers as key & value
        $this->request_headers['Content-Type'] = "application/x-amz-json-1.1";
        $this->request_headers['Host']         = $this->host_name;
        $this->request_headers['x-amz-date']   = $this->timestamp;

        ksort($this->request_headers);

        return $this->request_headers;
    }

    /**
     * Sets canonical headers
     *
     * @param $request_headers
     *
     * @return string
     */
    public function setCanonicalHeader($request_headers)
    {
        // Canonical headers
        $canonical_headers = [];

        foreach ($request_headers as $key => $value) {
            if (!array_key_exists(strtolower($key), $this->headerBlacklist)) {
                $canonical_headers[] = strtolower($key).":".$value;
            }
        }

        return implode("\n", $canonical_headers);
    }

    /**
     * Sets signed headers
     *
     * @param $request_headers
     *
     * @return string
     */
    public function setSignedHeaders($request_headers)
    {
        // Signed headers
        $signed_headers = [];
        foreach ($request_headers as $key => $value) {
            if (!array_key_exists(strtolower($key), $this->headerBlacklist)) {
                $signed_headers[] = strtolower($key);
            }
        }

        return implode(";", $signed_headers);
    }

    /**
     * Sets canonical request
     *
     * @param $canonical_headers
     * @param $signed_headers
     *
     * @return string
     */
    public function setCanonicalRequest($canonical_headers, $signed_headers)
    {
        // Cannonical request
        $canonical_request   = [];
        $canonical_request[] = "POST";
        $canonical_request[] = "/";
        $canonical_request[] = "";
        $canonical_request[] = $canonical_headers;
        $canonical_request[] = "";
        $canonical_request[] = $signed_headers;
        $canonical_request[] = hash('sha256', $this->content);
        $canonical_request   = implode("\n", $canonical_request);

        return hash('sha256', $canonical_request);
    }

    /**
     * Sets scope
     *
     * @return array
     */
    public function setScope()
    {
        // AWS Scope
        $scope   = [];
        $scope[] = $this->date;
        $scope[] = $this->mturk_region;
        $scope[] = $this->aws_service_name;
        $scope[] = "aws4_request";

        return $scope;
    }

    /**
     * Changes array to string to generate signed value
     *
     * @param $scope
     * @param $hashed_canonical_request
     *
     * @return string
     */
    public function setStringToSign($scope, $hashed_canonical_request)
    {
        // String to sign
        $string_to_sign   = [];
        $string_to_sign[] = "AWS4-HMAC-SHA256";
        $string_to_sign[] = $this->timestamp;
        $string_to_sign[] = implode('/', $scope);
        $string_to_sign[] = $hashed_canonical_request;

        return implode("\n", $string_to_sign);
    }

    /**
     * Generates signature
     *
     * @param $string_to_sign
     *
     * @return string
     */
    public function generateSignature($string_to_sign)
    {
        // Signing key
        $kSecret  = 'AWS4'.$this->mturk_secret_access_key;
        $kDate    = hash_hmac('sha256', $this->date, $kSecret, true);
        $kRegion  = hash_hmac('sha256', $this->mturk_region, $kDate, true);
        $kService = hash_hmac('sha256', $this->aws_service_name, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        // Signature
        return hash_hmac('sha256', $string_to_sign, $kSigning);
    }

    /**
     * Sets authorization headers
     *
     * @param $signed_headers
     * @param $scope
     * @param $signature
     *
     * @return string
     */
    public function setAuthorization($signed_headers, $scope, $signature)
    {
        // Authorization
        $authorization = [
            'Credential='.$this->mturk_access_key_id.'/'.implode('/', $scope),
            'SignedHeaders='.$signed_headers,
            'Signature='.$signature,
        ];

        return 'AWS4-HMAC-SHA256'.' '.implode(',', $authorization);
    }

    /**
     * Curls the request
     *
     * @return array
     */
    public function curlRequest()
    {
        $request_headers          = $this->setRequestHeaders();
        $canonical_headers        = $this->setCanonicalHeader($request_headers);
        $signed_headers           = $this->setSignedHeaders($request_headers);
        $hashed_canonical_request = $this->setCanonicalRequest($canonical_headers, $signed_headers);
        $scope                    = $this->setScope();
        $string_to_sign           = $this->setStringToSign($scope, $hashed_canonical_request);
        $signature                = $this->generateSignature($string_to_sign);
        $authorization            = $this->setAuthorization($signed_headers, $scope, $signature);

        // Curl headers
        $curl_headers = ['Authorization: '.$authorization];

        foreach ($request_headers as $key => $value) {
            $curl_headers[] = $key.": ".$value;
        }

        $ch = curl_init($this->end_point);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->content);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);

        $response  = curl_exec($ch);
        $err       = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headers   = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        /*print("\n\nHeaders:\n");
        print_r($headers);
        print("\n\nHTTP Status:\n");
        print($http_code);
        print("\n\nError:\n");
        print_r($err);
        print("\n\nResponse:\n");
        print_r($response);*/

        $resp = [
            'response'  => json_decode($response, true),
            'http_code' => $http_code,
            'error'     => $err,
            'headers'   => $headers,
        ];

        if($http_code!=200) {
            $dt   = Carbon::now();
            $log  = new \Illuminate\Support\Facades\Log();
            $file = storage_path().'/logs/'.'api-response'.$dt->format("Y-m-d").'mturk.log';
            $log::useFiles($file);
            $log::error(json_encode($resp));
        }


        return $resp;
    }

    /**
     * Returns account balance
     *
     * @return mixed
     */
    public function getAccountBalance()
    {
        $this->content                         = '{}';
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".GetAccountBalance";

        $resp = $this->curlRequest();

        return $resp['response'];
    }

    /**
     * Creates new HIT
     *
     * @param $params
     *
     * @return mixed
     */
    public function createHITByExternalQuestion($params)
    {
        $this->content                         = json_encode($params);
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".CreateHIT";

        $resp = $this->curlRequest();

        return $resp['response'];
    }

    /**
     * Returns new HIT
     *
     * @param $hit_id
     *
     * @return mixed
     */
    public function getHit($hit_id)
    {
        $this->content                         = json_encode(array('HITId' => $hit_id));
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".GetHIT";

        $resp = $this->curlRequest();

        return $resp['response'];
    }

    /**
     * Updates expiration for specific HIT
     *
     * @param $hit_id
     * @param $expire_at
     *
     * @return mixed
     */
    public function updateExpirationForHIT($hit_id, $expire_at)
    {
        $this->content                         = json_encode(array('HITId' => $hit_id, 'ExpireAt' => $expire_at));
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".UpdateExpirationForHIT";

        $resp = $this->curlRequest();

        return $resp['response'];
    }

    /**
     * Deletes specific HIT
     *
     * @param $hit_id
     *
     * @return mixed
     */
    public function deleteHIT($hit_id)
    {
        $this->content                         = json_encode(array('HITId' => $hit_id));
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".DeleteHIT";

        $resp = $this->curlRequest();

        return $resp['response'];
    }

    /**
     * List assignment for specific HIT
     *
     * @param $hit_id
     *
     * @return mixed
     */
    public function listAssignmentsForHIT($hit_id)
    {
        $this->content                         = json_encode(array('HITId' => $hit_id));
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".ListAssignmentsForHIT";

        $resp = $this->curlRequest();

        return $resp['response'];
    }

    /**
     * Approve the HIT
     *
     * @param $assignment_id
     *
     * @return array
     */
    public function approveAssignment($assignment_id)
    {
        $this->content                         = json_encode(array('AssignmentId' => $assignment_id));
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".ApproveAssignment";

        $resp = $this->curlRequest();

        return $resp;
    }

    /**
     * Rejects the hit
     *
     * @param        $assignment_id
     * @param string $feedback
     *
     * @return array
     */
    public function rejectAssignment($assignment_id, $feedback = '')
    {
        $param = array('AssignmentId' => $assignment_id, 'RequesterFeedback' => $feedback);

        $this->content                         = json_encode($param);
        $this->request_headers['x-amz-target'] = $this->metadata['targetPrefix'].".RejectAssignment";

        $resp = $this->curlRequest();

        return $resp;
    }
}