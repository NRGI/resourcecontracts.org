<?php
use Tests\Api\ApiTester;
use Laracasts\Integrated\Extensions\Goutte as IntegrationTest;

/**
 * Class AdminTest
 */
class AdminTest extends IntegrationTest
{
    public $baseUrl = 'http://192.168.1.63:8000';
    public $apiUrl = 'http://192.168.1.63:8002';
    public $elastic_search_url = 'http://192.168.1.63:8005';
    public $sub_site_url = 'http://localhost:8020';
    public $api;

    /**
     * Returns total number of Contracts Present in our Database
     * @return int
     */
    public function getTotalNumberOfContracts()
    {
        $this->api = new ApiTester();

        return $this->api->get($this->apiUrl . '/contracts')->getJson()->total;
    }


    /**
     * Returns the decoded input json.
     * @return Array
     */
    public function getInputFileContents()
    {
        return json_decode(file_get_contents(__DIR__ . '/../../files/input/input.json'), true);
    }


    public function testItVisitsHomePage()
    {

        $this->login()
             ->andSee('Total Contract')
             ->onPage('/home');

        $message = sprintf("%s  %s ", "Getting total contracts", $this->getTotalNumberOfContracts());
        $this->cliPrint($message);
    }

    /** @test */
    public function testItVisitsContractPage()
    {
        $this->login()
             ->visit('contract');

        return $this;
    }

    /** @test */
    public function testItShouldCreateNewMainContracts()
    {
        $this->cliPrint("Action: Making new contract");

        $inputs = $this->getInputFileContents();

        $this->login();
        foreach ($inputs as $input) {

            $this->visit('contract')
                 ->andClick('Add Contract')
                 ->visit('contract/create')
                 ->submitForm('Submit', $input);
            sleep(2);

        }

        $this->cliPrint('Completed');
    }

    /** @test */
    public function testItShouldCreateSupportingContracts()
    {
        $this->login()
             ->andvisit('contract')
             ->andClick('Young Innovations Nepal');
        $currentUrl = parse_url($this->currentPage());
        $contractId = substr($currentUrl['path'], 10);

        $this->cliPrint('Action: Making new supporting contract for contract id' . $contractId);

        $this->visit('contract/create?parent=' . $contractId)
             ->andType('Child of the contract Young Nepal', 'contract_name')
             ->attachFile('file', __DIR__ . '/../../files/pdf/laravel_1.pdf')
             ->andPress('Submit');

    }

    /** @test */
    public function testItShouldUpdateDetailsOfContract()
    {
        $this->cliPrint("Action: Seeing detail and updating the details");

        $this->testItVisitsContractPage()
             ->Click('Young Innovations Nepal')
             ->andSee('Young Innovations Nepal')
             ->andSee('<strong>Contract Name:</strong> Young Innovations Nepal')
             ->andSee('Edit')
             ->andClick('Edit')
             ->andSee('Editing')
             ->andType('Nepal Government', 'contract_identifier')
             ->andPress('Submit')
             ->andSee('Contract successfully updated');

        $this->cliPrint('Completed!!');

    }

    /** @test */
    public function testItShouldCheckIfFileIsAlreadyPresent()
    {
        $this->cliPrint("Action: Checking if file is already there");

        $this->testItVisitsContractPage()
             ->visit('contract/create')
             ->andType('Next Upload check', 'contract_name')
             ->select('country', 'NP')
             ->attachFile('file', __DIR__ . '/../../files/pdf/reupload.pdf')
             ->andPress('Submit')
             ->andSee('Whoops!');

        $this->cliPrint('Completed');
    }

    /** @test */
    public function testItShouldPublishAllContracts()
    {
        $inputs = $this->getInputFileContents();
        $this->login();

        foreach ($inputs as $input) {
            $this->visit('contract');
            sleep(5);
            $this->Click($input['contract_name'])
                 ->andPress('Publish All')
                 ->andSee('Published');
            sleep(5);
        }

    }


