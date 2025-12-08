<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infrastructure\Persistence\Eloquent\Models\Equipment;
use Illuminate\Support\Str;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        $year = date('Y');
        $sequence = Equipment::where('asset_tag', 'like', "ASSET-{$year}-%")->count() + 1;
        
        $type = fake()->randomElement([
            'Laptop',
            'Desktop',
            'Monitor',
            'Keyboard',
            'Mouse',
            'Headset',
            'Phone',
            'Tablet',
        ]);

        return [
            'id' => (string) Str::uuid(),
            'asset_tag' => sprintf('ASSET-%04d-%04d', $year, $sequence),
            'serial_number' => strtoupper(fake()->bothify('??##??####??')),
            'equipment_type' => $type,
            'brand' => $this->getBrand($type),
            'model' => $this->getModel($type),
            'specifications' => $this->getSpecifications($type),
            'purchase_date' => fake()->dateTimeBetween('-3 years', 'now'),
            'purchase_price' => fake()->randomFloat(2, 500, 3000),
            'purchase_currency' => 'USD',
            'supplier' => fake()->company(),
            'warranty_expiry_date' => fake()->dateTimeBetween('now', '+3 years'),
            'warranty_provider' => fake()->company(),
            'status' => 'Available',
            'condition' => fake()->randomElement(['New', 'Good']),
            'current_assignee_id' => null,
            'assigned_at' => null,
            'physical_location' => fake()->randomElement([
                'San Francisco HQ - Storage',
                'New York Office - Storage',
                'Austin Office - Storage',
            ]),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function assigned(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Assigned',
            'assigned_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function laptop(): self
    {
        return $this->state([
            'equipment_type' => 'Laptop',
            'brand' => fake()->randomElement(['Apple', 'Dell', 'Lenovo', 'HP']),
        ]);
    }

    private function getBrand(string $type): string
    {
        return match($type) {
            'Laptop', 'Desktop' => fake()->randomElement(['Apple', 'Dell', 'Lenovo', 'HP', 'Microsoft']),
            'Monitor' => fake()->randomElement(['Dell', 'LG', 'Samsung', 'BenQ']),
            'Phone' => fake()->randomElement(['Apple', 'Samsung', 'Google']),
            'Keyboard', 'Mouse' => fake()->randomElement(['Logitech', 'Microsoft', 'Apple', 'Razer']),
            default => fake()->company(),
        };
    }

    private function getModel(string $type): string
    {
        return match($type) {
            'Laptop' => fake()->randomElement(['MacBook Pro 16"', 'Dell XPS 15', 'ThinkPad X1', 'Surface Laptop']),
            'Monitor' => fake()->randomElement(['27" 4K', '32" UltraWide', '24" FHD']),
            'Phone' => fake()->randomElement(['iPhone 15 Pro', 'Galaxy S24', 'Pixel 8']),
            default => fake()->word() . ' ' . fake()->randomNumber(2),
        };
    }

    private function getSpecifications(string $type): ?array
    {
        return match($type) {
            'Laptop' => [
                'cpu' => fake()->randomElement(['M3 Pro', 'Intel i7', 'AMD Ryzen 7']),
                'ram' => fake()->randomElement(['16GB', '32GB', '64GB']),
                'storage' => fake()->randomElement(['512GB SSD', '1TB SSD', '2TB SSD']),
                'display' => fake()->randomElement(['14"', '15.6"', '16"']),
            ],
            'Monitor' => [
                'size' => fake()->randomElement(['24"', '27"', '32"']),
                'resolution' => fake()->randomElement(['1920x1080', '2560x1440', '3840x2160']),
                'panel' => fake()->randomElement(['IPS', 'VA', 'TN']),
            ],
            'Phone' => [
                'storage' => fake()->randomElement(['128GB', '256GB', '512GB']),
                'color' => fake()->randomElement(['Black', 'White', 'Blue', 'Gray']),
            ],
            default => null,
        };
    }
}

