<?php

class ProductsController
{
    public function __construct(
        protected ProductModel $model
    )
    {
    }

    private function addNewUserInDB(string $name, string $email, string $status, string $type, string $ssn)
    {
        $sql = "INSERT INTO users (id, name, email, status, type, ssn) VALUES (:id, :name, :email, :status, :type, :ssn);";
        $sth = $this->model->getDB()->prepare($sql);

        $created = $sth->execute([ 
            ':id' => NULL,
            ':name' => $name,
            ':email' => $email,
            ':status' => $status,
            ':type' => $type,
            ':ssn' => $ssn,
        ]);
    }

    public function getCategories(string $category, string $categorysub)
    {
        $items = [];

        $sql = "SELECT * FROM categorysubsub WHERE title LIKE :category AND categorysub_id = (SELECT id FROM categorysub WHERE title =  :category_sub);";
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