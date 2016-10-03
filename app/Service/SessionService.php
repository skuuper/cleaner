<?php

namespace App\Service;

class SessionService {

    public function __construct()
    {
        if(!session_id()) {
            session_start();
        }
    }


    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }


    public function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }


    public function destroy() {
        unset($_SESSION);
    }

}