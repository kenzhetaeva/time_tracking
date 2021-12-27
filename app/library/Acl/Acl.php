<?php

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;

class Acl
{
    public function indexAction() {

        $acl = new Memory();

        $roles = [
            'admin'  => new Role('Admin'),
            'users'  => new Role('Users'),
            'guests' => new Role('Guests'),
        ];

        foreach ($roles as $role) {
            $acl->addRole($role);
        }
    }
}