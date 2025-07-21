Feature: File Structure Validation Tests
  As a developer
  I want to ensure that the file structure validation works correctly
  So that I can maintain consistent file organization

  Background:
    Given I have a project in directory "./tests/Functional/fixtures"

  Scenario: PHP files should follow naming conventions
    Given I am checking files matching pattern "*.php"
    When I scan the project structure
    Then files should follow naming pattern "*Test.php"
    And files should have extension "php"

  Scenario: Configuration files should be in config directory
    Given I am checking files matching pattern "*.yaml"
    When I scan the project structure
    Then file "config/services.yaml" should exist
    And file "config/routes.yaml" should exist
    And directory "config" should exist

  Scenario: Source directory should contain limited file types
    Given I am checking files matching pattern "*"
    And I have a project in directory "./tests/Functional/fixtures/src"
    When I scan the project structure
    Then files should have extension "php"
    And files should not have extension "txt"

  Scenario: Documentation files should exist
    When I scan the project structure
    Then file "README.md" should exist
    And file "composer.json" should exist

  Scenario: File content validation
    When I scan the project structure
    Then file "composer.json" should contain "autoload"
    And file "composer.json" should contain "psr-4"
    And file "README.md" should contain "Test Project"