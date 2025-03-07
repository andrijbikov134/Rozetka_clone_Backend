<?php

class OrderIdProductIdController
{
    public function __construct(
        protected OrderIdProductIdModel $model
    )
    {
    }

    public function getIsSaleProductsByUser(string $product_id, string $user_id)
    {
      
      $sql = "SELECT user_id FROM orders WHERE id IN (SELECT order_id FROM orderidproductid WHERE product_id = :product_id) AND user_id = :user_id";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([ 
          ':product_id' => intval($product_id),
          ':user_id' => intval($user_id),    
      ]);

      $items = $sth->fetchAll();
      if(count($items) == 0)
      {
        $result = 'false';
        print_r(json_encode($result));
      }
      else
      {
        $result = 'true';
        print_r(json_encode($result));
      }
    }

    public function getPopularProducts()
    {
      $sql = "SELECT product_id, SUM(quantity) as sumQuantity FROM orderidproductid GROUP BY product_id ORDER BY sumQuantity DESC LIMIT 12";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([    
      ]);

      $items = $sth->fetchAll();
      $resultArrayProducts = [];
      for ($i=0; $i < count($items) ; ++$i) 
      { 
        $sql = "SELECT * FROM products WHERE id = :product_id";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([
          ':product_id' => $items[$i]['product_id']    
        ]);
        $product = $sth->fetchAll(PDO::FETCH_ASSOC);
        array_push($resultArrayProducts, $product[0]);
      }
      print_r(json_encode($resultArrayProducts));
    }
}