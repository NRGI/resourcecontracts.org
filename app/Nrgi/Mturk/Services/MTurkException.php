<?php namespace App\Nrgi\Mturk\Services;

use Exception;

/**
 * Class MTurkException
 * @package App\Nrgi\Mturk\Services
 */
class MTurkException extends Exception
{

    protected $errors;

    /**
     * @param string    $message
     * @param null      $errors
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($message, $errors = null, $code = 500, Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get Errors
     *
     * @return null
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
