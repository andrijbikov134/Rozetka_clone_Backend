<?php

declare(strict_types=1);

class FilterModel extends BaseModel
{
    public function __construct(array $params)
    {
        parent::initConnection($params);
    }

    /**
     * Get all Users list.
     *
     * @return array
     */

    public function getDB() : PDO
    {
        return $this->dbh;
    }
}