<?php

use Illuminate\Database\Seeder;
use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Entities\MturkTaskItem;
use Illuminate\Support\Facades\Log;

class MturkTaskItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        MturkTaskItem::truncate(); 
        DB::table('mturk_tasks')->orderBy('id')->chunk(100, function ($tasks) {
            foreach ($tasks as $task) {
                $task_assignment = json_decode($task->assignments, true);
                $assignment =  isset($task_assignment['assignment']) ? $task_assignment['assignment'] : null;
                $answer =  isset($assignment['answer']) ? json_encode($assignment['answer']) : null;
                var_dump('ANSWER');
                var_dump($answer);
                var_dump('ANSWEREND');
                var_dump($task->page_no);
                var_dump(json_encode($task->id));
                var_dump( $task->text);
                var_dump( $task->pdf_url);

                MturkTaskItem::create(['task_id'=>$task->id, 'page_no' => $task->page_no, 'text' => $task->text,'pdf_url' => $task->pdf_url, 'answer' => $answer]);
                //
            }
        });
    }
}