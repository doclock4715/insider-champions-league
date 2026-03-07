<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Fixture;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Yabancı anahtar kontrollerini geçici olarak kapat (Hata almamak için)
        Schema::disableForeignKeyConstraints();

        // 1. Önce eski verileri temizle
        Fixture::truncate();
        Team::truncate();

        // Kontrolleri tekrar aç
        Schema::enableForeignKeyConstraints();

        // 2. Takımları oluştur
        $teams = [
            Team::create(['name' => 'Manchester City', 'attack_strength' => 1.35, 'defense_strength' => 0.75, 'form' => 0]),
            Team::create(['name' => 'Arsenal', 'attack_strength' => 1.30, 'defense_strength' => 0.80, 'form' => 0]),
            Team::create(['name' => 'Liverpool', 'attack_strength' => 1.10, 'defense_strength' => 0.75, 'form' => 0]),
            Team::create(['name' => 'Aston Villa', 'attack_strength' => 1.10, 'defense_strength' => 0.90, 'form' => 0]),
        ];

        // 3. 6 Haftalık Fikstürü Oluştur
        $schedule = [
            1 => [[0, 1], [2, 3]],
            2 => [[0, 2], [1, 3]],
            3 => [[0, 3], [1, 2]],
            4 => [[1, 0], [3, 2]],
            5 => [[2, 0], [3, 1]],
            6 => [[3, 0], [2, 1]],
        ];

        foreach ($schedule as $week => $matches) {
            foreach ($matches as $match) {
                Fixture::create([
                    'week' => $week,
                    'home_team_id' => $teams[$match[0]]->id,
                    'away_team_id' => $teams[$match[1]]->id,
                    'is_played' => false
                ]);
            }
        }
    }
}
