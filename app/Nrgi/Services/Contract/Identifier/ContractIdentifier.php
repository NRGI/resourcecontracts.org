<?php namespace App\Nrgi\Services\Contract\Identifier;

/**
 * Class ContractIdentifier
 * @package App\Nrgi\Services\Contract\Identifier
 */
class ContractIdentifier
{
    /**
     * @var string
     */
    protected $agency = 'ocds-591adf';
    /**
     * @var string
     */
    protected $separator = '-';
    /**
     * @var int
     */
    protected $random_length = 10;
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $iso_code;

    /**
     * @param $identifier
     * @param $iso_code
     */
    public function __construct($identifier, $iso_code)
    {
        $this->identifier = $identifier;
        $this->iso_code   = $iso_code;
    }

    /**
     * Return random ID
     *
     * @return string
     */
    public function __toString()
    {
        return $this->generate();
    }

    /**
     * Build OpenContract ID
     *
     * @return string
     */
    public function generate()
    {
        return sprintf(
            $this->format(),
            $this->agency,
            $this->getIsoCode(),
            $this->getRandomNumber(),
            $this->getIdentifier()
        );
    }

    /**
     * Get Format for Identifier
     *
     * @return string
     */
    public function format()
    {
        return '%s' . $this->separator . '%s%s%s';
    }

    /**
     * Get Registered Prefix
     *
     * @return string
     */
    protected function getRandomNumber()
    {
        return str_random_number($this->random_length);
    }

    /**
     * Get Country Iso Code
     *
     * @return string
     */
    protected function getIsoCode()
    {
        return mb_substr(strtoupper($this->iso_code), 0, 2);
    }

    /**
     * Get Internal Identifier
     *
     * @return string
     */
    protected function getIdentifier()
    {
        return mb_substr(strtoupper($this->identifier), 0, 2);
    }

}