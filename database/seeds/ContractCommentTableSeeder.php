<?php
use Illuminate\Database\Seeder;
use App\Nrgi\Entities\Contract\Comment\Comment;

/**
 * Class ContractCommentTableSeeder
 */
class ContractCommentTableSeeder extends Seeder
{
    /**
     * update contract comment action column to rejected
     */
    public function run()
    {
        Comment::where('action', '=', null)->update(array('action' => 'rejected'));
    }
}
