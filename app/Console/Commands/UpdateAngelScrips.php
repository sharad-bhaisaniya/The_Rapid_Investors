<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAngelScrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrips:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update Angel One Scrip Master from JSON API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // The URL provided
        $url = 'https://margincalculator.angelbroking.com/OpenAPI_File/files/OpenAPIScripMaster.json';
        
        $this->info("Starting download from: $url");

        try {
            // 1. Fetch Data (Timeout set to 120s for large files)
            $response = Http::timeout(120)->get($url);

            if ($response->failed()) {
                $this->error('Failed to download JSON.');
                Log::error('Angel Scrip Sync Failed: ' . $response->body());
                return 1;
            }

            $data = $response->json();

            if (!is_array($data)) {
                $this->error('Invalid JSON format.');
                return 1;
            }

            $count = count($data);
            $this->info("Fetched $count records. Processing...");

            // 2. Process in Chunks
            // Processing 1000 records at a time to manage memory
            $chunks = array_chunk($data, 1000);
            $bar = $this->output->createProgressBar(count($chunks));
            $bar->start();

            foreach ($chunks as $chunk) {
                $upsertData = [];
                $now = now();

                foreach ($chunk as $row) {
                    // We map the JSON keys directly to your DB columns here
                    $upsertData[] = [
                        'token'          => $row['token'],           // "99926000"
                        'symbol'         => $row['symbol'],          // "Nifty 50"
                        'name'           => $row['name'],            // "NIFTY"
                        'expiry'         => $row['expiry'],          // "" (Empty string)
                        'strike'         => $row['strike'],          // "0.000000"
                        'lotsize'        => $row['lotsize'],         // "1"
                        'instrumenttype' => $row['instrumenttype'],  // "AMXIDX"
                        'exch_seg'       => $row['exch_seg'],        // "NSE"
                        'tick_size'      => $row['tick_size'],       // "0.000000"
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }

                // 3. Upsert into Database
                // We use 'token' AND 'exch_seg' as the unique identifier.
                // If a record with this token+exch_seg exists, we update the other fields.
                DB::table('angel_scrips')->upsert(
                    $upsertData, 
                    ['token', 'exch_seg'], // Unique Composite Key
                    [
                        'symbol', 
                        'name', 
                        'expiry', 
                        'strike', 
                        'lotsize', 
                        'instrumenttype', 
                        'tick_size', 
                        'updated_at'
                    ]
                );

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Angel Scrips updated successfully!');

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Angel Scrip Sync Exception: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}