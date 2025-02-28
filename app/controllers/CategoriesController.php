<?php

class CategoriesController
{
    public function __construct(
        protected CategoryModel $model
    )
    {
    }

    public function getCategoryTitleById(string $id)
    {      
      $sql = "SELECT title FROM category WHERE id = :id";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([
        ':id' => $id
      ]);

      $item = $sth->fetchAll(PDO::FETCH_ASSOC);
      print_r(json_encode($item[0])); 
    }
}