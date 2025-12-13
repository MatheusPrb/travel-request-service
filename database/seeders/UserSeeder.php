<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Pedro Oliveira',
                'email' => 'pedro.oliveira@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        User::factory(10)->create();

        $this->command->info('Usuários criados com sucesso!');
        $this->command->info('Usuários de teste:');
        $this->command->info('- admin@example.com / password');
        $this->command->info('- joao.silva@example.com / password');
        $this->command->info('- maria.santos@example.com / password');
        $this->command->info('- pedro.oliveira@example.com / password');
        $this->command->info('+ 10 usuários aleatórios');
    }
}

