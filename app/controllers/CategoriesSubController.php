<?php

class CategoriesSubController
{
    public function __construct(
        protected CategorySubModel $model
    )
    {
    }

    public function getCategoriesSub()
    {      
      $sql = "SELECT * FROM categorysub";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([     
      ]);

      $item = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($item)); 
    }
}