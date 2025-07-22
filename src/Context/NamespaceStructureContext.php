<?php

declare(strict_types=1);

namespace BddArkitect\Context;

use BddArkitect\Assert;
use Behat\Behat\Context\Context;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Context per la validazione della struttura dei namespace e PSR-4
 * Verifica che i namespace seguano le convenzioni e la struttura delle directory
 */
final class NamespaceStructureContext implements Context
{
    private string $projectRoot;
    private array $composerConfig = [];
    private array $foundClasses = [];
    private ?\BddArkitect\Extension\ArkitectConfiguration $configuration = null;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
        $this->loadComposerConfig();
    }

    /**
     * Set the configuration for this context.
     * This method is called by the ArkitectContextInitializer.
     */
    public function setConfiguration(\BddArkitect\Extension\ArkitectConfiguration $configuration): void
    {
        $this->configuration = $configuration;
        // Update the project root from the configuration if available
        $this->projectRoot = $this->configuration->getProjectRoot();
        $this->loadComposerConfig();
        Assert::setConfiguration($configuration);
    }

    /**
     * @Given I have a PSR-4 compliant project
     */
    public function iHaveAPsr4CompliantProject(): void
    {
        $composerPath = $this->projectRoot . '/composer.json';
        Assert::assertFileExists($composerPath, "composer.json file not found");

        Assert::assertArrayHasKey(
            'autoload',
            $this->composerConfig,
            "composer.json should contain autoload configuration"
        );

        Assert::assertArrayHasKey(
            'psr-4',
            $this->composerConfig['autoload'],
            "composer.json should contain PSR-4 autoload configuration"
        );
    }

    /**
     * @Given I am analyzing classes in namespace :namespace
     */
    public function iAmAnalyzingClassesInNamespace(string $namespace): void
    {
        $this->foundClasses = $this->findClassesInNamespace($namespace);
        Assert::assertNotEmpty(
            $this->foundClasses,
            "No classes found in namespace {$namespace}"
        );
    }

    /**
     * @When I check namespace compliance
     */
    public function iCheckNamespaceCompliance(): void
    {
        Assert::assertNotEmpty($this->composerConfig, "Composer configuration not loaded");
    }

    /**
     * @Then namespace :namespace should follow PSR-4 structure
     */
    public function namespaceShouldFollowPsr4Structure(string $namespace): void
    {
        $psr4Mappings = $this->composerConfig['autoload']['psr-4'] ?? [];

        $found = false;
        foreach ($psr4Mappings as $prefix => $path) {
            if (str_starts_with($namespace . '\\', $prefix)) {
                $found = true;
                $expectedPath = $this->calculateExpectedPath($namespace, $prefix, $path);
                Assert::assertDirectoryExists(
                    $expectedPath,
                    "Directory structure for namespace {$namespace} should exist at {$expectedPath}"
                );
                break;
            }
        }

        Assert::assertTrue($found, "Namespace {$namespace} not found in PSR-4 mappings");
    }

    /**
     * @Then class :className should be in correct directory
     */
    public function classShouldBeInCorrectDirectory(string $className): void
    {
        $expectedPath = $this->getExpectedClassPath($className);
        Assert::assertFileExists(
            $expectedPath,
            "Class {$className} should be located at {$expectedPath}"
        );
    }

    /**
     * @Then all classes should match their file paths
     */
    public function allClassesShouldMatchTheirFilePaths(): void
    {
        foreach ($this->foundClasses as $classInfo) {
            $className = $classInfo['class'];
            $actualPath = $classInfo['path'];
            $expectedPath = $this->getExpectedClassPath($className);

            Assert::assertEquals(
                $expectedPath,
                $actualPath,
                "Class {$className} is not in the expected location"
            );
        }
    }

    /**
     * @Then namespace :namespace should contain only classes matching pattern :pattern
     */
    public function namespaceShouldContainOnlyClassesMatchingPattern(string $namespace, string $pattern): void
    {
        $classes = $this->findClassesInNamespace($namespace);

        foreach ($classes as $classInfo) {
            $className = $classInfo['class'];
            $shortName = $this->getClassShortName($className);

            Assert::assertTrue(
                $this->matchesPattern($shortName, $pattern),
                "Class {$className} does not match pattern {$pattern}"
            );
        }
    }

    /**
     * @Then namespace :namespace should not contain classes matching pattern :pattern
     */
    public function namespaceShouldNotContainClassesMatchingPattern(string $namespace, string $pattern): void
    {
        $classes = $this->findClassesInNamespace($namespace);

        foreach ($classes as $classInfo) {
            $className = $classInfo['class'];
            $shortName = $this->getClassShortName($className);

            Assert::assertFalse(
                $this->matchesPattern($shortName, $pattern),
                "Class {$className} should not match pattern {$pattern}"
            );
        }
    }

    /**
     * @Then namespace :namespace should have maximum depth of :depth levels
     */
    public function namespaceShouldHaveMaximumDepthOfLevels(string $namespace, int $depth): void
    {
        $actualDepth = count(explode('\\', trim($namespace, '\\')));

        Assert::assertLessThanOrEqual(
            $depth,
            $actualDepth,
            "Namespace {$namespace} has depth {$actualDepth}, maximum allowed is {$depth}"
        );
    }

    /**
     * @Then namespace :namespace should have minimum depth of :depth levels
     */
    public function namespaceShouldHaveMinimumDepthOfLevels(string $namespace, int $depth): void
    {
        $actualDepth = count(explode('\\', trim($namespace, '\\')));

        Assert::assertGreaterThanOrEqual(
            $depth,
            $actualDepth,
            "Namespace {$namespace} has depth {$actualDepth}, minimum required is {$depth}"
        );
    }

    /**
     * @Then namespace :namespace should exist
     */
    public function namespaceShouldExist(string $namespace): void
    {
        $classes = $this->findClassesInNamespace($namespace);
        Assert::assertNotEmpty(
            $classes,
            "Namespace {$namespace} should contain at least one class"
        );
    }

    /**
     * @Then namespace :namespace should not exist
     */
    public function namespaceShouldNotExist(string $namespace): void
    {
        $classes = $this->findClassesInNamespace($namespace);
        Assert::assertEmpty(
            $classes,
            "Namespace {$namespace} should not contain any classes"
        );
    }

    /**
     * @Then namespace :namespace should contain :count classes
     */
    public function namespaceShouldContainClasses(string $namespace, int $count): void
    {
        $classes = $this->findClassesInNamespace($namespace);
        Assert::assertEquals(
            $count,
            count($classes),
            "Namespace {$namespace} should contain exactly {$count} classes"
        );
    }

    /**
     * @Then namespace :namespace should contain at least :count classes
     */
    public function namespaceShouldContainAtLeastClasses(string $namespace, int $count): void
    {
        $classes = $this->findClassesInNamespace($namespace);
        Assert::assertGreaterThanOrEqual(
            $count,
            count($classes),
            "Namespace {$namespace} should contain at least {$count} classes"
        );
    }

    /**
     * @Then namespace :namespace should contain at most :count classes
     */
    public function namespaceShouldContainAtMostClasses(string $namespace, int $count): void
    {
        $classes = $this->findClassesInNamespace($namespace);
        Assert::assertLessThanOrEqual(
            $count,
            count($classes),
            "Namespace {$namespace} should contain at most {$count} classes"
        );
    }

    /**
     * @Then all namespaces should follow naming convention :convention
     */
    public function allNamespacesShouldFollowNamingConvention(string $convention): void
    {
        $allClasses = $this->getAllClasses();

        foreach ($allClasses as $classInfo) {
            $namespace = $this->getClassNamespace($classInfo['class']);
            if ($namespace) {
                $parts = explode('\\', $namespace);
                foreach ($parts as $part) {
                    Assert::assertTrue(
                        $this->followsConvention($part, $convention),
                        "Namespace part '{$part}' does not follow {$convention} convention"
                    );
                }
            }
        }
    }

    /**
     * Carica la configurazione di Composer
     */
    private function loadComposerConfig(): void
    {
        $composerPath = $this->projectRoot . '/composer.json';
        if (file_exists($composerPath)) {
            $this->composerConfig = json_decode(file_get_contents($composerPath), true) ?? [];
        }
    }

    /**
     * Trova tutte le classi in un namespace specifico
     */
    private function findClassesInNamespace(string $namespace): array
    {
        $classes = [];
        $allClasses = $this->getAllClasses();

        foreach ($allClasses as $classInfo) {
            $classNamespace = $this->getClassNamespace($classInfo['class']);
            if ($classNamespace === $namespace || str_starts_with($classNamespace, $namespace . '\\')) {
                $classes[] = $classInfo;
            }
        }

        return $classes;
    }

    /**
     * Ottiene tutte le classi del progetto
     */
    private function getAllClasses(): array
    {
        $classes = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            // Skip if not a PHP file
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // Get the relative path from the project root
            $relativePath = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Skip if configuration is available and the path should not be analyzed
            if ($this->configuration !== null && !$this->configuration->shouldAnalyzePath($relativePath)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $classInfo = $this->extractClassInfo($content);

            if ($classInfo) {
                $classInfo['path'] = $file->getPathname();
                $classes[] = $classInfo;
            }
        }

        return $classes;
    }

    /**
     * Estrae informazioni sulla classe da un file PHP
     */
    private function extractClassInfo(string $content): ?array
    {
        $namespace = null;
        $className = null;

        // Estrae il namespace
        if (preg_match('/namespace\s+([A-Za-z0-9\\\\]+);/i', $content, $matches)) {
            $namespace = $matches[1];
        }

        // Estrae il nome della classe
        if (preg_match('/(class|interface|trait)\s+([A-Za-z_][A-Za-z0-9_]*)/i', $content, $matches)) {
            $className = $matches[2];
        }

        if (!$className) {
            return null;
        }

        $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

        return [
            'class' => $fullClassName,
            'namespace' => $namespace,
            'short_name' => $className
        ];
    }

    /**
     * Calcola il percorso atteso per un namespace basato su PSR-4
     */
    private function calculateExpectedPath(string $namespace, string $prefix, string $basePath): string
    {
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($namespace, strlen($prefix) - 1));
        return $this->projectRoot . DIRECTORY_SEPARATOR . trim($basePath, '/') . $relativePath;
    }

    /**
     * Ottiene il percorso atteso per una classe basato su PSR-4
     */
    private function getExpectedClassPath(string $className): string
    {
        $psr4Mappings = $this->composerConfig['autoload']['psr-4'] ?? [];

        foreach ($psr4Mappings as $prefix => $path) {
            if (str_starts_with($className . '\\', $prefix)) {
                $relativePath = substr($className, strlen($prefix) - 1);
                $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath) . '.php';
                return $this->projectRoot . DIRECTORY_SEPARATOR . trim($path, '/') . DIRECTORY_SEPARATOR . $filePath;
            }
        }

        throw new \InvalidArgumentException("No PSR-4 mapping found for class {$className}");
    }

    /**
     * Ottiene il namespace di una classe
     */
    private function getClassNamespace(string $className): ?string
    {
        $parts = explode('\\', $className);
        if (count($parts) > 1) {
            array_pop($parts);
            return implode('\\', $parts);
        }

        return null;
    }

    /**
     * Ottiene il nome breve di una classe (senza namespace)
     */
    private function getClassShortName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Verifica se una stringa corrisponde a un pattern
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
     * Verifica se un nome segue una convenzione specifica
     */
    private function followsConvention(string $name, string $convention): bool
    {
        return match($convention) {
            'PascalCase' => (bool) preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name),
            'camelCase' => (bool) preg_match('/^[a-z][a-zA-Z0-9]*$/', $name),
            'snake_case' => (bool) preg_match('/^[a-z][a-z0-9_]*$/', $name),
            'kebab-case' => (bool) preg_match('/^[a-z][a-z0-9-]*$/', $name),
            'UPPER_CASE' => (bool) preg_match('/^[A-Z][A-Z0-9_]*$/', $name),
            default => $this->matchesPattern($name, $convention)
        };
    }
}
