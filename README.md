# BDD Arkitect

A Behat extension for verifying and maintaining naming conventions and file structure rules in your PHP projects.

## Installation

Install via Composer:

```bash
composer require raffaelecarelle/bdd-arkitect
```

## Overview

BDD Arkitect provides a set of Behat contexts that allow you to write scenarios to validate your project's:

- File structure and naming conventions
- Namespace structure and PSR-4 compliance
- PHP class structure and dependencies

## Configuration

BDD Arkitect can be configured in your `behat.yml` file. Here's an example configuration:

```yaml
default:
  suites:
    # Your suites configuration...

  extensions:
    BddArkitect\Extension\ArkitectExtension:
      project_root: "%paths.base%"
      paths:
        - src
        - tests
      excludes:
        - vendor
        - var
        - cache
      ignore_errors:
        - ".*should be final.*"
```

### Configuration Options

#### project_root

The root directory of the project. Default: `%paths.base%`

```yaml
project_root: "%paths.base%"
```

#### paths

Array of relative paths where the tool should analyze and validate rules. If empty, all paths will be analyzed (except those in `excludes`).

```yaml
paths:
  - src
  - tests
```

#### excludes

Array of relative paths to exclude from validation.

```yaml
excludes:
  - vendor
  - var
  - cache
```

#### ignore_errors

Array of regex patterns to filter errors. Errors matching these patterns will be ignored.

```yaml
ignore_errors:
  - ".*should be final.*"
  - ".*should have attribute.*"
```

## Usage

### File Structure Validation

```gherkin
Feature: File Structure Validation

  Scenario: Validate controller file naming
    Given I have a project in directory "src/Controller"
    And I am checking files matching pattern "*.php"
    Then files should follow naming pattern "*Controller.php"
    And files should have extension "php"
```

### Namespace Structure Validation

```gherkin
Feature: Namespace Structure Validation

  Scenario: Validate controller namespace
    Given I have a PSR-4 compliant project
    And I am analyzing classes in namespace "App\Controller"
    Then namespace "App\Controller" should exist
    And namespace "App\Controller" should contain only classes matching pattern "*Controller"
```

### PHP Class Structure Validation

```gherkin
Feature: PHP Class Structure Validation

  Scenario: Validate controller class structure
    Given I have a PHP class matching pattern "*Controller"
    And I am analyzing the class "App\Controller\UserController"
    Then the class should be final
    And the class should not be abstract
    And the class should not be interface
```

## Examples

Check the example feature files in the project root:

- `example-file-structure.feature`
- `example-namespace-structure.feature`
- `example-class-structure.feature`

## Testing

See the [tests/README.md](tests/README.md) file for information on running the tests.

## License

MIT