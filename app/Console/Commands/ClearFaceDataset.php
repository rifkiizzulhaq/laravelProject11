<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearFaceDataset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'face:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear face recognition dataset and trainer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Path ke direktori dataset dan trainer
            $datasetPath = base_path('../faceDetection/dataset');
            $trainerPath = base_path('../faceDetection/trainer');

            // Hapus semua file di folder dataset
            if (File::exists($datasetPath)) {
                File::deleteDirectory($datasetPath);
                File::makeDirectory($datasetPath);
                $this->info('Dataset directory cleared successfully.');
            }

            // Hapus file trainer.yml
            if (File::exists($trainerPath)) {
                File::deleteDirectory($trainerPath);
                File::makeDirectory($trainerPath);
                $this->info('Trainer directory cleared successfully.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error clearing face data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
