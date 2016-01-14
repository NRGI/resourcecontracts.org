<?php namespace Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;

/**
 * Class ApiTester
 *
 * $per_page     = 1;
 * $keys         = ['total', 'per_page', 'from', 'results'];
 * $results_keys = ['contract_id', 'contract_name', 'country', 'country_code', 'signature_year', 'language', 'resources', 'file_size', 'category'];
 *
 * $this->baseUrl = 'http://192.168.1.63:8002/';
 * $this->get('contracts', ['per_page' => $per_page])
 * ->seeJson()
 * ->matchValue('per_page', $per_page)
 * ->seeKeys($keys)
 * ->seeKeys($results_keys, 'results')
 * ->shouldHaveResults('results', $per_page);
 *
 */
class ApiTester extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $baseUrl = 'http://localhost:8000';

    /**
     * @var FutureResponse
     */
    private $response = null;

    /**
     * Make Post request
     *
     * @param       $uri
     * @param array $data
     * @return $this
     */
    public function post($uri, array $data = [])
    {
        $this->makeRequest($uri, 'post', $data);

        return $this;
    }

    /**
     * Make get request
     *
     * @param       $uri
     * @param array $query
     * @return $this
     */
    public function get($uri, $query = [])
    {
        $this->makeRequest($uri, 'GET', $query);

        return $this;
    }

    /**
     * Make a http request
     *
     * @param        $uri
     * @param string $method
     * @param array  $param
     */
    public function makeRequest($uri, $method = 'get', array $param = [])
    {
        $http   = $this->http();
        $method = strtolower($method);
        if ($method == 'post') {
            $param = ['body' => $param];
        }

        if ($method == 'get') {
            $param = ['query' => $param];
        }

        if (method_exists($http, $method)) {
            $this->response = $http->$method($uri, $param);
        }

        $this->_e(sprintf('Test "%s" request on: %s ', strtoupper($method), $this->response->getEffectiveUrl()));

        $this->assertEquals(200, $this->getCode());
    }

    /**
     * Get Response Code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Get response in Object
     *
     * @return mixed
     */
    public function getJson()
    {
        return $this->response->json(['object' => true]);
    }

    /**
     * Get response in Array
     *
     * @return mixed
     */
    public function getArray()
    {
        return $this->response->json();
    }

    /**
     * Assert Response is in Json format
     *
     * @return $this
     */
    public function seeJson()
    {
        $object = $this->getJson();
        $this->assertEquals('object', gettype($object), 'Response is not in Json format.');

        return $this;
    }

    /**
     * Assert Keys exists in response
     *
     * @param array  $keys
     * @param string $useKey
     * @return $this
     */
    public function seeKeys(array $keys = [], $useKey = null)
    {
        $actual = $this->getArray();

        if (!is_null($useKey)) {
            $actual = $actual[$useKey][0];
        }

        $actual = array_keys($actual);
        sort($actual);
        sort($keys);
        $this->assertTrue($this->arrays_are_similar($keys, $actual), 'Keys doesn\'t match with response.');

        return $this;
    }

    /**
     * Assert Result data is not empty
     *
     * @param $key
     * @return $this
     */
    public function shouldHaveResults($key, $total = null)
    {
        $actual = $this->getJson();

        if (is_null($total)) {
            $this->assertGreaterThanOrEqual(1, count($actual->$key), 'Response result is empty.');
        } else {
            $this->assertEquals($total, count($actual->$key), 'Response result doesn\'t matched.');
        }

        return $this;
    }

    /**
     * Assert equal value of a key
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function matchValue($key, $value)
    {
        $actual = $this->getJson();
        $this->assertEquals($value, $actual->$key, 'Response key-value doesn\'t matched.');

        return $this;
    }


    /**
     * Determine if two associative arrays are similar
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $a
     * @param array $b
     * @return bool
     * @credit: http://stackoverflow.com/questions/3838288/phpunit-assert-two-arrays-are-equal-but-order-of-elements-not-important
     */
    protected function arrays_are_similar($a, $b)
    {
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Output text in console
     *
     * @param $string
     */
    protected function _e($string)
    {
        print sprintf('%s%s', $string, PHP_EOL);
    }

    /**
     * Get instance of GuzzleHttp client
     *
     * @return Client
     */
    protected function http()
    {
        return new Client(
            [
                'base_url' => $this->baseUrl,
                'defaults' => [
                    'timeout'         => 10,
                    'allow_redirects' => false,
                ]
            ]
        );

    }

}