    /** @test */
    public function testItShouldTestContractsApi()
    {

        $this->cliPrint("Action: Checking Metadata API");
        sleep(5);
        $api    = new ApiTester();
        $keys   = ['total', 'per_page', 'from', 'results'];
        $our    = $api->get($this->apiUrl . '/contracts?country_code=np')
                      ->seeJson()
                      ->seeKeys($keys)
                      ->getJson();
        $inputs = $this->getInputFileContents();
        $input  = $inputs[0];

        $id   = $our->results[0]->contract_id;
        $ocid = $our->results[0]->open_contracting_id;

        $keys_details = [
            'contract_id',
            'created_at',
            'total_pages',
            'resource_raw',
            'country',
            'file_url',
            'concession',
            'date_retrieval',
            'language',
            'contract_note',
            'source_url',
            'type_of_contract',
            'matrix_page',
            'signature_year',
            'word_file',
            'company',
            'document_type',
            'resource',
            'contract_name',
            'project_title',
            'project_identifier',
            'file_size',
            'open_contracting_id',
            'government_entity',
            'disclosure_mode',
            'signature_date',
            'show_pdf_text',
            'deal_number',
            'amla_url',
            'contract_identifier',
            'category',
            'is_supporting_document',
            'parent_document',
            'supporting_contracts',
        ];


        $this->cliPrint("We are checking the metadata for contract id " . $id);

        $metadata = $api->get($this->apiUrl . '/contract/' . $id . '/metadata')
                        ->seeKeys($keys_details)
                        ->getJson();


        $this->assertEquals(sort($input['resource']), sort($metadata->resource_raw));
        $this->assertEquals($input['disclosure_mode'], $metadata->disclosure_mode);
        $this->assertEquals($input['type_of_contract'], $metadata->type_of_contract);

        $concession = json_decode(json_encode($metadata->concession), true);
        $this->assertEquals($input['concession'], $concession);

        $this->assertEquals($input['contract_name'], $metadata->contract_name);
        $this->assertEquals($input['source_url'], $metadata->source_url);
        $this->assertEquals(sort($input['resource']), sort($metadata->resource));
        $this->assertEquals('Nepal Government', $metadata->contract_identifier);

        $govt_entity = json_decode(json_encode($metadata->government_entity), true);
        $this->assertEquals($input['government_entity'], $govt_entity);

        $this->assertEquals($input['document_type'], $metadata->document_type);
        $this->assertEquals($input['project_identifier'], $metadata->project_identifier);

        $country = [
            "name" => "Nepal",
            "code" => "NP",
        ];

        $this->assertEquals(json_encode($country), json_encode($metadata->country));

        $this->assertEquals($input['category'], $metadata->category);
        $this->assertEquals($input['date_retrieval'], $metadata->date_retrieval);
        $this->assertEquals($input['project_title'], $metadata->project_title);

        $company_input = $input['company'];
        $this->assertEquals(json_encode(asort($company_input)), json_encode(asort($metadata->company)));

        $this->assertEquals('2015', $metadata->signature_year);
        $this->assertEquals('en', $metadata->language);
        $this->assertEquals($input['signature_date'], $metadata->signature_date);

        $this->cliPrint("Test for Metadata Completed");

        $this->cliPrint("Action: Checking the PDF TEXT");
        $pdf_text = $api->get($this->apiUrl . '/contract/' . $id . '/text')
                        ->seeJson()
                        ->matchValue('total', 1)
                        ->getJson();

        $this->assertEquals($id, $pdf_text->result[0]->contract_id);
        $this->visit($this->apiUrl . '/contract/' . $id . '/text')
             ->See('Nepal.');

        $this->cliPrint("Test for the pdf test completed");

        $this->cliPrint("Action: Indexing the annotations");

        $annotations = file_get_contents(__DIR__ . '/../../files/json/admin_annotation.json');
        $annotations = str_replace(['CONTRACT_ID'], $id, $annotations);
        $annotations = str_replace(['OCID'], $ocid, $annotations);
        $annotations = json_decode($annotations, true);

        foreach ($annotations as $annotate) {
            $save_annotations = new ApiTester();
            print_r(
                $save_annotations->post($this->elastic_search_url . '/contract/annotations', $annotate)
                                 ->matchValue('_type', 'master')->getJson()
            );
        }

        $this->cliPrint("Indexing of annotations complete");

        $this->cliPrint("Action: Checking Annotation API");

        $check_annotation = new ApiTester();
        $check_annotation->get($this->apiUrl . '/contract/' . $id . '/text')
                         ->seeJson()
                         ->matchValue('total', 1)
                         ->getJson();

        $this->cliPrint('Completed');

    }

    /** @test */
    public function testItShouldCheckSubSiteHomePage()
    {
        $this->cliPrint("Checking the sub site home page");

        $this->visit($this->sub_site_url . '/')
             ->andSee('2</span> Countries')
             ->andSee('3</span> Resources')
             ->andClick('View all countries')
             ->seePageIs($this->sub_site_url . '/countries')
             ->andSee('Nepal')
             ->andSee('Bahamas')
             ->visit($this->sub_site_url . '/')
             ->andClick('View all resources')
             ->seePageIs($this->sub_site_url . '/resources')
             ->andSee('6PGM+Au')
             ->andSee('Boron')
             ->andSee('Coal');

        $this->cliPrint('Completed');

    }

