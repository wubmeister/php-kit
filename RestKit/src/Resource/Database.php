<?php

namespace RestKit\Resource;

use DatabaseKit\Database as Db;
use DatabaseKit\Table;
use CoreKit\FlashMessage;
use FormKit\Form;

class Database extends AbstractResource
{
    protected $table;

    public function __construct(string $name, $tableName, Db $db = null)
    {
        $this->name = $name;

        if ($tableName instanceof Table) {
            $this->table = $tableName;
        } else {
            $this->table = new \DatabaseKit\Table($tableName, $db);
        }
    }

    protected function getTable()
    {
        return $this->table;
    }

    public function index()
    {
        $table = $this->getTable();
        // $select = $table->select();
        // $this->trigger('buildIndexQuery', $select);
        $results = $table->findAll();
        // $this->trigger('parseIndexResult', $results);

        return [ 'items' => $results ];
    }

    public function detail($id)
    {
        $table = $this->getTable();
        // $this->trigger('buildDetailQuery');
        $item = $table->findOne([ 'id' => $id ]);
        if (!$item) {
            throw new NotFoundException('No item found with that ID');
        }
        $this->trigger('parseDetailResult', $item);

        return $item ? [ 'item' => $item ] : [];
    }

    public function add()
    {
        if ($this->request->getMethod() != 'POST') {
            return [];
        }

        $table = $this->getTable();
        $data = $this->request->getParsedBody();
        Form::addPostData($data);

        $canSave = true;
        $validator = $this->getInputValidator('create');
        if ($validator) {
            $validator->validate($data);
            if (!$validator->isValid()) {
                foreach ($validator->getErrors() as $key => $error) {
                    Form::addError($key, $error);
                }
                $canSave = false;
            }
        }

        if (!$canSave) {
            return [];
        }

        $newData = $this->trigger('beforeAdd', $data);
        if ($newData) $data = $newData;

        $id = $table->insert($data);
        if (!$id) return null;

        $item = $table->findOne([ 'id' => $id ]);
        $this->trigger('afterAdd', $item);

        FlashMessage::add(FlashMessage::SUCCESS, "The item has been added");

        if ($this->responseFormat == 'html') {
            header('Location:' . $this->request->getUri()->getPath());
            exit;
        }

        return $item ?? [];
    }

    public function update($id)
    {
        $table = $this->getTable();
        $item = $table->findOne([ 'id' => $id ]);
        if (!$item) {
            throw new Exception('No item found with that ID');
        }
        Form::addData($item->getArrayCopy());

        if ($this->request->getMethod() != 'POST') {
            return [ 'item' => $item ];
        }

        $data = $this->request->getParsedBody();
        Form::addPostData($data);

        $canSave = true;
        $validator = $this->getInputValidator('update');
        if ($validator) {
            $validator->validate($data);
            if (!$validator->isValid()) {
                foreach ($validator->getErrors() as $key => $error) {
                    Form::addError($key, $error);
                }
                $canSave = false;
            }
        }

        if (!$canSave) {
            return [ 'item' => $item ];
        }

        $this->trigger('beforeUpdate', $data, $item);
        foreach ($data as $key => $value) {
            $item->$key = $value;
        }
        $item->save();
        $this->trigger('afterUpdate', $item);

        FlashMessage::add(FlashMessage::SUCCESS, "The changes have been saved");

        if ($this->responseFormat == 'html') {
            header('Location:' . dirname($this->request->getUri()->getPath()));
            exit;
        }

        return [ 'item' => $item ];
    }

    public function delete($id)
    {
        if ($this->request->getMethod() != 'DELETE') {
            return [];
        }

        $table = $this->getTable();
        $item = $table->findOne([ 'id' => $id ]);
        if (!$item) {
            throw new Exception('No item found with that ID');
        }

        $this->trigger('beforeDelete', $item);
        $item->delete();
        $this->trigger('afterDelete', $item);

        FlashMessage::add(FlashMessage::SUCCESS, "The item has been deleted");

        return [ 'id' => $id ];
    }

    protected function getInputValidator($purpose)
    {
        return null;
    }
}
