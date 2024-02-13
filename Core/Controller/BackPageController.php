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
namespace WebApp\Core\Controller;

use WebApp\Core\App\AppCookies;
use WebApp\Model\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Page controller base for all backend pages.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class BackPageController extends PageController
{
    /**
     * User logged in.
     * When the user is not logged in, it is null.
     *
     * @var ?User
     */
    public ?User $user;

    /**
     * Initialize all objects and properties.
     * Load user from cookies, if exists previous login.
     *
     * @param string $className
     * @param string $uri
     */
    public function __construct(string $className, string $uri = '')
    {
        parent::__construct($className, $uri);
        $this->user = null;
    }

    /**
     * Runs the controller's logic.
     * if return false, the controller break the execution.
     *
     * @param Response $response
     * @return bool
     */
    public function exec(Response &$response): bool
    {
        if (false === parent::exec($response)) {
            return false;
        }

        $this->user = $this->cookieAuth();
        if (false === isset($this->user)) {
            $this->redirect('LoginUser');
            $this->setTemplate(false);
            return false;
        }

        $this->multiRequestProtection->addSeed($this->user->username);
        return true;
    }

    /**
     * Authenticate the user using the cookie.
     *
     * @return ?User
     */
    private function cookieAuth(): ?User
    {
        if ($this->request->query->get('logout')) {
            AppCookies::clearCookie($this->response, 'biblioUserName');
            AppCookies::clearCookie($this->response, 'biblioUserLogKey');
            return null;
        }

        $userName = AppCookies::getCookie($this->request, 'biblioUserName');
        if (empty($userName)) {
            return null;
        }

        $logKey = AppCookies::getCookie($this->request, 'biblioUserLogKey');
        $user = new User();
        if ($user->loadFromCode($userName)
            && $user->enabled
            && $user->logkey === $logKey
        ) {
            AppCookies::setCookie($this->response, 'biblioUserName', $user->username);
            AppCookies::setCookie($this->response, 'biblioUserLogKey', $user->logkey);
            return $user;
        }

        $this->ipWarning();
        AppCookies::clearCookie($this->response, 'biblioUserName');
        AppCookies::clearCookie($this->response, 'biblioUserLogKey');
        return null;
    }
}