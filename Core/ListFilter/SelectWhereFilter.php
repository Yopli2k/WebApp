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

/**
 * Selection filter of options where each option has a DataBaseWhere
 * associated for data filtering
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 */
class SelectWhereFilter extends SelectFilter
{
    /**
     * @param string $key
     * @param array $values
     * @param string $label
     */
    public function __construct(string $key, array $values = [], string $label = '')
    {
        parent::__construct($key, '', $label, $values);
    }

    /**
     * @param array $where
     * @return bool
     */
    public function getDataBaseWhere(array &$where): bool
    {
        $value = ($this->value == '' || $this->value == null) ? 0 : $this->value;
        foreach ($this->values[$value]['where'] as $condition) {
            $where[] = $condition;
        }

        return ($value > 0);
    }

    /**
     * @return string
     */
    protected function getHtmlOptions(): string
    {
        $html = '';
        foreach ($this->values as $key => $data) {
            $extra = ('' != $this->value && $key == $this->value) ? ' selected' : '';
            $html .= '<option value="' . $key . '"' . $extra . '>' . $data['label'] . '</option>';
        }

        return $html;
    }
}
