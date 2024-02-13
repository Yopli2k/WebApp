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
 * Tools for files and folders.
 */
class FileTools
{

    public static function folder(...$folders): string
    {
        if (empty($folders)) {
            return Tools::config('folder') ?? '';
        }

        array_unshift($folders, Tools::config('folder'));
        return implode(DIRECTORY_SEPARATOR, $folders);
    }

    public static function folderCheckOrCreate(string $folder): bool
    {
        return is_dir($folder) || mkdir($folder, 0777, true);
    }

    public static function folderCopy(string $src, string $dst): bool
    {
        static::folderCheckOrCreate($dst);

        $folder = opendir($src);
        while (false !== ($file = readdir($folder))) {
            if ($file === '.' || $file === '..') {
                continue;
            } elseif (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                static::folderCopy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
            } else {
                copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
            }
        }

        closedir($folder);
        return true;
    }

    public static function folderDelete(string $folder): bool
    {
        if (is_dir($folder) && false === is_link($folder)) {
            $files = array_diff(scandir($folder), ['.', '..']);
            foreach ($files as $file) {
                self::folderDelete($folder . DIRECTORY_SEPARATOR . $file);
            }

            return rmdir($folder);
        }

        return unlink($folder);
    }

    public static function folderScan(string $folder, bool $recursive = false, array $exclude = ['.DS_Store', '.well-known']): array
    {
        $scan = scandir($folder, SCANDIR_SORT_ASCENDING);
        if (false === is_array($scan)) {
            return [];
        }

        $exclude[] = '.';
        $exclude[] = '..';
        $rootFolder = array_diff($scan, $exclude);
        if (false === $recursive) {
            return $rootFolder;
        }

        $result = [];
        foreach ($rootFolder as $item) {
            $newItem = $folder . DIRECTORY_SEPARATOR . $item;
            $result[] = $item;
            if (is_dir($newItem)) {
                foreach (static::folderScan($newItem, true, $exclude) as $item2) {
                    $result[] = $item . DIRECTORY_SEPARATOR . $item2;
                }
            }
        }

        return $result;
    }
}
