<?php

namespace Database\Seeders;

use App\Models\Director;
use App\Models\Formatteur;
use App\Models\Stagiaire;
use App\Models\Tutteur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DirectorSeeder extends Seeder
{
    /**
     * Run the databasees seeeeds.
     */
    public function run(): void
    {
        // $formatteurs = [];

        // for ($i = 0; $i < 10; $i++) {
        //     $formatteurs[] = [
        //         'cin' => 'FB-' . ($i + 1),
        //         'nom' => 'Prenom ' . ($i + 1),
        //         'prenom' => 'Prenom ' . ($i + 1),
        //         'email' => 'formatteur' . ($i + 1) . '@edu-ofppt.ma',
        //         'password' => Hash::make('password'),
        //         'sexe' => ($i % 2 == 0) ? 'Male' : 'Female',
        //     ];
        // }

        // // Formatteur::create($data);
        // Stagiaire::create([
        //     'cin' => '23423d',
        //     'nom' => 'tawfik',
        //     'prenom' => 'jaber',
        //     'email' => 'tawfik@edu-ofppt.ma',
        //     'password' => Hash::make('password'),
        //     'fill_id'=>1,
        //     'numero'=>57,
        //     'cef'=>"123478765",
        //     'group'=>"101",
        //     'annee'=>1,
        //     'niveau'=>"ts",
        //     'sexe'=>"male",
        //     ]);

            // Stagiaire::create($data);
        $data = [
            'cin' => '23423d',
            'nom' => 'tawfik',
            'prenom' => 'jaber',
            'email' => 'tawfik@edu-ofppt.ma',
            'password' => Hash::make('password'),
            'fill_id'=>1,
            'numero'=>57,
            'cef'=>"123478765",
            'group'=>"101",
            'annee'=>1,
            'niveau'=>"ts",
            'sexe'=>"male",
            ];

        // Stagiaire::create($data);

        DB::table('directors')->insert([
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

