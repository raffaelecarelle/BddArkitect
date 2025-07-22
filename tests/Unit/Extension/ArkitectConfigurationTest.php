<?php

namespace BddArkitect\Tests\Unit\Extension;

use BddArkitect\Extension\ArkitectConfiguration;
use PHPUnit\Framework\TestCase;

class ArkitectConfigurationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new ArkitectConfiguration('/project/root');

        $this->assertEquals('/project/root', $config->getProjectRoot());
        $this->assertEquals([], $config->getPaths());
        $this->assertEquals([], $config->getExcludes());
        $this->assertEquals([], $config->getIgnoreErrors());
    }

    public function testCustomValues(): void
    {
        $config = new ArkitectConfiguration(
            '/project/root',
            ['src', 'tests'],
            ['vendor', 'var'],
            ['.*should be final.*']
        );

        $this->assertEquals('/project/root', $config->getProjectRoot());
        $this->assertEquals(['src', 'tests'], $config->getPaths());
        $this->assertEquals(['vendor', 'var'], $config->getExcludes());
        $this->assertEquals(['.*should be final.*'], $config->getIgnoreErrors());
    }

    public function testShouldAnalyzePathWithNoPaths(): void
    {
        $config = new ArkitectConfiguration(
            '/project/root',
            [],
            ['vendor', 'var']
        );

        // With no paths specified, all paths should be analyzed except those in excludes
        $this->assertTrue($config->shouldAnalyzePath('src/Controller/UserController.php'));
        $this->assertTrue($config->shouldAnalyzePath('tests/Unit/Test.php'));
        $this->assertFalse($config->shouldAnalyzePath('vendor/package/file.php'));
        $this->assertFalse($config->shouldAnalyzePath('var/cache/file.php'));
    }

    public function testShouldAnalyzePathWithPaths(): void
    {
        $config = new ArkitectConfiguration(
            '/project/root',
            ['src', 'tests'],
            ['vendor', 'var']
        );

        // Only paths specified should be analyzed, and excludes should be respected
        $this->assertTrue($config->shouldAnalyzePath('src/Controller/UserController.php'));
        $this->assertTrue($config->shouldAnalyzePath('tests/Unit/Test.php'));
        $this->assertFalse($config->shouldAnalyzePath('vendor/package/file.php'));
        $this->assertFalse($config->shouldAnalyzePath('var/cache/file.php'));
        $this->assertFalse($config->shouldAnalyzePath('config/services.yaml'));
    }

    public function testShouldIgnoreError(): void
    {
        $config = new ArkitectConfiguration(
            '/project/root',
            [],
            [],
            ['.*should be final.*', '.*should have attribute.*']
        );

        $this->assertTrue($config->shouldIgnoreError('Class App\\Controller\\UserController should be final'));
        $this->assertTrue($config->shouldIgnoreError('Method test should have attribute Route'));
        $this->assertFalse($config->shouldIgnoreError('Class not found'));
    }

    public function testPathMatchesWithWildcard(): void
    {
        $config = new ArkitectConfiguration(
            '/project/root',
            ['src/Controller/*', 'tests/Unit/*'],
            ['src/Controller/Legacy/*']
        );

        $this->assertTrue($config->shouldAnalyzePath('src/Controller/UserController.php'));
        $this->assertTrue($config->shouldAnalyzePath('tests/Unit/Test.php'));
        $this->assertFalse($config->shouldAnalyzePath('src/Controller/Legacy/OldController.php'));
        $this->assertFalse($config->shouldAnalyzePath('src/Service/UserService.php'));
    }
}
