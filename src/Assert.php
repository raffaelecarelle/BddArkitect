<?php

namespace BddArkitect;

use BddArkitect\Extension\ArkitectConfiguration;
use PHPUnit\Framework\Assert as PhpUnitAssert;

class Assert
{
    private static ?ArkitectConfiguration $configuration;

    public static function setConfiguration(ArkitectConfiguration $configuration): void
    {
        self::$configuration = $configuration;
    }

    public static function __callStatic($name, $arguments): void
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
        if (!isset(static::$configuration) || !static::$configuration instanceof ArkitectConfiguration) {
            return false;
        }

        return static::$configuration->shouldIgnoreError($error);
    }
}
