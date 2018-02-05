<?php

namespace DiskApp\Repository;

use Silex\Application;
use PHPUnit\Framework\TestCase;

use DiskApp\Controller\BaseController;
use DiskApp\Model\User;

class UserRepositoryTest extends TestCase
{
    private $users;
    private $dbConnection;
 
    protected function setUp()
    {
        $app = new Application();
        require __DIR__ . '/../../../src/app.php';

        $this->dbConnection = $app['db'];
        $this->users = new UserRepository($this->dbConnection);
    }
 
    protected function tearDown()
    {
        $this->deleteTestUsers();
        $this->dbConnection->close();
        $this->users = null;
    }

    public function insertTestUser($username, $hash)
    {
        $this->dbConnection->executeQuery(
            'INSERT INTO users (username, hash) VALUES (?, ?)', 
            [
                $username, $hash
            ]
        );

        return $this->dbConnection->lastInsertId();
    }

    public function deleteTestUsers()
    {
        $this->dbConnection->executeQuery('DELETE FROM users');
    }
 
    public function testAdd()
    {
        $user = new User(null, 'petya', hash('sha256', 'petya' . BaseController::SALT, false));
        $actual = $this->users->add($user);

        $this->assertEquals($actual, null);
    }

    public function testAddDuplicateUser()
    {
        try
        {
            $this->insertTestUser('petya', hash('sha256', 'petya' . BaseController::SALT, false));
            $user = new User(null, 'petya', hash('sha256', 'petya' . BaseController::SALT, false));
            $actual = $this->users->add($user);

            $this->assertEquals($actual, null);
        }
        catch (UserRepositoryException $ex)
        {
            $this->assertEquals($ex->getMessage(), 'user with that username already exists!');
        }
    }

    public function testGetByUsername()
    {
        $this->insertTestUser('petya', hash('sha256', 'petya' . BaseController::SALT, false));
        $actual = $this->users->getByUsername('petya');

        $this->assertEquals($actual->getUsername(), 'petya');
    }

    public function testGetByUsernameNoUser()
    {
        try
        {
            $this->insertTestUser('petya', hash('sha256', 'petya' . BaseController::SALT, false));
            $actual = $this->users->getByUsername('sasha');
        }
        catch (UserRepositoryException $ex)
        {
            $this->assertEquals($ex->getMessage(), 'user with this username does not exist!');
        }
    }
}