<?php
//use \Datetime;
header('Content-Type: application/json'); 
error_reporting(E_ALL ^ E_WARNING);
class OrdersController
{
    public function __construct(
        protected OrderModel $model
    )
    {
    }

    public function createOrder()
    {
      date_default_timezone_set('Europe/Kyiv');
      

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

      $sql = "SELECT id FROM recipients WHERE phone = :phone";
      $sth = $this->model->getDB()->prepare($sql);
      $sth->execute([ 
          ':phone' => $input['recipient']['phoneNumber'], 
      ]
      );

      $found_recipient = $sth->fetchAll();

      if ($found_recipient[0]['id'] != 0) 
      {
        $sql = "UPDATE recipients SET first_name = :first_name, last_name = :last_name, phone = :phone, patronymic = :patronymic WHERE id = :id";
        $sth = $this->model->getDB()->prepare($sql);
        $sth->execute([ 
            ':id' => $found_recipient[0]['id'], 
            ':first_name' => $input['recipient']['firstName'], 
            ':last_name' => $input['recipient']['lastName'],
            ':phone' => $input['recipient']['phoneNumber'],
            ':patronymic' => $input['recipient']['patronymic'], 
        ]
        );
        $recipient_id = $found_recipient[0]['id'];
      }
      else
      {
        $sql = "INSERT INTO recipients (id, first_name, last_name, phone, patronymic) VALUES (:id, :first_name, :last_name, :phone, :patronymic)";
        $sth = $this->model->getDB()->prepare($sql);
        $sth->execute([ 
            ':id' => NULL, 
            ':first_name' => $input['recipient']['firstName'], 
            ':last_name' => $input['recipient']['lastName'],
            ':phone' => $input['recipient']['phoneNumber'],
            ':patronymic' => $input['recipient']['patronymic'], 
        ]
        );
        $recipient_id = $this->model->getDB()->lastInsertId();
      }      

      ////////////////////////////////////////////////////////////
      // Вставити замовленняд до таблиці orders
      $sql = "INSERT INTO orders (id, user_id, date_order, delivery_type_id, payment_type_id, recipient_id, delivery_index, delivery_full_address) VALUES (:id, :user_id, :date_order, :delivery_type_id, :payment_type_id, :recipient_id, :delivery_index, :delivery_full_address)";
      $user_id = $input['user'] == 0 ? NULL : $input['user']['id'];
      $sth = $this->model->getDB()->prepare($sql);
      $now = new DateTime();
      $sth->execute([ 
          ':id' => NULL, 
          ':user_id' => $user_id, 
          ':date_order' => $now->format('Y-m-d H:i:s'),
          ':delivery_type_id' => $delivery_type_id,
          ':payment_type_id' => $payment_method_id,
          ':recipient_id' => $recipient_id,
          ':delivery_index' => $input['delivery']['index'],
          ':delivery_full_address' => $input['delivery']['address'],
      ]
      );
      $order_id = $this->model->getDB()->lastInsertId();
      ////////////////////////////////////////////////////////////
      // Вставити табличну частину до таблиці orderidproductid
      $sql = "INSERT INTO orderidproductid (id, order_id, product_id, quantity, price, size_id) VALUES (:id, :order_id, :product_id, :quantity, :price, :size_id)";
      $sth = $this->model->getDB()->prepare($sql);

      $products = $input['products'];
      for ($i=0; $i < count($products); ++$i) 
      { 
        $sth->execute([ 
            ':id' => NULL, 
            ':order_id' => $order_id, 
            ':product_id' => $products[$i]['product']['id'],
            ':quantity' => $products[$i]['quantity'],
            ':price' => $products[$i]['product']['price'],
            ':size_id' => $products[$i]['size']['id'],
        ]
        ); 
      }

    //   file_put_contents('D:/log.txt', print_r($order_id,true), FILE_APPEND);
      print_r(json_encode($order_id)); 
    }
}