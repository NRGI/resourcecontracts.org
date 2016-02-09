<?php
use Tests\Api\ApiTester;
use Laracasts\Integrated\Extensions\Goutte as IntegrationTest;

/**
 * Class AdminTest
 */
class AdminTest extends IntegrationTest
{
    public $baseUrl = 'http://resourcecontracts-demo1.elasticbeanstalk.com';
    public $apiUrl = 'http://rc-elasticsearch-demo.elasticbeanstalk.com/api';
    public $elastic_search_url = 'http://rc-elasticsearch-demo.elasticbeanstalk.com/index';
    public $sub_site_url = 'http://rc-site-demo.elasticbeanstalk.com/olc/public';
    public $api;

    /**
     * Returns total number of Contracts Present in our Database
     * @return int
     */
    public function getTotalNumberOfContracts()
    {
        print_r($_ENV);
        $this->api = new ApiTester();

        return $this->api->get($this->apiUrl . '/contracts')->getJson()->total;

    }


    /**
     * Returns the decoded input json.
     * @return Array
     */
    public function getInputFileContents()
    {
        return json_decode(file_get_contents(__DIR__ . '/../files/input/input.json'), true);
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
        foreach ($inputs as $key => $input) {

            $this->visit('contract/create');
            sleep(10);
            $this->submitForm('Submit', $input)->andSee('Successfully');

            sleep(10);

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
             ->attachFile('file', __DIR__ . '/../files/pdf/laravel_1.pdf')
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
             ->attachFile('file', __DIR__ . '/../files/pdf/reupload.pdf')
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

        $id   = $our->results[0]->id;
        $ocid = $our->results[0]->open_contracting_id;


        $keys_details = [
            'id',
            'open_contracting_id',
            'name',
            'identifier',
            'number_of_pages',
            'language',
            'country',
            'resource',
            'government_entity',
            'contract_type',
            'date_signed',
            'year_signed',
            'type',
            'participation',
            'project',
            'concession',
            'source_url',
            'amla_url',
            'publisher_type',
            'retrieved_at',
            'created_at',
            'note',
            'is_associated_document',
            'deal_number',
            'matrix_page',
            'is_ocr_reviewed',
            'is_pages_missing',
            'is_annexes_missing',
            'file',
            'parent',
            'associated'
        ];


        $this->cliPrint("We are checking the metadata for contract id " . $id);
        $metadata = $api->get($this->apiUrl . '/contract/' . $id . '/metadata')
                        ->seeKeys($keys_details)
                        ->getJson();

        $this->assertEquals($input['disclosure_mode'], $metadata->publisher_type);
        $this->assertEquals(sort($input['type_of_contract']), sort($metadata->contract_type));

        $concession = json_decode(json_encode($metadata->concession), true);

        $this->assertEquals($input['concession'][0]['license_name'], $concession[0]['name']);
        $this->assertEquals($input['concession'][0]['license_identifier'], $concession[0]['identifier']);

        $this->assertEquals($input['contract_name'], $metadata->name);
        $this->assertEquals($input['source_url'], $metadata->source_url);
        $this->assertEquals(sort($input['resource']), sort($metadata->resource));
        $this->assertEquals('Nepal Government', $metadata->identifier);

        $this->assertEquals($input['document_type'], $metadata->type);
        $this->assertEquals($input['project_identifier'], $metadata->project->identifier);
        $this->assertEquals($input['project_title'], $metadata->project->name);

        $country = [
            "code" => "NP",
            "name" => "Nepal"
        ];

        $this->assertEquals(json_encode($country), json_encode($metadata->country));

        $this->assertEquals($input['date_retrieval'], $metadata->retrieved_at);

        $company_input = $input['company'][0];
        $company       = $metadata->participation[0]->company;

        $this->assertEquals($company_input['name'], $company->name);
        $this->assertEquals($company_input['company_address'], $company->address);
        $this->assertEquals($company_input['company_founding_date'], $company->founding_date);
        $this->assertEquals($company_input['parent_company'], $company->corporate_grouping);
        $this->assertEquals($company_input['open_corporate_id'], $company->opencorporates_url);
        $this->assertEquals($company_input['company_number'], $company->identifier->id);
        $this->assertEquals($company_input['registration_agency'], $company->identifier->creator->name);
        $this->assertEquals($company_input['jurisdiction_of_incorporation'], $company->identifier->creator->spatial);
        $this->assertEquals($company_input['participation_share'], $metadata->participation[0]->share);


        $this->assertEquals('2015', $metadata->year_signed);
        $this->assertEquals('en', $metadata->language);
        $this->assertEquals($input['signature_date'], $metadata->date_signed);

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

        $annotations = file_get_contents(__DIR__ . '/../files/json/admin_annotation.json');
        $annotations = str_replace(['CONTRACT_ID'], $id, $annotations);
        $annotations = str_replace(['OCID'], $ocid, $annotations);
        $annotations = json_decode($annotations, true);

        foreach ($annotations as $annotate) {
            $save_annotations = new ApiTester();
            $save_annotations->post($this->elastic_search_url . '/contract/annotations', $annotate)
                             ->matchValue('_type', 'master')->getJson();
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

        $this->visit($this->sub_site_url . '/countries')
             ->andSee('Nepal')
             ->andSee('Afghanistan')
             ->visit($this->sub_site_url . '/resources')
             ->andSee('Gold')
             ->andSee('Coal');

        $this->cliPrint('Completed');

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
             ->visit($this->sub_site_url . '/search?q=young&country%5B%5D=np&resource%5B%5D=Coal&contract_type%5B%5D=Joint+Venture+Agreement')
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

        $this->visit('contract')
             ->andClick('Child of the contract Young Nepal')
             ->andPress('Delete')
             ->andSee('Contract successfully deleted.');

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
             ->andType('thisisnewpassword', 'password')
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
