<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckPythonServer extends Command
{
    protected $signature = 'python:check';
    protected $description = 'Check if Python face recognition server is running';

    public function handle()
    {
        try {
            $response = Http::timeout(5)->get('http://127.0.0.1:5000');
            
            if ($response->successful()) {
                $this->info('Python face recognition server is running.');
                return true;
            }
        } catch (\Exception $e) {
            $this->error('Python face recognition server is not running!');
            $this->error('Please start the server by running: python Detection.py');
            $this->error('Error: ' . $e->getMessage());
            return false;
        }
    }
} 