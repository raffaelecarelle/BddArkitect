<?php

namespace BddArkitect\Tests\Integration;

use BddArkitect\Context\FileStructureContext;
use BddArkitect\Context\NamespaceStructureContext;
use BddArkitect\Context\PHPClassStructureContext;
use BddArkitect\Extension\ArkitectConfiguration;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class ConfigurationIntegrationTest extends TestCase
{
    private vfsStreamDirectory $root;
    private string $projectRoot;
    private ArkitectConfiguration $configuration;
    private FileStructureContext $fileContext;
    private NamespaceStructureContext $namespaceContext;
    private PHPClassStructureContext $classContext;

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
                    'helper.txt' => 'This is a helper file'
                ],
                'Legacy' => [
                    'OldController.php' => '<?php namespace App\Legacy; class OldController {}'
                ]
            ],
            'tests' => [
                'Unit' => [
                    'Test.php' => '<?php namespace App\Tests\Unit; class Test {}'
                ]
            ],
            'vendor' => [
                'package' => [
                    'file.php' => '<?php namespace Vendor\Package; class File {}'
                ]
            ],
            'composer.json' => '{"name": "test/project", "autoload": {"psr-4": {"App\\\\": "src/"}}}'
        ]);

        $this->projectRoot = vfsStream::url('testRoot');

        // Create configuration with paths, excludes, and ignore_errors
        $this->configuration = new ArkitectConfiguration(
            $this->projectRoot,
            ['src/Controller', 'src/Service', 'tests'],
            ['src/Legacy', 'vendor'],
            ['.*should be final.*']
        );

        // Create contexts and set configuration
        $this->fileContext = new FileStructureContext($this->projectRoot);
        $this->fileContext->setConfiguration($this->configuration);

        $this->namespaceContext = new NamespaceStructureContext($this->projectRoot);
        $this->namespaceContext->setConfiguration($this->configuration);

        $this->classContext = new PHPClassStructureContext($this->projectRoot);
        $this->classContext->setConfiguration($this->configuration);
    }

//    public function testFileContextRespectsPathsAndExcludes(): void
//    {
//        // This should find only Controller files
//        $this->fileContext->iHaveAProjectInDirectory('src');
//        $this->fileContext->iAmCheckingFilesMatchingPattern('*.php');
//
//        // This should pass because Legacy files are excluded
//        $this->fileContext->filesShouldFollowNamingPattern('*Controller.php');
//
//        // This should pass because we're only checking Controller and Service directories
//        $this->fileContext->filesShouldHaveExtension('php');
//
//        // Add an assertion to make PHPUnit happy
//        $this->addToAssertionCount(1);
//    }

    public function testNamespaceContextRespectsPathsAndExcludes(): void
    {
        $this->namespaceContext->iHaveAPsr4CompliantProject();

        // This should find only Controller classes
        $this->namespaceContext->iAmAnalyzingClassesInNamespace('App\Controller');

        // This should pass because Legacy namespace is excluded
        $this->namespaceContext->namespaceShouldContainOnlyClassesMatchingPattern('App\Controller', '*Controller');

        // Add an assertion to make PHPUnit happy
        $this->addToAssertionCount(1);
    }

    public function testClassContextRespectsPathsAndExcludes(): void
    {
        // This should find only Controller classes
        $this->classContext->iHaveAPhpClassMatchingPattern('*Controller');

        // This should pass because Legacy classes are excluded
        $this->classContext->iAmAnalyzingTheClass('App\Controller\UserController');

        // This would normally fail, but should pass because of ignore_errors
        try {
            $this->classContext->theClassShouldBeFinal();
            // If we get here, the error was ignored
            $this->addToAssertionCount(1);
        } catch (\Exception $e) {
            $this->fail('Error should have been ignored: ' . $e->getMessage());
        }
    }
}
