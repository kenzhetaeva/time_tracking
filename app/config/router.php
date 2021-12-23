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

$router->add("/changepassword", array(
    "controller" => "user",
    "action"     => "changepassword",
));

$router->add("/user/changepassword", array(
    "controller" => "user",
    "action"     => "changeuserpassword",
));

$router->add("/mainpage", array(
    "controller" => "index",
    "action"     => "mainpage",
));

$router->add("/admin", array(
    "controller" => "admin",
    "action"     => "dashboard",
));

$router->add("/admin/staffmonth/[:id]", array(
    "controller" => "admin",
    "action"     => "staffmonth",
    "constraint" => [
        'id' => '[0-9]'
    ]
));

$router->add("/admin/changestaffhours/[:id]/[:day]/[:month]/[:year]", array(
    "controller" => "admin",
    "action"     => "changestaffhours",
    "constraint" => [
        'id' => '[0-9]',
        'day' => '[0-9]',
        'month' => '[0-9]',
        'year' => '[0-9]'
    ]
));

$router->add('/admin/changeworkhour', [
    "controller" => "admin",
    "action"     => "changeworkhour",
]);

$router->add('/admin/editworkhour', [
    "controller" => "admin",
    "action"     => "editworkhour",
]);


$router->add("/admin/staffhoursedit", [
    "controller" => "admin",
    "action" => "staffhoursedit"
]);

$router->add("/checkadmin", array(
    "controller" => "index",
    "action"     => "checkadmin",
));

$router->add("/admin/showlatecomers", array(
    "controller" => "admin",
    "action"     => "showlatecomers",
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

$router->add("/start", array(
    "controller" => "index",
    "action"     => "start",
));

$router->add("/stop", array(
    "controller" => "index",
    "action"     => "stop",
));

$router->handle($_SERVER['REQUEST_URI']);