<?php namespace App\Console\Commands;


use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepository;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class UpdateAnnotationCategory extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updateannotationcategory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Annotation Category.';
    /**
     * @var ContractRepository
     */
    protected $contract;
    /**
     * @var
     */
    private $annotation;

    public $wongCategory= [];
    /**
     * Create a new command instance.
     *
     * @param AnnotationRepository $annotation
     * @internal param ContractRepository $contract
     */
    public function __construct(AnnotationRepository $annotation)
    {
        parent::__construct();
        $this->annotation = $annotation;
    }

    /**
     * Execute the console command.
     *
     */

    public function handle()
    {
        $contract_id = $this->input->getOption('id');
        if (is_null($contract_id)) {
            $annotations = $this->annotation->getAllAnnotations();
        } else {
            $annotations = $this->annotation->getAnnotationByContractId($contract_id);
        }
        foreach ($annotations as $annotation) {
            $this->updateAnnotation($annotation);
        }
        dd(array_unique($this->wongCategory));
    }


    protected function getOptions()
    {
        return [
            ['id', null, InputOption::VALUE_OPTIONAL, 'Contract ID.', null],
        ];
    }

    /**
     * Update annotation category
     * @param Annotation $annotation
     */
    private function updateAnnotation(Annotation $annotation)
    {
        $ann      = (array) $annotation->annotation;
        $category = $this->getCategory($ann);
        if(!empty($category))
        {
            $ann['category'] = $category;
            $annotation->annotation = $ann;
            $annotation->save();
            $this->info(sprintf('Contract ID %s : Annotation ID %s : Annotation UPDATED', $annotation->contract_id,$annotation->id));
        }
        else{
            $this->info(sprintf('Contract ID %s : Annotation ID %s : Annotation Not UPDATED', $annotation->contract_id,$annotation->id));
        }

    }

    /**
     * Get annotation category key
     * @param $ann
     * @return string
     */
    private function getCategory($ann)
    {
        $allCategoryTemp = trans('codelist/annotation')['annotation_category_temp'];
        $allCategory     = trans('codelist/annotation')['annotation_category'];
        $categoryValue   = trim($ann['category']);
        $catKey          = '';
        if (in_array($categoryValue, $allCategoryTemp)) {
            $catKey = array_search($categoryValue, $allCategoryTemp);
        }
        elseif (in_array($categoryValue, $allCategory)) {
            $catKey = array_search($categoryValue, $allCategory);
        }
        elseif(!array_key_exists($categoryValue,$allCategory)){
            array_push($this->wongCategory,$categoryValue);
        }

        return $catKey;
    }
}