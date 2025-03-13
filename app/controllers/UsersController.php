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
                    "patronymic" => $user['patronymic'],
                    "gender" => $user['gender'],
                    "birthday" => $user['birthday'],
                    "email" => $user['email'],
                    "city" => $user['city'],
                    "phone" => $user['phone'],
                    "role" => $role['title']

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

    public function updateProfile()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(["error" => "ID користувача є обов'язковим"]);
            exit();
        }

        $user_id = intval($data['id']);
        $first_name = trim($data['first_name'] ?? '');
        $last_name = trim($data['last_name'] ?? '');
        $patronymic = trim($data['patronymic'] ?? '');
        $gender = trim($data['gender'] ?? '');
        $birthday = trim($data['birthday'] ?? '');
        $city = trim($data['city'] ?? '');
        $phone = trim($data['phone'] ?? '');


        try {
            $stmt = $this->model->getDB()->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, patronymic = ?, gender = ?, birthday = ?, city = ?, phone = ?
                WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $patronymic, $gender, $birthday, $city, $phone, $user_id]);

            echo json_encode(["message" => "Профіль оновлено успішно!"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Помилка оновлення: " . $e->getMessage()]);
        }
    }

    public function changePassword()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    
        $data = json_decode(file_get_contents("php://input"), true);
    
        error_log("JSON-запит отримано: " . print_r($data, true));
    
        if (!isset($data['userId']) || !isset($data['oldPassword']) || !isset($data['newPassword'])) {
            echo json_encode(["error" => "Всі поля обов'язкові"]);
            error_log("Відсутні дані у JSON-запиті!");
            return;
        }
    
        $userId = intval($data['userId']);
        $oldPassword = trim($data['oldPassword']);
        $newPassword = trim($data['newPassword']);
    
        try {
            $stmt = $this->model->getDB()->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                echo json_encode(["error" => "Користувача не знайдено"]);
                return;
            }
    
            if (!password_verify($oldPassword, $user['password'])) {
                echo json_encode(["error" => "Неправильний старий пароль"]);
                return;
            }
    
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
            $updateStmt = $this->model->getDB()->prepare("UPDATE users SET password = :password WHERE id = :id");
            $updateStmt->execute([':password' => $hashedPassword, ':id' => $userId]);
    
            echo json_encode(["success" => true]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Помилка сервера: " . $e->getMessage()]);
        }
    }
}