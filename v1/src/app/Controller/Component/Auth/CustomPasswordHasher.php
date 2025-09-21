<?php
App::uses('AbstractPasswordHasher', 'Controller/Component/Auth');

class CustomPasswordHasher extends AbstractPasswordHasher {
    public function hash($password) {
        return "teste";
    }

    public function check($password, $hashedPassword) {
        return true;
    }
}
