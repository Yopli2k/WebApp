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
namespace WebApp\Core\App;

use Closure;

/**
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
final class Cache
{
    const EXPIRATION = 3600;
    const FILE_PATH = APP_FOLDER . '/' . APP_FILES . '/FileCache';

    /**
     * Delete all cache files.
     *
     * @return void
     */
    public static function clear(): void
    {
        if (false === file_exists(self::FILE_PATH)) {
            return;
        }

        foreach (scandir(self::FILE_PATH) as $fileName) {
            if (str_ends_with($fileName, '.cache')) {
                unlink(self::FILE_PATH . '/' . $fileName);
            }
        }
    }

    /**
     * Delete file cache.
     *
     * @param string $key
     * @return void
     */
    public static function delete(string $key): void
    {
        // buscamos el archivo y lo borramos
        $fileName = self::filename($key);
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    /**
     * Delete files with contain the indicated prefix.
     *
     * @param string $prefix
     * @return void
     */
    public static function deleteMulti(string $prefix): void
    {
        foreach (scandir(self::FILE_PATH) as $fileName) {
            $len = strlen($prefix);
            if (substr($fileName, 0, $len) === $prefix) {
                unlink(self::FILE_PATH . '/' . $fileName);
            }
        }
    }

    /**
     * Delete expired files.
     *
     * @return void
     */
    public static function expire(): void
    {
        foreach (scandir(self::FILE_PATH) as $fileName) {
            if (filemtime(self::FILE_PATH . '/' . $fileName) < time() - self::EXPIRATION) {
                unlink(self::FILE_PATH . '/' . $fileName);
            }
        }
    }

    /**
     * Return the value of the cache file.
     *
     * @param string $key
     * @return mixed|null
     */
    public static function get(string $key): mixed
    {
        // buscamos el archivo y comprobamos su fecha de modificación
        $fileName = self::filename($key);
        if (file_exists($fileName) && filemtime($fileName) >= time() - self::EXPIRATION) {
            // todavía no ha expirado, devolvemos el contenido
            $data = file_get_contents($fileName);
            return unserialize($data);
        }

        return null;
    }

    /**
     * Save the value in the cache file.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        if (false === file_exists(self::FILE_PATH)) {
            mkdir(self::FILE_PATH, 0777, true);
        }

        // guardamos el contenido
        $data = serialize($value);
        $fileName = self::filename($key);
        @file_put_contents($fileName, $data);
    }

    /**
     * Return the filename of the cache file.
     *   - change "/" and "\" by "_"
     *
     * @param string $key
     * @return string
     */
    private static function filename(string $key): string
    {
        $name = str_replace(['/', '\\'], '_', $key);
        return self::FILE_PATH . '/' . $name . '.cache';
    }

    /**
     * Get the value stored if it exists or otherwise store what the callback function returns.
     *
     * @param  string  $key
     * @param  Closure  $callback
     * @return mixed
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        if (! is_null($value = self::get($key))) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value);
        return $value;
    }
}
