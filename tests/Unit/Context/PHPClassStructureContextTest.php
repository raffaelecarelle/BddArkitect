<?php

namespace BddArkitect\Tests\Unit\Context;

use BddArkitect\Context\PHPClassStructureContext;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Unit tests for PHPClassStructureContext
 */
class PHPClassStructureContextTest extends TestCase
{
    private vfsStreamDirectory $root;
    private PHPClassStructureContext $context;
    private string $projectRoot;

    protected function setUp(): void
    {
        // Set up a virtual file system for testing
        $this->root = vfsStream::setup('testRoot', null, [
            'src' => [
                'Controller' => [
                    'UserController.php' => '<?php
namespace App\Controller;

use App\Contracts\UserRepositoryInterface;

final class UserController
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return $this->userRepository->findAll();
    }
}',
                    'ProductController.php' => '<?php
namespace App\Controller;

use App\Contracts\ProductRepositoryInterface;

final class ProductController
{
    private $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        return $this->productRepository->findAll();
    }
}',
                ],
                'Service' => [
                    'UserService.php' => '<?php
namespace App\Service;

use App\Contracts\ServiceInterface;

final class UserService implements ServiceInterface
{
    public function process($data, $options = [], $config = null, $extra = false)
    {
        // Process data
        return $data;
    }
}',
                ],
                'Repository' => [
                    'BaseRepository.php' => '<?php
namespace App\Repository;

abstract class BaseRepository
{
    protected $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll()
    {
        // Find all entities
        return [];
    }
}',
                    'UserRepository.php' => '<?php
namespace App\Repository;

use App\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function findByUsername($username)
    {
        // Find user by username
        return null;
    }
}',
                ],
                'ValueObject' => [
                    'EmailValueObject.php' => '<?php
namespace App\ValueObject;

final class EmailValueObject
{
    private $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getValue(): string
    {
        return $this->email;
    }
}',
                ],
                'Contracts' => [
                    'ServiceInterface.php' => '<?php
namespace App\Contracts;

interface ServiceInterface
{
    public function process($data, $options = [], $config = null, $extra = false);
}',
                    'UserRepositoryInterface.php' => '<?php
namespace App\Contracts;

interface UserRepositoryInterface
{
    public function findAll();
    public function findByUsername($username);
}',
                ],
                'Event' => [
                    'UserCreatedEvent.php' => '<?php
namespace App\Event;

use \AsEventListener;

#[AsEventListener]
final class UserCreatedEvent
{
    private $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }
}',
                ],
                'Command' => [
                    'CreateUserCommand.php' => '<?php
namespace App\Command;

final class CreateUserCommand
{
    private $username;
    private $email;

    public function __construct(string $username, string $email)
    {
        $this->username = $username;
        $this->email = $email;
    }

    public function execute()
    {
        // Execute command
    }
}',
                ],
                'AbstractClass' => [
                    'AbstractEntity.php' => '<?php
namespace App\AbstractClass;

abstract class AbstractEntity
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}',
                ],
            ],
        ]);

        $this->projectRoot = vfsStream::url('testRoot');
        $this->context = new PHPClassStructureContext($this->projectRoot);
    }

    public function testIHaveAPhpClassMatchingPattern(): void
    {
        // Test that the method correctly finds classes matching a pattern
        $this->context->iHaveAPhpClassMatchingPattern('*Controller');

        // This method doesn't return anything, so we're just testing that it doesn't throw an exception
        $this->addToAssertionCount(1);
    }

    public function testIAmAnalyzingTheClass(): void
    {
        // Test that the method correctly sets the class to analyze
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');

        // This method doesn't return anything, so we're just testing that it doesn't throw an exception
        $this->addToAssertionCount(1);
    }

    public function testTheProjectFollowsPhpBestPractices(): void
    {
        // Test that the method correctly sets up PHP best practices
        $this->context->theProjectFollowsPhpBestPractices();

        // This method doesn't return anything, so we're just testing that it doesn't throw an exception
        $this->addToAssertionCount(1);
    }

    public function testTheClassShouldBeFinal(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');

        // Test that the method correctly validates that a class is final
        $this->context->theClassShouldBeFinal();

        // Test that the method throws an exception for non-final classes
        $this->context->iAmAnalyzingTheClass('App\Repository\UserRepository');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldBeFinal();
    }

    public function testTheClassShouldNotBeFinal(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Repository\UserRepository');

        // Test that the method correctly validates that a class is not final
        $this->context->theClassShouldNotBeFinal();

        // Test that the method throws an exception for final classes
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldNotBeFinal();
    }

    public function testTheClassShouldBeInterface(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Contracts\ServiceInterface');

        // Test that the method correctly validates that a class is an interface
        $this->context->theClassShouldBeInterface();

        // Test that the method throws an exception for non-interface classes
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldBeInterface();
    }

    public function testTheClassShouldNotBeInterface(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');

        // Test that the method correctly validates that a class is not an interface
        $this->context->theClassShouldNotBeInterface();

        // Test that the method throws an exception for interface classes
        $this->context->iAmAnalyzingTheClass('App\Contracts\ServiceInterface');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldNotBeInterface();
    }

    public function testTheClassShouldBeAbstract(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\AbstractClass\AbstractEntity');

        // Test that the method correctly validates that a class is abstract
        $this->context->theClassShouldBeAbstract();

        // Test that the method throws an exception for non-abstract classes
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldBeAbstract();
    }

    public function testTheClassShouldNotBeAbstract(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');

        // Test that the method correctly validates that a class is not abstract
        $this->context->theClassShouldNotBeAbstract();

        // Test that the method throws an exception for abstract classes
        $this->context->iAmAnalyzingTheClass('App\AbstractClass\AbstractEntity');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldNotBeAbstract();
    }

    public function testTheClassShouldImplement(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Service\UserService');

        // Test that the method correctly validates that a class implements an interface
        $this->context->theClassShouldImplement('App\Contracts\ServiceInterface');

        // Test that the method throws an exception for classes that don't implement the interface
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldImplement('App\Contracts\ServiceInterface');
    }

    public function testTheClassShouldExtend(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Repository\UserRepository');

        // Test that the method correctly validates that a class extends another class
        $this->context->theClassShouldExtend('App\Repository\BaseRepository');

        // Test that the method throws an exception for classes that don't extend the class
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldExtend('App\Repository\BaseRepository');
    }

    public function testMethodsShouldHaveMaximumArguments(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->context->iInspectTheClassMethods();

        // Test that the method correctly validates the maximum number of arguments
        $this->context->methodsShouldHaveMaximumArguments(1);

        // Test that the method throws an exception for methods with more arguments
        $this->context->iAmAnalyzingTheClass('App\Service\UserService');
        $this->context->iInspectTheClassMethods();
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->methodsShouldHaveMaximumArguments(1);
    }

    public function testTheClassShouldHaveAttribute(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Event\UserCreatedEvent');
        $this->context->iExamineTheClassAttributes();

        // Test that the method correctly validates that a class has an attribute
        $this->context->theClassShouldHaveAttribute('AsEventListener');

        // Test that the method throws an exception for classes without the attribute
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->context->iExamineTheClassAttributes();
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldHaveAttribute('AsEventListener');
    }

    public function testTheClassShouldNotHaveAttribute(): void
    {
        // Set up the context
        $this->context->iAmAnalyzingTheClass('App\Controller\UserController');
        $this->context->iExamineTheClassAttributes();

        // Test that the method correctly validates that a class doesn't have an attribute
        $this->context->theClassShouldNotHaveAttribute('AsEventListener');

        // Test that the method throws an exception for classes with the attribute
        $this->context->iAmAnalyzingTheClass('App\Event\UserCreatedEvent');
        $this->context->iExamineTheClassAttributes();
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->theClassShouldNotHaveAttribute('AsEventListener');
    }
}
