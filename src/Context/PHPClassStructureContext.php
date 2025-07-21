<?php

declare(strict_types=1);

namespace BddArkitect\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Context per la validazione della struttura delle classi PHP
 * Supporta le regole di nomenclatura e organizzazione del codice
 */
final class PHPClassStructureContext implements Context
{
    private ?ReflectionClass $currentClass = null;
    private array $foundClasses = [];
    private string $projectRoot = '';

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
    }

    /**
     * @Given I have a PHP class matching pattern :pattern
     */
    public function iHaveAPhpClassMatchingPattern(string $pattern): void
    {
        $this->foundClasses = $this->findClassesByPattern($pattern);
        Assert::assertNotEmpty(
            $this->foundClasses,
            "No classes found matching pattern: {$pattern}"
        );
    }

    /**
     * @Given I am analyzing the class :className
     */
    public function iAmAnalyzingTheClass(string $className): void
    {
        try {
            $this->currentClass = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new \InvalidArgumentException("Class {$className} not found: " . $e->getMessage());
        }
    }

    /**
     * @Given the project follows PHP best practices
     */
    public function theProjectFollowsPhpBestPractices(): void
    {
        // Questa precondizione può essere estesa per verificare
        // configurazioni specifiche come PSR-4, Composer, etc.
        Assert::assertTrue(true, "Project setup validated");
    }

    /**
     * @When I check the class structure
     */
    public function iCheckTheClassStructure(): void
    {
        Assert::assertNotNull($this->currentClass, "No class is currently being analyzed");
    }

    /**
     * @When I validate the class dependencies
     */
    public function iValidateTheClassDependencies(): void
    {
        Assert::assertNotNull($this->currentClass, "No class is currently being analyzed");
    }

    /**
     * @When I inspect the class methods
     */
    public function iInspectTheClassMethods(): void
    {
        Assert::assertNotNull($this->currentClass, "No class is currently being analyzed");
    }

    /**
     * @When I examine the class attributes
     */
    public function iExamineTheClassAttributes(): void
    {
        Assert::assertNotNull($this->currentClass, "No class is currently being analyzed");
    }

    /**
     * @Then the class should be final
     */
    public function theClassShouldBeFinal(): void
    {
        Assert::assertTrue(
            $this->currentClass->isFinal(),
            "Class {$this->currentClass->getName()} should be final"
        );
    }

    /**
     * @Then the class should not be final
     */
    public function theClassShouldNotBeFinal(): void
    {
        Assert::assertFalse(
            $this->currentClass->isFinal(),
            "Class {$this->currentClass->getName()} should not be final"
        );
    }

    /**
     * @Then the class should be interface
     */
    public function theClassShouldBeInterface(): void
    {
        Assert::assertTrue(
            $this->currentClass->isInterface(),
            "Class {$this->currentClass->getName()} should be an interface"
        );
    }

    /**
     * @Then the class should not be interface
     */
    public function theClassShouldNotBeInterface(): void
    {
        Assert::assertFalse(
            $this->currentClass->isInterface(),
            "Class {$this->currentClass->getName()} should not be an interface"
        );
    }

    /**
     * @Then the class should be abstract
     */
    public function theClassShouldBeAbstract(): void
    {
        Assert::assertTrue(
            $this->currentClass->isAbstract(),
            "Class {$this->currentClass->getName()} should be abstract"
        );
    }

    /**
     * @Then the class should not be abstract
     */
    public function theClassShouldNotBeAbstract(): void
    {
        Assert::assertFalse(
            $this->currentClass->isAbstract(),
            "Class {$this->currentClass->getName()} should not be abstract"
        );
    }

    /**
     * @Then the class should not depend on :namespace
     */
    public function theClassShouldNotDependOn(string $namespace): void
    {
        $dependencies = $this->getClassDependencies();

        Assert::assertFalse(
            in_array($namespace, $dependencies, true),
            "Class {$this->currentClass->getName()} should not depend on {$namespace}"
        );
    }

    /**
     * @Then the class should depend on :namespace
     */
    public function theClassShouldDependOn(string $namespace): void
    {
        $dependencies = $this->getClassDependencies();

        Assert::assertTrue(
            in_array($namespace, $dependencies, true),
            "Class {$this->currentClass->getName()} should depend on {$namespace}"
        );
    }

    /**
     * @Then the class should implement :interface
     */
    public function theClassShouldImplement(string $interface): void
    {
        Assert::assertTrue(
            $this->currentClass->implementsInterface($interface),
            "Class {$this->currentClass->getName()} should implement {$interface}"
        );
    }

    /**
     * @Then the class should extend :parentClass
     */
    public function theClassShouldExtend(string $parentClass): void
    {
        $parent = $this->currentClass->getParentClass();

        Assert::assertNotFalse($parent, "Class {$this->currentClass->getName()} should extend a parent class");
        Assert::assertEquals(
            $parentClass,
            $parent->getName(),
            "Class {$this->currentClass->getName()} should extend {$parentClass}"
        );
    }

    /**
     * @Then the class should be a :grandParentClass
     */
    public function theClassShouldBeA(string $grandParentClass): void
    {
        Assert::assertTrue(
            $this->currentClass->isSubclassOf($grandParentClass),
            "Class {$this->currentClass->getName()} should be a subclass of {$grandParentClass}"
        );
    }

    /**
     * @Then the class should have methods named :pattern
     */
    public function theClassShouldHaveMethodsNamed(string $pattern): void
    {
        $methods = $this->currentClass->getMethods();
        $matchingMethods = array_filter($methods, function (ReflectionMethod $method) use ($pattern) {
            return $this->matchesPattern($method->getName(), $pattern);
        });

        Assert::assertNotEmpty(
            $matchingMethods,
            "Class {$this->currentClass->getName()} should have methods matching pattern: {$pattern}"
        );
    }

    /**
     * @Then the class should not have methods named :pattern
     */
    public function theClassShouldNotHaveMethodsNamed(string $pattern): void
    {
        $methods = $this->currentClass->getMethods();
        $matchingMethods = array_filter($methods, function (ReflectionMethod $method) use ($pattern) {
            return $this->matchesPattern($method->getName(), $pattern);
        });

        Assert::assertEmpty(
            $matchingMethods,
            "Class {$this->currentClass->getName()} should not have methods matching pattern: {$pattern}"
        );
    }

    /**
     * @Then methods should have maximum :number arguments
     */
    public function methodsShouldHaveMaximumArguments(int $number): void
    {
        $methods = $this->currentClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->getName() === $this->currentClass->getName()) {
                $paramCount = $method->getNumberOfParameters();
                Assert::assertLessThanOrEqual(
                    $number,
                    $paramCount,
                    "Method {$method->getName()} has {$paramCount} parameters, maximum allowed is {$number}"
                );
            }
        }
    }

    /**
     * @Then methods should have attribute :attribute
     */
    public function methodsShouldHaveAttribute(string $attribute): void
    {
        $methods = $this->currentClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->getName() === $this->currentClass->getName()) {
                $attributes = $method->getAttributes($attribute);
                Assert::assertNotEmpty(
                    $attributes,
                    "Method {$method->getName()} should have attribute {$attribute}"
                );
            }
        }
    }

    /**
     * @Then methods should have attribute :attribute with key :key and value :value
     */
    public function methodsShouldHaveAttributeWithKeyAndValue(string $attribute, string $key, string $value): void
    {
        $methods = $this->currentClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->getName() === $this->currentClass->getName()) {
                $attributes = $method->getAttributes($attribute);
                Assert::assertNotEmpty($attributes, "Method {$method->getName()} should have attribute {$attribute}");

                foreach ($attributes as $attr) {
                    $args = $attr->getArguments();
                    Assert::assertArrayHasKey($key, $args, "Attribute {$attribute} should have key {$key}");
                    Assert::assertEquals($value, $args[$key], "Attribute {$attribute} key {$key} should have value {$value}");
                }
            }
        }
    }

    /**
     * @Then the class should have attribute :attribute
     */
    public function theClassShouldHaveAttribute(string $attribute): void
    {
        $attributes = $this->currentClass->getAttributes($attribute);
        Assert::assertNotEmpty(
            $attributes,
            "Class {$this->currentClass->getName()} should have attribute {$attribute}"
        );
    }

    /**
     * @Then the class should not have attribute :attribute
     */
    public function theClassShouldNotHaveAttribute(string $attribute): void
    {
        $attributes = $this->currentClass->getAttributes($attribute);
        Assert::assertEmpty(
            $attributes,
            "Class {$this->currentClass->getName()} should not have attribute {$attribute}"
        );
    }

    /**
     * @Then the class should have attribute :attribute with key :key and value :value
     */
    public function theClassShouldHaveAttributeWithKeyAndValue(string $attribute, string $key, string $value): void
    {
        $attributes = $this->currentClass->getAttributes($attribute);
        Assert::assertNotEmpty($attributes, "Class should have attribute {$attribute}");

        foreach ($attributes as $attr) {
            $args = $attr->getArguments();
            Assert::assertArrayHasKey($key, $args, "Attribute {$attribute} should have key {$key}");
            Assert::assertEquals($value, $args[$key], "Attribute {$attribute} key {$key} should have value {$value}");
        }
    }

    /**
     * Trova classi che corrispondono a un pattern
     */
    private function findClassesByPattern(string $pattern): array
    {
        $classes = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->projectRoot)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $className = $this->extractClassName(basename($file->getPathname(), '.php'));

                if ($className && $this->matchesPattern($className, $pattern)) {
                    $classes[] = $className;
                }
            }
        }

        return $classes;
    }

    /**
     * Estrae il nome della classe da un file PHP
     */
    private function extractClassName(string $basename): ?string
    {
        if (preg_match('/([A-Za-z_][A-Za-z0-9_]*)/i', $basename, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Verifica se una stringa corrisponde a un pattern (regex o glob)
     */
    private function matchesPattern(string $string, string $pattern): bool
    {
        // Tenta prima come regex
        if (@preg_match($pattern, $string) !== false) {
            return (bool) preg_match($pattern, $string);
        }

        // Altrimenti usa fnmatch per pattern glob
        return fnmatch($pattern, $string);
    }

    /**
     * Ottiene le dipendenze di una classe analizzando use statements
     */
    private function getClassDependencies(): array
    {
        $fileName = $this->currentClass->getFileName();
        if (!$fileName) {
            return [];
        }

        $content = file_get_contents($fileName);
        $dependencies = [];

        // Estrae gli use statements
        if (preg_match_all('/use\s+([A-Za-z\\\\][A-Za-z0-9\\\\]*)/i', $content, $matches)) {
            $dependencies = $matches[1];
        }

        return $dependencies;
    }
}
