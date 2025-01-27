<?php

class ProductsController
{
    public function __construct(
        protected ProductModel $model
    )
    {
    }

    private function addNewUserInDB(string $name, string $email, string $status, string $type, string $ssn)
    {
        $sql = "INSERT INTO users (id, name, email, status, type, ssn) VALUES (:id, :name, :email, :status, :type, :ssn);";
        $sth = $this->model->getDB()->prepare($sql);

        $created = $sth->execute([ 
            ':id' => NULL,
            ':name' => $name,
            ':email' => $email,
            ':status' => $status,
            ':type' => $type,
            ':ssn' => $ssn,
        ]);
    }

    public function getCategories(string $category, string $categorysub)
    {
        $items = [];

        $sql = "SELECT * FROM categorysubsub WHERE title LIKE :category AND categorysub_id = (SELECT id FROM categorysub WHERE title =  :category_sub);";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':category' => '%\_' .  $category . "%",
            'category_sub' => $categorysub,
        ]);

        $items = $sth->fetchAll();
        print_r(json_encode($items));  
    }

    public function getProductsFilteredByTitle(string $input_title)
    {
        $items = [];
        $input_title = strtolower($input_title);

        $sql = "SELECT * FROM products WHERE LOWER(title) LIKE :input_title;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':input_title' => '%' .  $input_title . "%"    
        ]);

        $items = $sth->fetchAll();
        print_r(json_encode($items));  
    }

    public function getProductById(string $id)
    {
        $items = [];


        $sql = "SELECT * FROM products WHERE id = :id;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)    
        ]);

        $items = $sth->fetchAll();
        print_r(json_encode($items));  
    }

    public function loadFromAPI()
    {
      $this->deleteUsersFromDB();

      $url = USERS_JSON;
      $jsonData = file_get_contents($url);
      $users = json_decode($jsonData, true)['users'];

      $statuses = ['active', 'not active'];
      $types = ['publisher', 'writer', 'moderator'];
      foreach ($users as $user) 
      {
        $newUser = [
          'name' => $user['firstName'] . " " . $user['lastName'],
          'email' => $user['email'],
          'status' => $statuses[rand(0,1)],
          'type' => $types[rand(0,2)],
          'ssn' => $user['ssn'],
        ];
        $this->addNewUserInDB($newUser['name'],$newUser['email'],$newUser['status'],$newUser['type'],$newUser['ssn']);
      }

      $this->render('index', [
          'users' =>  $this->model->getUsersList()
      ]);
    }

    public function deleteUsersFromDB()
    {
      $sql = "DELETE FROM users";
      $sth = $this->model->getDB()->prepare($sql);
      $edited = $sth->execute([]);
    }

    public function removeAllUsers()
    {
      $this->deleteUsersFromDB();

      $this->render('index', [
          'users' =>  $this->model->getUsersList()
      ]);
    }

    public function deleteUserFromDB(int $userId)
    {
        $sql = "DELETE FROM users WHERE id = :userId";
        $sth = $this->model->getDB()->prepare($sql);
        $edited = $sth->execute([ 
            ':userId' => $userId,
        ]);
    }

    public function remove(int $userId)
    {
        $this->deleteUserFromDB($userId);

        $this->render('index', [
            'users' =>  $this->model->getUsersList()
        ]);
    }

    public function getPopularProducts()
    {
        $data = $this->model->getProductsList();
        print_r(json_encode($data));
    }

    public function create(string $name, string $email, string $status, string $type, string $ssn)
    {
        if($name == "" ||  $email == "" || $ssn == "")
        {
            echo "<p class=\"red\">All fields must be filled in!</p>";
        }
        else if(!str_contains($email, "@"))
        {
            echo "<p class=\"red\">Email is incorrect!</p>";
        }
        else
        {
            $this->addNewUserInDB($name, $email, $status, $type, $ssn);
        }
        $this->render('index', [
            'users' => $this->model->getUsersList(),
        ]);
    }    

    public function render(string $page, array $data = []): void
    {
        extract($data);
        
        require_once VIEWS_PATH . '/master.php';
    }
}