<?php

class CategoriesSubSubController
{
    public function __construct(
        protected CategorySubSubModel $model
    )
    {
    }

    public function getCategorySubSubTitleById(string $id)
    {      
      $sql = "SELECT title, title_ua FROM categorysubsub WHERE id = :id";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([
        ':id' => $id
      ]);

      $item = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($item[0])); 
    }

    public function getCategorySubSubByCategoryTitle(string $category)
    {      
      $sql = "SELECT * FROM categorysubsub WHERE title LIKE :title ORDER BY title_ua ASC";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([
        ':title' => '%\_' . $category . "%",
      ]);

      $items = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($items)); 
    }

    public function getCategorySubSubTitleUa(string $categorysubsub)
    {
        $sql = "SELECT title_ua FROM categorysubsub WHERE LOWER(title) = :categorysubsub;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':categorysubsub' => $categorysubsub  
        ]);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($result));  
    }
}