<?php

return [
    'routes' => [
        'getpopularproducts' => 'ProductsController@getPopularProducts',     // index.php?action=index
        'createcomment' => 'CommentsController@putCommentDB',   // index.php?action=create
        'getcategories' => 'ProductsController@getCategories',   // index.php?action=delete&id=10
        'getproductsfilteredbytitle' => 'ProductsController@getProductsFilteredByTitle',   // index.php?action=delete&id=10
        'getproductbyid' => 'ProductsController@getProductById',   // index.php?action=delete&id=10
    ],
    'service_container' => [
            'ProductsController' => [
            'class' => 'ProductModel',
            ],
            'CommentsController' => [
            'class' => 'CommentModel',
            ],    
    ],
    'params' => 
        [
        'dsn' => 'mysql:host=database;dbname=clothes_store',
        'user' => trim(file_get_contents('/run/secrets/db_user')),
        'password' => trim(file_get_contents('/run/secrets/db_password')),
        ]
];