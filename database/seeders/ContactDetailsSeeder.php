<?php

namespace Database\Seeders;

use App\Models\ContactDetails;
use Illuminate\Database\Seeder;

class ContactDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ContactDetails::factory()->count(30)->create();
    }
}