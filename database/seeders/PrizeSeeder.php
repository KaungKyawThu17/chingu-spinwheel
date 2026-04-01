<?php

namespace Database\Seeders;

use App\Models\Prize;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PrizeSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('prizes')) {
            return;
        }

        $defaults = [
            ['name' => 'Sticker Pack', 'weight' => 60, 'color' => '#55B9E6', 'order' => 1, 'is_active' => true],
            ['name' => 'Fans', 'weight' => 30, 'color' => '#F7B7C4', 'order' => 2, 'is_active' => true],
            ['name' => 'Charm', 'weight' => 10, 'color' => '#52B848', 'order' => 3, 'is_active' => true],
        ];

        foreach ($defaults as $prize) {
            Prize::updateOrCreate(
                ['name' => $prize['name']],
                $prize,
            );
        }
    }
}
