<?php namespace Nrgi\Services\Contract;

use Nrgi\Repositories\Contract\AnnotationRepositoryInterface;
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
        $contactAnnotationId = $this->annotationRepo->getAnnotationByRange($data['ranges'][0], $inputData['contract']);
        $contactAnnotation = $this->annotationRepo->findOrCreate($contactAnnotationId);
        $contactAnnotation->annotation = $annotation;
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
            $annotationData[] = json_decode($annotation->annotation, true);
        }

        return array('total' => count($annotationData), 'rows' => $annotationData);
    }

}
