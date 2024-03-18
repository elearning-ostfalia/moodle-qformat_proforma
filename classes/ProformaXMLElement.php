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
 * ProformaXMLElement: SimpleXMLElement extended for handling one fixed namespace
 *
 * @package    qformat_proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/**
 * Class ProformaXMLElement.
 * In order to simplify accessing xml elements with namespace
 * this class extends the SimpleXMLElement class
 * by keeping an internal namespace attribute. Thus access with the default
 * namespace (set in constructor) is as simple as accessing data without namespace
 * with the SimpleXMLElement.
 * Unfortunately almost the whole interface of SimpleXMLElement must be
 * recreated. Extending SimpleXMLElement is not possible.
 * ...
 * (simply removing the namespace prefix would be easier but not safe)
 *
 * @package    qformat_proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */
class ProformaXMLElement  /* extends SimpleXMLElement */
        implements ArrayAccess, Countable, Iterator {
    /**  Simple XML element instance */
    private $element = null;

    /** @var null ProFormA namespace used in SimpleXMLElement */
    private $namespace = null;

    /** @var null cursor for iterating */
    private $cursor = null;

    /**
     * ProformaXMLElement constructor.
     *
     * @param SimpleXMLElement $element
     * @param $namespace
     */
    public function __construct(SimpleXMLElement $element, $namespace) {
        $this->element = $element;
        $this->namespace = $namespace;
    }

    /**
     * returns the child element(s) with a given tagname
     * @param $name
     * @return array|ProformaXMLElement|SimpleXMLElement
     */
    public function __get($name) {
        $child = $this->element->children($this->namespace)->$name;
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
     * @param mixed $offset
     * @return bool|void
     * @throws coding_exception
     */
    public function offsetExists($offset) : bool {
        throw new coding_exception('not implemented offsetExists');
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws coding_exception
     */
    public function offsetSet($offset, $value) : void {
        throw new coding_exception('not implemented offsetSet');
    }

    /**
     * @param mixed $offset
     * @throws coding_exception
     */
    public function offsetUnset($offset) : void {
        throw new coding_exception('not implemented offsetUnset');
    }

    /**
     * Class provides access to children by position, and attributes by name
     * delegate to element member
     * @param mixed $offset
     * @return mixed|SimpleXMLElement Either a named attribute or an element from a list of children
     */
    public function offsetGet ($offset) : mixed {
        return $this->element->attributes()->$offset;
    }

    /**
     * return number of elements
     * @return int
     */
    public function count() : int {
        return count($this->element);
    }

    /**
     * add child element
     * @param $name: name of chile
     * @param null $value: value of child
     * @param null $namespace: namespace of child
     * @return SimpleXMLElement: new element
     */
    public function addChild ($name, $value = null, $namespace = null) {
        return $this->element->addChild($name, $value, $namespace);
    }

    /**
     * convert to xml string
     * @param null $filename
     * @return mixed
     */
    public function asXML($filename = null) {
        return $this->element->asXML();
    }

    /**
     * convert to string
     * @return string
     */
    public function __toString () {
        return $this->element->__toString();
    }

    /**
     * Iterator interface: return element at cursor position
     * @return mixed|ProformaXMLElement
     */
    public function current() : mixed {
        return new ProformaXMLElement($this->cursor, $this->namespace);
    }

    /**
     * Iterator interface: increment cursor
     */
    public function next() : void {
        $this->cursor = null;
    }

    /**
     * Iterator interface?
     * @return bool|float|int|string|void|null
     * @throws coding_exception
     */
    public function key() : mixed {
        // TODO: Implement key() method.
        throw new coding_exception('not implemented key');
    }

    /**
     * Iterator interface: is cursor valid?
     * @return bool
     */
    public function valid() : bool {
         return ($this->cursor != null);
    }

    /**
     * Iterator interface: reset cursor
     */
    public function rewind() : void {
        $this->cursor = $this->element;
    }

    /**
     * forward children
     * @param null $ns
     * @param bool $is_prefix
     */
    public function children () {
        return $this->element->children($this->namespace);
    }
}