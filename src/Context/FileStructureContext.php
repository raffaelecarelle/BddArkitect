<?php

declare(strict_types=1);

namespace BddArkitect\Context;

use BddArkitect\Extension\ArkitectConfiguration;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Context per la validazione della struttura di file e directory
 * Supporta le regole di nomenclatura e organizzazione del filesystem
 */
final class FileStructureContext implements Context
{
    use ContextTrait;
    private string $projectRoot;
    private array $foundFiles = [];
    private array $foundDirectories = [];
    private ?ArkitectConfiguration $configuration = null;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
    }

    /**
     * Set the configuration for this context.
     * This method is called by the ArkitectContextInitializer.
     */
    public function setConfiguration(ArkitectConfiguration $configuration): void
    {
        $this->configuration = $configuration;
        // Update the project root from the configuration if available
        $this->projectRoot = $this->configuration->getProjectRoot();
    }

    /**
     * @Given I have a project in directory :directory
     */
    public function iHaveAProjectInDirectory(string $directory): void
    {
        $fullPath = $this->resolvePath($directory);
        Assert::assertDirectoryExists($fullPath, "Directory {$directory} does not exist");
        $this->projectRoot = $fullPath;
    }

    /**
     * @Given I am checking files matching pattern :pattern
     */
    public function iAmCheckingFilesMatchingPattern(string $pattern): void
    {
        $this->foundFiles = $this->findFilesByPattern($pattern);
    }

    /**
     * @Given I am checking directories matching pattern :pattern
     */
    public function iAmCheckingDirectoriesMatchingPattern(string $pattern): void
    {
        $this->foundDirectories = $this->findDirectoriesByPattern($pattern);
    }

    /**
     * @When I scan the project structure
     */
    public function iScanTheProjectStructure(): void
    {
        Assert::assertDirectoryExists($this->projectRoot, "Project root directory does not exist");
    }

    /**
     * @Then files should follow naming pattern :pattern
     */
    public function filesShouldFollowNamingPattern(string $pattern): void
    {
        Assert::assertNotEmpty($this->foundFiles, "No files found to validate");

        foreach ($this->foundFiles as $file) {
            $fileName = basename($file);
            Assert::assertTrue(
                $this->matchesPattern($fileName, $pattern),
                "File {$fileName} does not match pattern {$pattern}"
            );
        }
    }

    /**
     * @Then directories should follow naming pattern :pattern
     */
    public function directoriesShouldFollowNamingPattern(string $pattern): void
    {
        Assert::assertNotEmpty($this->foundDirectories, "No directories found to validate");

        foreach ($this->foundDirectories as $directory) {
            $dirName = basename($directory);
            Assert::assertTrue(
                $this->matchesPattern($dirName, $pattern),
                "Directory {$dirName} does not match pattern {$pattern}"
            );
        }
    }

    /**
     * @Then file :fileName should exist
     */
    public function fileShouldExist(string $fileName): void
    {
        $fullPath = $this->resolvePath($fileName);
        Assert::assertFileExists($fullPath, "File {$fileName} does not exist");
    }

    /**
     * @Then file :fileName should not exist
     */
    public function fileShouldNotExist(string $fileName): void
    {
        $fullPath = $this->resolvePath($fileName);
        Assert::assertFileDoesNotExist($fullPath, "File {$fileName} should not exist");
    }

    /**
     * @Then directory :dirName should exist
     */
    public function directoryShouldExist(string $dirName): void
    {
        $fullPath = $this->resolvePath($dirName);
        Assert::assertDirectoryExists($fullPath, "Directory {$dirName} does not exist");
    }

    /**
     * @Then directory :dirName should not exist
     */
    public function directoryShouldNotExist(string $dirName): void
    {
        $fullPath = $this->resolvePath($dirName);
        Assert::assertDirectoryDoesNotExist($fullPath, "Directory {$dirName} should not exist");
    }

    /**
     * @Then files should be readable
     */
    public function filesShouldBeReadable(): void
    {
        foreach ($this->foundFiles as $file) {
            Assert::assertIsReadable($file, "File {$file} is not readable");
        }
    }

    /**
     * @Then files should be writable
     */
    public function filesShouldBeWritable(): void
    {
        foreach ($this->foundFiles as $file) {
            Assert::assertIsWritable($file, "File {$file} is not writable");
        }
    }

    /**
     * @Then file :fileName should contain :content
     */
    public function fileShouldContain(string $fileName, string $content): void
    {
        $fullPath = $this->resolvePath($fileName);
        Assert::assertFileExists($fullPath, "File {$fileName} does not exist");

        $fileContent = file_get_contents($fullPath);
        Assert::assertStringContainsString(
            $content,
            $fileContent,
            "File {$fileName} does not contain expected content"
        );
    }

    /**
     * @Then file :fileName should not contain :content
     */
    public function fileShouldNotContain(string $fileName, string $content): void
    {
        $fullPath = $this->resolvePath($fileName);
        Assert::assertFileExists($fullPath, "File {$fileName} does not exist");

        $fileContent = file_get_contents($fullPath);
        Assert::assertStringNotContainsString(
            $content,
            $fileContent,
            "File {$fileName} should not contain specified content"
        );
    }

    /**
     * @Then directory :dirName should contain :count files
     */
    public function directoryShouldContainFiles(string $dirName, int $count): void
    {
        $fullPath = $this->resolvePath($dirName);
        Assert::assertDirectoryExists($fullPath, "Directory {$dirName} does not exist");

        $files = glob($fullPath . '/*');
        $fileCount = count(array_filter($files, 'is_file'));

        Assert::assertEquals(
            $count,
            $fileCount,
            "Directory {$dirName} should contain {$count} files, found {$fileCount}"
        );
    }

    /**
     * @Then directory :dirName should contain at least :count files
     */
    public function directoryShouldContainAtLeastFiles(string $dirName, int $count): void
    {
        $fullPath = $this->resolvePath($dirName);
        Assert::assertDirectoryExists($fullPath, "Directory {$dirName} does not exist");

        $files = glob($fullPath . '/*');
        $fileCount = count(array_filter($files, 'is_file'));

        Assert::assertGreaterThanOrEqual(
            $count,
            $fileCount,
            "Directory {$dirName} should contain at least {$count} files, found {$fileCount}"
        );
    }

    /**
     * @Then directory :dirName should contain at most :count files
     */
    public function directoryShouldContainAtMostFiles(string $dirName, int $count): void
    {
        $fullPath = $this->resolvePath($dirName);
        Assert::assertDirectoryExists($fullPath, "Directory {$dirName} does not exist");

        $files = glob($fullPath . '/*');
        $fileCount = count(array_filter($files, 'is_file'));

        Assert::assertLessThanOrEqual(
            $count,
            $fileCount,
            "Directory {$dirName} should contain at most {$count} files, found {$fileCount}"
        );
    }

    /**
     * @Then files should have extension :extension
     */
    public function filesShouldHaveExtension(string $extension): void
    {
        Assert::assertNotEmpty($this->foundFiles, "No files found to validate");

        foreach ($this->foundFiles as $file) {
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
            Assert::assertEquals(
                $extension,
                $fileExtension,
                "File {$file} should have extension {$extension}, found {$fileExtension}"
            );
        }
    }

    /**
     * @Then files should not have extension :extension
     */
    public function filesShouldNotHaveExtension(string $extension): void
    {
        Assert::assertNotEmpty($this->foundFiles, "No files found to validate");

        foreach ($this->foundFiles as $file) {
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
            Assert::assertNotEquals(
                $extension,
                $fileExtension,
                "File {$file} should not have extension {$extension}"
            );
        }
    }

    /**
     * @Then files should be smaller than :size bytes
     */
    public function filesShouldBeSmallerThan(int $size): void
    {
        foreach ($this->foundFiles as $file) {
            $fileSize = filesize($file);
            Assert::assertLessThan(
                $size,
                $fileSize,
                "File {$file} should be smaller than {$size} bytes, found {$fileSize} bytes"
            );
        }
    }

    /**
     * @Then files should be larger than :size bytes
     */
    public function filesShouldBeLargerThan(int $size): void
    {
        foreach ($this->foundFiles as $file) {
            $fileSize = filesize($file);
            Assert::assertGreaterThan(
                $size,
                $fileSize,
                "File {$file} should be larger than {$size} bytes, found {$fileSize} bytes"
            );
        }
    }

    /**
     * Trova file che corrispondono a un pattern
     */
    private function findFilesByPattern(string $pattern): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            // Skip if not a file or doesn't match the pattern
            if (!$file->isFile() || !$this->matchesPattern($file->getFilename(), $pattern)) {
                continue;
            }

            // Get the relative path from the project root
            $relativePath = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Skip if configuration is available and the path should not be analyzed
            if ($this->configuration !== null && !$this->configuration->shouldAnalyzePath($relativePath)) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }

    /**
     * Trova directory che corrispondono a un pattern
     */
    private function findDirectoriesByPattern(string $pattern): array
    {
        $directories = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            // Skip if not a directory or doesn't match the pattern
            if (!$file->isDir() || !$this->matchesPattern($file->getFilename(), $pattern)) {
                continue;
            }

            // Get the relative path from the project root
            $relativePath = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Skip if configuration is available and the path should not be analyzed
            if ($this->configuration !== null && !$this->configuration->shouldAnalyzePath($relativePath)) {
                continue;
            }

            $directories[] = $file->getPathname();
        }

        return $directories;
    }

    /**
     * Verifica se una stringa corrisponde a un pattern (regex o glob)
     */
    private function matchesPattern(string $string, string $pattern): bool
    {
        // Tenta prima come regex
        if (str_starts_with($pattern, '/') && str_ends_with($pattern, '/')) {
            return (bool) preg_match($pattern, $string);
        }

        // Altrimenti usa fnmatch per pattern glob
        return fnmatch($pattern, $string);
    }

    /**
     * Risolve un percorso relativo rispetto al project root
     */
    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return $this->projectRoot . DIRECTORY_SEPARATOR . $path;
    }
}
