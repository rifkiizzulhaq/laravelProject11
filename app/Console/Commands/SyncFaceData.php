<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SyncFaceData extends Command
{
    protected $signature = 'face:sync';
    protected $description = 'Synchronize face recognition data with database';

    public function handle()
    {
        try {
            // Clear face data di Python server
            $response = Http::post('http://127.0.0.1:5000/clear_face_data');
            
            if (!$response->successful()) {
                throw new \Exception('Failed to clear face data from Python server');
            }

            // Reset face_id di database
            DB::table('users')->update(['face_id' => null]);

            $this->info('Face recognition data synchronized successfully');
            
        } catch (\Exception $e) {
            $this->error('Error syncing face data: ' . $e->getMessage());
        }
    }
} 