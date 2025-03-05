<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class TutteurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tutteurs')->insert([
            'cin' => 'AB123456',
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password123'),
            'sexe' => 'male',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
