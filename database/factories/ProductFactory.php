<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'salesforce_id' => '01t'.$this->faker->unique()->regexify('[A-Za-z0-9]{12}'),
            'name' => $name,
            'description' => $this->faker->sentence(),
            'slug' => Str::slug($name),
            'product_line' => $this->faker->randomElement(array_keys(Product::PRODUCT_LINES)),
            'code' => strtoupper($this->faker->lexify('???')),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'status' => 'published',
        ];
    }
}
