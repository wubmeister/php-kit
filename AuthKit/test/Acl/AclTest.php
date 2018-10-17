<?php

use PHPUnit\Framework\TestCase;

use AuthKit\Acl\Acl;

class AuthKit_Acl_AclTest extends TestCase
{
    public function testAddRole()
    {
        Acl::createRole('MyRole');
        Acl::createRole('YourRole', 'MyRole');

        $myRole = Acl::getRole('MyRole');
        $this->assertInternalType('array', $myRole);
        $this->assertNull($myRole['extends']);
        $this->assertInternalType('array', $myRole['access']);

        $yourRole = Acl::getRole('YourRole');
        $this->assertEquals('MyRole', $yourRole['extends']);
        $this->assertInternalType('array', $yourRole['access']);
    }

    public function testAccess()
    {
        Acl::createRole('MyRole');
        Acl::createRole('YourRole', 'MyRole');
        Acl::allow('MyRole', 'MyResource', 'edit');
        Acl::allow('YourRole', 'YourResource', 'edit');

        $this->assertTrue(Acl::isAllowed('MyRole', 'MyResource', 'edit'));
        $this->assertTrue(Acl::isAllowed('YourRole', 'MyResource', 'edit'));
        $this->assertTrue(Acl::isAllowed('YourRole', 'YourResource', 'edit'));
        $this->assertFalse(Acl::isAllowed('MyRole', 'YourResource', 'edit'));
    }
}
