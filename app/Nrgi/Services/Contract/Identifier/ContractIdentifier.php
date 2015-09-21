<?php namespace App\Nrgi\Services\Contract\Identifier;

/**
 * Class Generator
 * @package App\Nrgi\Services\Contract\Identifier
 */
/**
 * Class ContractIdentifier
 * @package App\Nrgi\Services\Contract\Identifier
 */
class ContractIdentifier
{
    /**
     * @var string
     */
    protected $agency = 'ocds';
    /**
     * @var string
     */
    protected $separator = '-';
    /**
     * @var int
     */
    protected $prefix_length = 6;

    /**
     * @var string
     */
    protected $namespace = 'rc';
    /**
     * @var
     */
    protected $identifier;

    /**
     * @param $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
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
        return sprintf($this->format(), $this->agency, $this->getRegisteredPrefix(), $this->getPublisherNamespace(), $this->getInternalIdentifier());
    }

    /**
     * Get Format for Identifier
     *
     * @return string
     */
    public function format()
    {
        return '%s' . $this->separator . '%s%s' . $this->separator . '%s';
    }

    /**
     * Get Registered Prefix
     *
     * @return string
     */
    protected function getRegisteredPrefix()
    {
        return str_random($this->prefix_length);
    }

    /**
     * Get Publisher namespace
     *
     * @return string
     */
    protected function getPublisherNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get Internal Identifier
     *
     * @return string
     */
    protected function getInternalIdentifier()
    {
        return $this->identifier;
    }

}