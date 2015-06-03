<?php namespace app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Nrgi\Services\Contract\AnnotationService;
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
        $content = $request->getContent();
        $response = $this->annotationService->save($content, $request->all());

        return stripslashes($response->annotation);
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

        return json_encode(['status' => 'success']);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function search(Request $request)
    {
        $response = $this->annotationService->search($request->all());

        return json_encode($response);
    }
}
