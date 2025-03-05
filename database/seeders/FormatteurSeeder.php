<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FormatteurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('formatteurs')->insert([
            'cin' => 'MN123456',
            'nom' => 'Eltayeb',
            'prenom' => 'Hassan',
            'email' => 'eltayebhassan@example.com',
            'password' => Hash::make('password123'),
            'sexe' => 'male',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
