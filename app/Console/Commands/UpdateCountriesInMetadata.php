<?php

namespace App\Console\Commands;
use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateCountriesInMetadata extends Command
{
    protected $name = 'nrgi:contracts-metadata';
    protected $description = 'Update metadata column in contracts table';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Define the chunk size
        $chunkSize = 100; // You can adjust this size based on your server's capacity

        // Process each chunk of contracts
        Contract::where('id', 1881)->chunk($chunkSize, function ($contracts) {
            foreach ($contracts as $contract) {
                // Check and update metadata only if 'country' field exists
                if (isset($contract->metadata['country'])) {
                    // Change 'country' to 'countries' and make it an array
                    $contract->metadata['countries'] = [$contract->metadata['country']];
                    
                    // Remove the old 'country' field
                    // unset($contract->metadata['country']);

                    // Save the changes
                    $contract->save();
                }
            }

            // Optional: Free up memory
            unset($contracts);
        });

        Log::info('Metadata for contracts updated successfully.');
    }
}
