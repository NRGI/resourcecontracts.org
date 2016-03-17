<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Nrgi\Entities\Contract\Annotation\Annotation as Annotation;
use App\Nrgi\Entities\Contract\Annotation\Page\Page as Page;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AnnotationHarmonization extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:harmonizeannotation';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Harmonizes the annotation';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $contracts      = $this->getContractId();
        $uniqueContract = array_unique($contracts);
        foreach ($uniqueContract as $contract) {
            $data = $this->getAnnotationData($contract);

            if (!empty($data)) {
                $collection = collect($data);
                $collection = $collection->sortBy('id')->groupBy('category')->all();


                foreach ($collection as $key => $collect) {
                    $id   = [];
                    $text = [];

                    foreach ($collect as $c) {
                        $id[]   = $c->id;
                        $text[] = $c->text;

                    }
                    $firstId    = $id[0];
                    $idToRemove = array_slice($id, 1);
                    $uniqueText = array_unique($text);

                    $textToRemove = implode(' | ', $uniqueText);

                    DB::table('contract_annotations')
                      ->where('id', $firstId)
                      ->update(['text' => $textToRemove]);


                    foreach ($idToRemove as $id) {

                        $idToGet = DB::table('contract_annotation_pages')
                                     ->select('id')
                                     ->where('annotation_id', $id)
                                     ->update(['annotation_id' => $firstId]);

                        DB::table('contract_annotations')
                          ->where('id', $id)
                          ->delete();

                    }


                }

            }

        }
    }

    /**
     * Get the unique contract id from the table.
     * @return array
     */
    private function getContractId()
    {
        $annotationData = Annotation::all();

        $contractId = [];
        foreach ($annotationData as $data) {

            $contractId[] = $data->contract_id;
        }

        return $contractId;
    }

    /**
     * write brief description
     * @param $contractId
     * @return int
     */
    private function getAnnotationData($contractId)
    {
        $data = DB::select(
            "SELECT * from contract_annotations where contract_id = $contractId AND category in (select category as count from contract_annotations where contract_id = $contractId group by category having count(category) >1) order by category desc"
        );

        return $data;

    }


}
