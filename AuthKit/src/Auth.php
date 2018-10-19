<?php

namespace AuthKit;

use Psr\Http\Message\ServerRequestInterface;
use AuthKit\Authenticate\Adapter\AbstractAdapter;
use AuthKit\Authenticate\Storage\StorageInterface;

/**
 * Class to handle authentication calls
 */
class Auth
{
    protected $adapter;
    protected $storage;

    /**
     * Creates a new instance
     */
    public function __construct(AbstractAdapter $adapter, StorageInterface $storage)
    {
        $this->adapter = $adapter;
        $this->storage = $storage;
    }

    /**
     * Gets the current logged in identity, if any.
     *
     * @return object|null The identity
     */
    public function getIdentity()
    {
        return $this->storage->getIdentity();
    }

    /**
     * Checks if there's a logged in identity.
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return $this->storage->hasIdentity();
    }

    /**
     * Attempts to authenticate a user based on the current request.
     *
     * @param ServerRequestInterface $request The request
     * @param callable $onSuccess A function or invokable object to call when the authentication was successful.
     *    The callback gets passed the logged in identity as a parameter.
     * @param callable $onSuccess A function or invokable object to call when the authentication failed.
     *    The callback gets passed the error message as a parameter.
     */
    public function authenticate(ServerRequestInterface $request, callable $onSuccess = null, callable $onError = null)
    {
        $this->adapter->handleRequest($request);
        $status = $this->adapter->getStatus();
        if ($status == AbstractAdapter::STATUS_SUCCESS) {
            $identity = $this->adapter->getIdentity();
            $this->storage->storeIdentity($identity);
            if ($onSuccess) {
                $onSuccess($identity);
            }
        } else if ($status == AbstractAdapter::STATUS_ERROR) {
            if ($onError) {
                $onError($this->adapter->getError());
            }
        }
    }
}
