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

use WebApp\Core\App\Router;

const APP_FOLDER = __DIR__;

// load required files
require_once  APP_FOLDER . '/vendor/autoload.php';
require_once APP_FOLDER . '/config.php';

// disable execution time and connection abort
@set_time_limit(0);
ignore_user_abort(true);

// set timezone
date_default_timezone_set(APP_TIMEZONE);

// Run application. Can be a download file or a web page.
// First try to get the file to download, if not, run the web page.
$router = new Router();
if (false === $router->getFile()) {
    $app = $router->getApp();
    $app->connect();        // Connect to the database, cache, etc.
    if ($app->run()) {      // Executes App logic
        $app->render();     // Render web page
    }
    $app->close();          // Disconnect from everything
}