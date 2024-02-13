<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace WebApp\Core\Tools;

/**
 * Tools for dates and times.
 */
class DateTools
{
    const DATE_STYLE = 'd-m-Y';
    const DATETIME_STYLE = 'd-m-Y H:i:s';
    const HOUR_STYLE = 'H:i:s';

    public static function date(?string $date = null): string
    {
        return empty($date) ? date(self::DATE_STYLE) : date(self::DATE_STYLE, strtotime($date));
    }

    /**
     * Indicates if one date is greater than another.
     * If indicated, equals to greater than is considered.
     * If the maximum date is not reported, the current day is assumed.
     *
     * @param string $value
     * @param bool $orEqual
     * @param string $maxDate
     * @return bool
     */
    public static function dateGreaterThan(string $value, bool $orEqual = false, ?string $maxDate = null): bool
    {
        if (empty($maxDate)) {
            $maxDate = date(self::DATE_STYLE);
        }

        return $orEqual
            ? (strtotime($value) >= strtotime($maxDate))
            : (strtotime($value) > strtotime($maxDate));
    }

    public static function dateTime(?string $date = null): string
    {
        return empty($date) ? date(self::DATETIME_STYLE) : date(self::DATETIME_STYLE, strtotime($date));
    }

    public static function hour(?string $date = null): string
    {
        return empty($date) ? date(self::HOUR_STYLE) : date(self::HOUR_STYLE, strtotime($date));
    }

    public static function timeToDate(int $time): string
    {
        return date(self::DATE_STYLE, $time);
    }

    public static function timeToDateTime(int $time): string
    {
        return date(self::DATETIME_STYLE, $time);
    }

    public static function daysBetweenDates(string $start, string $end): int
    {
        $result = (strtotime($end) - strtotime($start)) / 86400;
        return floor($result);
    }
}
