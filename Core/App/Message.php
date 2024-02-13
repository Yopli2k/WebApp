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
 * Class for manage the messages from app to user.
 *
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
final class Message
{
    /**
     * Contains all message to show classified by level.
     * levels:
     *   - info
     *   - warning
     *   - error
     *
     * @var array
     */
    private static array $data = [];

    /**
     * Class constructor. Inicializate the message array.
     */
    public function __construct()
    {
        if (empty(self::$data)) {
            self::$data = [
                'info' => [],
                'warning' => [],
                'error' => [],
            ];
        }
    }

    /**
     * Adds a message to the list.
     *
     * @param string $level
     * @param string $message
     */
    public static function addMessage(string $level, string $message): void
    {
        switch ($level) {
            case 'error':
            case 'warning':
                self::$data[$level][] = $message;
                break;

            default:
                self::$data['info'][] = $message;
        }
    }

    /**
     * Clears all message.
     *
     * @param string $level
     */
    public static function clear(string $level = ''): void
    {
        if (empty($level)) {
            self::$data = [];
            return;
        }

        self::$data[$level] = [];
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     */
    public function error(string $message): void
    {
        self::$data['error'][] = $message;
    }

    /**
     * Returns all messages for one level.
     *
     * @param string $level
     * @return array
     */
    public static function getMessages(string $level = ''): array
    {
        return empty($level) ? self::$data : self::$data[$level] ?? [];
    }

    /**
     * Interesting information, advices.
     *
     * @param string $message
     */
    public function info(string $message): void
    {
        self::$data['info'][] = $message;
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * @param string $message
     */
    public function warning(string $message): void
    {
        self::$data['warning'][] = $message;
    }
}