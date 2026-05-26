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

        $this->createMonthlyAttendance($patterns, 1, 0, 17);

        $normalPatterns = [
            'normal' => ['clock_in' => '09:00', 'clock_out' => '18:00', 'count' => 15],
        ];

        for ($i=1; $i <= 5 ; $i++) {
            $this->createMonthlyAttendance($normalPatterns, 1, $i, 15);
        }

        for ($userId=2; $userId <= 3 ; $userId++) {
            for ($i = 0; $i <= 5; $i++) {
                $this->createMonthlyAttendance($normalPatterns, $userId, $i, 15);
            }
        }
    }

    private function createMonthlyAttendance(array $patterns, int $userId, int $monthAgo, int $days)
    {
        $weekdays = [];
        $start = Carbon::now()->subMonths($monthAgo)->startOfMonth();
        $end = Carbon::now()->subMonths($monthAgo)->endOfMonth();

        for ($date = $start; $date <= $end; $date->addDay()) {
            if ($date->isWeekday()) {
                $weekdays[] = $date->format('Y-m-d');
            }
        }

        $weekdaysArray = [];
        foreach ($patterns as $pattern) {
            $weekdaysArray = array_merge(
                $weekdaysArray,
                array_fill(0, $pattern['count'], $pattern)
            );
        }

        shuffle($weekdaysArray);
        shuffle($weekdays);
        $weekdays = array_slice($weekdays, 0, $days);
        $weekdaysArray = array_slice($weekdaysArray, 0, $days);
        $schedule = array_combine($weekdays, $weekdaysArray);
        $sortedSchedule = collect($schedule)->sortKeysDesc();

        foreach ($sortedSchedule as $date => $pattern) {
            Attendance::factory()->create([
                'user_id' => $userId,
                'date' => $date,
                'clock_in' => $pattern['clock_in'],
                'clock_out' => $pattern['clock_out'],
            ]);
        }
    }
}
