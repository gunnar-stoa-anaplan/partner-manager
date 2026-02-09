<?php

namespace App\Helpers;

use App\Models\AppSetting;
use Carbon\Carbon;

class FiscalYearHelper
{
    /**
     * Calculate fiscal quarter and year based on a given date.
     * Fiscal year start month is read from AppSetting (defaults to February).
     *
     * @param string|Carbon|null $date
     * @return array ['fiscal_quarter' => 'Q1 FY2027', 'fiscal_year' => 2027]
     */
    public static function calculateFiscalPeriod($date): array
    {
        if (empty($date)) {
            return [
                'fiscal_quarter' => null,
                'fiscal_year' => null,
            ];
        }

        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $month = $carbonDate->month;
        $year = $carbonDate->year;
        $startMonth = (int) AppSetting::get('fiscal_year_start_month', 2);

        // Calculate fiscal year
        if ($startMonth === 1) {
            $fiscalYear = $year;
        } elseif ($month >= $startMonth) {
            $fiscalYear = $year + 1;
        } else {
            $fiscalYear = $year;
        }

        // Calculate quarter based on offset from fiscal year start
        $monthsFromStart = ($month - $startMonth + 12) % 12;
        $quarterNum = intdiv($monthsFromStart, 3) + 1;

        return [
            'fiscal_quarter' => 'Q' . $quarterNum . ' FY' . $fiscalYear,
            'fiscal_year' => $fiscalYear,
        ];
    }

    /**
     * Get the start and end dates for a given fiscal quarter
     *
     * @param string $fiscalQuarter Format: 'Q1 FY2027'
     * @return array ['start_date' => Carbon, 'end_date' => Carbon]
     */
    public static function getFiscalQuarterDates(string $fiscalQuarter): array
    {
        preg_match('/Q(\d) FY(\d{4})/', $fiscalQuarter, $matches);

        if (count($matches) !== 3) {
            throw new \InvalidArgumentException("Invalid fiscal quarter format: {$fiscalQuarter}");
        }

        $quarter = (int) $matches[1];
        $fiscalYear = (int) $matches[2];
        $startMonth = (int) AppSetting::get('fiscal_year_start_month', 2);

        // Calculate the calendar month this quarter starts in
        $quarterStartOffset = ($quarter - 1) * 3;
        $qStartMonth = (($startMonth - 1 + $quarterStartOffset) % 12) + 1;

        // Determine the calendar year of the quarter start
        if ($startMonth === 1) {
            $calendarYear = $fiscalYear;
        } else {
            $calendarYear = $fiscalYear - 1;
            if ($qStartMonth < $startMonth) {
                $calendarYear = $fiscalYear;
            }
        }

        $startDate = Carbon::create($calendarYear, $qStartMonth, 1);
        $endDate = $startDate->copy()->addMonths(3)->subDay()->endOfDay();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Get current fiscal quarter and year
     *
     * @return array ['fiscal_quarter' => 'Q1 FY2027', 'fiscal_year' => 2027]
     */
    public static function getCurrentFiscalPeriod(): array
    {
        return self::calculateFiscalPeriod(now());
    }
}
