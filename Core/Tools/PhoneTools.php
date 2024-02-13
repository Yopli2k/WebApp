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
class PhoneTools
{
    const PHONE_PATTERN = "(\+34|0034|34)?[ -]*(6|7|8|9)[ -]*([0-9][ -]*){8}";

    public static function isPhone(string $phone): bool
    {
        return preg_match('/^' . self::PHONE_PATTERN . '$/', $phone) === 1;
    }
}