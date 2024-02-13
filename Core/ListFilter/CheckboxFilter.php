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
namespace WebApp\Core\ListFilter;

use WebApp\Core\DataBase\DataBaseWhere;

/**
 * Description of CheckboxFilter
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class CheckboxFilter extends BaseFilter
{

    /**
     * @var DataBaseWhere[]
     */
    public array $default;

    /**
     * @var mixed
     */
    public mixed $matchValue;

    /**
     * @var string
     */
    public string $operation;

    /**
     * @param string $key
     * @param string $field
     * @param string $label
     * @param string $operation
     * @param bool $matchValue
     * @param array $default
     */
    public function __construct(string $key, string $field = '', string $label = '', string $operation = '=', bool $matchValue = true, array $default = [])
    {
        parent::__construct($key, $field, $label);
        $this->autosubmit = true;
        $this->default = $default;
        $this->matchValue = $matchValue;
        $this->operation = $operation;
        $this->ordernum += 100;
    }

    /**
     * @param array $where
     * @return bool
     */
    public function getDataBaseWhere(array &$where): bool
    {
        if ('TRUE' === $this->value) {
            $where[] = new DataBaseWhere($this->field, $this->matchValue, $this->operation);
            return true;
        }

        $result = false;
        foreach ($this->default as $value) {
            $where[] = $value;
            $result = true;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $extra = is_null($this->value) ? '' : ' checked=""';
        return '<div class="col-sm-auto">'
            . '<div class="form-group">'
            . '<div class="form-check mb-2 mb-sm-0">'
            . '<label class="form-check-label mr-3">'
            . '<input class="form-check-input" type="checkbox" name="' . $this->name() . '" value="TRUE"' . $extra . $this->onChange() . $this->readonly() . '/>'
            . $this->label
            . '</label>'
            . '</div>'
            . '</div>'
            . '</div>';
    }
}
