<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Repositories\Contract\AnnotationRepositoryInterface;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class AnnotationService
 * @package Nrgi\Services\Contract
 */
class AnnotationService
{
    /**
     * @var AnnotationRepositoryInterface
     */
    protected $annotationRepo;
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * Constructor
     * @param AnnotationRepositoryInterface $annotationRepo
     * @param Guard $auth
     */
    function __construct(AnnotationRepositoryInterface $annotationRepo, Guard $auth)
    {
        $this->annotationRepo = $annotationRepo;
        $this->auth = $auth;
        $this->user = $auth->user();
    }

    /**
     * Store/Update a contact annotation.
     * @param $annotation
     * @param $inputData
     * @return mixed
     */
    public function save($annotation, $inputData)
    {
        $data = json_decode($annotation, true);
        $contactAnnotation = $this->annotationRepo->findOrCreate(isset($data['id'])?$data['id']:null);
        $contactAnnotation->annotation = $data;
        $contactAnnotation->user_id = $this->user->id;
        $contactAnnotation->contract_id = $inputData['contract'];
        $contactAnnotation->url = $inputData['url'];
        $contactAnnotation->document_page_no = $inputData['document_page_no'];

        return $this->annotationRepo->save($contactAnnotation);
    }

    /**
     * @param $annotation
     * @param array $inputs
     * @return boolean
     */
    public function delete($annotation, $inputs)
    {
        $data = json_decode($annotation, true);
        $contactAnnotationId = $this->annotationRepo->getAnnotationByRange($data['ranges'][0], $inputs['contract']);
        if ($contactAnnotationId != null) {
            return $this->annotationRepo->delete($contactAnnotationId);
        }

        return false;
    }

    /**
     * search annotation
     * @param array $params
     * @return mixed
     */
    public function search(array $params)
    {
        $annotationData = [];
        $annotations = $this->annotationRepo->search($params);

        foreach ($annotations as $annotation) {
            $json = $annotation->annotation;
            $json->id = $annotation->id;
            $annotationData[] = $json;
        }

        return array('total' => count($annotationData), 'rows' => $annotationData);
    }

    /**
     * @param $contractId
     * return List of annotation
     */
    public function getAllByContractId($contractId)
    {
        return $this->annotationRepo->getAllByContractId($contractId);
    }

}
