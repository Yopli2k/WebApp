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
namespace WebApp\Core\Widget;

use Symfony\Component\HttpFoundation\Request;

/**
 * Description of WidgetDate
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello <yopli2000@gmail.com>
 */
class WidgetDate extends BaseWidget
{

    /**
     * @param $model
     * @param Request $request
     */
    public function processFormData($model, Request $request): void
    {
        $fieldName = $request->request->get($this->fieldname);
        $model->{$this->fieldname} = empty($fieldName) ? null : $fieldName;
    }

    /**
     * @param string $type
     * @param string $extraClass
     *
     * @return string
     */
    protected function inputHtml(string $type = 'date', string $extraClass = ''): string
    {
        $class = $this->combineClasses($this->css('form-control'), $this->class, $extraClass);
        $value = empty($this->value) ? '' : date('Y-m-d', strtotime($this->value));
        return '<input type="' . $type . '" name="' . $this->fieldname . '" value="' . $value
            . '" class="' . $class . '"' . $this->inputHtmlExtraParams() . '/>';
    }

    /**
     * @return string
     */
    protected function show(): string
    {
        if (is_null($this->value)) {
            return '-';
        }

        if (is_numeric($this->value)) {
            return date('d-m-Y', $this->value);
        }

        return date('d-m-Y', strtotime($this->value));
    }
}
