<?php

declare(strict_types=1);

class UserModel extends BaseModel
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

    public function getUsersList(): array
    {
        $items = [];
        $sql = "SELECT * FROM users ORDER BY id ASC;";
        $sth = $this->dbh->query($sql, PDO::FETCH_ASSOC);

        if ($sth->rowCount() > 0)
        {
            $items = $sth->fetchAll();
        }
        return $items;
    }

    public function getDB() : PDO
    {
        return $this->dbh;
    }
}