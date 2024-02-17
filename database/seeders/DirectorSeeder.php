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
     * Run the database seeds.
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
            'cin' => 'FB285261' ,
            'nom' => 'Jaber',
            'prenom' => 'Tawfik',
            'email' => 'tawfik@edu-ofppt.ma',
            'password' => Hash::make('password'),
            'sexe' =>'Male',
            ];

            Director::create($data);
}
}

