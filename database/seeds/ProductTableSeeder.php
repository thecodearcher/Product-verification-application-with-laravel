<?php

use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->insert([
            [
                'name' => "Product 1",
                'is_original' => false,
                'product_id' => "prod_1",
            ],
            [
                'name' => "Product 2",
                'is_original' => true,
                'product_id' => "prod_2",
            ],
            [
                'name' => "Product 3",
                'is_original' => false,
                'product_id' => "prod_3",
            ],
            [
                'name' => "Product 4",
                'is_original' => true,
                'product_id' => "prod_4",
            ],
        ]);

    }
}
