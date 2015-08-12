<?php

namespace App\Nrgi\Services\Quality;

use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;

/**
 * Checks the quality of Metadata,Text and Annotations
 *
 * Class QualityService
 * @package App\Nrgi\Quality
 */
class QualityService
{
    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;

    /**
     * @param ContractRepositoryInterface $contract
     */
    public function __construct(ContractRepositoryInterface $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Get the count of presence of contract's metadata
     *
     * @return array
     */
    public function getMetadataQuality()
    {
        $metadata       = [];
        $metadataSchema = config('metadata.schema.metadata');
        unset($metadataSchema['file_size'], $metadataSchema['company'], $metadataSchema['concession']);
        foreach ($metadataSchema as $key => $value) {
            $count          = $this->contract->getMetadataQuality($key);
            $metadata[$key] = $count;
        }

        return $metadata;
    }

    /**
     * Get the count of presence of annotation's category
     *
     * @return array
     */
    public function getAnnotationsQuality()
    {
        $annotations       = [];
        $annotationsSchema = trans('codelist/annotation.categories');
        asort($annotationsSchema);

        foreach ($annotationsSchema as $key) {
            $check    = 0;
            $response = $this->contract->getAnnotationsQuality($key);

            if (!empty($response)) {
                $check = 1;
            }
            if (isset($annotations[$key])) {
                $annotations[$key] = $annotations[$key] + $check;
            } else {
                $annotations[$key] = $check;
            }

        }

        return $annotations;
    }

    /**
     * Get the total contract count
     *
     * @return int
     */
    public function getTotalContractCount()
    {
        return $this->contract->getTotalContractCount();
    }


}
