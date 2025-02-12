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
        'createreview' => 'ReviewsController@putReviewDB',
        'getreviewsproductbyid' => 'ReviewsController@getReviewsProductById',
        
        'getproductsizes' => 'ProductIdSizeIdController@getProductSizes',
        
        'getsizebyid' => 'SizesController@getSizeById',
        
        'getcolorbyid' => 'ColorsController@getColorById',
        
        'createorder' => 'OrdersController@createOrder',

        'loginuser' => 'UsersController@loginUser' ,
        'registeruser' => 'UsersController@registerUser' ,

        'getcategoriessub' => 'CategoriesSubController@getCategoriesSub' ,
    ],
    'service_container' => [
            'ProductsController' => [
            'class' => 'ProductModel',
            ],
            'ReviewsController' => [
            'class' => 'ReviewModel',
            ],
            'ProductIdSizeIdController' =>
            [
                'class' => 'ProductIdSizeIdModel',
            ], 
            'SizesController' =>
            [
                'class' => 'SizeModel',
            ], 
            'ColorsController' =>
            [
                'class' => 'ColorModel',
            ],
            'UsersController' =>
            [
                'class' => 'UserModel',
            ],
            'OrdersController' =>
            [
                'class' => 'OrderModel',
            ],
            'RecipientsController' =>
            [
                'class' => 'RecipientModel',
            ],
            'OrderIdProductIdController' =>
            [
                'class' => 'OrderIdProductIdModel',
            ],
            'CategoriesSubController' =>
            [
                'class' => 'CategorySubModel',
            ]
               
    ],
    'params' => 
        [
        'dsn' => 'mysql:host=localhost;dbname=clothes_store',
        'user' => 'root',
        'password' => '',
        ]
];