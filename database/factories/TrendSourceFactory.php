<?php

namespace Database\Factories;

use App\Models\TrendSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrendSource>
 */
class TrendSourceFactory extends Factory
{
    protected $model = TrendSource::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'platform' => fake()->randomElement(['Website', 'Instagram', 'TikTok', 'Google Trends']),
            'source_url' => fake()->url(),
            'is_active' => true,
        ];
    }
}
