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
        $clockIn = '9:00';
        $clockOut = '18:00';

        return [
            'user_id' => 2,
            'date' => now()->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
