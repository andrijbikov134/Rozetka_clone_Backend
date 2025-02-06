<?php

class ReviewsController
{
    public function __construct(
        protected ReviewModel $model
    )
    {
    }

    public function putReviewDB(string $product_id, string $comment, string $advantages, string $disadvantages, string $grade, string $datereview, string $user_id)
    {
      $sql = "INSERT INTO reviews (id, product_id, comment, advantages, disadvantages, grade, datereview, user_id) VALUES (:id, :product_id, :comment, :advantages, :disadvantages, :grade, :datereview, :user_id);";

      $sth = $this->model->getDB()->prepare($sql);
      $sth->execute(
        [
          'id' => NULL,
          'product_id' => intval($product_id),
          'comment' => $comment,
          'advantages' => $advantages,
          'disadvantages' => $disadvantages,
          'grade' => intval($grade),
          'datereview' => $datereview,
          'user_id' => intval($user_id)
        ]
        );
    }

    public function getReviewsProductById(string $id)
    {
        $items = [];
        $sql = "SELECT * FROM reviews WHERE product_id = :id;";
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

    public function getAllProducts()
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