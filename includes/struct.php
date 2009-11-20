<?php

abstract class struct_array implements arrayaccess {

    public function __construct($array) {
        if(struct_is_array($array)) {
            foreach($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }
    public function offsetExists($offset) {
        return property_exists($this->$offset);
    }
    public function offsetUnset($offset) {
        return false;
    }
    public function offsetGet($offset) {
        return property_exists($this, $offset) ? $this->$offset : null;
    }

}

abstract class struct_fixed_array extends struct_array {

    public function __construct($array = null) {
        if(struct_is_array($array)) {
            foreach($array as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function __set($name, $value) {
        return false;
    }
    
    public function offsetSet($offset, $value) {
        if(property_exists($this, $offset)) {
            $this->$offset = $value;
            return true;
        }
        return false;
    }
    
}

function struct_is_array($variable) {
    return (is_array($variable) || $variable instanceof ArrayAccess);
}

?>
