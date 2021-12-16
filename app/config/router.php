<?php

$router = $di->getRouter();

// Define your routes here

$router->add("/", array(
    "controller" => "index",
    "action"     => "index",
));

$router->add("/login", array(
    "controller" => "user",
    "action"     => "login",
));

$router->add("/logout", array(
    "controller" => "user",
    "action"     => "logout",
));

$router->add("/mainpage", array(
    "controller" => "index",
    "action"     => "mainpage",
));

$router->add("/admin", array(
    "controller" => "admin",
    "action"     => "dashboard",
));

$router->add("/checkadmin", array(
    "controller" => "index",
    "action"     => "checkadmin",
));

$router->add("/admin/userdelete/[:id]", array(
    "controller" => "admin",
    "action"     => "userdelete",
    "constraint" => [
        'id' => '[0-9]'
    ]
));

$router->add("/admin/useredit/[:id]", array(
    "controller" => "admin",
    "action"     => "useredit",
    "constraint" => [
        'id' => '[0-9]'
    ]
));

$router->add("/admin/userupdate", array(
    "controller" => "admin",
    "action"     => "userupdate",
));

$router->add("/admin/useradd", array(
    "controller" => "admin",
    "action"     => "useradd",
));

$router->add("/admin/usernew", array(
    "controller" => "admin",
    "action"     => "usernew",
));

$router->handle($_SERVER['REQUEST_URI']);