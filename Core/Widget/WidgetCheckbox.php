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
 * Description of WidgetCheckbox
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class WidgetCheckbox extends BaseWidget
{

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
        $checked = $this->value ? ' checked=""' : '';
        $id = 'checkbox' . $this->getUniqueId();
        $class = $this->combineClasses($this->css('form-check-input'), $this->class);
        $readonly = $this->readonly() ? ' onclick="return false;"' : '';
        $tabindex = $this->tabindex ? ' tabindex="' . $this->tabindex . '"' : '';

        $inputHtml = '<input type="checkbox" name="' . $this->fieldname . '" value="TRUE" id="' . $id
            . '" class="' . $class . '"' . $checked . $readonly . $tabindex . '/>';
        $labelHtml = '<label for="' . $id . '">' . ucfirst($title) . '</label>';
        $descriptionHtml = empty($description) ? '' :
            '<small class="form-text text-muted">' . ucfirst($description) . '</small>';

        return '<div class="form-group form-check pr-3 mb-2">'
            . $inputHtml
            . $labelHtml
            . $descriptionHtml
            . '</div>';
    }

    /**
     * @param $model
     * @param Request $request
     */
    public function processFormData($model, Request $request): void
    {
        $value = $request->request->get($this->fieldname);
        $model->{$this->fieldname} = null !== $value;
    }

    /**
     * @param $model
     * @return string
     */
    public function inputHidden($model): string
    {
        $this->setValue($model);
        return $this->value ? '<input type="hidden" name="' . $this->fieldname . '" value="TRUE"/>' : '';
    }

    /**
     * @param $model
     */
    protected function setValue($model): void
    {
        parent::setValue($model);
        $this->value = $this->value === 'true' || (bool)$this->value;
    }

    /**
     * @return string
     */
    protected function show(): string
    {
        if (null === $this->value) {
            return '-';
        }

        return $this->value ? 'Sí' : 'No';
    }

    /**
     * @param string $initialClass
     * @param string $alternativeClass
     * @return string
     */
    protected function tableCellClass(string $initialClass = '', string $alternativeClass = ''): string
    {
        if (false === $this->value) {
            $alternativeClass = $this->colorToClass('danger', 'text-');
        } elseif (true === $this->value) {
            $alternativeClass = $this->colorToClass('success', 'text-');
        }

        return parent::tableCellClass($initialClass, $alternativeClass);
    }
}
