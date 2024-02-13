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

use WebApp\Core\Tools\Tools;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of BaseFilter
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class BaseFilter
{

    /**
     * Submit form on every filter change.
     *
     * @var bool
     */
    public bool $autosubmit;

    /**
     * Field name.
     *
     * @var string
     */
    public string $field;

    /**
     * Filter key.
     *
     * @var string
     */
    public string $key;

    /**
     * Label to show on this filter.
     *
     * @var string
     */
    public string $label;

    /**
     * @var int
     */
    public int $ordernum;

    /**
     * @var bool
     */
    public bool $readonly = false;

    /**
     * @var int
     */
    private static int $total = 0;

    /**
     * @var mixed
     */
    protected mixed $value;

    abstract public function getDataBaseWhere(array &$where): bool;

    abstract public function render(): string;

    /**
     * @param string $key
     * @param string $field
     * @param string $label
     */
    public function __construct(string $key, string $field = '', string $label = '')
    {
        $this->autosubmit = false;
        $this->key = $key;
        $this->field = empty($field) ? $this->key : $field;
        $this->label = empty($label) ? $this->field : $label;
        $this->ordernum = ++self::$total;
        $this->value = null;
    }

    /**
     * Get the filter value
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'filter' . $this->key;
    }

    /**
     * Set value to filter
     *
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = Tools::noHtml($value);
    }

    /**
     * Set value to filter from form request
     *
     * @param Request $request
     */
    public function setValueFromRequest(Request $request): void
    {
        $this->setValue($request->request->get($this->name()));
    }

    /**
     * @return string
     */
    protected function onChange(): string
    {
        return $this->autosubmit ? ' onchange="this.form.submit();"' : '';
    }

    /**
     * @return string
     */
    protected function readonly(): string
    {
        return $this->readonly ? ' readonly="" disabled=""' : '';
    }
}
