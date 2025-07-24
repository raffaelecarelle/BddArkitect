<?php

namespace BddArkitect\Tests\Integration;

use BddArkitect\Context\FileStructureContext;
use BddArkitect\Context\NamespaceStructureContext;
use BddArkitect\Context\PHPClassStructureContext;
use BddArkitect\Extension\ArkitectConfiguration;
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Integration tests for Context classes working together
 */
class ContextIntegrationTest extends TestCase
{
    private vfsStreamDirectory $root;
    private string $projectRoot;
    private FileStructureContext $fileContext;
    private NamespaceStructureContext $namespaceContext;
    private PHPClassStructureContext $classContext;
    private ArkitectConfiguration $configuration;

    protected function setUp(): void
    {
        // Set up a virtual file system for testing
        $this->root = vfsStream::setup('testRoot', null, [
            'src' => [
                'Controller' => [
                    'UserEntityController.php' => '<?php
namespace App\Controller;

use App\Contracts\UserRepositoryInterface;

final class UserEntityController
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
            ],
            'tests' => [
                'Unit' => [],
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
        $this->configuration = new ArkitectConfiguration($this->projectRoot);
        $this->fileContext = new FileStructureContext($this->projectRoot);
        $this->fileContext->setConfiguration($this->configuration);
        $this->namespaceContext = new NamespaceStructureContext($this->projectRoot);
        $this->namespaceContext->setConfiguration($this->configuration);
        $this->classContext = new PHPClassStructureContext($this->projectRoot);
        $this->classContext->setConfiguration($this->configuration);
    }

    /**
     * Test that the file structure, namespace structure, and class structure contexts
     * work together to validate controller classes
     */
    public function testControllerValidation(): void
    {
        // File structure validation
        $this->fileContext->iHaveAProjectInDirectory('src/Controller');
        $this->fileContext->iAmCheckingFilesMatchingPattern('*.php');
        $this->fileContext->filesShouldFollowNamingPattern('*Controller.php');
        $this->fileContext->filesShouldHaveExtension('php');

        // Namespace structure validation
        $this->namespaceContext->iHaveAPsr4CompliantProject();
        $this->namespaceContext->iAmAnalyzingClassesInNamespace('App\Controller');
        $this->namespaceContext->namespaceShouldExist('App\Controller');
        $this->namespaceContext->namespaceShouldContainOnlyClassesMatchingPattern('App\Controller', '*Controller');

        // Class structure validation
        $this->classContext->iHaveAPhpClassMatchingPattern('*Controller');
        $this->classContext->iAmAnalyzingTheClass('App\Controller\UserEntityController');
        $this->classContext->theClassShouldBeFinal();
        $this->classContext->theClassShouldNotBeAbstract();
        $this->classContext->theClassShouldNotBeInterface();

        // This test passes if all the assertions above pass
        $this->addToAssertionCount(1);
    }

    /**
     * Test that the file structure, namespace structure, and class structure contexts
     * work together to validate repository classes
     */
    public function testRepositoryValidation(): void
    {
        // File structure validation
        $this->fileContext->iHaveAProjectInDirectory('src/Repository');
        $this->fileContext->iAmCheckingFilesMatchingPattern('*.php');
        $this->fileContext->filesShouldFollowNamingPattern('*Repository.php');
        $this->fileContext->filesShouldHaveExtension('php');

        // Namespace structure validation
        $this->namespaceContext->iHaveAPsr4CompliantProject();
        $this->namespaceContext->iAmAnalyzingClassesInNamespace('App\Repository');
        $this->namespaceContext->namespaceShouldExist('App\Repository');
        $this->namespaceContext->namespaceShouldContainOnlyClassesMatchingPattern('App\Repository', '*Repository');

        // Class structure validation
        $this->classContext->iHaveAPhpClassMatchingPattern('*Repository');
        $this->classContext->iAmAnalyzingTheClass('App\Repository\UserRepository');
        $this->classContext->theClassShouldNotBeFinal();
        $this->classContext->theClassShouldNotBeAbstract();
        $this->classContext->theClassShouldNotBeInterface();
        $this->classContext->theClassShouldExtend('App\Repository\BaseRepository');
        $this->classContext->theClassShouldImplement('App\Contracts\UserRepositoryInterface');

        // This test passes if all the assertions above pass
        $this->addToAssertionCount(1);
    }

    /**
     * Test that the file structure, namespace structure, and class structure contexts
     * work together to validate interface classes
     */
    public function testInterfaceValidation(): void
    {
        // File structure validation
        $this->fileContext->iHaveAProjectInDirectory('src/Contracts');
        $this->fileContext->iAmCheckingFilesMatchingPattern('*.php');
        $this->fileContext->filesShouldFollowNamingPattern('*Interface.php');
        $this->fileContext->filesShouldHaveExtension('php');

        // Namespace structure validation
        $this->namespaceContext->iHaveAPsr4CompliantProject();
        $this->namespaceContext->iAmAnalyzingClassesInNamespace('App\Contracts');
        $this->namespaceContext->namespaceShouldExist('App\Contracts');
        $this->namespaceContext->namespaceShouldContainOnlyClassesMatchingPattern('App\Contracts', '*Interface');

        // Class structure validation
        $this->classContext->iHaveAPhpClassMatchingPattern('*Interface');
        $this->classContext->iAmAnalyzingTheClass('App\Contracts\ServiceInterface');
        $this->classContext->theClassShouldBeInterface();
        $this->classContext->theClassShouldNotBeFinal();
        //        $this->classContext->theClassShouldNotBeAbstract(); TODO check it fails

        // This test passes if all the assertions above pass
        $this->addToAssertionCount(1);
    }

    /**
     * Test that the file structure, namespace structure, and class structure contexts
     * work together to validate service classes
     */
    public function testServiceValidation(): void
    {
        // File structure validation
        $this->fileContext->iHaveAProjectInDirectory('src/Service');
        $this->fileContext->iAmCheckingFilesMatchingPattern('*.php');
        $this->fileContext->filesShouldFollowNamingPattern('*Service.php');
        $this->fileContext->filesShouldHaveExtension('php');

        // Namespace structure validation
        $this->namespaceContext->iHaveAPsr4CompliantProject();
        $this->namespaceContext->iAmAnalyzingClassesInNamespace('App\Service');
        $this->namespaceContext->namespaceShouldExist('App\Service');
        $this->namespaceContext->namespaceShouldContainOnlyClassesMatchingPattern('App\Service', '*Service');

        // Class structure validation
        $this->classContext->iHaveAPhpClassMatchingPattern('*Service');
        $this->classContext->iAmAnalyzingTheClass('App\Service\UserService');
        $this->classContext->theClassShouldBeFinal();
        $this->classContext->theClassShouldNotBeAbstract();
        $this->classContext->theClassShouldNotBeInterface();
        $this->classContext->theClassShouldImplement('App\Contracts\ServiceInterface');
        $this->classContext->iInspectTheClassMethods();
        $this->classContext->methodsShouldHaveMaximumArguments(4);

        // This test passes if all the assertions above pass
        $this->addToAssertionCount(1);
    }
}
