<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',  'display_name' => 'Administrator',  'description' => 'Pengelola sistem SundaLearn'],
            ['name' => 'guru',   'display_name' => 'Guru',           'description' => 'Pengajar yang mengelola materi dan soal'],
            ['name' => 'siswa',  'display_name' => 'Siswa',          'description' => 'Peserta didik yang mengikuti pembelajaran'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
