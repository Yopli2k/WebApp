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

use WebApp\Core\DataBase\DataBase;
use WebApp\Core\Tools\Tools;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class used for encapsulate common parts of code for the normal WebApp execution.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class AppBase
{
    /**
     * Database access manager.
     *
     * @var DataBase
     */
    protected $dataBase;

    /**
     * Gives us access to the HTTP request parameters.
     *
     * @var Request
     */
    protected $request;

    /**
     * HTTP response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * Requested Uri
     *
     * @var string
     */
    protected $uri;

    abstract protected function die(int $status, string $message = '');

    /**
     * Initializes the app.
     *
     * @param string $uri
     */
    public function __construct(string $uri = '/')
    {
        $this->request = Request::createFromGlobals();
        $this->dataBase = new DataBase();
        $this->response = new Response();
        $this->uri = $uri;

        // add security headers
        $this->response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $this->response->headers->set('X-XSS-Protection', '1; mode=block');
        $this->response->headers->set('X-Content-Type-Options', 'nosniff');
        $this->response->headers->set('Strict-Transport-Security', 'max-age=31536000');
    }

    /**
     * Connects to the database and loads the configuration.
     *
     * @return bool
     */
    public function connect(): bool
    {
        return $this->dataBase->connect();
    }

    /**
     * Save log and disconnects from the database.
     */
    public function close(): void
    {
        $this->dataBase->close();
    }

    /**
     * Returns the data into the standard output.
     */
    public function render(): void
    {
        $this->response->send();
    }

    /**
     * Runs the application core.
     *
     * @return bool
     */
    public function run(): bool
    {
        if (false === $this->dataBase->connected()) {
            $this->die(Response::HTTP_INTERNAL_SERVER_ERROR);
            return false;
        }

        if ($this->isIPBanned()) {
            echo '<h1>Ha sobrepasado el número de intentos permitidos</h1>';
            echo '<h3>Por motivos de seguridad se ha bloqueado temporalmente el acceso desde su IP.</h3>';
            $this->die(Response::HTTP_TOO_MANY_REQUESTS);
            return false;
        }

        return true;
    }

    /**
     * Returns param number $num in uri.
     *
     * @param string $num
     * @return string
     */
    protected function getUriParam(string $num): string
    {
        $params = explode('/', substr($this->uri, 1));
        return $params[$num] ?? '';
    }

    /**
     * Returns true if the client IP has been banned.
     *
     * @return bool
     */
    protected function isIPBanned(): bool
    {
        $ipFilter = new IPFilter();
        return $ipFilter->isBanned(Tools::getClientIp());
    }
}
