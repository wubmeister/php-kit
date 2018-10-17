<?php

namespace AuthKit\Acl;

class Acl
{
    protected static $roles = [];
    protected static $superRole = 'Super';

    public static function setSuperRole(string $role)
    {
        self::$superRole = $role;
    }

    public static function allow(string $role, string $resource, string $action = '*') {
        self::setRule($role, $resource, $action, true);
    }

    public static function deny(string $role, string $resource, string $action = '*') {
        self::setRule($role, $resource, $action, false);
    }

    protected static function setRule(string $role, string $resource, string $action, bool $allowed)
    {
        if (!isset(self::$roles[$role])) {
            self::createRole($role);
        }

        if (!isset(self::$roles[$role]['access'][$resource])) {
            self::$roles[$role]['access'][$resource] = [];
        }

        self::$roles[$role]['access'][$resource][$action] = true;
    }

    public static function createRole(string $role, $extends = null)
    {
        self::$roles[$role] = [
            'extends' => $extends,
            'access' => []
        ];
    }

    public static function getRole(string $role)
    {
        return self::$roles[$role];
    }

    public static function isAllowed(string $role, string $resource, string $action = '*')
    {
        if ($role == self::$superRole) {
            return true;
        }

        if (!isset(self::$roles[$role])) {
            return false;
        }

        if (!isset(self::$roles[$role]['access'][$resource]) || (!isset(self::$roles[$role]['access'][$resource][$action]) && !isset(self::$roles[$role]['access'][$resource]['*']))) {
            if (self::$roles[$role]['extends']) {
                return self::isAllowed(self::$roles[$role]['extends'], $resource, $action);
            } else {
                return false;
            }
        }

        if (!isset(self::$roles[$role]['access'][$resource][$action])) {
            return self::$roles[$role]['access'][$resource]['*'];
        }

        return self::$roles[$role]['access'][$resource][$action];
    }
}
