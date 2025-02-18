<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class UsersController
{
    public function __construct(
        protected UserModel $model
    )
    {
    }

    public function getUserById(string $id)
    {
      $sql = "SELECT * FROM users WHERE id = :id;";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
          ':id' => intval($id)    
      ]);

      $item = $sth->fetchAll();

      print_r(json_encode($item[0])); 
    }

    public function loginUser()
    {        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode(["error" => "e-mail та пароль обов’язкові"]);
            exit();
        }
        
        $email = trim($data['email']);
        $password = trim($data['password']);
        
        $stmt = $this->model->getDB()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password']))
        {   
            session_start();

            $stmt = $this->model->getDB()->prepare("SELECT title FROM roles WHERE id = :role_id");
            $stmt->execute([':role_id' => $user['role_id']]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
        
            echo json_encode([
                "message" => "Авторизація успішна!",
                "user" => [
                    "id" => $user['id'],
                    "first_name" => $user['first_name'],
                    "last_name" => $user['last_name'],
                    "phone" => $user['phone'],
                    "role" => $role['title'],
                ]
            ]);
        } else {
            echo json_encode(["error" => "Невірний e-mail або пароль"]);
        }  
    }

    public function registerUser()
    {        
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['first_name']) || !isset($data['email']) || !isset($data['password'])) {
            echo json_encode(["error" => "Всі поля обов'язкові"]);
            exit();
        }


        $first_name = trim($data['first_name']);
        $email = trim($data['email']);
        $password = trim($data['password']);


        $stmt = $this->model->getDB()->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "Некоректний email", "type" => "email"]);
            exit();
        }
        else if($password == '')
        {
            echo json_encode(["error" => "Некоректний password", "type" => "password" ]);
            exit();
        }
        else if(is_array($foundUser))
        {
            echo json_encode(["error" => "Введений e-mail вже існує!", "type" => "email" ]);
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            // 2 - id ролі Client в базі даних  
            $stmt = $this->model->getDB()->prepare("INSERT INTO users (first_name, email, password, role_id) VALUES (?, ?, ?, 2)");
            $stmt->execute([$first_name, $email, $hashed_password]);

            echo json_encode(["message" => "Реєстрація успішна!"]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Код помилки унікального e-mail
                echo json_encode(["error" => "Користувач із таким e-mail вже існує"]);
            } else {
                echo json_encode(["error" => "Помилка сервера: " . $e->getMessage()]);
            }
        } 
    }
}