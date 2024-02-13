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
 * Description of AutocompleteFilter
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class AutocompleteFilter extends BaseFilter
{

    /**
     * @var string
     */
    public string $fieldcode;

    /**
     * @var string
     */
    public string $fieldtitle;

    /**
     * @var string
     */
    public string $table;

    /**
     * @var array
     */
    public array $where;

    /**
     * @param string $key
     * @param string $field
     * @param string $label
     * @param string $table
     * @param string $fieldcode
     * @param string $fieldtitle
     * @param array $where
     */
    public function __construct(string $key, string $field, string $label, string $table, string $fieldcode = '', string $fieldtitle = '', array $where = [])
    {
        parent::__construct($key, $field, $label);
        $this->autosubmit = true;
        $this->table = $table;
        $this->fieldcode = empty($fieldcode) ? $this->field : $fieldcode;
        $this->fieldtitle = empty($fieldtitle) ? $this->fieldcode : $fieldtitle;
        $this->where = $where;
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
        $html = '<div class="col-sm-3 col-lg-2">'
            . '<input type="hidden" name="' . $this->name() . '" value="' . $this->value . '"/>'
            . '<div class="form-group">'
            . '<div class="input-group">';

        if ('' === $this->value || null === $this->value) {
            $html .= '<span class="input-group-prepend" title="' . $this->label . '">'
                . '<span class="input-group-text">'
                . '<i class="fas fa-search fa-fw" aria-hidden="true"></i>'
                . '</span>'
                . '</span>';
        } else {
            $formName = 'this.form.' . $this->name();
            $html .= '<span class="input-group-prepend" title="' . $this->label . '">'
                . '<button class="btn btn-warning" type="button" onclick="' . $formName . '.value = \'\'; this.form.submit();">'
                . '<i class="fas fa-times fa-fw" aria-hidden="true"></i>'
                . '</button>'
                . '</span>';
        }

        $html .= '<input type="text" value="' . $this->getDescription() . '" class="form-control filter-autocomplete"'
            . ' data-name="' . $this->name() . '" data-field="' . $this->field . '" data-source="' . $this->table . '" data-fieldcode="' . $this->fieldcode
            . '" data-fieldtitle="' . $this->fieldtitle . '" placeholder = "' . $this->label . '" autocomplete="off" ' . $this->readonly() . '/>'
            . '</div>'
            . '</div>'
            . '</div>';

        return $html;
    }

    /**
     * @return string
     */
    protected function getDescription(): string
    {
        $codeModel = new CodeModel();
        return $codeModel->getDescription(
            $this->table, $this->fieldcode, $this->value, $this->fieldtitle
        );
    }
}
