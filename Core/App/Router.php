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

/**
 * Class for manage the routes of the application.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
final class Router
{
    /**
     * Return the specific App controller for any kind of petition.
     *
     * @return AppBase
     */
    public function getApp()
    {
        $uri = $this->getUri();
        return $this->newAppController($uri);
    }

    /**
     * Return true if can output a file, false otherwise.
     *
     * @return bool
     */
    public function getFile(): bool
    {
        $uri = $this->getUri();

        // Not a file? Not a safe file?
        $filePath = APP_FOLDER . urldecode($uri);
        if (false === $this->isFileSafe($filePath)) {
            return false;
        }

        // Allowed folder?
        $allowedFolders = ['Assets', 'Core', 'MyFiles', 'node_modules', 'vendor'];
        foreach ($allowedFolders as $folder) {
            if ('/' . $folder === substr($uri, 0, 1 + strlen($folder))) {
                $this->download($filePath);
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    public static function isFileSafe(string $filePath): bool
    {
        $parts = explode('.', $filePath);
        $safe = [
            'accdb', 'avi', 'cdr', 'css', 'csv', 'doc', 'docx', 'eot', 'gif', 'gz', 'html', 'ico', 'jpeg', 'jpg', 'js',
            'json', 'map', 'mdb', 'mkv', 'mp3', 'mp4', 'ndg', 'ods', 'odt', 'ogg', 'pdf', 'png', 'pptx', 'sql', 'svg',
            'ttf', 'txt', 'webm', 'woff', 'woff2', 'xls', 'xlsx', 'xml', 'xsig', 'zip', 'scss'
        ];
        return empty($parts)
            || count($parts) === 1
            || in_array(end($parts), $safe, true);
    }

    /**
     * Return the file to download.
     *
     * @param string $filePath
     */
    private function download(string $filePath)
    {
        header('Content-Type: ' . $this->getMime($filePath));

        // disable the buffer if enabled
        if (ob_get_contents()) {
            ob_end_flush();
        }

        // force to download svg, xml and xsig files to prevent XSS attacks
        $info = pathinfo($filePath);
        $extension = strtolower($info['extension']);
        if (in_array($extension, ['svg', 'xml', 'xsig'])) {
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        }

        if (is_file($filePath)) {
            readfile($filePath);
        }
    }

    /**
     * Return the mime type from given file.
     *
     * @param string $filePath
     * @return string
     */
    private function getMime(string $filePath): string
    {
        $info = pathinfo($filePath);
        $extension = strtolower($info['extension']);
        return match ($extension) {
            'map',
            'css'   => 'text/css',

            'xml',
            'xsig'  => 'text/xml',

            'js'    => 'application/javascript',
            default => mime_content_type($filePath),
        };
    }

    /**
     * Return the uri from the request.
     *
     * @return bool|string
     */
    private function getUri()
    {
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $uri2 = is_null($uri) ? filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL) : $uri;
        $uriArray = explode('?', $uri2);

        return substr($uriArray[0], strlen(APP_ROUTE));
    }

    /**
     * @param string $uri
     * @param string $pageName
     *
     * @return AppController|AppDebugController
     */
    private function newAppController(string $uri, string $pageName = ''): AppController|AppDebugController
    {
        return APP_DEBUG
            ? new AppDebugController($uri, $pageName)
            : new AppController($uri, $pageName);
    }
}