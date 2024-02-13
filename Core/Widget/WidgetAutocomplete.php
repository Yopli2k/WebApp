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
 * Description of WidgetAutocomplete
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello <yopli2000@gmail.com>
 */
class WidgetAutocomplete extends WidgetSelect
{

    /**
     * Name of the field by which it is filtered.
     *
     * @var string
     */
    protected $fieldfilter;

    /**
     * Descriptive text of the selected value
     *
     * @var ?string
     */
    protected ?string $selected = null;

    /**
     * Indicates whether a value should be selected strictly from the list
     * of values or whether the user can enter a new or different value
     * from the list.
     *
     * @var bool
     */
    public bool $strict = true;

    /**
     * @param $model
     * @param string $title
     * @param string $description
     * @param string $titleurl
     *
     * @return string
     */
    public function edit($model, string $title = '', string $description = '', string $titleurl = ''): string
    {
        $this->setValue($model);
        $descriptionHtml = empty($description) ? '' : '<small class="form-text text-muted">' . $description . '</small>';
        $inputHtml = $this->inputHtml();
        $labelHtml = '<label class="mb-0">' . $this->onclickHtml($title, $titleurl) . '</label>';

        if ('' === $this->value || null === $this->value) {
            return '<input type="hidden" name="' . $this->fieldname . '" value="' . $this->value . '"/>'
                . '<div class="form-group mb-2">'
                . $labelHtml
                . '<div class="input-group">'
                . '<div class="' . $this->css('input-group-prepend') . '">'
                . '<span class="input-group-text"><i class="fas fa-search fa-fw"></i></span>'
                . '</div>'
                . $inputHtml
                . '</div>'
                . $descriptionHtml
                . '</div>';
        }

        return '<input type="hidden" name="' . $this->fieldname . '" value="' . $this->value . '"/>'
            . '<div class="form-group mb-2">'
            . $labelHtml
            . '<div class="input-group">'
            . $this->inputGroupClearBtn()
            . $inputHtml
            . '</div>'
            . $descriptionHtml
            . '</div>';
    }

    /**
     * Set a descriptive text for the selected value
     *
     * @param string $text
     */
    public function setSelected(string $text): void
    {
        $this->selected = $text;
    }

    /**
     * Get the descriptive text of the selected value
     *
     * @return ?string
     */
    protected function getSelected(): ?string
    {
        return empty($this->selected)
            ? static::$codeModel->getDescription($this->source, $this->fieldcode, $this->value, $this->fieldtitle)
            : $this->selected;
    }

    /**
     * @return string
     */
    protected function inputGroupClearBtn(): string
    {
        if ($this->readonly()) {
            return '<div class="' . $this->css('input-group-prepend') . '">'
                . '<span class="input-group-text"><i class="fas fa-search fa-fw"></i></span>'
                . '</div>';
        }

        return '<div class="' . $this->css('input-group-prepend') . '">'
            . '<button class="btn btn-warning" type="button" onclick="this.form.' . $this->fieldname . '.value = \'\'; this.form.submit();">'
            . '<i class="fas fa-times" aria-hidden="true"></i>'
            . '</button>'
            . '</div>';
    }

    /**
     * @param string $type
     * @param string $extraClass
     * @return string
     */
    protected function inputHtml(string $type = 'text', string $extraClass = 'widget-autocomplete'): string
    {
        $class = $this->combineClasses($this->css('form-control'), $this->class, $extraClass);
        return '<input type="' . $type . '" value="' . $this->getSelected() . '" class="' . $class . '"'
            . ' data-field="' . $this->fieldname . '"'
            . ' data-source="' . $this->source . '"'
            . ' data-fieldcode="' . $this->fieldcode . '"'
            . ' data-fieldtitle="' . $this->fieldtitle . '"'
            . ' data-fieldfilter="' . $this->fieldfilter . '"'
            . ' data-strict="' . $this->strictStr() . '"'
            . $this->inputHtmlExtraParams() . '/>';
    }

    /**
     * Set datasource data and Load data from Model into values array
     */
    protected function setSourceData(array $child, bool $loadData = true): void
    {
        // The values are filled in automatically by the view controller
        // according to the information entered by the user.
        parent::setSourceData($child, false);
        $this->fieldfilter = $child['fieldfilter'] ?? '';
        $this->strict = !isset($child['strict']) || $child['strict'] == 'true';
    }

    /**
     * @return string
     */
    protected function strictStr(): string
    {
        return $this->strict ? '1' : '0';
    }
}
