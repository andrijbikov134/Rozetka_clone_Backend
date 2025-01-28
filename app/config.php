<?php

return [
    'routes' => [
        'getpopularproducts' => 'ProductsController@getPopularProducts',     // index.php?action=index
        'getcategories' => 'ProductsController@getCategories',   // index.php?action=delete&id=10
        'getproductsfilteredbytitle' => 'ProductsController@getProductsFilteredByTitle',   // index.php?action=delete&id=10
        'getproductbyid' => 'ProductsController@getProductById',
        'getproductswithoutfilters' => 'ProductsController@getProductsWithoutFilters',  
        'getproductswithfilters' => 'ProductsController@getProductsWithFilters',
        'getcategorysubsubtitle' => 'ProductsController@getCategorySubSubTitle',
        'getproductcharacteristics' => 'ProductsController@getProductCharacteristics',
         
        
        
        // index.php?action=delete&id=10
        'createcomment' => 'CommentsController@putCommentDB',   // index.php?action=create
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