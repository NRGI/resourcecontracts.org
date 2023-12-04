<?php

namespace App\Nrgi\Services\Abby;

use Illuminate\Support\Facades\Log;

/**
 * Class AbbyService
 */
class AbbyService
{

    protected $abby_app_id;
    protected $abby_app_password;

    // AWS region and Host Name (Host names are different for each AWS region)
    // As an example these are set to us-east-1 (US Standard)
    protected $service_url;
    protected $end_point;

    /**
     * Sets ABBY Keys, Abby Password and Abby Url.
     *
     * AbbyService constructor.
     */
    public function __construct()
    {
        $this->abby_app_id       = env('ABBYY_APP_ID');
        $this->abby_app_password = env('ABBYY_PASSWORD');
        $this->service_url       = env('ABBY_OCR_URL');
    }

    /**
     * Curls the request
     *
     * @return array
     */
    public function curlRequest()
    {
        $curlHandle = curl_init();
        $url = $this->service_url.$this->end_point;
        $applicationId = $this->abby_app_id;
        $password = $this->abby_app_password;
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curlHandle, CURLOPT_USERPWD, "$applicationId:$password");
        curl_setopt($curlHandle, CURLOPT_USERAGENT, "Resource Contracts");
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);

        $response  = curl_exec($curlHandle);
        $err       = curl_error($curlHandle);
        $http_code = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $headers   = curl_getinfo($curlHandle, CURLINFO_HEADER_OUT);

        $resp = [
            'response'  => json_decode($response, true),
            'http_code' => $http_code,
            'error'     => $err,
            'headers'   => $headers,
        ];

        return $resp;
    }

/**
 * Returns application info
 *
 * @return mixed
 */
public function getApplicationInfo()
{
    $this->end_point = '/License/pageCount';

    try {
        $resp = $this->curlRequest();
        if (isset($resp['response']) && isset($resp['response']['Value'])) {
            $pageCount = $resp['response']['Value'];
            return ['pages' => $pageCount];
        } else {
            return ['pages' => 'N/A'];
        }
    } catch (Exception $e) {
        return ['pages' => 'N/A'];
    }
}

}
