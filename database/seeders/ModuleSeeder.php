<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleTeacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::role('Teacher')
            ->inRandomOrder()
            ->limit(3)
            ->pluck('id')
            ->toArray();


        $teacher = User::where('email', 'teacher@lms.com')->first();
        Module::factory()
            ->count(10)
            ->create()->each(function (Module $module) use ($teachers, $teacher) {
                $module->addMediaFromUrl(asset('images/cover.png'))
                    ->toMediaCollection('cover');

                ModuleTeacher::updateOrCreate([
                    'module_id' => $module->id,
                    'teacher_id' => $teacher->id,
                ]);

                foreach ($teachers as $value) {
                    ModuleTeacher::updateOrCreate([
                        'module_id' => $module->id,
                        'teacher_id' => $value,
                    ]);
                }
            });
    }
}
