<?php

class OrdersController
{
    public function __construct(
        protected OrderModel $model
    )
    {
    }

    public function createOrder()
    {
      header('Content-Type: application/json');   
      $input = json_decode(file_get_contents('php://input'), true);

      for ($i = 1; $i <= count($item); $i++) 
      {
        echo $i;
      }
      
      file_put_contents('D:/log.txt', $input[0]['product']['title'], FILE_APPEND);
    }

}