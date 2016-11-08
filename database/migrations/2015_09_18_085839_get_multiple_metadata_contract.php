<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetMultipleMetadataContract extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("
			create or replace function getMultipleMetadataContract(word text)
		returns int[][] as $$
		DECLARE
   			present int[][];
   			j RECORD;
   			k integer;
		BEGIN
			k=0;
     		FOR j IN select contracts.\"id\",contracts.\"metadata\" from contracts LOOP
     	IF word='company' THEN
			IF j.metadata->'company'->0->>'name'<> '' THEN

				present[k]:=j.id;

			END IF;
     	END IF;
     IF word='concession' THEN
	IF j.metadata->'concession'->0->>'license_name'<> '' THEN

		present[k]:=j.id;
	END IF;
     END IF;
     IF word='government' THEN
	IF j.metadata->'government_entity'->0->>'entity'<> '' THEN

		present[k]:=j.id;
	END IF;
     END IF;
	k=k+1;
     END LOOP;

     RETURN present;

END
$$ language plpgsql;
		");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("DROP function getMultipleMetadataContract(word text)");
	}

}
