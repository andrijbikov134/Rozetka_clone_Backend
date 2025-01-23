<?php

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
        
        if($action == "addManually")
        {
            $body = $_GET['body'];
            $status = $_GET['status'];
            $image_path = $_GET['image_path'];
            $subject = $_GET['subject'];
            $user_id = $_GET['user_id'];
            $controller->$method($body, $status, $image_path, $subject, $user_id);
        }
        else if($action == "remove")
        {
            $userId = $_GET['id'];
            $controller->$method(intval($userId));
        }
        else if($action == "create")
        {
            $userName = $_GET['name'];
            $userEmail = $_GET['email'];
            $userStatus = $_GET['status'];
            $userType = $_GET['type'];
            $userSsn = $_GET['ssn'];
            
            $controller->$method($userName, $userEmail, $userStatus, $userType, $userSsn);
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