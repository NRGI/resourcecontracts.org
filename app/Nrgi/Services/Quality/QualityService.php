<?php

namespace App\Nrgi\Services\Quality;

use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
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
     * @var AnnotationRepositoryInterface
     */
    private $annotation;

    /**
     * @param ContractRepositoryInterface   $contract
     * @param AnnotationRepositoryInterface $annotation
     */
    public function __construct(ContractRepositoryInterface $contract, AnnotationRepositoryInterface $annotation)
    {
        $this->contract   = $contract;
        $this->annotation = $annotation;
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
        unset($metadataSchema['file_size'], $metadataSchema['company'], $metadataSchema['concession'], $metadataSchema['government_entity']);
        foreach ($metadataSchema as $key => $value) {
            $count = $this->contract->getMetadataQuality($key);
            if (is_array($value) && $key != "country") {
                $count = $this->contract->getResourceAndCategoryIssue($key);
            }

            $metadata[$key] = $count;
        }
        $metadata["Government Entity"] = $this->contract->getGovernmentEntityCount();
        $metadata["Company"]           = $this->contract->getCompanyCount();
        $metadata["Concession"]        = $this->contract->getConcessionCount();

        return $metadata;
    }

    /**
     * Get the count of presence of annotation's category
     *
     * @return array
     */
    public function getAnnotationsQuality()
    {
        $annotations         = [];
        $annotationsCategory = trans('codelist/annotation.annotation_category');

        foreach ($annotationsCategory as $key => $value) {
            $response          = $this->annotation->getAnnotationsQuality($key);
            $count             = !empty($response) ? count($response) : 0;
            $annotations[$key] = $count;
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
