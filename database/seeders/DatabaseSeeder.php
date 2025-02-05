<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rols;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /**********************************
         * Crea el Primer rol de usuario
         **********************************/
        Rols::factory()->create([
            'name' => 'Administrador',

       ]);
        /**********************************
         * Crea el primer usuario
         ***********************************/
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'rol_id' => 1,
        ]);


    }
}
