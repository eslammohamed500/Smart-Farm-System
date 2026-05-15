<?php

class Shift {
    public $id;
    public $title;
    public $start_time;
    public $end_time;
    public $status; // open, closed, cancelled

    public function __construct($id = null, $title = "", $start = "", $end = "") {
        $this->id = $id;
        $this->title = $title;
        $this->start_time = $start;
        $this->end_time = $end;
    }
}