<?php

namespace BddArkitect;

use BddArkitect\Extension\ArkitectConfiguration;
use PHPUnit\Framework\Assert as PhpUnitAssert;

final class Assert
{
    private static ?ArkitectConfiguration $configuration;

    final private function __construct()
    {
    }

    public static function setConfiguration(ArkitectConfiguration $configuration): void
    {
        self::$configuration = $configuration;
    }

    /**
     * @param string $name
     * @param array<mixed> $arguments
     * @return void
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        if (!method_exists(PhpUnitAssert::class, $name)) {
            return;
        }

        $method = new \ReflectionMethod(PhpUnitAssert::class, $name);
        $params = $method->getParameters();

        $message = null;
        foreach ($params as $param) {
            if ($param->getName() === 'message') {
                $message = $arguments[$param->getPosition()];
            }
        }

        if (!$message || static::shouldIgnoreError($message)) {
            return;
        }

        PhpUnitAssert::$name(...$arguments);
    }

    /**
     * Check if an error should be ignored based on the configuration.
     *
     * @param string $error The error message
     * @return bool True if the error should be ignored, false otherwise
     */
    protected static function shouldIgnoreError(string $error): bool
    {
        if (!static::$configuration instanceof ArkitectConfiguration) {
            return false;
        }

        return static::$configuration->shouldIgnoreError($error);
    }
}
