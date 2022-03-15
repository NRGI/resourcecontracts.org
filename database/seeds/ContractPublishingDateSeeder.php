<?php

use Illuminate\Database\Seeder;

class ContractPublishingDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('contracts')->orderBy('id')->chunk(100, function ($contracts) {
            foreach ($contracts as $contract) {

                $elements = ["metadata", "text", "annotation"];
                $data     = [];
                $publishing_date = array();
                foreach ($elements as $element) {
                    $data[$element] =DB::table('activity_logs')
                    ->where('contract_id', $contract->id)
                    ->whereRaw(sprintf("message_params->>'type' = '%s'", $element))
                    ->orderBy("created_at", "desc")
                    ->first();
                    if(isset($data[$element]))
                    {
                        $message_params = json_decode($data[$element]->message_params);
                        if(isset($message_params) && isset($message_params->new_status))
                        {
                            $publishing_date[$element] = ['status' => $message_params->new_status, 'datetime'=>$data[$element]->created_at];
                        } else {
                            var_dump('CONTRACT ID'.$contract->id.$element);
                            var_dump(json_encode($data[$element]));
                            var_dump(json_encode($data[$element]->message_params));
                            var_dump(json_encode($message_params));
                        }
                    }
                    else {
                        var_dump('NOT FOUND'.$contract->id.$element);
                    }
                   
                }
                $contract->publishing_date=$publishing_date;

                DB::table('contracts')->where('id', $contract->id)->update(['publishing_date'=>json_encode($publishing_date)]);

                
                //
            }
        });
    }
}
