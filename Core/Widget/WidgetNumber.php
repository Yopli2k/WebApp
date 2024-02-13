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

use WebApp\Core\Tools\NumberTools;
use Symfony\Component\HttpFoundation\Request;

/*
use FacturaScripts\Core\Base\NumberTools;
*/

/**
 * Description of WidgetNumber
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class WidgetNumber extends BaseWidget
{

    /**
     * @var int
     */
    public int $decimal;

    /**
     * Indicates the max value
     *
     * @var string
     */
    public string $max;

    /**
     * Indicates the min value
     *
     * @var string
     */
    public string $min;

    /**
     * Indicates the step value
     *
     * @var string
     */
    public string $step;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->decimal = (int)($data['decimal'] ?? 0);
        $this->max = $data['max'] ?? '';
        $this->min = $data['min'] ?? '';
        $this->step = $data['step'] ?? 'any';
    }

    /**
     * @param $model
     * @param Request $request
     */
    public function processFormData($model, Request $request): void
    {
        $model->{$this->fieldname} = (float)$request->request->get($this->fieldname);
    }

    /**
     * @param string $type
     * @param string $extraClass
     * @return string
     */
    protected function inputHtml(string $type = 'number', string $extraClass = ''): string
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
        $min = $this->min !== '' ? ' min="' . $this->min . '"' : '';
        $max = $this->max !== '' ? ' max="' . $this->max . '"' : '';
        return $min . $max . $step . parent::inputHtmlExtraParams();
    }

    /**
     * @param $model
     */
    protected function setValue($model): void
    {
        parent::setValue($model);
        if (null === $this->value && $this->required) {
            $this->value = empty($this->min) ? 0 : (float)$this->min;
        }
    }

    /**
     * @return string
     */
    protected function show(): string
    {
        return is_null($this->value) ? '-' : NumberTools::number($this->value, $this->decimal);
    }

    /**
     * @param string $initialClass
     * @param string $alternativeClass
     * @return string
     */
    protected function tableCellClass(string $initialClass = '', string $alternativeClass = ''): string
    {
        $initialClass .= ' text-nowrap';
        return parent::tableCellClass($initialClass, $alternativeClass);
    }
}
