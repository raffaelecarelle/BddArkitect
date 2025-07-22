<?php

namespace BddArkitect\Extension;

/**
 * Configuration class for the Arkitect extension.
 *
 * This class holds the configuration parameters and provides methods to access them.
 */
class ArkitectConfiguration
{
    /**
     * @var string
     */
    private string $projectRoot;

    /**
     * @var array
     */
    private array $paths;

    /**
     * @var array
     */
    private array $excludes;

    /**
     * @var array
     */
    private array $ignoreErrors;

    /**
     * Constructor.
     *
     * @param string $projectRoot The root directory of the project
     * @param array $paths Array of relative paths where the tool should analyze and validate rules
     * @param array $excludes Array of relative paths to exclude from validation
     * @param array $ignoreErrors Array of regex patterns to filter errors
     */
    public function __construct(string $projectRoot, array $paths = [], array $excludes = [], array $ignoreErrors = [])
    {
        $this->projectRoot = $projectRoot;
        $this->paths = $paths;
        $this->excludes = $excludes;
        $this->ignoreErrors = $ignoreErrors;
    }

    /**
     * Get the project root directory.
     *
     * @return string
     */
    public function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    /**
     * Get the paths to analyze.
     *
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Get the paths to exclude from analysis.
     *
     * @return array
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * Get the error patterns to ignore.
     *
     * @return array
     */
    public function getIgnoreErrors(): array
    {
        return $this->ignoreErrors;
    }

    /**
     * Check if a path should be analyzed.
     *
     * @param string $path The path to check
     * @return bool
     */
    public function shouldAnalyzePath(string $path): bool
    {
        // If no paths are specified, analyze all paths
        if (empty($this->paths)) {
            // Check if the path is excluded
            foreach ($this->excludes as $exclude) {
                if ($this->pathMatches($path, $exclude)) {
                    return false;
                }
            }
            return true;
        }

        // Check if the path is in the paths to analyze
        $shouldAnalyze = false;
        foreach ($this->paths as $includePath) {
            if ($this->pathMatches($path, $includePath)) {
                $shouldAnalyze = true;
                break;
            }
        }

        // If the path is in the paths to analyze, check if it's excluded
        if ($shouldAnalyze) {
            foreach ($this->excludes as $exclude) {
                if ($this->pathMatches($path, $exclude)) {
                    return false;
                }
            }
        }

        return $shouldAnalyze;
    }

    /**
     * Check if an error should be ignored.
     *
     * @param string $error The error message
     * @return bool
     */
    public function shouldIgnoreError(string $error): bool
    {
        foreach ($this->ignoreErrors as $pattern) {
            if (preg_match('/' . $pattern . '/', $error)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a path matches a pattern.
     *
     * @param string $path The path to check
     * @param string $pattern The pattern to match against
     * @return bool
     */
    private function pathMatches(string $path, string $pattern): bool
    {
        // Convert the pattern to a regex pattern
        $pattern = str_replace('\\', '\\\\', $pattern);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '/^' . $pattern . '/';

        return preg_match($pattern, $path) === 1;
    }
}
