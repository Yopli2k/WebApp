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

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class to manage cookies.
 * Require the response object for manage (update/clear) the cookies.
 * Require the request object for get the cookies.
 *
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
class AppCookies
{

    /**
     * Clear the cookie value.
     *
     * @param Response $response
     * @param string $cookie
     * @return void
     */
    public static function clearCookie(Response $response, string $cookie): void
    {
        $response->headers->clearCookie($cookie);
    }

    /**
     * Get the cookie value.
     *
     * @param Request $request
     * @param string $cookie
     * @param string $default
     * @return string
     */
    public static function getCookie(Request $request, string $cookie, string $default = ''): string
    {
        return $request->cookies->get($cookie, $default);
    }

    /**
     * Set value to cookies.
     *
     * @param Response $response
     * @param string $cookie
     * @param string $value
     * @return void
     */
    public static function setCookie(Response $response, string $cookie, string $value): void
    {
        $expire = time() + APP_COOKIES_EXPIRE;
        $response->headers->setCookie(new Cookie($cookie, $value, $expire, APP_ROUTE, null, false, false, false, null));
    }
}