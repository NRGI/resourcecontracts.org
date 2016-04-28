<?php namespace App\Nrgi\Services\Contract\Annotation\Page;


use App\Nrgi\Entities\Contract\Annotation\Page\Page;

/**
 * Class PageService
 * @package App\Nrgi\Services\Contract\Annotation\Page
 */
class PageService
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * PageService constructor.
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * Get annotation
     *
     * @param $page_id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getWithAnnotation($page_id)
    {
        return $this->page->with('parent.child')->where('id', $page_id)->first();
    }

    /**
     * Delete Annotation
     *
     * @param $contactAnnotationPageId
     * @return int
     */
    public function delete($contactAnnotationPageId)
    {
        return $this->page->destroy($contactAnnotationPageId);
    }

    /**
     * Create new annotation
     *
     * @param $annotationPageData
     * @return static
     */
    public function save($annotationPageData)
    {
        return $this->page->create($annotationPageData);
    }

    /**
     *
     *Update Annotation
     *
     * @param $id
     * @param $annotationPageData
     * @return static|boolean
     */
    public function update($id, $annotationPageData)
    {
        $page = $this->page->where('id', $id)->firstOrFail();
        $page->fill($annotationPageData);
        if ($page->save()) {
            return $page;
        }

        return false;
    }

    /**
     * Find annotation by id
     *
     * @param $id
     * @return \Illuminate\Support\Collection|null|static
     */
    public function find($id)
    {
        return $this->page->find($id);
    }

    /**
     * Updated annotation text or category
     *
     * @param       $id
     * @param array $data
     * @return Page
     */
    public function updateChildField($id, array $data)
    {
        $page = $this->page->find($id);

        if (array_key_exists('page_no', $data)) {
            $page->page_no = $data['page_no'];
        }

        if (array_key_exists('article_reference', $data)) {
            $page->article_reference = $data['article_reference'];
        }

        $page->save();

        return $page;
    }


}
