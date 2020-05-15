<?php


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