<?php

namespace App\Nrgi\Services\Microsoft;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use Psr\Log\LoggerInterface;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class MicrosoftService
 */
class MicrosoftService
{

    protected $tenant_id;
    protected $client_id;

    // AWS region and Host Name (Host names are different for each AWS region)
    // As an example these are set to us-east-1 (US Standard)
    protected $client_secret;
    protected $access_token;

    protected $access_scope = 'files.read.all offline_access';

    protected $redirect_uri;
     /**
     * @var LoggerInterface
     */
    protected $logger;
    protected $error;
    /**
     * Sets ABBY Keys, Abby Password and Abby Url.
     *
     * AbbyService constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->tenant_id = env('ONE_DRIVE_TENANT_ID');
        $this->client_id = env('ONE_DRIVE_CLIENT_ID');
        $this->client_secret = env('ONE_DRIVE_CLIENT_SECRET');
        $this->redirect_uri = env('ONE_DRIVE_AUTH_REDIRECT');
        $this->logger = $logger;
    }

    //Should only be used while implementing application auth instead of user auth
    public function setup() 
    {
        $guzzle = new \GuzzleHttp\Client();
        $url = 'https://login.microsoftonline.com/' . $tenant_id . '/oauth2/v2.0/token';
        $token = json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ])->getBody()->getContents());
        $this->access_token = $token->access_token;
    }

    public function setAccessToken($one_drive_data)
    {
        $value = $one_drive_data;
        if(!isset($value) || !is_string($value)) {
        $value = session('ONE_DRIVE_AUTH', null);
        }
        $is_one_drive_authenticated = false;
        $this->access_token = null;
        if(isset($value) && is_string($value) && strlen($value) > 0) {
        $this->logger->info('ONE_DRIVE_VALUE FOUND');
            $auth_data = json_decode($value, true);
            if(isset($auth_data['expiry_date'])) {
                $is_one_drive_authenticated = $this->isTokenDateLeft($auth_data['expiry_date']);

            }
            if(!$is_one_drive_authenticated) {
                $this->access_token = null;
            } else if(isset($auth_data['one_drive_token'])) {
                $this->access_token = $auth_data['one_drive_token'];
            }
        }
        return $this->access_token;
    }

    public function getUrl()
    {
        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id='.$this->client_id.'&scope='.$this->access_scope.'&response_type=code&redirect_uri='.$this->redirect_uri;
        return $url;
    }

    public function getAccessToken($code) 
    {
       try { 
        $this->access_token = null;
        $this->error = false;
        $guzzle = new \GuzzleHttp\Client();
        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
        $token = json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope' => $this->access_scope,
                'redirect_uri' => $this->redirect_uri,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ],
        ])->getBody()->getContents());
        $this->logger->info('ACCESS_DATA'.json_encode($token));
        if(!isset($token->access_token)) {
            $this->error = true;
        } else {
            $this->access_token = $token->access_token;
            $this->logger->info('ACCESS_DATA_token'.json_encode($this->access_token));
            $this->storeAccessTokenData($token);
        }
        return !$this->error;
        }
        catch(\Exception $e) {
            $this->error = true;
            return !$this->error;
        }

    }

    public function storeAccessTokenData ($token_data)
    {
        if(isset($token_data->expires_in))
        {
            //adding 15 mins cushion time because if token is going to expire within next 15 mins,
            //then contract import may fail in the middle
            $expiry_date = Carbon::now()->addSeconds($token_data->expires_in - 450)->format('Y-m-d H:i:s');
            $token_arr = json_encode(['expiry_date' => $expiry_date, 'one_drive_token' => $token_data->access_token]);
            $this->logger->info('ACCESS_DATA_store'.$token_arr);
            session(['ONE_DRIVE_AUTH' => $token_arr]);
        }
    }

    public function isTokenDateLeft($expiry_timestamp)
    {
        $expiry_date = Carbon::createFromFormat('Y-m-d H:i:s', $expiry_timestamp);
        $current_date = Carbon::now();
        return $expiry_date->gt($current_date);
    }

    public function hasAuthenticatedToken() 
    {
        $is_one_drive_authenticated = false;
        $value = session('ONE_DRIVE_AUTH', null);
        if(isset($value) && is_string($value) && strlen($value) > 0) {
            $auth_data = json_decode($value, true);
            if(isset($auth_data['expiry_date']) && isset($auth_data['one_drive_token'])) {
                $is_one_drive_authenticated = $this->isTokenDateLeft($auth_data['expiry_date']);
            }
        }
        return $is_one_drive_authenticated;
    }

    public function getAuthLink() 
    {
        return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id='.$this->client_id.'&scope='.$this->access_scope.'&response_type=code&redirect_uri='.$this->redirect_uri;
    }
    public function downloadFile($url, $save_file_loc, $one_drive_data) 
    {
        try {
            $this->setAccessToken($one_drive_data);
            if(!is_string($this->access_token)) {
                $this->logger->error('ONE_DRIVE_NOT_AUTHENTICATED'.$this->access_token);
                return false;
            }
            $delim = "/Documents/";
            $filePath = substr($url, strpos($url, $delim) + strlen($delim));
            $urlArr = explode('/Documents/', $url);
            if(strlen(trim($filePath)) === 0) {
                return null;
            }
            $download_url = 'https://graph.microsoft.com/v1.0/me/drive/root:/'.$filePath.':/content';
            $authorization = "Authorization: Bearer ".$this->access_token; // Prepare the authorisation token
            $guzzle = new \GuzzleHttp\Client(['headers' => ['Authorization' => "Bearer ".$this->access_token]]);
            $guzzle->get($download_url, ['sink' => $save_file_loc]);
            return true;
        }
        catch(\Exception $e) {
            $this->logger->error('Error downloading fiel'.$e->getMessage());
            return false;
        }
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
        curl_setopt($curlHandle, CURLOPT_USERPWD, "$applicationId:$password");
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

}
