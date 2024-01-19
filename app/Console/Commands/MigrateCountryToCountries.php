<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Nrgi\Entities\Contract\Contract; // Ensure this is the correct namespace for your Contract model

class MigrateCountryToCountries extends Command
{
    protected $signature = 'migrate:country-to-countries';
    protected $description = 'Migrates JSON metadata from country to countries field.';

    public function handle()
    {
        $this->info('Starting the migration...');
    
        $batchSize = 100;
    
        Contract::whereNotNull('metadata->country')
            ->chunkById($batchSize, function ($contracts) {
                foreach ($contracts as $contract) {
                    try {
                        $metadata = $contract->metadata;
                        if (isset($metadata->country)) {
                            $metadata->countries = [$metadata->country];
                            unset($metadata->country);
    
                            $contract->metadata = $metadata;
                            if ($contract->isDirty('metadata')) {
                                $contract->save();
                                $this->info("Migrated updated contract: {$contract->id}");
                            } else {
                                $this->info("No changes needed for contract: {$contract->id}");
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("Error migrating contract: {$contract->id}. Error: " . $e->getMessage());
                    }
                }
            });
    
        $this->info('Migration completed!');
    }
}
