<?php

namespace AuthKit\Authenticate\Adapter;

use Psr\Http\Message\ServerRequestInterface;
use AuthKit\Authenticate\Identity;

class Database extends AbstractAdapter
{
    protected $usernameAttr = 'username';
    protected $credentialAttr = 'password';
    protected $usernameColumn = 'username';
    protected $credentialColumn = 'password';
    protected $saltColumn = 'salt';
    protected $db;

    public function __construct(\DatabaseKit\Database $db, $options = [])
    {
        $this->db = $db;

        if (isset($options['usernameAttribute'])) $this->setUsernameAttribute($options['usernameAttribute']);
        if (isset($options['credentialAttribute'])) $this->setCredentialAttribute($options['credentialAttribute']);
        if (isset($options['usernameColumn'])) $this->setUsernameColumn($options['usernameColumn']);
        if (isset($options['credentialColumn'])) $this->setCredentialColumn($options['credentialColumn']);
        if (isset($options['saltColumn'])) $this->setSaltColumn($options['saltColumn']);
    }

    public function setUsernameAttribute(string $usernameAttr)
    {
        $this->usernameAttr = $usernameAttr;
    }

    public function setCredentialAttribute(string $credentialAttr)
    {
        $this->credentialAttr = $credentialAttr;
    }

    public function setUsernameColumn(string $usernameColumn)
    {
        $this->usernameColumn = $usernameColumn;
    }

    public function setCredentialColumn(string $credentialColumn)
    {
        $this->credentialColumn = $credentialColumn;
    }

    public function setSaltColumn(string $saltColumn)
    {
        $this->saltColumn = $saltColumn;
    }

    public function handleRequest(ServerRequestInterface $request)
    {
        $username = $request->getAttribute($this->usernameAttr);
        $credential = $request->getAttribute($this->credentialAttr);

        // Fetch identity
        $sql = "SELECT * FROM " . $this->db->quoteIdentifier($this->table) . " WHERE " . $this->db->quoteIdentifier($this->usernameColumn) . " = ?";
        $user = $this->db->fetchRow($sql, [ $username ]);

        $credentialHash = hash('sha256', $credential . $user[$this->saltColumn]);
        if ($credentialHash != $user[$this->credentialColumn]) {
            $this->status = self::STATUS_ERROR;
            $this->error = 'invalid_credentials';
        } else {
            $this->status = self::SUCCESS;
            unset($user[$this->credentialColumn], $user[$this->saltColumn]);
            $this->identity = new Identity($user);
        }
    }
}
