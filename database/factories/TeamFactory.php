<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infrastructure\Persistence\Eloquent\Models\Team;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        static $sequence = 0;
        $sequence++;
        
        return [
            'team_id' => sprintf('TEAM-%04d', $sequence),
            'name' => fake()->words(2, true) . ' Team',
            'description' => fake()->sentence(),
            'type' => fake()->randomElement([
                'Development',
                'QA',
                'DevOps',
                'Design',
                'Product',
                'Management',
            ]),
            'department' => fake()->randomElement([
                'Engineering',
                'Product',
                'Design',
                'Operations',
            ]),
            'parent_team_id' => null,
            'max_size' => fake()->optional()->randomElement([8, 10, 12, 15]),
            'is_active' => true,
            'disbanded_at' => null,
            'disbanded_reason' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function disbanded(): self
    {
        return $this->state([
            'is_active' => false,
            'disbanded_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'disbanded_reason' => fake()->sentence(),
        ]);
    }

    public function withParent(Team $parent): self
    {
        return $this->state([
            'parent_team_id' => $parent->id,
        ]);
    }
}

