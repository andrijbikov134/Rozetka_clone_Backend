<?php

class ProductIdSizeIdController
{
    public function __construct(
        protected ProductIdSizeIdModel $model
    )
    {
    }

    public function getProductSizes(string $productId)
    {
      $items = [];
      $sql = "SELECT * FROM sizes WHERE id in (SELECT sizeid FROM productidsizeid WHERE productid = :productId);";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
          ':productId' => intval($productId)    
      ]);

      $items = $sth->fetchAll();
      print_r(json_encode($items)); 
    }
}