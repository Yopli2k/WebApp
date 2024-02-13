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
use WebApp\Core\Tools\CodeModel;

/**
 * Description of SelectFilter
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class SelectFilter extends BaseFilter
{

    /** @var string */
    public string $icon = '';

    /** @var array */
    public array $values;

    /**
     * @param string $key
     * @param string $field
     * @param string $label
     * @param array $values
     */
    public function __construct(string $key, string $field, string $label, array $values = [])
    {
        parent::__construct($key, $field, $label);
        $this->autosubmit = true;
        $this->values = $values;
    }

    /**
     * @param array $where
     * @return bool
     */
    public function getDataBaseWhere(array &$where): bool
    {
        if ('' !== $this->value && null !== $this->value) {
            $where[] = new DataBaseWhere($this->field, $this->value);
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        if (empty($this->icon)) {
            return '<div class="col-sm-3 col-lg-2">'
                . '<div class="form-group">'
                . '<select name="' . $this->name() . '" class="form-control"' . $this->onChange() . $this->readonly()
                . ' title="' . $this->label . '">' . $this->getHtmlOptions()
                . '</select>'
                . '</div>'
                . '</div>';
        }

        return '<div class="col-sm-3 col-lg-2">'
            . '<div class="form-group">'
            . '<div class="input-group" title="' . $this->label . '">'
            . '<span class="input-group-prepend">'
            . '<span class="input-group-text">'
            . '<i class="' . $this->icon . ' fa-fw" aria-hidden="true"></i>'
            . '</span>'
            . '</span>'
            . '<select name="' . $this->name() . '" class="form-control"' . $this->onChange() . $this->readonly() . '>'
            . $this->getHtmlOptions()
            . '</select>'
            . '</div>'
            . '</div>'
            . '</div>';
    }

    /**
     * @return string
     */
    protected function getHtmlOptions(): string
    {
        $html = '<option value="">' . $this->label . '</option>';
        foreach ($this->values as $key => $data) {
            if ($data instanceof CodeModel) {
                $extra = ('' != $this->value && $data->code == $this->value) ? ' selected=""' : '';
                $html .= '<option value="' . $data->code . '"' . $extra . '>' . $data->description . '</option>';
                continue;
            }

            if (is_array($data) && array_key_exists('code', $data) && array_key_exists('description', $data)) {
                $extra = ('' != $this->value && $data['code'] == $this->value) ? ' selected=""' : '';
                $html .= '<option value="' . $data['code'] . '"' . $extra . '>' . $data['description'] . '</option>';
                continue;
            }

            if (is_string($data)) {
                $extra = ('' != $this->value && $key == $this->value) ? ' selected=""' : '';
                $html .= '<option value="' . $key . '"' . $extra . '>' . $data . '</option>';
            }
        }

        return $html;
    }
}
