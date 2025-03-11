<?php

use Google\Cloud\Storage\StorageClient; // для відправки картинки на Google Cloud

class ProductsController
{
    public function __construct(
        protected ProductModel $model
    )
    {
    }

    private function getCategoryIdByTitle (string $title)
    {
        $sql = "SELECT id FROM category WHERE title = :title";
        $sth = $this->model->getDB()->prepare($sql);

        $created = $sth->execute([ 
            ':title' => $title,
        ]);
        $item = $sth->fetchAll();
        return $item[0][0];
    }

    private function getCategorySubIdByTitle (string $title)
    {
        $sql = "SELECT id FROM categorysub WHERE title = :title";
        $sth = $this->model->getDB()->prepare($sql);

        $created = $sth->execute([ 
            ':title' => $title,
        ]);
        $item = $sth->fetchAll();
        return $item[0][0];
    }

    private function getCategorySubSubIdByTitle (string $category, string $category_sub_sub)
    {
        $title = $category_sub_sub . '_' . $category;

        $sql = "SELECT id FROM categorysubsub WHERE title = :title";
        $sth = $this->model->getDB()->prepare($sql);

        $created = $sth->execute([ 
            ':title' => $title,
        ]);
        $item = $sth->fetchAll();
        return $item[0][0];
    }
    
    public function addOrUpdateProductInDB()
    {
        require dirname(__DIR__,2) . '/vendor/autoload.php'; // Підключити автозавантажувач Composer для відправки картинки на Google Cloud
    
        $input = json_decode(file_get_contents("php://input"), true);
        
        // error_log(print_r($_POST,true));
        // error_log(print_r($_FILES,true));


        $id = $_POST['id'];
        $title = $_POST['title'];
        $color_id = $_POST['color_id'];
        $brand_id = $_POST['brand_id'];
        $price = $_POST['price'];
        $material_id = $_POST['material_id'];
        $country_product_id = $_POST['country_product_id'];
        $part_number = $_POST['part_number'];
        $category = $_POST['category'];
        $categorySub = $_POST['categorySub'];
        $categorySubSub = $_POST['categorySubSub'];
        $oldImgPath = $_POST['oldImgPath'];
        $price_with_discount = $_POST['price_with_discount'];
        $new_product = $_POST['new_product'];
        $new_product == 'true' ? $new_product = true : $new_product = false;
       
        if(isset($_FILES['file']))
        {
            $file = $_FILES['file'];
            // Настройте переменные
            $projectId = 'arctic-marking-450608-f8'; // Ваш Project ID
            $bucketName = 'clothes_store'; // Название вашего бакета
    
            // Путь к вашему файлу с учетными данными JSON
            $path_env = dirname(__DIR__,2) . '/arctic-marking-450608-f8-6f543f6e3329.json';
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $path_env);
    
            // Создаём объект StorageClient
            $storage = new StorageClient([
                'projectId' => $projectId,
            ]);
    
            // Получаем объект бакета
            $bucket = $storage->bucket($bucketName);
    
            $objectName = $file['name']; 
            $fileNames = explode(".", $objectName);
    
            // Указываем путь к файлу, который хотите загрузить
            $filePath = $file['tmp_name'];
    
            $hashed_filename = floor(microtime(true) * 1000) . '.' . $fileNames[1];  // Имя объекта в DB
            $filename = 'img/' . $category . "/" . $categorySub . '/' . $categorySubSub . '/' . $hashed_filename;  // Имя объекта в бакете
            
            // Загружаем файл в Google Cloud Storage
            $bucket->upload(
                fopen($filePath, 'r'),
                [
                    'name' => $filename, // Имя файла в облаке
                ]
            );
            if($oldImgPath != '')
            {
                // Видалити картинку
                $object = $bucket->object($oldImgPath);
                $object->delete();
            }
        }
        else
        {
            $filename = $oldImgPath;
        }
       


        // Видалити картинку
        // $object = $bucket->object('img/women/clothes/jackets/6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b.png');
        // $object->delete();

