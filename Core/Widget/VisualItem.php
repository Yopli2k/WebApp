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
 * Description of VisualItem
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class VisualItem
{

    /**
     * @var string
     */
    public string $class;

    /**
     * Identifies the object with a defined name in the view
     *
     * @var string
     */
    public string $id;

    /**
     * Name defined in the view as key
     *
     * @var string
     */
    public string $name;

    /**
     * @var int
     */
    protected static int $uniqueId = -1;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->class = $data['class'] ?? '';
        $this->id = $data['id'] ?? '';
        $this->name = $data['name'] ?? '';
    }

    /**
     * Return the class name for selected color.
     *
     * @param string $color
     * @param string $prefix
     * @return string
     */
    protected function colorToClass(string $color, string $prefix): string
    {
    $colors = ['danger', 'dark', 'info', 'light', 'outline-danger', 'outline-dark', 'outline-info', 'outline-light', 'outline-primary', 'outline-secondary', 'outline-success', 'outline-warning', 'primary', 'secondary', 'success', 'warning'];
        if (in_array($color, $colors)) {
            return $prefix . $color;
        }
        return $color;
    }

    /**
     * Calculate color from option configuration
     *
     * @param string[] $option
     * @param mixed $value
     * @param string $prefix
     * @return string
     */
    public function getColorFromOption(array $option, mixed $value, string $prefix): string
    {
        return $this->applyOperatorFromOption($option, $value) ? $this->colorToClass($option['color'], $prefix) : '';
    }

    /**
     * @param string[] $option
     * @param mixed $value
     * @return bool
     */
    protected function applyOperatorFromOption(array $option, mixed $value): bool
    {
        $text = $option['text'] ?? '';

        $applyOperator = '';
        $operators = ['>', 'gt:', 'gte:', '<', 'lt:', 'lte:', '!', 'neq:', 'like:', 'null:', 'notnull:'];
        foreach ($operators as $operator) {
            if (str_starts_with($text, $operator)) {
                $applyOperator = $operator;
                break;
            }
        }

        $matchValue = substr($text, strlen($applyOperator));
        return match ($applyOperator) {
            '>',
            'gt:'   => (float)$value > (float)$matchValue,
            'gte:'  => (float)$value >= (float)$matchValue,
            '<',
            'lt:'   => (float)$value < (float)$matchValue,
            'lte:'  => (float)$value <= (float)$matchValue,
            '!',
            'neq:'  => $value != $matchValue,
            'like:' => false !== stripos($value, $matchValue),
            'null:' => null === $value,
            'notnull:' => null !== $value,
            default => ($matchValue == $value),
        };
    }

    /**
     * @param array $classes
     * @return string
     */
    protected function combineClasses(...$classes): string
    {
        $mix = [];
        foreach ($classes as $class) {
            if (!empty($class)) {
                $mix[] = $class;
            }
        }

        return implode(' ', $mix);
    }

    /**
     * Returns equivalent css class to $class. To extend in plugins.
     *
     * @param string $class
     * @return string
     */
    protected function css(string $class): string
    {
        return $class;
    }

    /**
     * @return int
     */
    protected function getUniqueId(): int
    {
        static::$uniqueId++;
        return static::$uniqueId;
    }
}
