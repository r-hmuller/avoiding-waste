<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Laticínios', 'Carne', 'Pães e massas', 'Frios', 'Vegetais'];
        foreach ($categories as $category) {
            $c = new Category(['name' => $category]);
            $c->save();
        }
    }
}
