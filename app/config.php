<?php

return [
    'routes' => [
        'getpopularproducts' => 'OrderIdProductIdController@getPopularProducts',     // index.php?action=index
        'getcategories' => 'ProductsController@getCategories', 
          // index.php?action=delete&id=10
        'getproductsfilteredbytitle' => 'ProductsController@getProductsFilteredByTitle',   // index.php?action=delete&id=10
        'getproductbyid' => 'ProductsController@getProductById',
        'getproductswithoutfilters' => 'ProductsController@getProductsWithoutFilters',  
        'getproductswithfilters' => 'ProductsController@getProductsWithFilters',
        'getcategorysubsubtitle' => 'ProductsController@getCategorySubSubTitle',
        'getproductcharacteristics' => 'ProductsController@getProductCharacteristics',
        'addorupdateproductindb' => 'ProductsController@addOrUpdateProductInDB',
        'deleteproductfromdb' => 'ProductsController@deleteProductFromDB',

        
        
        // index.php?action=delete&id=10
        'createreview' => 'ReviewsController@putReviewDB',
        'getreviewsproductbyid' => 'ReviewsController@getReviewsProductById',
        
        'getproductsizes' => 'ProductIdSizeIdController@getProductSizes',
        
        'getsizebyid' => 'SizesController@getSizeById',
        
        'getcolorbyid' => 'ColorsController@getColorById',
        
        'createorder' => 'OrdersController@createOrder',

        'loginuser' => 'UsersController@loginUser' ,
        'registeruser' => 'UsersController@registerUser' ,
        'getuserbyid' => 'UsersController@getUserById',
        'updateprofile' => 'UsersController@updateProfile',

        'getcategoriessub' => 'CategoriesSubController@getCategoriesSub' ,

        'getissaleproductsbyuser' => 'OrderIdProductIdController@getIsSaleProductsByUser' ,
        'getordersbyuserid' => 'OrdersController@getOrdersByUserId',


        'getallbrands' => 'FiltersController@getAllBrands', 
        'getallsizes' => 'FiltersController@getAllSizes',
        'getallmaterials' => 'FiltersController@getAllMaterials',
        'getallcolors' => 'FiltersController@getAllColors',
        'getallcountries' => 'FiltersController@getAllCountries',

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
            ],
            'FiltersController' =>
            [
                'class' => 'FilterModel',
            ]
               
    ],
    'params' => 
        [
        'dsn' => 'mysql:host=localhost;dbname=clothes_store',
        'user' => 'root',
        'password' => '',
        ]
];