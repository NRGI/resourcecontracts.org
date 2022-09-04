<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateGovernmentEntities extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updategovernmententities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command updates the government entitites.';

    protected $api = 'http://joinedupdata.org/PoolParty/api/thesaurus/1DCE5BA4-B07D-0001-17D3-93C6F01F18F4/childconcepts?parent=http://joinedupdata.org/NRGI/4c61a3a0-8c34-4391-976d-4065f9d47340&properties=skos:altLabel,skos:narrower&transitive=true';


    /**
     * Create a new command instance.
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
    public function handle()
    {
        $client   = new GuzzleHttp\Client();
        $response = $client->get(
            $this->api,
            [
                'auth' => [
                    env('SPARQL_USERNAME'),
                    env('SPARQL_PASSWORD')
                ]
            ]
        );

        $output = $response->json();
        $countries          = [];
        $governmentEntities = [];

        foreach ($output as $data) {
            if (array_key_exists('narrowers', $data)) {

                $countries[$data['prefLabel']] = $data['narrowers'];
            }
        }

        foreach ($countries as $key => $country) {
            $k = $this->getCountryCode($key);
            if (!empty($k)) {
                $key = $k;
            }
            $governmentEntities[$key] = [];
            foreach ($country as $narrowerValue) {
                foreach ($output as $data1) {

                    if ($data1['uri'] == $narrowerValue) {

                        array_push(
                            $governmentEntities[$key],
                            [
                                'identifier' => $this->getIdentifier($narrowerValue),
                                'entity'     => $data1['prefLabel']
                            ]
                        );

                    }
                }
            }
        }

        $text = '<?php';
        $text .= "\n";
        $text .= '$governmentEntity=\''.json_encode($governmentEntities).'\';';
        $text .= "\n";
        $text .= 'return json_decode($governmentEntity);';
        $text .= "\n";
        $config = public_path() . '/../config/governmentEntities.php';
        file_put_contents($config, $text);
        $this->info('Complete.');

        $file = storage_path().'/logs/scheduler.log';
        Log::useFiles($file);
        Log::info("Update government entities command successfully executed");
    }

    /**
     * Returns Government Identifier value from a long string.
     * @param $narrowerValue
     * @return string
     */
    private function getIdentifier($narrowerValue)
    {
        return substr($narrowerValue, 52);
    }


    /**
     * Replacing the country name with country code
     * @param $countryName
     * @return int|string
     */
    private function getCountryCode($countryName)
    {
        $countries         = trans("codelist/country");
        $unListedCountries =
            [
                "congo the democratic republic of the" => "Congo, the Democratic Republic of the",
                "tanzania united republic of"          => "Tanzania, United Republic of",
                "bolivia plurinational state of"       => "Bolivia, Plurinational State of",
                "libyan arab jamahiriya"               => "Libya"
            ];
        $countryName       = array_key_exists($countryName, $unListedCountries) ? strtolower($unListedCountries[$countryName]) : $countryName;

        foreach ($countries as $key => $value) {
            $value = strtolower($value);
            if ($value == $countryName) {

                $countryName = $key;;
            }
        }
        return $countryName;
    }


}
