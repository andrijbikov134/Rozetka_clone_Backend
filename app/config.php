<?php

return [
    'routes' => [
        'getallproducts' => 'ProductsController@getAllProducts',     // index.php?action=index
        'createcomment' => 'CommentsController@putCommentDB',   // index.php?action=create
        'getcategories' => 'ProductsController@getCategories',   // index.php?action=delete&id=10
        'getproductsfilteredbytitle' => 'ProductsController@getProductsFilteredByTitle',   // index.php?action=delete&id=10
        'removeallusers' => 'ProductsController@removeallusers',   // index.php?action=delete&id=10
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
        'dsn' => 'mysql:host=localhost;dbname=clothes_store',
        'user' => 'root',
        'password' => '',
        ]
];