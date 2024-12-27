<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ClearFaceData extends Command
{
    protected $signature = 'face:clear';
    protected $description = 'Clear face recognition dataset';

    public function handle()
    {
        $this->info('Checking Python Face Recognition Server...');

        try {
            // Check if Python server is running
            $checkResponse = Http::timeout(5)->get('http://127.0.0.1:5000');
            
            if (!$checkResponse->successful()) {
                $this->error('Python Face Recognition Server is not running!');
                $this->error('Please start the server with: python Detection.py');
                return 1;
            }

            $this->info('Python server is running. Clearing face dataset...');

            // Clear face data
            $response = Http::post('http://127.0.0.1:5000/clear_face_data');
            
            if ($response->successful()) {
                $this->info('Face dataset cleared successfully!');
                return 0;
            } else {
                $this->error('Failed to clear face dataset!');
                $this->error($response->json()['error'] ?? 'Unknown error');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Error connecting to Python server:');
            $this->error($e->getMessage());
            $this->warn('Face dataset might not be cleared properly.');
            return 1;
        }
    }
} 