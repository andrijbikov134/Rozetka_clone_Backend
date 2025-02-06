<?php

class ColorsController
{
    public function __construct(
        protected ColorModel $model
    )
    {
    }

    public function getColorById(string $id)
    {
      
      $sql = "SELECT * FROM colors WHERE id = :id;";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
          ':id' => intval($id)    
      ]);

      $item = $sth->fetchAll();

      print_r(json_encode($item)); 
    }
}