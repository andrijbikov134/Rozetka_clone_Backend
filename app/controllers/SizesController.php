<?php

class SizesController
{
    public function __construct(
        protected SizeModel $model
    )
    {
    }

    public function getSizeById(string $id)
    {
      
      $sql = "SELECT * FROM sizes WHERE id = :id;";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
          ':id' => intval($id)    
      ]);

      $item = $sth->fetchAll();

      print_r(json_encode($item)); 
    }
}