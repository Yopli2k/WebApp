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
namespace WebApp\Model;

use WebApp\Core\App\AppModel;
use WebApp\Core\DataBase\DataBaseWhere;
use WebApp\Core\Tools\PasswordTrait;
use WebApp\Core\Tools\PhoneTools;
use WebApp\Core\Tools\Tools;

/**
 * Class to manage the member data.
 *
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
class Member extends AppModel
{

    use PasswordTrait;

    /**
     * The street address of the member.
     *
     * @var string
     */
    public string $address;

    /**
     * The date of creation of the member.
     *
     * @var string
     */
    public string $creationdate;

    /**
     * The document legal identification. DNI, NIE, Passport, etc.
     *
     * @var string
     */
    public string $document;

    /**
     * The email address of the member.
     *
     * @var string
     */
    public string $email;

    /**
     * Indicates if the member is enabled.
     *
     * @var bool
     */
    public bool $enabled;

    /**
     * Primary Key.
     *
     * @var int|null
     */
    public ?int $id;

    /** @var string */
    public string $logkey;

    /**
     * Full name of the member.
     *
     * @var string
     */
    public string $name;

    /**
     * Internal notes about the member.
     *
     * @var string
     */
    public string $notes;

    /**
     * The password of the member.
     *
     * @var string
     */
    public string $password;

    /**
     * Phone number for contact.
     *
     * @var string
     */
    public string $phone;

    /**
     * Indicates if the email of the member has been verified.
     *
     * @var bool
     */
    public bool $verified;

    /**
     * Check if the email is already in use or is the same as the current member.
     *
     * @param string $email
     * @return bool
     */
    public function checkNewEmail(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        if ($this->email === $email) {
            return true;
        }

        $where = [ new DataBaseWhere('email', $email) ];
        $member = new Member();
        if ($member->loadFromCode('', $where)) {
            return $this->id === $member->id;
        }

        return true;
    }

    /**
     * Reset the values of all model properties.
     */
    public function clear(): void
    {
        $this->address = '';
        $this->creationdate = date('d-m-Y');
        $this->document = '';
        $this->email = '';
        $this->enabled = true;
        $this->id = null;
        $this->logkey = '';
        $this->name = '';
        $this->notes = '';
        $this->password = '';
        $this->phone = '';
        $this->verified = false;
    }

    /**
     * Indicates if the member has a loan pending of return.
     *
     * @return bool
     */
    public function hasLoan(): bool
    {
        $where = [
            new DataBaseWhere('member_id', $this->id),
            new DataBaseWhere('return_date', null, 'IS')
        ];
        $loan = new Loan();
        return $loan->loadFromCode('', $where);
    }

    /**
     * Assign the values of the $data array to the model properties.
     *
     * @param array $data
     */
    public function loadFromData(array $data = []): void
    {
        $this->address = $data['address'] ?? '';
        $this->creationdate = $data['creationdate'] ?? date('d-m-Y');
        $this->document = $data['document'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->enabled = (bool)$data['enabled'] ?? false;
        $this->id = (int)$data['id'] ?? null;
        $this->logkey = $data['logkey'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->notes = $data['notes'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->verified = (bool)$data['verified'] ?? false;
    }

    /**
     * Set new logkey to user.
     *
     * @return void
     */
    public function newLogkey()
    {
        $this->logkey = Tools::randomString(99);
        $this->save();
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'id';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'members';
    }

    /**
     * Returns true if there are no errors in the values of the model properties.
     * It runs inside the save method.
     *
     * @return bool
     */
    public function test(): bool
    {
        $this->address = Tools::noHtml($this->address ?? '');
        $this->document = Tools::noHtml($this->document ?? '');
        $this->name = Tools::noHtml($this->name ?? '');
        $this->notes = Tools::noHtml($this->notes ?? '');

        $this->email = Tools::noHtml(mb_strtolower($this->email ?? '', 'UTF8'));
        if (false === empty($this->email) && false === filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->message->warning('El email no es válido.');
            return false;
        }

        $this->phone = Tools::noHtml($this->phone ?? '');
        if (false === empty($this->phone) && false === PhoneTools::isPhone($this->phone)) {
            $this->message->warning('El teléfono no es válido.');
            return false;
        }

        return parent::test();
    }

    /**
     * Insert the model data in the database.
     *
     * @return bool
     */
    protected function insert(): bool
    {
        $sql = 'INSERT INTO ' . static::tableName()
            . ' (address, creationdate, document, email, enabled, name, notes, password, phone, verified)'
            . ' VALUES ('
            . self::$dataBase->var2str($this->address) . ','
            . self::$dataBase->var2str($this->creationdate) . ','
            . self::$dataBase->var2str($this->document) . ','
            . self::$dataBase->var2str($this->email) . ','
            . self::$dataBase->var2str($this->enabled) . ','
            . self::$dataBase->var2str($this->name) . ','
            . self::$dataBase->var2str($this->notes) . ','
            . self::$dataBase->var2str($this->password) . ','
            . self::$dataBase->var2str($this->phone) . ','
            . self::$dataBase->var2str($this->verified)
            . ')';
        return self::$dataBase->exec($sql);
    }

    /**
     * Returns the list of fields that are required.
     *
     * @return string[]
     */
    protected function requiredFields(): array
    {
        return ['address', 'document', 'email', 'name', 'phone', 'password'];
    }

    /**
     * Update the model data in the database.
     *
     * @return bool
     */
    protected function update(): bool
    {
        $sql = 'UPDATE ' . static::tableName() . ' SET '
                . 'address = ' . self::$dataBase->var2str($this->address) . ','
                . 'document = ' . self::$dataBase->var2str($this->document) . ','
                . 'email = ' . self::$dataBase->var2str($this->email) . ','
                . 'enabled = ' . self::$dataBase->var2str($this->enabled) . ','
                . 'logkey = ' . self::$dataBase->var2str($this->logkey) . ','
                . 'name = ' . self::$dataBase->var2str($this->name) . ','
                . 'notes = ' . self::$dataBase->var2str($this->notes) . ','
                . 'password = ' . self::$dataBase->var2str($this->password) . ','
                . 'phone = ' . self::$dataBase->var2str($this->phone) . ','
                . 'verified = ' . self::$dataBase->var2str($this->verified)
            . ' WHERE id = ' . self::$dataBase->var2str($this->id);
        return self::$dataBase->exec($sql);
    }
}
