<?php
use \Datetime;

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
      date_default_timezone_set('Europe/Kyiv');
      
      $now = new DateTime();

      $input = json_decode(file_get_contents('php://input'), true);
      $payment_method = $input['payment_method'];
      // file_put_contents('D:/log.txt', print_r(intval($input['user']['id']),true), FILE_APPEND);

      ////////////////////////////////////////////////////////////
      // Знайти id за назвою в таблиці deliverytype
      $sql = "SELECT id FROM deliverytype WHERE title = :title";

      $sth = $this->model->getDB()->prepare($sql);
      $sth->execute([ 
          ':title' => $input['delivery_type'],
      ]
      );

      $delivery_type_result = $sth->fetchAll();  
      $delivery_type_id = $delivery_type_result[0]['id'];

      ////////////////////////////////////////////////////////////
      $sql = "SELECT id FROM paymenttype WHERE title = :title";
      // Знайти id за назвою в таблиці paymenttype

      $sth = $this->model->getDB()->prepare($sql);
      $sth->execute([ 
          ':title' => $input['payment_method'],
      ]
      );

      $payment_method_result = $sth->fetchAll();  
      $payment_method_id = $payment_method_result[0]['id'];

      ////////////////////////////////////////////////////////////
      // Вставити замовленняд до таблиці orders
      $sql = "INSERT INTO orders (id, user_id, date_order, delivery_type_id, payment_type_id, recipient_id, delivery_index, delivery_full_address) VALUES (:id, :user_id, :date_order, :delivery_type_id, :payment_type_id, :recipient_id, :delivery_index, :delivery_full_address)";

      $sth = $this->model->getDB()->prepare($sql);
      $sth->execute([ 
          ':id' => NULL, 
          ':user_id' => intval($input['user']['id']), 
          ':date_order' => $now->format('Y-m-d H:i:s'),
          ':delivery_type_id' => $delivery_type_id,
          ':payment_type_id' => $payment_method_id,
          ':recipient_id' => 1,
          ':delivery_index' => $input['delivery']['index'],
          ':delivery_full_address' => $input['delivery']['address'],
      ]
      );

      // file_put_contents('D:/log.txt', print_r($input,true), FILE_APPEND);
    }
}