<?php

namespace Database\Factories;

use App\Models\TrendPost;
use App\Models\TrendSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrendPost>
 */
class TrendPostFactory extends Factory
{
    protected $model = TrendPost::class;

    public function definition(): array
    {
        return [
            'trend_source_id' => TrendSource::factory(),
            'title' => fake()->sentence(6),
            'content' => fake()->paragraph(),
            'post_url' => fake()->url(),
            'posted_at' => fake()->dateTimeBetween('-14 days', 'now'),
        ];
    }
}
