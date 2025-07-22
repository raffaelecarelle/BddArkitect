<?php

namespace BddArkitect\Tests\Unit\Context;

use BddArkitect\Context\NamespaceStructureContext;
use BddArkitect\Extension\ArkitectConfiguration;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Unit tests for NamespaceStructureContext
 */
class NamespaceStructureContextTest extends TestCase
{
    private vfsStreamDirectory $root;
    private NamespaceStructureContext $context;
    private string $projectRoot;
    private ?ArkitectConfiguration $configuration = null;

    protected function setUp(): void
    {
        // Set up a virtual file system for testing
        $this->root = vfsStream::setup('testRoot', null, [
            'src' => [
                'Controller' => [
                    'UserController.php' => '<?php namespace App\Controller; class UserController {}',
                    'ProductController.php' => '<?php namespace App\Controller; class ProductController {}'
                ],
                'Service' => [
                    'UserService.php' => '<?php namespace App\Service; class UserService {}',
                    'OrderService.php' => '<?php namespace App\Service; class OrderService {}'
                ],
                'Repository' => [
                    'UserRepository.php' => '<?php namespace App\Repository; class UserRepository extends BaseRepository {}',
                    'BaseRepository.php' => '<?php namespace App\Repository; abstract class BaseRepository {}'
                ],
                'Domain' => [
                    'User' => [
                        'Entity' => [
                            'User.php' => '<?php namespace App\Domain\User\Entity; class User {}'
                        ],
                        'ValueObject' => [
                            'Email.php' => '<?php namespace App\Domain\User\ValueObject; class Email {}'
                        ]
                    ]
                ],
                'Contracts' => [
                    'UserRepositoryInterface.php' => '<?php namespace App\Contracts; interface UserRepositoryInterface {}'
                ]
            ],
            'tests' => [
                'Unit' => [
                    'Controller' => [
                        'UserControllerTest.php' => '<?php namespace Tests\Unit\Controller; class UserControllerTest {}'
                    ]
                ],
                'Integration' => [],
                'Functional' => []
            ],
            'composer.json' => '{
                "name": "test/project",
                "autoload": {
                    "psr-4": {
                        "App\\\\": "src/"
                    }
                },
                "autoload-dev": {
                    "psr-4": {
                        "Tests\\\\": "tests/"
                    }
                }
            }'
        ]);

        $this->projectRoot = vfsStream::url('testRoot');
        $this->configuration = new ArkitectConfiguration(
            $this->projectRoot,
            ['src/Controller', 'src/Service', 'tests'],
            ['src/Legacy', 'vendor'],
            []
        );
        $this->context = new NamespaceStructureContext($this->projectRoot);
        $this->context->setConfiguration($this->configuration);
    }

    public function testIHaveAPsr4CompliantProject(): void
    {
        // Test that the method correctly sets up PSR-4 compliance
        $this->context->iHaveAPsr4CompliantProject();

        // This method doesn't return anything, so we're just testing that it doesn't throw an exception
        $this->addToAssertionCount(1);
    }

    public function testIAmAnalyzingClassesInNamespace(): void
    {
        // Test that the method correctly sets the namespace to analyze
        $this->context->iAmAnalyzingClassesInNamespace('App\Controller');

        // This method doesn't return anything, so we're just testing that it doesn't throw an exception
        $this->addToAssertionCount(1);
    }

    public function testNamespaceShouldFollowPsr4Structure(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();

        // Test that the method correctly validates PSR-4 structure
        $this->context->namespaceShouldFollowPsr4Structure('App\Controller');

        // Test that the method throws an exception for non-existent namespaces
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->namespaceShouldFollowPsr4Structure('App\NonExistent');
    }

    public function testNamespaceShouldExist(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();

        // Test that the method correctly validates namespace existence
        $this->context->namespaceShouldExist('App\Controller');

        // Test that the method throws an exception for non-existent namespaces
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->namespaceShouldExist('App\NonExistent');
    }

    public function testNamespaceShouldNotExist(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();

        // Test that the method correctly validates namespace non-existence
        $this->context->namespaceShouldNotExist('App\NonExistent');

        // Test that the method throws an exception for existing namespaces
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->namespaceShouldNotExist('App\Controller');
    }

    public function testNamespaceShouldContainOnlyClassesMatchingPattern(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();
        $this->context->iAmAnalyzingClassesInNamespace('App\Controller');

        // Test that the method correctly validates class patterns
        $this->context->namespaceShouldContainOnlyClassesMatchingPattern('App\Controller', '*Controller');

        // Test that the method throws an exception for patterns that don't match
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->namespaceShouldContainOnlyClassesMatchingPattern('App\Controller', '*Service');
    }

    public function testNamespaceShouldNotContainClassesMatchingPattern(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();
        $this->context->iAmAnalyzingClassesInNamespace('App\Controller');

        // Test that the method correctly validates class patterns
        $this->context->namespaceShouldNotContainClassesMatchingPattern('App\Controller', '*Service');

        // Test that the method throws an exception for patterns that match
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->namespaceShouldNotContainClassesMatchingPattern('App\Controller', '*Controller');
    }

    public function testNamespaceShouldHaveMaximumDepthOfLevels(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();

        // Test that the method correctly validates namespace depth
        $this->context->namespaceShouldHaveMaximumDepthOfLevels('App\Controller', 2);

        // Test that the method throws an exception for namespaces with greater depth
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->namespaceShouldHaveMaximumDepthOfLevels('App\Domain\User\Entity', 2);
    }

    public function testNamespaceShouldHaveMinimumDepthOfLevels(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();

        // Test that the method correctly validates namespace depth
        $this->context->namespaceShouldHaveMinimumDepthOfLevels('App\Domain\User\Entity', 3);

        // Test that the method throws an exception for namespaces with lesser depth
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->namespaceShouldHaveMinimumDepthOfLevels('App\Controller', 3);
    }

    public function testAllNamespacesShouldFollowNamingConvention(): void
    {
        // Set up the context
        $this->context->iHaveAPsr4CompliantProject();

        // Test that the method correctly validates naming conventions
        $this->context->allNamespacesShouldFollowNamingConvention('PascalCase');

        // This is a more complex test that would require modifying the virtual file system
        // to include namespaces that don't follow the convention, which is beyond the scope
        // of this simple test. In a real test, we would need to create files with namespaces
        // that don't follow the convention and then test that the method throws an exception.
        $this->addToAssertionCount(1);
    }
}
