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
 * Description of WidgetTime
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetTime extends BaseWidget
{

    /**
     * Indicates the max value
     *
     * @var int
     */
    protected int $max;

    /**
     * Indicates the min value
     *
     * @var int
     */
    protected int $min;

    /**
     * Indicates the step value
     * If value is major than 59, then cant edit seconds
     *
     * @var int
     */
    protected int $step;

    /**
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->max = isset($data['max']) ? (int)$data['max'] : -1;
        $this->min = isset($data['min']) ? (int)$data['min'] : -1;
        $this->step = isset($data['step']) ? (int)$data['step'] : 1;
    }

    /**
     *
     * @param  $model
     * @param Request $request
     */
    public function processFormData($model, Request $request): void
    {
        $fieldName = $request->request->get($this->fieldname);
        $model->{$this->fieldname} = empty($fieldName) ? null : $fieldName;
    }

    /**
     *
     * @param string $type
     * @param string $extraClass
     *
     * @return string
     */
    protected function inputHtml(string $type = 'time', string $extraClass = ''): string
    {
        return parent::inputHtml($type, $extraClass);
    }

    /**
     * Add extra attributes to html input field
     *
     * @return string
     */
    protected function inputHtmlExtraParams(): string
    {
        $step = ' step="' . $this->step . '"';
        $min = $this->min >= 0 ? ' min="' . $this->min . '"' : '';
        $max = $this->max >= 0 ? ' max="' . $this->max . '"' : '';
        return $min . $max . $step . parent::inputHtmlExtraParams();
    }

    /**
     *
     * @param $model
     */
    protected function setValue($model): void
    {
        parent::setValue($model);
        if (null === $this->value && $this->required) {
            $this->value = $this->min < 0
                ? $this->getTimeValue(0)
                : $this->getTimeValue($this->value);
        }
    }

    /**
     *
     * @return string
     */
    protected function show(): string
    {
        return is_null($this->value)
            ? '-'
            : $this->getTimeValue($this->value);
    }

    /**
     *
     * @param string|int $value
     *
     * @return string
     */
    protected function getTimeValue(string $value): string
    {
        $format = $this->step < 60 ? 'H:i:s' : 'H:i';
        return is_numeric($value)
            ? date($format, $value)
            : date($format, strtotime($value));
    }
}
