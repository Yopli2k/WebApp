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

/**
 * Description of WidgetTextarea
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class WidgetTextarea extends WidgetText
{

    /**
     * Indicates the number of rows value
     *
     * @var int
     */
    protected int $rows;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rows = (int)($data['rows'] ?? 3);
    }

    /**
     * @param $model
     * @param string $title
     * @param string $description
     * @param string $titleurl
     * @return string
     */
    public function edit($model, string $title = '', string $description = '', string $titleurl = ''): string
    {
        $this->setValue($model);
        $descriptionHtml = empty($description) ? '' : '<small class="form-text text-muted">' . $description . '</small>';
        $inputHtml = $this->inputHtml();
        $labelHtml = '<label class="mb-0">' . $this->onclickHtml($title, $titleurl) . '</label>';

        return '<div class="form-group mb-2">'
            . $labelHtml
            . $inputHtml
            . $descriptionHtml
            . '</div>';
    }

    /**
     * @param $model
     * @param string $display
     * @return string
     */
    public function tableCell($model, string $display = 'left'): string
    {
        $limit = 60;
        $this->setValue($model);
        $class = 'text-' . $display;
        $value = $this->show();
        $final = mb_strlen($value) > $limit ? mb_substr($value, 0, $limit) . '...' : $value;

        return mb_strlen($value) > $limit ?
            '<td class="' . $this->tableCellClass($class) . '" title="' . $value . '">' . $this->onclickHtml($final) . '</td>' :
            '<td class="' . $this->tableCellClass($class) . '">' . $this->onclickHtml($final) . '</td>';
    }

    /**
     * @param string $type
     * @param string $extraClass
     * @return string
     */
    protected function inputHtml(string $type = 'text', string $extraClass = ''): string
    {
        $class = $this->combineClasses($this->css('form-control'), $this->class, $extraClass);
        return '<textarea rows="' . $this->rows . '" name="' . $this->fieldname . '" class="' . $class . '"'
            . $this->inputHtmlExtraParams() . '>' . $this->value . '</textarea>';
    }
}
