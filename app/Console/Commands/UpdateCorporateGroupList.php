<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
/**
 * Class UpdateCorporateGroupList
 * @package App\Console\Commands
 */
class UpdateCorporateGroupList extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updategroup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update openCorporate group list.';


    /**
     * @var string
     */
    protected $api_url = 'https://api.opencorporates.com/v0.4/corporate_groupings/search';

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
     * @return void
     */
    public function handle()
    {
        $url = sprintf('%s?q=&per_page=%s', $this->api_url, 100);
        try {
            $content = file_get_contents($url);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error('File get error.');

            return;
        }
        $data = json_decode($content);

        if (!empty($data)) {
            $groups   = [];
            $groups[] = $data->results->corporate_groupings;

            for ($i = 2; $i <= $data->results->total_pages; $i ++) {
                $url .= '&page=' . $i;
                try {
                    $content = file_get_contents($url);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());

                    return;
                }

                $data     = json_decode($content);
                $groups[] = $data->results->corporate_groupings;
            }

            $config = public_path() . '/../config/groups.php';
            file_put_contents($config, $this->generateConfig($groups));
            $this->info('Complete.');
        }

        $file = storage_path().'/logs/scheduler.log';
        Log::useFiles($file);
        Log::info("Update corporate list command successfully executed");
    }

    /**
     * Generate Config
     *
     * @param $groups
     * @return string
     */
    protected function generateConfig($groups)
    {
        $text = '<?php';
        $text .= "\n";
        $text .= 'return [';
        $text .= "\n";

        foreach ($groups as $key => $group) {
            foreach ($group as $k => $v) {
                $text .= sprintf(
                    '[ "wikipedia_id" => "%s", "name" => "%s", "opencorporates_url" =>"%s"],',
                    $v->corporate_grouping->wikipedia_id,
                    addslashes($v->corporate_grouping->name),
                    $v->corporate_grouping->opencorporates_url
                );
                $text .= "\n";
            }
        }
        $text .= "\n";
        $text .= '];';

        return $text;
    }

}
