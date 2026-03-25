<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name'        => 'Pointage des Ouvriers',
                'slug'        => 'pointage-ouvriers',
                'description' => 'Gérez la présence et les salaires de vos ouvriers journaliers sur chantier.',
                'icon'        => '🏗️',
                'color'       => 'blue',
                'is_active'   => true,
            ],
            [
                'name'        => 'Sécurité Privée',
                'slug'        => 'securite-privee',
                'description' => 'Gérez les plannings et rondes de vos agents de sécurité privée.',
                'icon'        => '🛡️',
                'color'       => 'red',
                'is_active'   => true,
            ],
            [
                'name'        => 'Pointage des Enseignants',
                'slug'        => 'pointage-enseignants',
                'description' => 'Suivez la présence et les heures de cours de vos enseignants.',
                'icon'        => '🎓',
                'color'       => 'green',
                'is_active'   => true,
            ],
        ];

        foreach ($modules as $data) {
            Module::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // Attach all existing plans (without a module) to "pointage-ouvriers"
        $ouvriersModule = Module::where('slug', 'pointage-ouvriers')->first();
        if ($ouvriersModule) {
            Plan::whereNull('module_id')->update(['module_id' => $ouvriersModule->id]);
        }
    }
}
