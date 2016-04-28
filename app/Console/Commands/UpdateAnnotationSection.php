<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class updateAnnotationSection
 * @package App\Console\Commands
 */
class UpdateAnnotationSection extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updateannotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update annotation data from annotations table.';
    /**
     * @var Annotation
     */
    protected $annotation;

    /**
     * Create a new command instance.
     *
     * @param Annotation $annotation
     */
    public function __construct(Annotation $annotation)
    {
        parent::__construct();
        $this->annotation = $annotation;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $annotation_id = $this->input->getOption('id');
        $this->updateAnnotation($annotation_id);
    }

    /**
     * Update annotation
     *
     * @param null $annotation
     * @return bool
     */
    public function updateAnnotation($annotation = null)
    {
        if (is_null($annotation)) {
            $annotations = $this->annotation->all();
            if (!is_null($annotations)) {
                foreach ($annotations as $annotation) {
                    $this->updateAnnotation($annotation);
                }

                return true;
            }
            $this->info('Annotation not found');

            return false;
        }

        if (is_numeric($annotation)) {
            $annotation = Annotation::find($annotation);
            if (is_null($annotation)) {
                $this->info('Annotation not found');

                return false;
            }
        }

        if ($annotation instanceof Annotation) {
            $separator = '--';

            $annotationArray = json_decode($annotation->annotation, true);
            $text            = explode($separator, $annotationArray['text']);

            $comment = $annotationArray['text'];
            $section = '';

            if (count($text) == 2) {
                $comment = $text[0];
                $section = isset($text[1]) ? trim($text[1]) : '';
            }

            if (count($text) > 2) {
                $section = trim(end($text));
                unset($text[count($text) - 1]);
                $comment = join($separator, $text);
            }

            $annotationArray['text']    = $comment;
            $annotationArray['section'] = $section;
            $annotationArray['parent']  = '';
            //$annotation->annotation     = $annotationArray;

            \DB::table('contract_annotations')
              ->where('id', $annotation->id)
              ->update(['annotation' => json_encode($annotationArray)]);

            $this->info(sprintf('Annotation %s %s - updated', $annotation->contract_id, $annotation->id));
        }
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['id', null, InputOption::VALUE_OPTIONAL, 'Annotation ID.', null],
        ];
    }

}
