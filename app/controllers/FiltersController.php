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
      $input = json_decode(file_get_contents('php://input'), true);
      // file_put_contents('D:/log.txt', print_r($input,true), FILE_APPEND);
      $sql = "SELECT * FROM sizes WHERE id in (SELECT sizeid FROM productidsizeid WHERE productid in (";
      
      $sql_sizes = "";
      for ($i=0; $i < count($input); ++$i)
      { 
          if($i == count($input)-1)
          {
              $sql_sizes .= $input[$i]['id'] . ")";
          }
          else
          {
              $sql_sizes .= $input[$i]['id'] . ", ";
          }
      }
      $sql .= $sql_sizes . " GROUP BY sizeid)";
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