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
        $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($items)); 
    }
}