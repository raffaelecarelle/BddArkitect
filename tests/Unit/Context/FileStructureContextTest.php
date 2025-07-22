<?php

namespace BddArkitect\Tests\Unit\Context;

use BddArkitect\Context\FileStructureContext;
use BddArkitect\Extension\ArkitectConfiguration;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Unit tests for FileStructureContext
 */
class FileStructureContextTest extends TestCase
{
    private vfsStreamDirectory $root;
    private FileStructureContext $context;
    private string $projectRoot;
    private ?ArkitectConfiguration $configuration = null;

    protected function setUp(): void
    {
        // Set up a virtual file system for testing
        $this->root = vfsStream::setup('testRoot', null, [
            'src' => [
                'Controller' => [
                    'UserController.php' => '<?php class UserController {}',
                    'ProductController.php' => '<?php class ProductController {}'
                ],
                'Service' => [
                    'UserService.php' => '<?php class UserService {}',
                    'helper.txt' => 'This is a helper file'
                ]
            ],
            'config' => [
                'services.yaml' => 'services: []',
                'routes.yaml' => 'routes: []'
            ],
            'README.md' => '# Test Project',
            'composer.json' => '{"name": "test/project", "autoload": {"psr-4": {"App\\\\": "src/"}}}',
            '.gitignore' => "vendor/\n/var/cache/"
        ]);

        $this->projectRoot = vfsStream::url('testRoot');
        $this->configuration = new ArkitectConfiguration($this->projectRoot);
        $this->context = new FileStructureContext($this->projectRoot);
        $this->context->setConfiguration($this->configuration);
    }

    public function testIHaveAProjectInDirectory(): void
    {
        // Test that the method correctly sets the project root
        $this->context->iHaveAProjectInDirectory('.');

        // This method doesn't return anything, so we're just testing that it doesn't throw an exception
        $this->addToAssertionCount(1);
    }

    public function testIAmCheckingFilesMatchingPattern(): void
    {
        // Test that the method correctly finds files matching a pattern
        $this->context->iAmCheckingFilesMatchingPattern('*.php');

        // This method doesn't return anything, so we're just testing that it doesn't throw an exception
        $this->addToAssertionCount(1);
    }

    public function testFilesShouldFollowNamingPattern(): void
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        // Set up the context to check PHP files
        $this->context->iAmCheckingFilesMatchingPattern('*.php');

        // Test that the method correctly validates file naming patterns
        $this->context->filesShouldFollowNamingPattern('*Controller.php');

        // This should pass for the Controller files but fail for Service files
        $this->context->filesShouldFollowNamingPattern('*Repository.php');
    }

    public function testFileShouldExist(): void
    {
        // Test that the method correctly validates file existence
        $this->context->fileShouldExist('README.md');

        // Test that the method throws an exception for non-existent files
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->fileShouldExist('non-existent-file.txt');
    }

    public function testFileShouldNotExist(): void
    {
        // Test that the method correctly validates file non-existence
        $this->context->fileShouldNotExist('non-existent-file.txt');

        // Test that the method throws an exception for existing files
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->fileShouldNotExist('README.md');
    }

    public function testDirectoryShouldExist(): void
    {
        // Test that the method correctly validates directory existence
        $this->context->directoryShouldExist('src');

        // Test that the method throws an exception for non-existent directories
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->directoryShouldExist('non-existent-directory');
    }

    public function testDirectoryShouldNotExist(): void
    {
        // Test that the method correctly validates directory non-existence
        $this->context->directoryShouldNotExist('non-existent-directory');

        // Test that the method throws an exception for existing directories
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->directoryShouldNotExist('src');
    }

    public function testFileShouldContain(): void
    {
        // Test that the method correctly validates file content
        $this->context->fileShouldContain('.gitignore', 'vendor/');

        // Test that the method throws an exception for files that don't contain the expected content
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->fileShouldContain('.gitignore', 'non-existent-content');
    }

    public function testFileShouldNotContain(): void
    {
        // Test that the method correctly validates file content
        $this->context->fileShouldNotContain('.gitignore', 'non-existent-content');

        // Test that the method throws an exception for files that contain the unexpected content
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->fileShouldNotContain('.gitignore', 'vendor/');
    }

    public function testFilesShouldHaveExtension(): void
    {
        // Set up the context to check files in the src/Controller directory
        $this->context->iHaveAProjectInDirectory('src/Controller');
        $this->context->iAmCheckingFilesMatchingPattern('*');

        // Test that the method correctly validates file extensions
        $this->context->filesShouldHaveExtension('php');
    }

    public function testFilesShouldHaveExtensionThrownsException(): void
    {
        // Test that the method throws an exception for files with different extensions
        $this->context->iHaveAProjectInDirectory('src/Service');
        $this->context->iAmCheckingFilesMatchingPattern('*');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->filesShouldHaveExtension('php');
    }

    public function testFilesShouldNotHaveExtension(): void
    {
        // Set up the context to check files in the src/Controller directory
        $this->context->iHaveAProjectInDirectory('src/Controller');
        $this->context->iAmCheckingFilesMatchingPattern('*');

        // Test that the method correctly validates file extensions
        $this->context->filesShouldNotHaveExtension('txt');

        // Test that the method throws an exception for files with the forbidden extension
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->context->filesShouldNotHaveExtension('php');
    }
}
