<?php

// $log = date('Y-m-d H:i:s') . 'class name' . $class_name . '  ' . $method;
// file_put_contents('D:/log.txt', $log . PHP_EOL, FILE_APPEND);

$action = 'index';
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
        
        
        
        if($action == "createcomment")
        {
            $product_id = $_GET['productid'];
            $comment = $_GET['comment'];
            $advantages = $_GET['advantages'];
            $disadvantages = $_GET['disadvantages'];
            $star_quantity = $_GET['starquantity'];
            $user_id = $_GET['userid'];

            $controller->$method($product_id, $comment, $advantages, $disadvantages, $star_quantity, $user_id);
        }
        else if($action == "getcategories")
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
        else if($action == "getproductbyid")
        {
            $id = $_GET['id'];
            $controller->$method($id);
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