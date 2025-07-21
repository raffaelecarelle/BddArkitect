Feature: File and Directory Structure Validation
    As a developer
    I want to ensure that files and directories follow project conventions
    So that the project structure remains organized and predictable

    Background:
        Given I have a project in directory "."

    Scenario: PHP files should follow naming conventions
        Given I am checking files matching pattern "*.php"
        When I scan the project structure
        Then files should follow naming pattern "/^[A-Z][a-zA-Z0-9]*\.php$/"
        And files should have extension "php"

    Scenario: Configuration files should be in config directory
        Given I am checking files matching pattern "*.yaml"
        When I scan the project structure
        Then file "config/services.yaml" should exist
        And file "config/routes.yaml" should exist
        And directory "config" should exist

    Scenario: Test files should follow naming pattern
        Given I am checking files matching pattern "*Test.php"
        When I scan the project structure
        Then files should follow naming pattern "*Test.php"
        And files should be in directory "tests"

    Scenario: Source directory should contain limited file types
        Given I am checking files matching pattern "*"
        And I have a project in directory "./src"
        When I scan the project structure
        Then files should have extension "php"
        And files should not have extension "txt"
        And files should not have extension "md"

    Scenario: Documentation files should exist
        When I scan the project structure
        Then file "README.md" should exist
        And file "composer.json" should exist
        And file ".gitignore" should exist

    Scenario: Vendor directory should not contain custom files
        Given I have a project in directory "./vendor"
        When I scan the project structure
        Then directory "vendor" should exist
        But file "vendor/custom-file.php" should not exist

    Scenario: Log directory should have proper permissions
        Given I am checking files matching pattern "*.log"
        And directory "var/log" should exist
        When I scan the project structure
        Then files should be writable

    Scenario: Cache directory structure
        Given directory "var/cache" should exist
        When I scan the project structure
        Then directory "var/cache/dev" should exist
        And directory "var/cache/prod" should exist

    Scenario: Public assets organization
        Given I have a project in directory "./public"
        When I scan the project structure
        Then directory "public/css" should exist
        And directory "public/js" should exist
        And directory "public/images" should exist
        And file "public/index.php" should exist

    Scenario: File size constraints for source files
        Given I am checking files matching pattern "*.php"
        And I have a project in directory "./src"
        When I scan the project structure
        Then files should be smaller than 10000 bytes
        And files should be larger than 0 bytes

    Scenario: Configuration files should contain required content
        When I scan the project structure
        Then file "composer.json" should contain "autoload"
        And file "composer.json" should contain "psr-4"
        And file ".gitignore" should contain "vendor/"
        And file ".gitignore" should contain "/var/cache/"

    Scenario: Template directory organization
        Given directory "templates" should exist
        When I scan the project structure
        Then directory "templates" should contain at least 1 files
        And directory "templates/email" should exist