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
 * Class with general tools.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
class Tools
{
    const HTML_CHARS = ['<', '>', '"', "'"];
    const HTML_REPLACEMENTS = ['&lt;', '&gt;', '&quot;', '&#39;'];

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public static function config(string $key, $default = null)
    {
        $constants = [$key, strtoupper($key), 'APP_' . strtoupper($key)];
        foreach ($constants as $constant) {
            if (defined($constant)) {
                return constant($constant);
            }
        }

        return $default;
    }

    public static function fixHtml(?string $text = null): ?string
    {
        if (empty($text)) {
            return $text;
        }
        return str_replace(self::HTML_REPLACEMENTS, self::HTML_CHARS, trim($text));
    }

    public static function noHtml(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }
        return str_replace(self::HTML_CHARS, self::HTML_REPLACEMENTS, trim($text));
    }

    /**
     * Returns the client IP.
     *
     * @return string
     */
    public static function getClientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $field) {
            if (isset($_SERVER[$field])) {
                return (string)$_SERVER[$field];
            }
        }

        return '::1';
    }

    /**
     * Return a list of page items for pagination.
     *
     * @return array
     */
    public static function getPagination(int $count, int $offset, int $limit = APP_ITEM_LIMIT): array
    {
        $pages = [];
        $key1 = $key2 = 0;
        $current = 1;

        // add all pages
        while ($key2 < $count) {
            $pages[$key1] = [
                'active' => ($key2 == $offset),
                'num' => $key1 + 1,
                'offset' => $key1 * $limit,
            ];
            if ($key2 == $offset) {
                $current = $key1;
            }
            $key1++;
            $key2 += $limit;
        }

        // now descarting pages
        foreach (array_keys($pages) as $key2) {
            $middle = intval($key1 / 2);

            /**
             * We discard everything except the first page, the last one, the middle one,
             * the current one, the 5 previous and 5 following ones.
             */
            if (($key2 > 1 && $key2 < $current - 5 && $key2 != $middle) || ($key2 > $current + 5 && $key2 < $key1 - 1 && $key2 != $middle)) {
                unset($pages[$key2]);
            }
        }

        // if there is only one page, we return an empty array
        return count($pages) > 1 ? $pages : [];
    }

    /**
     * Returns a random text string of length $length.
     *
     * @param int $length
     * @return string
     */
    public static function randomString(int $length = 10): string
    {
        return mb_substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
    }

    public static function textBreak(string $text, int $length = 50, string $break = '...'): string
    {
        if (strlen($text) <= $length) {
            return trim($text);
        }

        // separamos el texto en palabras
        $words = explode(' ', trim($text));
        $result = '';
        foreach ($words as $word) {
            if (strlen($result . ' ' . $word . $break) <= $length) {
                $result .= $result === '' ? $word : ' ' . $word;
                continue;
            }

            $result .= $break;
            break;
        }

        return $result;
    }
}
