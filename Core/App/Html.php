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

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Class used for render html template.
 *
 * @author Carlos García Gómez      <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
final class Html
{
    /** @var FilesystemLoader */
    private static $loader;

    /** @var Environment */
    private static $twig;

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public static function render(string $template, array $params = []): string
    {
        $templateVars = [
            'message' => new Message(),
        ];
        return self::twig()->render($template, array_merge($params, $templateVars));
    }

    private static function assetFunction(): TwigFunction
    {
        return new TwigFunction('asset', function ($string) {
            $path = APP_ROUTE . '/';
            return substr($string, 0, strlen($path)) == $path
                ? $string
                : str_replace('//', '/', $path . $string);
        });
    }

    /**
     * @throws LoaderError
     */
    private static function twig(): Environment
    {
        if (false === defined('APP_DEBUG')) {
            define('APP_DEBUG', true);
        }

        self::$loader = new FilesystemLoader(APP_FOLDER . '/View');
        self::$twig = new Environment(self::$loader, ['debug' => APP_DEBUG]);
        self::$twig->addFunction(self::assetFunction());
        return self::$twig;
    }
}
