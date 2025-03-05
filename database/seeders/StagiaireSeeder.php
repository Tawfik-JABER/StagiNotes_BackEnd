<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StagiaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stagiairesData = [];

        $groups = [
            ['group' => '101', 'fill_id' => 3, 'niveau' => 'Technicien Spécialisé'],
            ['group' => '102', 'fill_id' => 5, 'niveau' => 'Technicien Spécialisé'],
            ['group' => '103', 'fill_id' => 4, 'niveau' => 'Technicien'],
            ['group' => '104', 'fill_id' => 6, 'niveau' => 'Technicien'],
        ];

        foreach ($groups as $groupData) {
            for ($i = 1; $i <= 30; $i++) {
                $stagiairesData[] = [
                    'cin' => Str::upper(Str::random(8)),
                    'nom' => 'Nom' . $i,
                    'prenom' => 'Prenom' . $i,
                    'email' => Str::lower(Str::random(5)) . $i . '@example.com',
                    'password' => Hash::make('password123'),
                    'fill_id' => $groupData['fill_id'],
                    'numero' => rand(1000, 9999),
                    'cef' => Str::random(10),
                    'group' => $groupData['group'],
                    'annee' => '2024',
                    'niveau' => $groupData['niveau'],
                    'sexe' => ['Male', 'Female'][rand(0, 1)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // DB::table('stagiaires')->insert($stagiairesData);

        // stagiaire_id from 3 to 32 ,they studied module_id from 4 to 16
        // Insert stagiaire_modules data
        $stagiaireModulesData = [];
        for ($stagiaire_id = 93; $stagiaire_id <= 122; $stagiaire_id++) {
            for ($module_id = 38; $module_id <= 43; $module_id++) {
                $stagiaireModulesData[] = [
                    'stagiaire_id' => $stagiaire_id,
                    'module_id' => $module_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('stagiaire_modules')->insert($stagiaireModulesData);
    }
}
