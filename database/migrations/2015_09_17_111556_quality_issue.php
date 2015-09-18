<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QualityIssue extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "

	CREATE OR REPLACE FUNCTION get_quality_issue()
  	RETURNS quality AS
	\$BODY\$BEGIN
	DECLARE
		issue quality;
		company json;
		metadata_data json;
		company_no integer;
		concession json;
		concession_no integer;
		government json;
		government_no integer;

	BEGIN

		company_no=0;
		concession_no=0;
		government_no=0;
		FOR metadata_data in select metadata from contracts
		LOOP
			company=metadata_data->'company';
			concession=metadata_data->'concession';
			government=metadata_data->'government_entity';

			IF company->0->>'name'<>'' THEN
				company_no=company_no+1;
			END IF;
			IF concession->0->>'license_name'<>'' THEN
				concession_no=concession_no+1;
			END IF;
			IF government->0->>'entity'<>'' THEN
				government_no=government_no+1;
			END IF;


		END LOOP;
		issue.company_no=company_no;
		issue.concession_no=concession_no;
		issue.government_no=government_no;

		RETURN issue;

	END;
	END;\$BODY\$
  	LANGUAGE plpgsql VOLATILE;
			"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		DB::statement("DROP function get_quality_issue()");
    }

}
