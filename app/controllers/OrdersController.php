<?php
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
      error_log(print_r($input,true));
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
      $sql = "INSERT INTO orders (id, user_id, date_order, delivery_type_id, payment_type_id, recipient_id, delivery_index, delivery_full_address, status_order) VALUES (:id, :user_id, :date_order, :delivery_type_id, :payment_type_id, :recipient_id, :delivery_index, :delivery_full_address, 'inprocessing')";
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

      print_r(json_encode($order_id)); 
    }

    public function updateOrder()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $order_id = $input['order_id'];
        $status_order = $input['status_order'];
        $sql = "UPDATE orders set status_order = :status_order WHERE id = :order_id;";
        $sth = $this->model->getDB()->prepare($sql);
        $created = $sth->execute([ 
            ':order_id' => intval($order_id),
            ':status_order' => $status_order,
        ]); 
        print_r('');
        
    }

    public function getOrdersByUserId()
    {

        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id']; 

        $sql = "SELECT o.id AS order_id, o.status_order as status_order, 
                o.date_order, 
                GROUP_CONCAT(
                    CONCAT(
                        '{\"title\":\"', p.title, 
                        '\", \"quantity\":', op.quantity, 
                        ', \"price\":', op.price, '}'
                    ) 
                    SEPARATOR ','
                ) AS products, 
                SUM(op.quantity) AS totalQuantity, 
                SUM(op.quantity * op.price) AS totalPrice
          FROM orders o
          JOIN orderidproductid op ON o.id = op.order_id
          JOIN products p ON op.product_id = p.id
          WHERE o.user_id = :user_id
          GROUP BY o.id
          ORDER BY o.date_order DESC";

        $sth = $this->model->getDB()->prepare($sql);
        $sth->execute([":user_id" => $user_id]);
        $orders = $sth->fetchAll(PDO::FETCH_ASSOC);
      
        // Оновлюємо структуру, щоб products було масивом JSON
        foreach ($orders as &$order)
        {
            $order['products'] = "[" . $order['products'] . "]";
        }
        // Відправляємо як JSON
        echo json_encode(["orders" => $orders]);
    }

    public function getOrders()
    {
    header('Content-Type: application/json');

    try
    {
        $sql = "SELECT o.id AS order_id, o.status_order as status_order,
        CONCAT(r.first_name, \" \", r.last_name) AS full_name, r.phone as phone, 
        o.date_order, 
        GROUP_CONCAT(
            CONCAT(
                '{\"title\":\"', p.title, 
                '\", \"quantity\":', op.quantity, 
                ', \"price\":', op.price, '}'
            ) 
            SEPARATOR ','
        ) AS products, 
        SUM(op.quantity) AS totalQuantity, 
        SUM(op.quantity * op.price) AS totalPrice
        FROM orders o
        JOIN orderidproductid op ON o.id = op.order_id
        JOIN products p ON op.product_id = p.id
        JOIN recipients r ON o.recipient_id = r.id
        GROUP BY o.id
        ORDER BY o.date_order DESC";

        $sth = $this->model->getDB()->prepare($sql);
        $sth->execute([]);
        $orders = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orders as &$order)
        {
            $order['products'] = "[" . $order['products'] . "]";
        }

        if (!$orders) 
        {
            error_log("getOrders: Замовлення не знайдено");
            echo json_encode(["message" => "Замовлення не знайдено", "orders" => []]);
            exit();
        }
        error_log("getOrders: SQL виконано, кількість результатів: " . count($orders));
        echo json_encode(["orders" => $orders]);
        exit();
    } 
    catch (Exception $e) 
    {
        error_log("getOrders: Помилка сервера: " . $e->getMessage());
        echo json_encode(["error" => "Помилка сервера", "details" => $e->getMessage()]);
    }
  } 
}