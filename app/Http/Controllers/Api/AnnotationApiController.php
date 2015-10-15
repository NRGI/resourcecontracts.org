<?php namespace app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\AnnotationService;
use Illuminate\Http\Request;

/**
 * Class AnnotationApiController
 * @package app\Http\Controllers\Api
 */
class AnnotationApiController extends Controller
{
    protected $annotationService;

    /**
     * @param AnnotationService $annotationService
     */
    public function __construct(AnnotationService $annotationService)
    {
        $this->annotationService = $annotationService;
        $this->middleware('auth');
    }

    /**
     * save annotations
     * @param Request $request
     * @return string
     */
    public function save(Request $request)
    {
        $content    = $request->getContent();
        $annotation = $this->annotationService->save($content, $request->all());
        if ($annotation) {
            return response()->json(['status' => 'success', "id" => $annotation->id]);
        }

        return response()->json(['status' => 'error']);
    }


    /**
     * @param Request $request
     * @return json string
     *
     */
    public function delete(Request $request)
    {
        $this->annotationService->delete($request->all());

        return response()->json(['status' => 'success']);
    }

    /**
     * @param Request $request
     * @return json
     */
    public function search(Request $request)
    {
        $response = $this->annotationService->search($request->all());

        return response()->json($response);
    }

    /**
     * @param $contractId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getContractAnnotations($contractId)
    {
        $contractAnnotations = $this->annotationService->getContractAnnotations($contractId);

        return response()->json($contractAnnotations);
    }

    /**
     * update annotations
     * @param Request $request
     * @return string
     */
    public function update(Request $request)
    {
        $id   = $request->get('pk');
        $data = [];
        if (!in_array($request->get('name'), ['text', 'category'])) {
            return response()->json(['status' => 'error']);
        }
        $data[$request->get('name')] = $request->get('value');
        $annotation                  = $this->annotationService->update($id, $data);
        if ($annotation) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error']);
    }
}
