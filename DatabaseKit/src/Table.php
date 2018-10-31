<?php

namespace DatabaseKit;

use DatabaseKit\Table\Rowset;
use DatabaseKit\Table\Row;

class Table
{
    protected $name;
    protected $db;

    public function __construct($name, Database $db)
    {
        $this->name = $name;
        $this->db = $db;
    }

    public function findAll($where = null)
    {
        $query = new Query($this->db);
        $query->select()->from($this->name);
        if ($where) {
            $query->where($where);
        }

        return new Rowset($this, $query);
    }

    public function findOne($where = null)
    {
        $query = new Query($this->db);
        $query->select()->from($this->name);
        if ($where) {
            $query->where($where);
        }

        $statement = $query->execute();
        return new Row($this, $statement->fetch(\PDO::FETCH_ASSOC));
    }

    public function insert($values)
    {
        $columns = $this->getColumns();
        if (in_array('created', $columns) && !isset($values['created'])) {
            $values['created'] = date('Y-m-d H:i:s');
        }

        $query = new Query($this->db);
        $query->insert()->into($this->name)->values($values);
        if (!$query->execute()) {
            $err = $statement->errorInfo();
            throw new Exception("Failed to insert data: {$err[2]}");
        }
        return $this->db->lastInsertId();
    }

    public function update($values, $where = null)
    {
        $columns = $this->getColumns();
        if (in_array('modified', $columns) && !isset($values['modified'])) {
            $values['modified'] = date('Y-m-d H:i:s');
        }

        $query = new Query($this->db);
        $query->update()->table($this->name)->values($values);
        if ($where) {
            $query->where($where);
        }
        if (!($statement = $query->execute())) {
            $err = $statement->errorInfo();
            throw new Exception("Failed to update data: {$err[2]}");
        }
        return $statement->rowCount();
    }

    public function delete($where = null)
    {
        $query = new Query($this->db);
        $query->delete()->from($this->name);
        if ($where) {
            $query->where($where);
        }
        $statement = $query->execute();
        return $statement->rowCount();
    }

    protected function getColumns()
    {
        $sql = "SHOW COLUMNS FROM " . $this->db->quoteIdentifier($this->name);
        $rows = $this->db->fetchAll($sql);
        $columns = [];
        foreach ($rows as $row) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }
}
