<?php
use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\Seeder;

/**
 * Class UserTableSeeder
 */
class UpdateMetadata extends Seeder
{
    /**
     * Seed Admin User with Roles
     */
    public function run()
    {
        $contracts = Contract::all();
        foreach ($contracts as $contract) {
            $metadata          = $contract->metadata;
            $licenseName       = $metadata->license_name;
            $licenseIdentifier = $metadata->license_identifier;
            unset($metadata->license_name);
            unset($metadata->license_identifier);
            $metadata->concession =
                [
                    [
                        "license_name"       => $licenseName,
                        "license_identifier" => $licenseIdentifier
                    ]
                ];

            $contract->metadata = $metadata;
            $contract->save();
        }
    }
}
