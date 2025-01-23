<?php

declare(strict_types=1);

abstract class BaseModel
{
    protected PDO $dbh;

    protected function initConnection(array $params): void
    {
        extract($params);

        try
        {
            $this->dbh = new PDO($dsn, $user, $password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            throw new Exception("Error! Code: {$e->getCode()}. Message: {$e->getMessage()}");
        }
    }
}