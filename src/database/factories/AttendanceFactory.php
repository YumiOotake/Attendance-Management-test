<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-5 month', 'now');

        return [
            'user_id' => 1,
            'date' => $date->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ];
    }
}
