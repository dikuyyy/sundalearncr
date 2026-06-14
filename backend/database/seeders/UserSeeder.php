<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole  = Role::where('name', 'admin')->first();
        $guruRole   = Role::where('name', 'guru')->first();
        $siswaRole  = Role::where('name', 'siswa')->first();

        // Admin
        User::firstOrCreate(['email' => 'admin@sundalearncr.local'], [
            'role_id'  => $adminRole->id,
            'name'     => 'Administrator SundaLearn',
            'password' => 'password123',
            'is_active' => true,
        ]);

        // Guru
        $gurus = [
            ['name' => 'Drs. Ahmad Sunda', 'email' => 'ahmad@sundalearncr.local', 'nip' => '198501012010011001'],
            ['name' => 'Ibu Siti Rahayu',  'email' => 'siti@sundalearncr.local',  'nip' => '199002022015012001'],
        ];

        foreach ($gurus as $guru) {
            User::firstOrCreate(['email' => $guru['email']], [
                ...$guru,
                'role_id'  => $guruRole->id,
                'password' => 'password123',
                'gender'   => 'L',
                'is_active' => true,
            ]);
        }

        // Siswa
        $siswas = [
            ['name' => 'Budi Santoso',    'email' => 'budi@sundalearncr.local',    'nisn' => '1234567890'],
            ['name' => 'Sari Dewi',       'email' => 'sari@sundalearncr.local',    'nisn' => '1234567891'],
            ['name' => 'Rizki Pratama',   'email' => 'rizki@sundalearncr.local',   'nisn' => '1234567892'],
            ['name' => 'Nurul Hidayah',   'email' => 'nurul@sundalearncr.local',   'nisn' => '1234567893'],
            ['name' => 'Dian Purnama',    'email' => 'dian@sundalearncr.local',    'nisn' => '1234567894'],
        ];

        foreach ($siswas as $siswa) {
            User::firstOrCreate(['email' => $siswa['email']], [
                ...$siswa,
                'role_id'  => $siswaRole->id,
                'password' => 'password123',
                'is_active' => true,
            ]);
        }
    }
}
