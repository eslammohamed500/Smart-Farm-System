<?php

class Tool {
    public $id;
    public $name;
    public $description;
    public $quantity;
    public $availability; // available, unavailable

    public function __construct($id = null, $name = "", $quantity = 0) {
        $this->id = $id;
        $this->name = $name;
        $this->quantity = $quantity;
    }
}