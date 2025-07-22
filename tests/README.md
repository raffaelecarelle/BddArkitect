# BDD Arkitect Tests

This directory contains automated tests for the BDD Arkitect project.

## Test Structure

The tests are organized into three categories:

1. **Unit Tests** (`tests/Unit/`): Tests for individual components in isolation
   - `Context/FileStructureContextTest.php`: Tests for the FileStructureContext class
   - `Context/NamespaceStructureContextTest.php`: Tests for the NamespaceStructureContext class
   - `Context/PHPClassStructureContextTest.php`: Tests for the PHPClassStructureContext class

2. **Integration Tests** (`tests/Integration/`): Tests for components working together
   - `ContextIntegrationTest.php`: Tests for the Context classes working together

3. **Functional Tests** (`tests/Functional/`): End-to-end tests with Behat
   - `features/file-structure-test.feature`: Feature file for testing file structure validation
   - `fixtures/`: Test fixtures for the functional tests

## Running the Tests

### Unit and Integration Tests

To run the unit and integration tests, use PHPUnit:

```bash
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Integration
```

### Functional Tests

To run the functional tests, use Behat:

```bash
vendor/bin/behat --config=tests/Functional/fixtures/behat.yml
```

## Test Dependencies

The tests use the following dependencies:

- **PHPUnit**: For unit and integration testing
- **Behat**: For BDD testing
- **vfsStream**: For virtual file system testing

These dependencies are included in the `composer.json` file.

## Adding New Tests

### Unit Tests

To add a new unit test:

1. Create a new test class in the `tests/Unit/` directory
2. Extend the `PHPUnit\Framework\TestCase` class
3. Use the `vfsStream` library to create a virtual file system for testing
4. Write test methods for the class you want to test

### Integration Tests

To add a new integration test:

1. Create a new test class in the `tests/Integration/` directory
2. Extend the `PHPUnit\Framework\TestCase` class
3. Use the `vfsStream` library to create a virtual file system for testing
4. Write test methods that test multiple components working together

### Functional Tests

To add a new functional test:

1. Create a new feature file in the `tests/Functional/features/` directory
2. Create test fixtures in the `tests/Functional/fixtures/` directory
3. Run the test with Behat