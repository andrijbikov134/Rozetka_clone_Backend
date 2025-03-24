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
      $sql = "SELECT product_id, SUM(quantity) as sumQuantity FROM orderidproductid GROUP BY product_id ORDER BY sumQuantity DESC";
      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([    
      ]);

      $items = $sth->fetchAll();
      $resultArrayProducts = [];
      for ($i=0; $i < count($items) ; ++$i) 
      { 
        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND id = :product_id";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([
          ':product_id' => $items[$i]['product_id']    
        ]);
        $product = $sth->fetchAll(PDO::FETCH_ASSOC);

        if(count($product) > 0)
        {
          array_push($resultArrayProducts, $product[0]);
        }
        if(count($resultArrayProducts) == 12)
        {
          break;
        }
      }
      print_r(json_encode($resultArrayProducts));
    }

    public function getTopProductsQuantity()
    {
      $response = [];

      $sql = "SELECT products.title as title, SUM(quantity) as sum_quantity FROM orderidproductid
      JOIN products ON orderidproductid.product_id = products.id GROUP BY product_id ORDER BY sum_quantity DESC LIMIT 10";

      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([    
      ]);

      $result = $sth->fetchAll();
      for ($i=0; $i < count($result) ; ++$i)
      { 
        $response['labels'][] = $result[$i]['title'];
        $response['values'][] = intval($result[$i]['sum_quantity']);  
      }

      print_r(json_encode($response));
    }

    public function getSalesByMonth()
    {
      $response = [];

      $sql = "SELECT YEAR(date_order) AS year, MONTH(date_order) AS month, SUM(orderidproductid.quantity * orderidproductid.price) AS total_sales FROM orders JOIN orderidproductid ON orders.id = orderidproductid.order_id GROUP BY YEAR(date_order), MONTH(date_order) ORDER BY year, month;";

      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([    
      ]);

      $result = $sth->fetchAll();
      for ($i=0; $i < count($result) ; ++$i)
      { 
        if($result[$i]['month'] == 1)
        {
          $response['labels'][] = "Січень";
        }
        else if($result[$i]['month'] == 2)
        {
          $response['labels'][] = "Лютий";
        }
        else if($result[$i]['month'] == 3)
        {
          $response['labels'][] = "Березень";
        }
        else if($result[$i]['month'] == 4)
        {
          $response['labels'][] = "Квітень";
        }
        else if($result[$i]['month'] == 5)
        {
          $response['labels'][] = "Травень";
        }
        else if($result[$i]['month'] == 6)
        {
          $response['labels'][] = "Червень";
        }
        else if($result[$i]['month'] == 7)
        {
          $response['labels'][] = "Липень";
        }
        else if($result[$i]['month'] == 8)
        {
          $response['labels'][] = "Серпень";
        }
        else if($result[$i]['month'] == 9)
        {
          $response['labels'][] = "Вересень";
        }
        else if($result[$i]['month'] == 10)
        {
          $response['labels'][] = "Жовтень";
        }
        else if($result[$i]['month'] == 11)
        {
          $response['labels'][] = "Листопад";
        }
        else
        {
          $response['labels'][] = "Грудень";
        }

        // $response['labels'][] = $result[$i]['month'];
        $response['values'][] = intval($result[$i]['total_sales']);  
      }

      print_r(json_encode($response));
    }

    public function getSalesByBrand()
    {
      $response = [];

      $sql = "SELECT brands.title as title, SUM(orderidproductid.quantity * orderidproductid.price) as total_sum FROM orderidproductid
      JOIN products ON orderidproductid.product_id = products.id JOIN brands ON products.brand_id = brands.id GROUP BY brands.id ORDER BY total_sum DESC";

      $sth = $this->model->getDB()->prepare($sql); 
      
      $sth->execute([    
      ]);
      $result = $sth->fetchAll();
      for ($i=0; $i < count($result) ; ++$i)
      { 
        $response['labels'][] = $result[$i]['title'];
        $response['values'][] = intval($result[$i]['total_sum']);  
      }
      print_r(json_encode($response));
    }

    public function getSalesByCategory()
    {
      header('Content-Type: application/json');

      // Запрос к базе данных
      $sql = "SELECT category.title_ua as title, SUM(orderidproductid.quantity * orderidproductid.price) as total_sum FROM orderidproductid JOIN products ON orderidproductid.product_id = products.id JOIN category ON products.category_id = category.id GROUP BY category.id ORDER BY total_sum DESC";

      $sth = $this->model->getDB()->prepare($sql);
      $sth->execute([    
      ]);
      $result = $sth->fetchAll(PDO::FETCH_ASSOC);
      $categorySales = ["labels" => [], "values" => []];
      for ($i=0; $i < count($result) ; ++$i)
      { 
        $response['labels'][] = $result[$i]['title'];
        $response['values'][] = intval($result[$i]['total_sum']);  
      }
      // Отправка JSON-данных в React
      echo json_encode($response);
    }
}