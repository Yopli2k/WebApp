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
 * Base class for all widgets.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello <yopli2000@gmail.com>
 */
abstract class BaseWidget extends VisualItem
{

    /**
     * @var bool
     */
    public bool $autocomplete;

    /**
     * @var string
     */
    public string $fieldname;

    /**
     * @var string
     */
    public string $icon;

    /**
     * @var string
     */
    public string $onclick;

    /**
     * @var array
     */
    public array $options = [];

    /**
     * @var string
     */
    public string $readonly;

    /**
     * @var bool
     */
    public bool $required;

    /**
     * @var int
     */
    public int $tabindex;

    /**
     * @var mixed
     */
    protected mixed $value;

    /**
     * @var string
     */
    private string $type;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->autocomplete = false;
        $this->fieldname = $data['fieldname'];
        $this->icon = $data['icon'] ?? '';
        $this->onclick = $data['onclick'] ?? '';
        $this->readonly = $data['readonly'] ?? 'false';
        $this->tabindex = intval($data['tabindex'] ?? '-1');
        $this->required = isset($data['required']) && strtolower($data['required']) === 'true';
        $this->type = $data['type'];
        $this->loadOptions($data['children']);
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
        $labelHtml = '<label class="mb-0">' . $this->onclickHtml($title, $titleurl) . '</label>';

        if (empty($this->icon)) {
            return '<div class="form-group mb-2">'
                . $labelHtml
                . $this->inputHtml()
                . $descriptionHtml
                . '</div>';
        }

        return '<div class="form-group mb-2">'
            . $labelHtml
            . '<div class="input-group">'
            . '<div class="' . $this->css('input-group-prepend') . ' d-flex d-sm-none d-xl-flex">'
            . '<span class="input-group-text"><i class="' . $this->icon . ' fa-fw"></i></span>'
            . '</div>'
            . $this->inputHtml()
            . '</div>'
            . $descriptionHtml
            . '</div>';
    }

    /**
     * Get the widget type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  $model
     * @return string
     */
    public function inputHidden($model): string
    {
        $this->setValue($model);
        return '<input type="hidden" name="' . $this->fieldname . '" value="' . $this->value . '"/>';
    }

    /**
     * @param $model
     * @return string
     */
    public function plainText($model): string
    {
        $this->setValue($model);
        return $this->show();
    }

    /**
     * @param $model
     * @param Request $request
     */
    public function processFormData($model, Request $request): void
    {
        $model->{$this->fieldname} = $request->request->get($this->fieldname);
    }

    /**
     * Set custom fixed value to widget
     *
     * @param mixed $value
     */
    public function setCustomValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @param $model
     * @param string $display
     *
     * @return string
     */
    public function tableCell($model, string $display = 'left'): string
    {
        $this->setValue($model);
        $class = $this->combineClasses($this->tableCellClass('text-' . $display), $this->class);
        return '<td class="' . $class . '">' . $this->onclickHtml($this->show()) . '</td>';
    }

    /**
     * @param string $type
     * @param string $extraClass
     *
     * @return string
     */
    protected function inputHtml(string $type = 'text', string $extraClass = ''): string
    {
        $class = $this->combineClasses($this->css('form-control'), $this->class, $extraClass);
        return '<input type="' . $type . '" name="' . $this->fieldname . '" value="' . $this->value
            . '" class="' . $class . '"' . $this->inputHtmlExtraParams() . '/>';
    }

    /**
     * @return string
     */
    protected function inputHtmlExtraParams(): string
    {
        $params = $this->required ? ' required=""' : '';
        $params .= $this->readonly() ? ' readonly=""' : '';
        $params .= $this->autocomplete ? '' : ' autocomplete="off"';
        $params .= $this->tabindex >= 0 ? ' tabindex="' . $this->tabindex . '"' : '';

        return $params;
    }

    /**
     * @param array $children
     */
    protected function loadOptions(array $children): void
    {
        foreach ($children as $child) {
            if ($child['tag'] === 'option') {
                $child['text'] = html_entity_decode($child['text']);
                $this->options[] = $child;
            }
        }
    }

    /**
     * @param string $inside
     * @param string $titleurl
     *
     * @return string
     */
    protected function onclickHtml(string $inside, string $titleurl = ''): string
    {
        if (empty($this->onclick) || is_null($this->value)) {
            return empty($titleurl) ? $inside : '<a href="' . $titleurl . '">' . $inside . '</a>';
        }

        $params = str_contains($this->onclick, '?') ? '&' : '?';
        return '<a href="' . APP_ROUTE . '/' . $this->onclick . $params . 'code=' . rawurlencode($this->value)
            . '" class="cancelClickable">' . $inside . '</a>';
    }

    /**
     * @return bool
     */
    protected function readonly(): bool
    {
        if ($this->readonly === 'dinamic') {
            return !empty($this->value);
        }

        return $this->readonly === 'true';
    }

    /**
     * @param $model
     */
    protected function setValue($model): void
    {
        $this->value = @$model->{$this->fieldname};
    }

    /**
     * @return string
     */
    protected function show(): string
    {
        return is_null($this->value) ? '-' : (string)$this->value;
    }

    /**
     * @param string $initialClass
     * @param string $alternativeClass
     *
     * @return string
     */
    protected function tableCellClass(string $initialClass = '', string $alternativeClass = ''): string
    {
        foreach ($this->options as $opt) {
            $textClass = $this->getColorFromOption($opt, $this->value, 'text-');
            if ($textClass) {
                $alternativeClass = $textClass;
                break;
            }
        }

        $class = [trim($initialClass)];
        if ($alternativeClass) {
            $class[] = $alternativeClass;
        } elseif (is_null($this->value)) {
            $class[] = $this->colorToClass('warning', 'text-');
        }

        return implode(' ', $class);
    }
}
