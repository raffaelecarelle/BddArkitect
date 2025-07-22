<?php

namespace BddArkitect\Context;

use BddArkitect\Extension\ArkitectConfiguration;
use PHPUnit\Framework\Assert;

/**
 * Trait with common functionality for all contexts.
 */
trait ContextTrait
{
    /**
     * Check if an error should be ignored based on the configuration.
     *
     * @param string $error The error message
     * @return bool True if the error should be ignored, false otherwise
     */
    protected function shouldIgnoreError(string $error): bool
    {
        if (!isset($this->configuration) || !$this->configuration instanceof ArkitectConfiguration) {
            return false;
        }

        return $this->configuration->shouldIgnoreError($error);
    }

    /**
     * Assert true with error filtering.
     * This method wraps PHPUnit's assertions and adds error filtering based on the configuration.
     *
     * @param bool $condition The condition to assert
     * @param string $message The error message if the assertion fails
     * @throws \PHPUnit\Framework\AssertionFailedError If the assertion fails and the error should not be ignored
     */
    protected function assertTrueWithErrorFiltering(bool $condition, string $message): void
    {
        if ($condition || $this->shouldIgnoreError($message)) {
            return;
        }

        Assert::assertTrue($condition, $message);
    }

    /**
     * Assert false with error filtering.
     * This method wraps PHPUnit's assertions and adds error filtering based on the configuration.
     *
     * @param bool $condition The condition to assert
     * @param string $message The error message if the assertion fails
     * @throws \PHPUnit\Framework\AssertionFailedError If the assertion fails and the error should not be ignored
     */
    protected function assertFalseWithErrorFiltering(bool $condition, string $message): void
    {
        if ($condition || $this->shouldIgnoreError($message)) {
            return;
        }

        Assert::assertFalse($condition, $message);
    }

    protected function assertNotFalseWithErrorFiltering($actual, string $message): void
    {
        if ($this->shouldIgnoreError($message)) {
            return;
        }

        Assert::assertNotFalse($actual, $message);
    }

    protected function assertEqualsWithErrorFiltering($expected, $actual, string $message): void
    {
        if ($this->shouldIgnoreError($message)) {
            return;
        }

        Assert::assertEquals(
            $expected,
            $actual,
            $message
        );
    }

    protected function assertNotEmptyWithErrorFiltering($actual, string $message): void
    {
        if ($this->shouldIgnoreError($message)) {
            return;
        }

        Assert::assertNotEmpty(
            $actual,
            $message
        );
    }

    protected function assertEmptyWithErrorFiltering($actual, string $message): void
    {
        if ($this->shouldIgnoreError($message)) {
            return;
        }

        Assert::assertEmpty(
            $actual,
            $message
        );
    }
}
