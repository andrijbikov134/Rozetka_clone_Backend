<?php

// $log = date('Y-m-d H:i:s') . 'class name' . $class_name . '  ' . $method;
// file_put_contents('D:/log.txt', $log . PHP_EOL, FILE_APPEND);

if (isset($_GET['action']))
{
    $action = strtolower($_GET['action']);
}

$routes = $config['routes'];

if (isset($routes[$action]))
{
    $handler = $routes[$action];
    [$class_name, $method] = explode('@', $handler);

    $dependency_params = $config['params'];
    $dependency = $config['service_container'][$class_name];
    $dependency_class = $dependency['class'];

    try
    {
        $controller = new $class_name(
            new $dependency_class($dependency_params)
        );
        
        if($action == "createreview")
        {
            $product_id = $_GET['productid'];
            $comment = $_GET['comment'];
            $advantages = $_GET['advantages'];
            $disadvantages = $_GET['disadvantages'];
            $grade = $_GET['grade'];
            $datereview = $_GET['datereview'];
            $user_id = $_GET['userid'];

            $controller->$method($product_id, $comment, $advantages, $disadvantages, $grade, $datereview, $user_id);
        }
        else if($action == 'getcategorysubsubbycategorytitle')
        {
            $category = $_GET['category'];
            
            $controller->$method($category);
        }
        else if($action == 'getsizesbycategorysub')
        {
            $categorysub = $_GET['categorysub'];
            
            $controller->$method($categorysub);
        }
        else if($action == "getcategoriessubsubbycategorysubandcategory")
        {
            $category = $_GET['category'];
            $categorysub = $_GET['categorysub'];
            
            $controller->$method($category,$categorysub);
        }
        else if($action == "getproductsfilteredbytitle")
        {
            $input_title = $_GET['input_title'];
            
            $controller->$method($input_title);
        }
        else if($action == "getproductbyid" || $action == "getreviewsproductbyid" || $action == "getsizebyid" || $action == "getcolorbyid" || $action == 'getuserbyid' || $action == 'getcategorysubtitlebyid' || $action == 'getcategorytitlebyid' || $action == 'getcategorysubsubtitlebyid' || $action == 'changeishidden')
        {
            $id = $_GET['id'];
            $controller->$method($id);
        }
        else if($action == "getproductswithoutfilters")
        {
            $category = $_GET['category'];
            $categorysub = $_GET['categorysub'];
            $categorysubsub = $_GET['categorysubsub'];
            $controller->$method($category,$categorysub,$categorysubsub);
        }
        else if($action == "getproductswithfilters")
        {
            $category = $_GET['category'];
            $categorysub = $_GET['categorysub'];
            $categorysubsub = $_GET['categorysubsub'];
            $controller->$method($category,$categorysub,$categorysubsub);
        }
        else if($action == "getcategorysubsubtitleua")
        {
            $categorysubsub = $_GET['categorysubsub'];
            $controller->$method($categorysubsub);
        }
        else if($action == "getproductcharacteristics" || $action == 'getsizebyid')
        {
            $id = $_GET['id'];
            $controller->$method($id);
        }
        else if($action == 'getproductsizes')
        {
            $productId = $_GET['productId'];
            $controller->$method($productId);
        }
        else if($action == 'getissaleproductsbyuser')
        {
            $product_id = $_GET['product_id'];
            $user_id = $_GET['user_id'];
            $controller->$method($product_id,$user_id);
        }
        elseif ($action == 'getusersbyrole') 
        {
            $role = $_GET['role'];
            $controller->$method($role);
        }
        else
        {
            $controller->$method();
        }
    }
    catch (PDOException $error)
    {
        echo $error;
        die("Sorry, failed to connect to database!");
    }
    catch (Exception $error)
    {
        echo "Oops, an error occurred: " . $error->getMessage();
    }
}