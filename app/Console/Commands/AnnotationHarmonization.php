<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Console\Command;
use App\Nrgi\Entities\Contract\Annotation\Annotation as Annotation;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;

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
        foreach ($contracts as $contract) {

            $data = $this->getAnnotationData($contract);
            if (empty($data)) {
                $this->error($contract . ': Annotation not found.');
                continue;
            }
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

                    DB::table('contract_annotation_pages')
                      ->select('id')
                      ->where('annotation_id', $id)
                      ->update(['annotation_id' => $firstId]);

                    DB::table('contract_annotations')
                      ->where('id', $id)
                      ->delete();

                }

                $this->info($contract .': ' . $id. ' - Complete.');
            }

        }
    }

    /**
     * Get the unique contract id from the table.
     *
     * @return array
     */
    private function getContractId()
    {
        $contractId = [];
        $id         = $this->input->getOption('id');

        if (is_null($id)) {
            $annotationData = Annotation::all();
            foreach ($annotationData as $data) {
                $contractId[] = $data->contract_id;
            }
        } else {
            $contractId[] = $id;
        }

        return array_unique($contractId);
    }

    /**
     * Get Annotation data by contract id
     *
     * @param $contractId
     * @return int
     */
    private function getAnnotationData($contractId)
    {
        return DB::select(
            "SELECT * from contract_annotations where  contract_id = $contractId AND category in (select category as count from contract_annotations where contract_id = $contractId group by category having count(category) >1) order by category desc"
        );
    }

    /**
     * Get Command Options
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['id', null, InputOption::VALUE_OPTIONAL, 'Contract ID.', null],
        ];
    }

    /**
     * Check if contract from Philippines
     *
     * @param $contract
     * @return bool
     */
    private function isPHContract($contract)
    {
        $count = Contract::where('id', $contract)->whereRaw("metadata->'country'->>'code' = 'PH'")->count();

        return $count > 0;
    }

}