    /** @test */
    public function testItShouldVisitContractsPageOnSubSite()
    {
        $this->cliPrint("Checking the subsite and seeing the contracts");

        $this->visit($this->sub_site_url . '/contracts')
             ->andSee('2</span>  Contracts')
             ->andSee('Young Innovations Nepal')
             ->andSee('Second Innovations Nepal');

        $this->cliPrint('Completed.');

    }

    /** @test */
    public function testItShouldCheckSummaryOfContractsOnSubSite()
    {
        $this->cliPrint('Action: Check the summary of contract');
        $api = new ApiTester();

        $our  = $api->get($this->apiUrl . '/contracts?country_code=np')->getJson();
        $ocid = $our->results[0]->open_contracting_id;

        $this->visit($this->sub_site_url . '/contract/' . $ocid)
             ->andSee('Young Innovations Nepal')
             ->andSee($ocid)
             ->andSee('Country//Pays');

        $this->cliPrint('Completed');
    }

    /** @test */
    public function testItShouldCheckSearchOnSubSite()
    {
        $this->cliPrint("searching for the contracts where resource=coal");

        $this->visit($this->sub_site_url . '/search?q=&resource%5B%5D=Coal')
             ->andSee('2</span>  Contracts')
             ->visit($this->sub_site_url . '/search?q=young&country%5B%5D=np&resource%5B%5D=Coal&contract_type%5B%5D=Joint+Venture+Agreement')
             ->andSee('Search results for  <span>young</span>')
             ->andSee('1')
             ->andSee('Young Innovations Nepal')
             ->andSee('Download search results as csv');

        $this->cliPrint('Completed');

    }

    /** @test */
    public function testItShouldDeleteAllContracts()
    {
        $this->cliPrint("Action: deleting the contract");

        $this->login();
        $inputs = $this->getInputFileContents();

        foreach ($inputs as $input) {
            $this->visit('contract')
                 ->andClick($input['contract_name'])
                 ->andPress('Delete')
                 ->andSee('Contract successfully deleted.');

            sleep(5);
        }

        sleep(5);

        $this->getTotalNumberOfContracts();

        $action_1 = "Action: Checking the API after deleting";
        $this->cliPrint($action_1);

        $check_api = new ApiTester();
        $check_api->get($this->apiUrl . '/contracts')
                  ->seeJson()
                  ->matchValue('total', $this->getTotalNumberOfContracts());

        $this->cliPrint('Completed');
    }


    /** @test */
    public function testItShouldAddNewUser()
    {

        $this->cliPrint("Action: Adding new user with role super Admin");

        $this->testItVisitsContractPage()
             ->andClick('Users')
             ->andClick('Add User')
             ->andType('Bijaya Prasad kuikel', 'name')
             ->andType('sadhakbj@gmail.com', 'email')
             ->andType('krishna', 'password')
             ->andType('krishna', 'password_confirmation')
             ->andSelect('role', 'superadmin')
             ->andPress('Submit')
             ->andSee('User successfully created');

        $this->cliPrint('Completed');
    }

    /** @test */
    public function testItShouldUpdateUserDetails()
    {

        $action = "Action: Updating the details of added user";
        $this->cliPrint($action);

        $this->testItVisitsContractPage()
             ->andClick('Users')
             ->andClick('user_edit_0')
             ->andType('Updated Name', 'name')
             ->andType('YIPL', 'organization')
             ->andPress('Submit')
             ->andSee('User successfully updated');

        $this->cliPrint('Completed');
    }

    /** @test */
    public function testItShouldCheckIfEmailExists()
    {
        $action = "Action: Checking the email is already exists for new user";
        $this->cliPrint($action);

        $this->testItVisitsContractPage()
             ->andClick('Users')
             ->andClick('Add User')
             ->andType('Try New Name', 'name')
             ->andType('sadhakbj@gmail.com', 'email')
             ->andType('krishna', 'password')
             ->andType('krishna', 'password_confirmation')
             ->andSelect('role', 'superadmin')
             ->andPress('Submit')
             ->andSee('The email has already been taken');

        $this->cliPrint('The email already exists');
    }


    /** @test */
    public function testItShouldDeleteUser()
    {
        echo PHP_EOL;
        echo "Action: Deleting the recently added new user";
        echo PHP_EOL;

        $this->testItVisitsContractPage()
             ->andClick('Users')
             ->andClick('user_delete_0')
             ->andSee('User successfully deleted');

        $this->cliPrint('Completed');
    }

    /**
     * Test case for logging in the user.
     * @return $this
     */
    public function login()
    {
        $this->visit('/')
             ->andType('admin@nrgi.app', 'email')
             ->andType('admin123', 'password')
             ->press('Login');

        return $this;
    }

    /**
     * @param $message
     */
    public function cliPrint($message)
    {
        echo PHP_EOL;
        echo $message;
        echo PHP_EOL;
    }


}
