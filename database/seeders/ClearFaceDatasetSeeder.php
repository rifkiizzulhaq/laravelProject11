<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ClearFaceDatasetSeeder extends Seeder
{
    public function run()
    {
        Artisan::call('face:clear');
    }
} 