<?php

class Notification {
    public $id;
    public $user_id;
    public $message;
    public $is_read;
    public $created_at;

    public function __construct($user_id = null, $message = "") {
        $this->user_id = $user_id;
        $this->message = $message;
    }
}