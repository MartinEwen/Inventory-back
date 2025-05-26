<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (\App\Models\Category::count() === 0) {
            \App\Models\Category::factory(5)->create();
        }

        \App\Models\Product::factory(150)->create();
    }
}