        // Подивитися зміст backet
        // $objects = $bucket->objects();
        // error_log("!!!!!!!!!!!!" . PHP_EOL);
        // foreach ($objects as $object) {
        //     error_log($object->name());
        // }
        $category_id = $this->getCategoryIdByTitle($category);
        $categorysub_id = $this->getCategorySubIdByTitle($categorySub);
        $categorysubsub_id = $this->getCategorySubSubIdByTitle($category, $categorySubSub);
        if($id == 'null')
        {
            $sql = "INSERT INTO products (id, title, color_id, brand_id, price, price_with_discount, material_id, country_product_id, part_number, category_id, category_sub_id, category_sub_sub_id, pictures_path, new_product) VALUES (:id, :title, :color_id, :brand_id, :price, :price_with_discount, :material_id, :country_product_id, :part_number, :category_id, :category_sub_id, :category_sub_sub_id, :pictures_path, :new_product);";
            $sth = $this->model->getDB()->prepare($sql);
    
            $created = $sth->execute([ 
                ':id' => NULL,
                ':title' => $title,
                ':color_id' => $color_id,
                ':brand_id' => $brand_id,
                ':price' => $price,
                ':price_with_discount' => $price_with_discount == "null" ? NULL : $price_with_discount,
                ':material_id' => $material_id,
                ':country_product_id' => $country_product_id,
                ':part_number' => $part_number,
                ':category_id' => $category_id,
                ':category_sub_id' => $categorysub_id,
                ':category_sub_sub_id' => $categorysubsub_id,
                ':pictures_path' => $filename, 
                ':new_product' => $new_product,

            ]);

            $product_id = $this->model->getDB()->lastInsertId();

            $sizes_json = $_POST['sizes'];
            $sizes = [];
            for ($i=0; $i < count($sizes_json); $i++) { 
                $sizes[$i] = json_decode($sizes_json[$i], true);
            }

            for ($i=0; $i < count($sizes); ++$i) 
            { 
                $sql = "INSERT INTO productidsizeid (id, productid, sizeid) VALUES (:id, :product_id, :size_id);";
                $sth = $this->model->getDB()->prepare($sql);
                $created = $sth->execute([ 
                    ':id' => NULL,
                    ':product_id' => $product_id,
                    ':size_id' => $sizes[$i]['id'],    
                ]);
            }
        }
        else
        {
            
            $sql = "UPDATE products set title = :title, new_product = :new_product, color_id = :color_id, brand_id = :brand_id, price = :price, price_with_discount = :price_with_discount, material_id = :material_id, country_product_id = :country_product_id, part_number = :part_number, category_id = :category_id, category_sub_id = :category_sub_id, category_sub_sub_id = :category_sub_sub_id, pictures_path = :pictures_path WHERE id = :id;";
            $sth = $this->model->getDB()->prepare($sql);
            error_log("NEW FILE NAME" . $filename);
            $created = $sth->execute([ 
                ':id' => $id,
                ':title' => $title,
                ':color_id' => $color_id,
                ':brand_id' => $brand_id,
                ':price' => $price,
                ':price_with_discount' => $price_with_discount == "null" ? NULL : $price_with_discount,
                ':material_id' => $material_id,
                ':country_product_id' => $country_product_id,
                ':part_number' => $part_number,
                ':category_id' => $category_id,
                ':category_sub_id' => $categorysub_id,
                ':category_sub_sub_id' => $categorysubsub_id,
                ':pictures_path' => $filename, 
                ':new_product' => $new_product,

            ]);

            $sizes_json = $_POST['sizes'];
            $sizes = [];
            for ($i=0; $i < count($sizes_json); $i++) { 
                $sizes[$i] = json_decode($sizes_json[$i], true);
            }

            $sql = "SELECT * FROM productidsizeid WHERE productid = :product_id;";
            $sth = $this->model->getDB()->prepare($sql);
            $created = $sth->execute([ 
                ':product_id' => $id,
            ]);
            $oldSizes = $sth->fetchAll();

            for ($i=0; $i < count($sizes); ++$i) 
            {
                $oldSizesId = array_column($oldSizes, 'sizeid');
                $found_key = array_search($sizes[$i]['id'], $oldSizesId);

                if($found_key === false)
                {
                    $sql = "INSERT INTO productidsizeid (id, productid, sizeid) VALUES (:id, :product_id, :size_id);";
                    $sth = $this->model->getDB()->prepare($sql);
                    $created = $sth->execute([ 
                        ':id' => NULL,
                        ':product_id' => $id,
                        ':size_id' => $sizes[$i]['id'],    
                    ]);
                }
            }
            for ($i=0; $i < count($oldSizes); ++$i)
            { 
                $newSizesId = array_column($sizes, 'id');
                $found_key = array_search($oldSizes[$i]['sizeid'], $newSizesId);
                if($found_key === false)
                {
                    $sql = "DELETE FROM productidsizeid WHERE id = :id;";
                    $sth = $this->model->getDB()->prepare($sql);
                    $created = $sth->execute([ 
                        ':id' => $oldSizes[$i]['id'],
                    ]);
                }
            }
        }
        return 0;
    }

    public function deleteImgFromGoogleBucket()
    {
        require dirname(__DIR__,2) . '/vendor/autoload.php'; // Підключити автозавантажувач Composer для відправки картинки на Google Cloud

        $name = $_POST['imgPath'];

        $projectId = 'arctic-marking-450608-f8'; // Ваш Project ID
        $bucketName = 'clothes_store'; // Название вашего бакета

        // Путь к вашему файлу с учетными данными JSON
        $path_env = dirname(__DIR__,2) . '/arctic-marking-450608-f8-6f543f6e3329.json';
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $path_env);

        // Создаём объект StorageClient
        $storage = new StorageClient([
            'projectId' => $projectId,
        ]);

        // Получаем объект бакета
        $bucket = $storage->bucket($bucketName);

        // Видалити картинку
        $object = $bucket->object($name);
        $object->delete();
    }

    public function deleteProductFromDB()
    {
        require dirname(__DIR__,2) . '/vendor/autoload.php'; // Підключити автозавантажувач Composer для відправки картинки на Google Cloud

        $input = json_decode(file_get_contents("php://input"), true);
        error_log(print_r($input,true));
        $id = $input['id'];
        $imgPath = $input['imgPath'];
        
        if($imgPath != '')
        {
            // Настройте переменные
            $projectId = 'arctic-marking-450608-f8'; // Ваш Project ID
            $bucketName = 'clothes_store'; // Название вашего бакета
            // Путь к вашему файлу с учетными данными JSON
            $path_env = dirname(__DIR__,2) . '/arctic-marking-450608-f8-6f543f6e3329.json';
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $path_env);
            // Создаём объект StorageClient
            $storage = new StorageClient([
                'projectId' => $projectId,
            ]);
            // Получаем объект бакета
            $bucket = $storage->bucket($bucketName);
            
            // Видалити картинку
            $object = $bucket->object($imgPath);
            $object->delete();
        }
        
        $sql = "SELECT * FROM orderidproductid WHERE product_id = :id;";
        $sth = $this->model->getDB()->prepare($sql);

        $created = $sth->execute([ 
            ':id' => $id,
            ]);
        
            $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($items) == 0)
        {

            $sql = "DELETE FROM productidsizeid WHERE productid = :id;";
            $sth = $this->model->getDB()->prepare($sql);
    
            $created = $sth->execute([ 
                ':id' => $id,
                ]);
    
            $sql = "DELETE FROM products WHERE id = :id;";
            $sth = $this->model->getDB()->prepare($sql);
    
            $created = $sth->execute([ 
                ':id' => $id,
                ]);
            error_log('OK');

            print_r(json_encode(["error" => "OK"]));
        }
        else
        {
            error_log('Існують замовлення з обраним товаром! Товар можна тільки приховати!');
            print_r(json_encode(["error" => "Існують замовлення з обраним товаром! Товар можна тільки приховати!"]));
        }

    }

    public function getCategoriesSubSubByCategorySubAndCategory(string $category, string $categorysub)
    {
        $items = [];

        $sql = "SELECT * FROM categorysubsub WHERE title LIKE :category AND categorysub_id = (SELECT id FROM categorysub WHERE title =  :category_sub) ORDER BY title_ua ASC;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':category' => '%\_' .  $category . "%",
            'category_sub' => $categorysub,
        ]);

        $items = $sth->fetchAll();
        print_r(json_encode($items));  
    }

    public function getProductsFilteredByTitle(string $input_title)
    {
        $items = [];
        $input_title = strtolower($input_title);

        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND LOWER(title) LIKE :input_title ";

        $params = [
            ':input_title' => '%' .  $input_title . "%"
        ];

        $input = json_decode(file_get_contents('php://input'), true);
        if(count($input['brands']) != 0)
        {
            $sql_brands = "AND brand_id in (";
            for ($i=0; $i < count($input['brands']); ++$i)
            { 
                if($i == count($input['brands'])-1)
                {
                    $sql_brands .= $input['brands'][$i] . ")";
                }
                else
                {
                    $sql_brands .= $input['brands'][$i] . ", ";
                }
            }
            $sql .= $sql_brands;
        }
        
        $priceRange = $input['priceRange'];
        $sql .= " AND price BETWEEN " . $priceRange['min']  . " AND " . $priceRange['max'];

        if(count($input['sizes']) != 0)
        {
            $sql_sizes = " AND id IN (SELECT productid FROM productidsizeid WHERE sizeid in (";
            for ($i=0; $i < count($input['sizes']); ++$i)
            { 
                if($i == count($input['sizes'])-1)
                {
                    $sql_sizes .= $input['sizes'][$i] . "))";
                }
                else
                {
                    $sql_sizes .= $input['sizes'][$i] . ", ";
                }
            }
            $sql .= $sql_sizes;
        }
        if(count($input['countries']) != 0)
        {
            $sql_countries = " AND country_product_id IN (";
            for ($i=0; $i < count($input['countries']); ++$i)
            { 
                if($i == count($input['countries'])-1)
                {
                    $sql_countries .= $input['countries'][$i] . ")";
                }
                else
                {
                    $sql_countries .= $input['countries'][$i] . ", ";
                }
            }
            $sql .= $sql_countries;
            error_log($sql);
        }
        if($input['sort'] != 'rating')
        {
            $sql .= ' ORDER BY price ' . $input['sort'];
        }
    

        $sth = $this->model->getDB()->prepare($sql); 
        $sth->execute($params);
        $items = $sth->fetchAll(PDO::FETCH_ASSOC);

        print_r(json_encode($items));  
    }

    public function getProductsWithoutFilters(string $category, string $categorysub, string $categorysubsub)
    {
        $items = [];
        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND category_id = (SELECT id FROM category WHERE LOWER(title) =  :category)
        AND category_sub_id = (SELECT id FROM categorysub WHERE LOWER(title) =  :category_sub)
        AND category_sub_sub_id = (SELECT id FROM categorysubsub WHERE LOWER(title) =  :category_sub_sub);";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':category' => $category,
            ':category_sub' => $categorysub,
            ':category_sub_sub' => $categorysubsub . "_" . $category,
        ]);

        $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($items)); 
    }

    public function getProductsWithFilters(string $category, string $categorysub, string $categorysubsub, ?string $price = null, ?string $sort = null)
    {
        $items = [];
        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND category_id = (SELECT id FROM category WHERE LOWER(title) =  :category)
        AND category_sub_id = (SELECT id FROM categorysub WHERE LOWER(title) =  :category_sub)
        AND category_sub_sub_id = (SELECT id FROM categorysubsub WHERE LOWER(title) =  :category_sub_sub)";

        $params = [
            ':category' => strtolower($category),
            ':category_sub' => strtolower($categorysub),
            ':category_sub_sub' => strtolower($categorysubsub) . "_" . strtolower($category),
        ];

        $input = json_decode(file_get_contents('php://input'), true);
        if(count($input['brands']) != 0)
        {
            $sql_brands = " AND brand_id in (";
            for ($i=0; $i < count($input['brands']); ++$i)
            { 
                if($i == count($input['brands'])-1)
                {
                    $sql_brands .= $input['brands'][$i] . ")";
                }
                else
                {
                    $sql_brands .= $input['brands'][$i] . ", ";
                }
            }
            $sql .= $sql_brands;
        }
        
        $priceRange = $input['priceRange'];
        $sql .= " AND price BETWEEN " . $priceRange['min']  . " AND " . $priceRange['max'];

        if(count($input['sizes']) != 0)
        {
            $sql_sizes = " AND id IN (SELECT productid FROM productidsizeid WHERE sizeid in (";
            for ($i=0; $i < count($input['sizes']); ++$i)
            { 
                if($i == count($input['sizes'])-1)
                {
                    $sql_sizes .= $input['sizes'][$i] . "))";
                }
                else
                {
                    $sql_sizes .= $input['sizes'][$i] . ", ";
                }
            }
            $sql .= $sql_sizes;
        }
        if(count($input['countries']) != 0)
        {
            $sql_countries = " AND country_product_id IN (";
            for ($i=0; $i < count($input['countries']); ++$i)
            { 
                if($i == count($input['countries'])-1)
                {
                    $sql_countries .= $input['countries'][$i] . ")";
                }
                else
                {
                    $sql_countries .= $input['countries'][$i] . ", ";
                }
            }
            $sql .= $sql_countries;
            error_log($sql);
        }
        if($input['sort'] != 'rating')
        {
            $sql .= ' ORDER BY price ' . $input['sort'];
        }
        
        // file_put_contents('D:/log.txt', print_r($sql,true), FILE_APPEND);

        $sth = $this->model->getDB()->prepare($sql);
        $sth->execute($params);
        $items = $sth->fetchAll(PDO::FETCH_ASSOC);

        print_r(json_encode($items));
    }

    public function getHiddenProducts()
    {
        $items = [];
        $sql = "SELECT * FROM products WHERE is_hidden = 1";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
        ]);

        $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($items));
    }

    public function getProductsSale()
    {
        $items = [];
        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND price_with_discount IS NOT NULL ";

        $params = [
        ];

        $input = json_decode(file_get_contents('php://input'), true);
        if(count($input['brands']) != 0)
        {
            $sql_brands = "AND brand_id in (";
            for ($i=0; $i < count($input['brands']); ++$i)
            { 
                if($i == count($input['brands'])-1)
                {
                    $sql_brands .= $input['brands'][$i] . ")";
                }
                else
                {
                    $sql_brands .= $input['brands'][$i] . ", ";
                }
            }
            $sql .= $sql_brands;
        }
        
        $priceRange = $input['priceRange'];
        $sql .= " AND price BETWEEN " . $priceRange['min']  . " AND " . $priceRange['max'];

        if(count($input['sizes']) != 0)
        {
            $sql_sizes = " AND id IN (SELECT productid FROM productidsizeid WHERE sizeid in (";
            for ($i=0; $i < count($input['sizes']); ++$i)
            { 
                if($i == count($input['sizes'])-1)
                {
                    $sql_sizes .= $input['sizes'][$i] . "))";
                }
                else
                {
                    $sql_sizes .= $input['sizes'][$i] . ", ";
                }
            }
            $sql .= $sql_sizes;
        }
        if(count($input['countries']) != 0)
        {
            $sql_countries = " AND country_product_id IN (";
            for ($i=0; $i < count($input['countries']); ++$i)
            { 
                if($i == count($input['countries'])-1)
                {
                    $sql_countries .= $input['countries'][$i] . ")";
                }
                else
                {
                    $sql_countries .= $input['countries'][$i] . ", ";
                }
            }
            $sql .= $sql_countries;
            error_log($sql);
        }
        if($input['sort'] != 'rating')
        {
            $sql .= ' ORDER BY price ' . $input['sort'];
        }
    
        $sth = $this->model->getDB()->prepare($sql);
        $sth->execute($params);
        $items = $sth->fetchAll(PDO::FETCH_ASSOC);

        print_r(json_encode($items));
    }
    public function getProductsNew()
    {
        $items = [];
        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND new_product = 1 ";

        $params = [
        ];

        $input = json_decode(file_get_contents('php://input'), true);
        if(count($input['brands']) != 0)
        {
            $sql_brands = "AND brand_id in (";
            for ($i=0; $i < count($input['brands']); ++$i)
            { 
                if($i == count($input['brands'])-1)
                {
                    $sql_brands .= $input['brands'][$i] . ")";
                }
                else
                {
                    $sql_brands .= $input['brands'][$i] . ", ";
                }
            }
            $sql .= $sql_brands;
        }
        
        $priceRange = $input['priceRange'];
        $sql .= " AND price BETWEEN " . $priceRange['min']  . " AND " . $priceRange['max'];

        if(count($input['sizes']) != 0)
        {
            $sql_sizes = " AND id IN (SELECT productid FROM productidsizeid WHERE sizeid in (";
            for ($i=0; $i < count($input['sizes']); ++$i)
            { 
                if($i == count($input['sizes'])-1)
                {
                    $sql_sizes .= $input['sizes'][$i] . "))";
                }
                else
                {
                    $sql_sizes .= $input['sizes'][$i] . ", ";
                }
            }
            $sql .= $sql_sizes;
        }
        if(count($input['countries']) != 0)
        {
            $sql_countries = " AND country_product_id IN (";
            for ($i=0; $i < count($input['countries']); ++$i)
            { 
                if($i == count($input['countries'])-1)
                {
                    $sql_countries .= $input['countries'][$i] . ")";
                }
                else
                {
                    $sql_countries .= $input['countries'][$i] . ", ";
                }
            }
            $sql .= $sql_countries;
            error_log($sql);
        }
        if($input['sort'] != 'rating')
        {
            $sql .= ' ORDER BY price ' . $input['sort'];
        }
    
        $sth = $this->model->getDB()->prepare($sql);
        $sth->execute($params);
        $items = $sth->fetchAll(PDO::FETCH_ASSOC);

        print_r(json_encode($items));
    }

    private function getBrandProductById(string $id)
    {
        $sql = "SELECT title FROM brands WHERE id = (SELECT brand_id FROM products WHERE id = :id);";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)  
        ]);
        $result = $sth->fetchAll();
        return $result;
    }

    private function getCategorySubSubProductById(string $id)
    {
        $sql = "SELECT title_ua FROM categorysubsub WHERE id = (SELECT category_sub_sub_id FROM products WHERE id = :id);";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)  
        ]);
        $result = $sth->fetchAll();
        return $result;
    }

    private function getColorProductById(string $id)
    {
        $sql = "SELECT title FROM colors WHERE id = (SELECT color_id FROM products WHERE id = :id);";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)  
        ]);
        $result = $sth->fetchAll();
        return $result;
    }

    private function getMaterialProductById(string $id)
    {
        $sql = "SELECT title FROM materials WHERE id = (SELECT material_id FROM products WHERE id = :id);";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)  
        ]);
        $result = $sth->fetchAll();
        return $result;
    }

    private function getCountryProductById(string $id)
    {
        $sql = "SELECT title FROM countriesproduct WHERE id = (SELECT country_product_id FROM products WHERE id = :id);";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)  
        ]);
        $result = $sth->fetchAll();
        return $result;
    }

    public function getProductCharacteristics(string $id)
    {
        $items = [];
        $result = $this->getBrandProductById($id);
        $items['brand'] = $result[0][0];

        $result = $this->getCategorySubSubProductById($id);
        $items['type'] = $result[0][0];

        $result = $this->getColorProductById($id);
        $items['color'] = $result[0][0];

        $result = $this->getMaterialProductById($id);
        $items['material'] = $result[0][0];

        $result = $this->getCountryProductById($id);
        $items['country'] = $result[0][0];

        print_r(json_encode($items)); 
    }

    public function getSaleProducts()
    {
        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND price_with_discount IS NOT NULL";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([    
        ]);

        $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        print_r(json_encode($items));
    }

    public function getNewProducts()
    {
        $sql = "SELECT * FROM products WHERE is_hidden = 0 AND new_product = 1";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([    
        ]);

        $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        print_r(json_encode($items));
    }

    
    public function getProductById(string $id)
    {
        $items = [];


        $sql = "SELECT * FROM products WHERE id = :id;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)    
        ]);

        $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($items));  
    }
    
    public function changeIsHidden(string $id)
    {
        $sql = "UPDATE products SET is_hidden = NOT is_hidden WHERE id = :id;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':id' => intval($id)
        ]);

        // $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        // print_r(json_encode($items)); 
    }
    
}