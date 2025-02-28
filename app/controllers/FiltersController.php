<?php

class FiltersController
{
    public function __construct(
        protected FilterModel $model
    )
    {
    }

    public function getAllBrands()
    {
      $sql = "SELECT * FROM brands ORDER BY title ASC";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
      ]);

      $items = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($items)); 
    }

    public function getAllSizes()
    {
      $sql = "SELECT * FROM sizes WHERE title_key = 'clothes'";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
      ]);

      $items = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($items)); 
    }

    public function getAllMaterials()
    {
      $sql = "SELECT * FROM materials ORDER BY title ASC";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
      ]);

      $items = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($items)); 
    }

    public function getAllColors()
    {
      $sql = "SELECT * FROM colors ORDER BY title ASC";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
      ]);

      $items = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($items)); 
    }

    public function getAllCountries()
    {
      $sql = "SELECT * FROM countriesproduct ORDER BY title ASC";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
      ]);

      $items = $sth->fetchAll(PDO::FETCH_ASSOC);

      print_r(json_encode($items)); 
    }

    
}