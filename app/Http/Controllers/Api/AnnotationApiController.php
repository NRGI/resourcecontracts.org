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
            return response()->json(['status' => 'success']);
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
        $content = $request->getContent();
        $this->annotationService->delete($content, $request->all());

        return response()->json(['status' => 'success']);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function search(Request $request)
    {
        $response = $this->annotationService->search($request->all());

        return response()->json($response);
    }
}
