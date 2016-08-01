<?php namespace App\Http\Controllers\Annotation\Api;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\Annotation\AnnotationService;
use Illuminate\Http\Request;

/**
 * Class AnnotationApiController
 * @package app\Http\Controllers\Annotation\Api
 */
class ApiController extends Controller
{
    /**
     * @var AnnotationService
     */
    protected $annotationService;

    /**
     * @param AnnotationService $annotationService
     */
    public function __construct(AnnotationService $annotationService)
    {
        $this->middleware('auth');
        $this->annotationService = $annotationService;
    }

    /**
     * Save annotation
     *
     * @param Request $request
     * @return string
     */
    public function save(Request $request)
    {
        $annotation = $this->annotationService->save($request->all());

        if ($annotation) {
            return response()->json(['status' => 'success', "id" => $annotation->id]);
        }

        return response()->json(['status' => 'error']);
    }

    /**
     * Delete an annotation
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id)
    {
        $this->annotationService->delete($id);

        return response()->json(['status' => 'success']);
    }

    /**
     * Search Annotation
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function search(Request $request)
    {
        $response = $this->annotationService->search($request->all());

        return response()->json($response);
    }

    /**
     * Get All annotation by contract id
     *
     * @param $contractId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getContractAnnotations($contractId)
    {
        $contractAnnotations = $this->annotationService->getContractAnnotations($contractId);

        return response()->json($contractAnnotations);
    }

    /**
     * Update annotation
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request)
    {
        $data  = [];
        $field = $request->get('name');
        if (!in_array($field, ['text', 'category', 'page_no', 'article_reference'])) {
            return response()->json(['status' => 'error']);
        }
        $data[$field] = $request->get('value');
        $annotation   = $this->annotationService->update($request->get('pk'), $data);
        if ($annotation) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error']);
    }
}
