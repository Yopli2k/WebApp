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

use WebApp\Core\ExtendedController\PageOption;
use SimpleXMLElement;

/**
 * Description of VisualItemLoadEngine
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
class VisualItemLoadEngine
{

    /**
     * @var string
     */
    private static string $namespace = '\\WebApp\\Core\\Widget\\';

    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    /**
     * Loads a xmlview data into a PageOption.
     *
     * @param string $name
     * @param PageOption $pageOption
     *
     * @return bool
     */
    public static function installXML(string $name, PageOption $pageOption): bool
    {
        $pageOption->name = htmlspecialchars($name);
        $fileName = APP_FOLDER . '/XMLView/' . $pageOption->name . '.xml';
        if (false === file_exists($fileName)) {
            return false;
        }

        $xml = simplexml_load_string(file_get_contents($fileName));
        if ($xml === false) {
            return false;
        }

        // turns xml into an array
        $array = static::xmlToArray($xml);
        $pageOption->clear();                       // don't clear the name property
        foreach ($array['children'] as $value) {
            switch ($value['tag']) {
                case 'columns':
                    $pageOption->columns = $value['children'];
                    break;

                case 'modals':
                    $pageOption->modals = $value['children'];
                    break;

                case 'rows':
                    $pageOption->rows = $value['children'];
                    break;
            }
        }

        return true;
    }

    /**
     * Reads PageOption data and loads groups, columns, rows and widgets into selected arrays.
     *
     * @param array $columns
     * @param array $modals
     * @param array $rows
     * @param PageOption $pageOption
     */
    public static function loadArray(array &$columns, array &$modals, array &$rows, PageOption $pageOption): void
    {
        static::getGroupsColumns($pageOption->columns, $columns);
        static::getGroupsColumns($pageOption->modals, $modals);

        foreach ($pageOption->rows as $name => $item) {
            $className = static::getNamespace() . 'Row' . ucfirst($name);
            if (class_exists($className)) {
                $rowItem = new $className($item);
                $rows[$name] = $rowItem;
            }
        }

        // we always need a row type actions
        $className = static::getNamespace() . 'RowActions';
        if (!isset($rows['actions']) && class_exists($className)) {
            $rowItem = new $className([]);
            $rows['actions'] = $rowItem;
        }
    }

    /**
     * Load the column structure from the JSON
     *
     * @param array $columns
     * @param array $target
     */
    private static function getGroupsColumns(array $columns, array &$target): void
    {
        $groupClass = static::getNamespace() . 'GroupItem';
        $newGroupArray = [
            'children' => [],
            'name' => 'main',
            'tag' => 'group',
        ];

        foreach ($columns as $key => $item) {
            if ($item['tag'] === 'group') {
                $groupItem = new $groupClass($item);
                $target[$groupItem->name] = $groupItem;
            } else {
                $newGroupArray['children'][$key] = $item;
            }
        }

        // is there are loose columns, then we put it on a new group
        if (!empty($newGroupArray['children'])) {
            $groupItem = new $groupClass($newGroupArray);
            $target[$groupItem->name] = $groupItem;
        }
    }

    /**
     * Turns a xml into an array.
     * The xml file can have a tree structure, with items containing other items.
     * This function is recursive.
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    private static function xmlToArray(SimpleXMLElement $xml): array
    {
        $array = [
            'tag' => $xml->getName(),
            'children' => [],
        ];

        // attributes
        foreach ($xml->attributes() as $name => $value) {
            $array[$name] = (string)$value;
        }

        // children
        foreach ($xml->children() as $tag => $child) {
            $childAttr = $child->attributes();
            $name = static::xmlToArrayAux($tag, $childAttr);
            if ('' === $name) {
                $array['children'][] = static::xmlToArray($child);
                continue;
            }

            $array['children'][$name] = static::xmlToArray($child);
        }

        // text
        $text = (string)$xml;
        if ('' !== $text) {
            $array['text'] = $text;
        }

        return $array;
    }

    /**
     * @param string $tag
     * @param SimpleXMLElement $attributes
     * @return string
     */
    private static function xmlToArrayAux(string $tag, SimpleXMLElement $attributes): string
    {
        if (isset($attributes->name)) {
            return (string)$attributes->name;
        }

        if ($tag === 'row' && isset($attributes->type)) {
            return (string)$attributes->type;
        }

        return '';
    }
}
