Feature: Namespace Structure Validation
    As a developer
    I want to ensure that namespaces follow PSR-4 and project conventions
    So that autoloading works correctly and code is well organized

    Background:
        Given I have a PSR-4 compliant project

    Scenario: Main application namespace should follow PSR-4
        When I check namespace compliance
        Then namespace "App" should follow PSR-4 structure
        And namespace "App\Controller" should follow PSR-4 structure
        And namespace "App\Service" should follow PSR-4 structure
        And namespace "App\Repository" should follow PSR-4 structure

    Scenario: Controller classes should be in correct namespace
        Given I am analyzing classes in namespace "App\Controller"
        When I check namespace compliance
        Then namespace "App\Controller" should contain only classes matching pattern "*Controller"
        And all classes should match their file paths

    Scenario: Service classes organization
        Given I am analyzing classes in namespace "App\Service"
        When I check namespace compliance
        Then namespace "App\Service" should exist
        And namespace "App\Service" should contain only classes matching pattern "*Service"
        And namespace "App\Service" should contain at least 1 classes

    Scenario: Repository namespace structure
        Given I am analyzing classes in namespace "App\Repository"
        When I check namespace compliance
        Then namespace "App\Repository" should exist
        And namespace "App\Repository" should contain only classes matching pattern "*Repository"
        And class "App\Repository\UserRepository" should be in correct directory

    Scenario: Namespace depth limitations
        When I check namespace compliance
        Then namespace "App\Controller\Admin" should have maximum depth of 3 levels
        And namespace "App\Service\External\Payment" should have maximum depth of 4 levels

    Scenario: Contract interfaces should be properly organized
        Given I am analyzing classes in namespace "App\Contracts"
        When I check namespace compliance
        Then namespace "App\Contracts" should exist
        And namespace "App\Contracts" should contain only classes matching pattern "*Interface"
        And namespace "App\Contracts" should not contain classes matching pattern "*Impl"

    Scenario: Value objects namespace
        Given I am analyzing classes in namespace "App\ValueObject"
        When I check namespace compliance
        Then namespace "App\ValueObject" should exist
        And namespace "App\ValueObject" should contain only classes matching pattern "*ValueObject"
        And namespace "App\ValueObject" should have minimum depth of 2 levels

    Scenario: Test namespace structure
        Given I am analyzing classes in namespace "Tests"
        When I check namespace compliance
        Then namespace "Tests" should follow PSR-4 structure
        And namespace "Tests\Unit" should exist
        And namespace "Tests\Integration" should exist
        And namespace "Tests\Functional" should exist

    Scenario: Forbidden namespaces should not exist
        When I check namespace compliance
        Then namespace "App\Legacy" should not exist
        And namespace "App\Old" should not exist
        And namespace "App\Deprecated" should not exist

    Scenario: Namespace naming conventions
        When I check namespace compliance
        Then all namespaces should follow naming convention "PascalCase"

    Scenario: Domain-driven design namespace structure
        Given I am analyzing classes in namespace "App\Domain"
        When I check namespace compliance
        Then namespace "App\Domain\User" should exist
        And namespace "App\Domain\User\Entity" should exist
        And namespace "App\Domain\User\ValueObject" should exist
        And namespace "App\Domain\User\Repository" should exist
        And namespace "App\Domain\User" should contain at least 1 classes

    Scenario: Infrastructure namespace organization
        Given I am analyzing classes in namespace "App\Infrastructure"
        When I check namespace compliance
        Then namespace "App\Infrastructure" should exist
        And namespace "App\Infrastructure\Persistence" should exist
        And namespace "App\Infrastructure\Http" should exist

    Scenario: Maximum classes per namespace
        Given I am analyzing classes in namespace "App\Controller"
        When I check namespace compliance
        Then namespace "App\Controller" should contain at most 20 classes

    Scenario: Command and Query separation
        When I check namespace compliance
        Then namespace "App\Command" should exist
        And namespace "App\Query" should exist
        And namespace "App\Command" should not contain classes matching pattern "*Query*"
        And namespace "App\Query" should not contain classes matching pattern "*Command*"