<?php

return [
    'routes' => [
        'index' => 'ProductsController@index',     // index.php?action=index
        'create' => 'ProductsController@create',   // index.php?action=create
        'remove' => 'ProductsController@remove',   // index.php?action=delete&id=10
        'loadfromapi' => 'ProductsController@loadfromapi',   // index.php?action=delete&id=10
        'removeallusers' => 'ProductsController@removeallusers',   // index.php?action=delete&id=10
    ],
    'service_container' => [
            'ProductsController' => [
            'class' => 'ProductModel',
            ],  
    ],
    'params' => 
        [
        'dsn' => 'mysql:host=database;dbname=clothes_store',
        'user' => 'user',
        'password' => 'password',
        ]
];