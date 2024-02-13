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

use WebApp\Core\Tools\Tools;

/**
 * Class Tools for number formatting
 */
class NumberTools
{

    public static function money(float $number, string $symbol = '€', $align = 'right'): string
    {
        return $align === 'right'
            ? self::number($number) . ' ' . $symbol
            : $symbol . ' ' . self::number($number);
    }

    public static function number(float $number, ?int $decimals = null): string
    {
        if ($decimals === null) {
            $decimals = 0;
        }

        $decimalSeparator = ',';
        $thousandsSeparator = '.';
        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}
