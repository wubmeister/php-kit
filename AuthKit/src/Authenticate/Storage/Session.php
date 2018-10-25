<?php

namespace AuthKit\Authenticate\Storage;

use AuthKit\Authenticate\Identity;

class Session implements StorageInterface
{
    public function storeIdentity(Identity $identity)
    {
        $_SESSION['authkit_identity'] = $identity;
    }

    public function getIdentity()
    {
        return isset($_SESSION['authkit_identity']) ? $_SESSION['authkit_identity'] : null;
    }

    public function hasIdentity()
    {
        return isset($_SESSION['authkit_identity']);
    }

    public function clearIdentity()
    {
        if (isset($_SESSION['authkit_identity'])) {
            unset($_SESSION['authkit_identity']);
        }
    }
}
