<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $patterns = [
            'normal' => ['clock_in' => '09:00', 'clock_out' => '18:00', 'count' => 10],
            'overtime' => ['clock_in' => '09:00', 'clock_out' => '20:00', 'count' => 3],
            'late' => ['clock_in' => '09:30', 'clock_out' => '18:00', 'count' => 2],
            'early' => ['clock_in' => '09:00', 'clock_out' => '17:00', 'count' => 1],
            'long' => ['clock_in' => '08:00', 'clock_out' => '21:00', 'count' => 1],
        ];

        $weekdays = [];
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        for ($date=$start; $date < $end; $date->addDay()) {
            if ($date->isWeekday()) {
                $weekdays[] = $date->format('Y-m-d');
            }
        }

        // patternsのcountを元に配列を作る
        $weekdaysArray1 = array_fill(0, $patterns['normal']['count'], $patterns['normal']);
        $weekdaysArray2 = array_fill(0, $patterns['overtime']['count'], $patterns['overtime']);
        $weekdaysArray3 = array_fill(0, $patterns['late']['count'], $patterns['late']);
        $weekdaysArray4 = array_fill(0, $patterns['early']['count'], $patterns['early']);
        $weekdaysArray5 = array_fill(0, $patterns['long']['count'], $patterns['long']);

        $weekdaysArray = array_merge($weekdaysArray1, $weekdaysArray2, $weekdaysArray3, $weekdaysArray4, $weekdaysArray5);

        

        // シャッフルして平日に割り当てる
        shuffle($weekdaysArray);

        $weekdays = array_slice($weekdays, 0, 15);
        $schedule = array_combine($weekdays, $weekdaysArray);

        foreach ($schedule as $date => $pattern) {
            Attendance::factory()->create([
                'date' => $date,
                'clock_in' => $pattern['clock_in'],
                'clock_out' => $pattern['clock_out'],
            ]);
        }
    }
}
