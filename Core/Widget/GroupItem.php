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

use WebApp\Core\Widget\ColumnItem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of GroupItem
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
class GroupItem extends VisualItem
{

    /**
     * Define the columns that the group includes
     *
     * @var ColumnItem[]
     */
    public array $columns = [];

    /**
     * Description
     *
     * @var string
     */
    protected string $description;

    /**
     * Icon used as the value or accompanying the group title
     *
     * @var string
     */
    public string $icon;

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
    public string $valign;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->description = $data['description'] ?? '';
        $this->icon = $data['icon'] ?? '';
        $this->numcolumns = isset($data['numcolumns']) ? (int)$data['numcolumns'] : 0;
        $this->order = isset($data['order']) ? (int)$data['order'] : 0;
        $this->title = $data['title'] ?? '';
        $this->valign = $data['valign'] ?? '';
        $this->loadColumns($data['children']);
    }

    /**
     * @param mixed $model
     * @param bool $forceReadOnly
     * @param bool $onlyField
     * @return string
     */
    public function edit(mixed $model, bool $forceReadOnly = false, bool $onlyField = false): string
    {
        $divClass = $this->numcolumns > 0 ? $this->css('col-md-') . $this->numcolumns : $this->css('col');
        $divId = empty($this->id) ? '' : ' id="' . $this->id . '"';
        $rowClass = $this->css('form-row') . ' ' . $this->valign();

        $html = '<div' . $divId . ' class="' . $divClass . '"><div class="' . $rowClass . '">';
        if ($this->title) {
            $html .= $this->legend();
        }

        foreach ($this->columns as $col) {
            if ($forceReadOnly) {
                $col->widget->readonly = 'true';
            }
            $html .= $col->edit($model, $onlyField);
        }

        return $html . '</div></div>';
    }

    /**
     * @param mixed $model
     * @param string $viewName
     * @return string
     */
    public function modal(mixed $model, string $viewName): string
    {
        $icon = empty($this->icon) ? '' : '<i class="' . $this->icon . ' fa-fw"></i> ';
        $html = '<form id="formModal' . $this->getUniqueId() . '" method="post" enctype="multipart/form-data">'
            . '<input type="hidden" name="activetab" value="' . $viewName . '"/>'
            . '<input type="hidden" name="code" value=""/>'
            . '<div class="modal" id="modal' . $this->name . '" tabindex="-1" role="dialog">'
            . '<div class="modal-dialog ' . $this->class . '" role="document">'
            . '<div class="modal-content">'
            . '<div class="modal-header">'
            . '<h5 class="modal-title">' . $icon . $this->title . '</h5>'
            . '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'
            . '<span aria-hidden="true">&times;</span>'
            . '</button>'
            . '</div>'
            . '<div class="modal-body">'
            . '<div class="' . $this->css('row') . '">';

        foreach ($this->columns as $col) {
            $html .= $col->edit($model);
        }

        $html .= '</div>'
            . '</div>'
            . '<div class="modal-footer">'
            . '<button type="button" class="btn btn-secondary" data-dismiss="modal">'
            . 'Cancelar'
            . '</button>'
            . '<button type="submit" name="action" value="' . $this->name . '" class="btn btn-primary">'
            . 'Aceptar'
            . '</button>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</form>';

        return $html;
    }

    /**
     * @param mixed $model
     * @param Request $request
     */
    public function processFormData(mixed &$model, Request $request): void
    {
        foreach ($this->columns as $col) {
            $col->processFormData($model, $request);
        }
    }

    /**
     * Sorts the columns
     *
     * @param ColumnItem $column1
     * @param ColumnItem $column2
     *
     * @return int
     */
    public static function sortColumns(ColumnItem $column1, ColumnItem $column2): int
    {
        if ($column1->order === $column2->order) {
            return 0;
        }

        return $column1->order < $column2->order ? -1 : 1;
    }

    /**
     * @return string
     */
    protected function legend(): string
    {
        $icon = empty($this->icon) ? '' : '<i class="' . $this->icon . ' fa-fw"></i> ';
        if (empty($this->description)) {
            return '<legend class="text-info mt-2 mb-0">' . $icon . $this->title . '</legend>';
        }

        return '<legend class="text-info mt-2 mb-1">' . $icon . $this->title . '</legend>'
            . '<small class="form-text text-muted w-100 mb-2">' . $this->description . '</small>';
    }

    /**
     * @param array $children
     */
    protected function loadColumns(array $children): void
    {
        $columnClass = VisualItemLoadEngine::getNamespace() . 'ColumnItem';
        foreach ($children as $child) {
            if ($child['tag'] !== 'column') {
                continue;
            }

            $columnItem = new $columnClass($child);
            $this->columns[$columnItem->name] = $columnItem;
        }

        uasort($this->columns, ['self', 'sortColumns']);
    }

    /**
     * @return string
     */
    protected function valign(): string
    {
        return match ($this->valign) {
            'bottom' => 'align-items-end',
            'center' => 'align-items-center',
            default => '',
        };

    }
}
