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
 * Password utilities (trait).
 */
trait PasswordTrait
{

    /**
     * New password.
     *
     * @var string
     */
    public string $newPassword = '';

    /**
     * Repeated new password.
     *
     * @var string
     */
    public string $newPassword2 = '';

    /**
     * Password hashed with password_hash()
     *
     * @var string
     */
    public string $password;

    abstract public function primaryColumnValue();

    /**
     * Asigns the new password to the user.
     *
     * @param string $value
     */
    public function setPassword(string $value): void
    {
        $this->password = password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * Check if user have been change the password.
     *  If so, it checks that the two passwords are the same and updates the password.
     *
     * @return bool
     */
    public function testPassword(): bool
    {
        if (false === empty($this->newPassword) && false === empty($this->newPassword2)) {
            if ($this->newPassword !== $this->newPassword2) {
                $this->message->warning('La nueva contraseña no coincide con su comprobación.');
                return false;
            }

            $this->setPassword($this->newPassword);
        }

        if (empty($this->password)) {
            $this->message->warning('La contraseña no puede estar vacía.');
            return false;
        }

        return true;
    }

    /**
     * Verifies password. It also rehash the password if needed.
     *
     * @param string $value
     * @return bool
     */
    public function verifyPassword(string $value): bool
    {
        if (password_verify($value, $this->password)) {
            if (password_needs_rehash($this->password, PASSWORD_DEFAULT)) {
                $this->setPassword($value);
            }

            return true;
        }

        return false;
    }
}
