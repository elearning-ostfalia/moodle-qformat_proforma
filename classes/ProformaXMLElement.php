<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * ProformaXMLElement extended for simpler namespace handling
 *
 * @package    qformat_proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


class ProformaXMLElement /* extends SimpleXMLElement */ implements ArrayAccess, Countable {
    /**  Simple XML element instance */
    private $element = null;

    /** @var null ProFormA namespace used in SimpleXMLElement */
    private $namespace = null;

    public function __construct(SimpleXMLElement $element, $namespace) {
        $this->element = $element;
        $this->namespace = $namespace;
    }

    public function __get($name) {
        // echo "Getting '$name'";
        // $children = $this->element->children($this->namespace);
        $child = $this->element->children($this->namespace)->$name;
        // echo " '" . get_class($child) . "'";
        // echo " count=" . count($child) . PHP_EOL;
        switch (count($child)) {
            case 0:
                break;
            case 1:
                return new ProformaXMLElement($child, $this->namespace);
            default:
                // More than 1 element.
                $array = array();
                for ($i = 0; $i < count($child); $i++) {
                    $array[] = new ProformaXMLElement($child[$i], $this->namespace);
                }
                return $array;
        }

        return $child;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset) {
        // TODO: Implement offsetExists() method.
        throw new coding_exception('not implemented offsetExists');
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value) {
        // TODO: Implement offsetSet() method.
        throw new coding_exception('not implemented offsetSet');
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset) {
        // TODO: Implement offsetUnset() method.
        throw new coding_exception('not implemented offsetUnset');
    }

    /** OFFICIAL SimpleXMLElement
     * Class provides access to children by position, and attributes by name
     * @access private Method not callable directly, stub exists for typehint only
     * @param string|int $offset
     * @return SimpleXMLElement Either a named attribute or an element from a list of children
     */
    public function offsetGet ($offset) {
        return $this->element->attributes()->$offset;
    }

    /**
     * @inheritDoc
     */
    public function count() {
        return count($this->element);
    }

    public function asXML ($filename = null) {
        return $this->element->asXML();
    }

    public function addChild ($name, $value = null, $namespace = null) {
        return $this->element->addChild($name, $value, $namespace);
    }

    public function __toString () {
        return $this->element->__toString();
    }
}