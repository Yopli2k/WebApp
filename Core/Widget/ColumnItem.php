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
 * Description of ColumnItem
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
class ColumnItem extends VisualItem
{

    /**
     * Additional text that explains the field to the user
     *
     * @var string
     */
    public string $description;

    /**
     * State and alignment of the display configuration
     * (left|right|center|none)
     *
     * @var string
     */
    public string $display;

    /**
     * @var int
     */
    public int $numcolumns;

    /**
     * @var int
     */
    public int $order;

    /**
     * @var string
     */
    public string $title;

    /**
     * @var string
     */
    public string $titleurl;

    /**
     * Field display object configuration
     *
     * @var mixed
     */
    public mixed $widget;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->description = $data['description'] ?? '';
        $this->display = $data['display'] ?? 'left';
        $this->numcolumns = isset($data['numcolumns']) ? (int)$data['numcolumns'] : 0;
        $this->order = isset($data['order']) ? (int)$data['order'] : 0;
        $this->title = ucwords($data['title'] ?? $this->name);
        $this->titleurl = $data['titleurl'] ?? '';
        $this->loadWidget($data['children']);
    }

    /**
     * @param mixed $model
     * @param bool $onlyField
     * @return string
     */
    public function edit(mixed $model, bool $onlyField = false): string
    {
        if ($this->hidden()) {
            return $this->widget->inputHidden($model);
        }

        // para los checkbox forzamos el col-sm-auto
        $colAuto = $this->widget->getType() === 'checkbox' ? 'col-sm-auto' : 'col-sm';

        $divClass = $this->numcolumns > 0 ? $this->css('col-md-') . $this->numcolumns : $this->css($colAuto);
        $divID = empty($this->id) ? '' : ' id="' . $this->id . '"';
        $editHtml = $onlyField ? $this->widget->edit($model) : $this->widget->edit($model, $this->title, $this->description, $this->titleurl);
        return '<div' . $divID . ' class="' . $divClass . '">'
            . $editHtml
            . '</div>';
    }

    /**
     * Returns CSS percentage width
     *
     * @return string
     */
    public function htmlWidth(): string
    {
        if ($this->numcolumns < 1 || $this->numcolumns > 11) {
            return '100%';
        }

        return round((100.00 / 12 * $this->numcolumns), 5) . '%';
    }

    /**
     * Indicates if the column is hidden
     *
     * @return bool
     */
    public function hidden(): bool
    {
        return ($this->display === 'none');
    }

    /**
     * @param mixed $model
     * @param Request $request
     */
    public function processFormData(mixed $model, Request $request): void
    {
        $this->widget->processFormData($model, $request);
    }

    /**
     * @param mixed $model
     * @return string
     */
    public function tableCell(mixed $model): string
    {
        return $this->hidden() ? '' : $this->widget->tableCell($model, $this->display);
    }

    /**
     * @return string
     */
    public function tableHeader(): string
    {
        if ($this->hidden()) {
            return '';
        }

        if (empty($this->titleurl)) {
            return '<th class="text-' . $this->display . '">' . $this->title . '</th>';
        }

        return '<th class="text-' . $this->display . '">'
            . '<a href="' . $this->titleurl . '">' . $this->title . '</a>'
            . '</th>';
    }

    protected function loadWidget(array $children): void
    {
        foreach ($children as $child) {
            if ($child['tag'] !== 'widget') {
                continue;
            }

            $className = VisualItemLoadEngine::getNamespace() . 'Widget' . ucfirst($child['type']);
            if (class_exists($className)) {
                $this->widget = new $className($child);
                break;
            }

            $defaultWidget = VisualItemLoadEngine::getNamespace() . 'WidgetText';
            $this->widget = new $defaultWidget($child);
            break;
        }
    }
}
