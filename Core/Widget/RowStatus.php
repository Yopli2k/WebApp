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
 * Description of RowStatus
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class RowStatus extends VisualItem
{
    /** @var string */
    public string $fieldname;

    /** @var array */
    public array $options;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->fieldname = empty($data['fieldname']) ? '' : $data['fieldname'];
        $this->options = $data['children'] ?? [];
    }

    /**
     * @return string
     */
    public function legend(): string
    {
        $trs = '';
        foreach ($this->options as $opt) {
            if (false === empty($opt['title'])) {
                $trs .= '<tr class="' . $this->colorToClass($opt['color'], 'table-') . '">'
                    . '<td class="text-center">' . ucfirst($opt['title']) . '</td>'
                    . '</tr>';
            }
        }

        return empty($trs) ? '' : '<table class="table mb-0">' . $trs . '</table>';
    }

    /**
     * @param mixed $model
     * @param string $classPrefix
     * @return string
     */
    public function trClass(mixed $model, string $classPrefix = 'table-'): string
    {
        foreach ($this->options as $opt) {
            $fieldname = $opt['fieldname'] ?? $this->fieldname;
            $value = $model->{$fieldname} ?? null;
            $rowColor = $this->getColorFromOption($opt, $value, $classPrefix);
            if (false === empty($rowColor)) {
                return $rowColor;
            }
        }
        return '';
    }

    /**
     * @param mixed $model
     * @return string
     */
    public function trTitle(mixed $model): string
    {
        foreach ($this->options as $opt) {
            $fieldname = $opt['fieldname'] ?? $this->fieldname;
            $value = $model->{$fieldname} ?? null;
            if ($this->applyOperatorFromOption($opt, $value)) {
                return $opt['title'] ?? '';
            }
        }

        return '';
    }
}
