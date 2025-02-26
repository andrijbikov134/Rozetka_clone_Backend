<?php

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
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id'];
        $title = $input['title'];
        $color_id = $input['color_id'];
        $brand_id = $input['brand_id'];
        $price = $input['price'];
        $material_id = $input['material_id'];
        $country_product_id = $input['country_product_id'];
        $part_number = $input['part_number'];
        $category = $input['category'];
        $categorySub = $input['categorySub'];
        $categorySubSub = $input['categorySubSub'];

        $category_id = $this->getCategoryIdByTitle($category);
        $categorysub_id = $this->getCategorySubIdByTitle($categorySub);
        $categorysubsub_id = $this->getCategorySubSubIdByTitle($category, $categorySubSub);

        if($id == null)
        {
            $sql = "INSERT INTO products (id, title, color_id, brand_id, price, material_id, country_product_id, part_number, category_id, category_sub_id, category_sub_sub_id) VALUES (:id, :title, :color_id, :brand_id, :price, :material_id, :country_product_id, :part_number, :category_id, :category_sub_id, :category_sub_sub_id);";
            $sth = $this->model->getDB()->prepare($sql);
    
            $created = $sth->execute([ 
                ':id' => NULL,
                ':title' => $title,
                ':color_id' => $color_id,
                ':brand_id' => $brand_id,
                ':price' => $price,
                ':material_id' => $material_id,
                ':country_product_id' => $country_product_id,
                ':part_number' => $part_number,
                ':category_id' => $category_id,
                ':category_sub_id' => $categorysub_id,
                ':category_sub_sub_id' => $categorysubsub_id,

            ]);

            $product_id = $this->model->getDB()->lastInsertId();
            $sizes = $input['sizes'];

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
            $sql = "UPDATE products set title = :title, color_id = :color_id, brand_id = :brand_id, price = :price, material_id = :material_id, country_product_id = :country_product_id, part_number = :part_number, category_id = :category_id, category_sub_id = :category_sub_id, category_sub_sub_id = :category_sub_sub_id WHERE id = :id;";
            $sth = $this->model->getDB()->prepare($sql);
    
            $created = $sth->execute([ 
                ':id' => $id,
                ':title' => $title,
                ':color_id' => $color_id,
                ':brand_id' => $brand_id,
                ':price' => $price,
                ':material_id' => $material_id,
                ':country_product_id' => $country_product_id,
                ':part_number' => $part_number,
                ':category_id' => $category_id,
                ':category_sub_id' => $categorysub_id,
                ':category_sub_sub_id' => $categorysubsub_id,

            ]);

            $sizes = $input['sizes'];

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

    public function deleteProductFromDB()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id'];

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
            print_r(json_encode(["error" => "OK"]));
        }
        else
        {
            print_r(json_encode(["error" => "Існують замовлення з обраним товаром! Товар можна тільки приховати!"]));
        }

    }

    public function getCategories(string $category, string $categorysub)
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

        $sql = "SELECT * FROM products WHERE LOWER(title) LIKE :input_title;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':input_title' => '%' .  $input_title . "%"    
        ]);

        $items = $sth->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($items));  
    }

    public function getProductsWithoutFilters(string $category, string $categorysub, string $categorysubsub)
    {
        $items = [];
        $sql = "SELECT * FROM products WHERE category_id = (SELECT id FROM category WHERE LOWER(title) =  :category)
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
        $sql = "SELECT * FROM products WHERE category_id = (SELECT id FROM category WHERE LOWER(title) =  :category)
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

    public function getCategorySubSubTitle(string $categorysubsub)
    {
        $sql = "SELECT title_ua FROM categorysubsub WHERE LOWER(title) = :categorysubsub;";
        $sth = $this->model->getDB()->prepare($sql); 
        
        $sth->execute([ 
            ':categorysubsub' => $categorysubsub  
        ]);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($result));  
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
  

    public function getPopularProducts()
    {
        $data = $this->model->getProductsList();
        print_r(json_encode($data));
    }
}