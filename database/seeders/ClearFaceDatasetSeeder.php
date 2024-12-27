<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class ClearFaceDatasetSeeder extends Seeder
{
    public function run()
    {
        try {
            // Check if Python server is running
            $response = Http::timeout(5)->get('http://127.0.0.1:5000');
            
            if ($response->successful() && $response->json('status') === 'running') {
                // Clear face dataset
                $clearResponse = Http::post('http://127.0.0.1:5000/clear_face_data');
                if (!$clearResponse->successful()) {
                    throw new \Exception('Failed to clear face dataset');
                }
            } else {
                throw new \Exception('Server not responding correctly');
            }

        } catch (\Exception $e) {
            $this->command->line("\nChecking Python Face Recognition Server...");
            $this->command->error("Python Face Recognition Server is not running!");
            $this->command->line("Please start the server with: python Detection.py\n");
            exit(1);
        }
    }
} 