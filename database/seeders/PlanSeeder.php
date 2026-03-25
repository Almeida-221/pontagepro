<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPlans = [
            [
                'slug'        => 'gratuit',
                'name'        => 'Gratuit',
                'description' => 'Idéal pour les petites structures qui débutent.',
                'max_workers' => 5,
                'price'       => 0,
                'is_active'   => true,
            ],
            [
                'slug'        => 'plan-s',
                'name'        => 'Plan S',
                'description' => 'Pour les petites structures en croissance.',
                'max_workers' => 15,
                'price'       => 15000,
                'is_active'   => true,
            ],
            [
                'slug'        => 'plan-m',
                'name'        => 'Plan M',
                'description' => 'Le choix populaire pour les structures moyennes.',
                'max_workers' => 25,
                'price'       => 25000,
                'is_active'   => true,
            ],
            [
                'slug'        => 'plan-l',
                'name'        => 'Plan L',
                'description' => 'Pour les grandes structures.',
                'max_workers' => 50,
                'price'       => 50000,
                'is_active'   => true,
            ],
            [
                'slug'        => 'plan-xl',
                'name'        => 'Plan XL',
                'description' => 'Utilisateurs illimités. Pour les très grandes structures.',
                'max_workers' => -1,
                'price'       => 100000,
                'is_active'   => true,
            ],
        ];

        $modules = Module::all();

        foreach ($modules as $module) {
            foreach ($defaultPlans as $planData) {
                Plan::updateOrCreate(
                    ['module_id' => $module->id, 'slug' => $planData['slug']],
                    array_merge($planData, ['module_id' => $module->id])
                );
            }
        }
    }
}
