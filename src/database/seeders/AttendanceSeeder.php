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

        $normalPatterns = [
            'normal' => ['clock_in' => '09:00', 'clock_out' => '18:00', 'count' => 15],
        ];

        $allSchedule = collect();

        // user1当月だけパターンあり17日
        $schedule = $this->buildMonthlySchedule($patterns, 0, 17, 1);
        foreach ($schedule as $date => $pattern) {
            $allSchedule->put($date . '_1', array_merge($pattern, ['user_id' => 1, 'date' => $date]));
        }

        // user1過去5ヶ月は通常15日
        for ($i = 1; $i <= 5; $i++) {
            $schedule = $this->buildMonthlySchedule($normalPatterns, $i, 15, 1);
            foreach ($schedule as $date => $pattern) {
                $allSchedule->put($date . '_1', array_merge($pattern, ['user_id' => 1, 'date' => $date]));
            }
        }

        // user2,3は全5ヶ月通常15日
        foreach ([2, 3] as $userId) {
            for ($i = 1; $i <= 5; $i++) {
                $schedule = $this->buildMonthlySchedule($normalPatterns, $i, 15, $userId);
                foreach ($schedule as $date => $pattern) {
                    $allSchedule->put($date . '_' . $userId, array_merge($pattern, ['user_id' => $userId, 'date' => $date]));
                }
            }
        }

        // 古い順にソートしてinsert
        foreach ($allSchedule->sortBy('date') as $data) {
            Attendance::factory()->create([
                'user_id' => $data['user_id'],
                'date' => $data['date'],
                'clock_in' => $data['clock_in'],
                'clock_out' => $data['clock_out'],
            ]);
        };
    }

    private function buildMonthlySchedule(array $patterns, int $monthAgo, int $days, int $userId)
    {
        $weekdays = [];
        $start = Carbon::now()->subMonths($monthAgo)->startOfMonth();

        if ($monthAgo === 0) {
            if ($userId === 1) {
                $end = Carbon::now()->endOfMonth();
            } else {
                $end = Carbon::now()->subDays(1);
            }
        } else {
            $end = Carbon::now()->subMonths($monthAgo)->endOfMonth();
        }

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

        return collect(array_combine($weekdays, $weekdaysArray));
    }
}
