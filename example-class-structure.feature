Feature: PHP Class Structure Validation
    As a developer
    I want to ensure that my PHP classes follow project conventions
    So that the codebase remains consistent and maintainable

    Background:
        Given I have a project in directory "./src"
        And the project follows PHP best practices

    Scenario: Service classes should be final and implement interfaces
        Given I have a PHP class matching pattern "*Service"
        When I check the class structure
        Then the class should be final
        And the class should not be abstract
        And the class should not be interface
        And the class should implement "App\Contracts\ServiceInterface"

    Scenario: Repository classes should extend base repository
        Given I have a PHP class matching pattern "*Repository"
        When I check the class structure
        Then the class should extend "App\Repository\BaseRepository"
        And the class should not be final
        And the class should not be abstract

    Scenario: Abstract classes should follow naming pattern
        Given I have a PHP class matching pattern "Abstract*"
        When I check the class structure
        Then the class should be abstract
        And the class should not be final
        And the class should not be interface

    Scenario: Controllers should not depend on specific implementations
        Given I am analyzing the class "App\Controller\UserController"
        When I validate the class dependencies
        Then the class should not depend on "App\Repository\UserRepository"
        And the class should depend on "App\Contracts\UserRepositoryInterface"

    Scenario: Methods in service classes should have limited parameters
        Given I am analyzing the class "App\Service\UserService"
        When I inspect the class methods
        Then methods should have maximum 4 arguments

    Scenario: Value objects should be final and immutable
        Given I have a PHP class matching pattern "*ValueObject"
        When I check the class structure
        Then the class should be final
        And the class should not be abstract

    Scenario: Interfaces should follow naming convention
        Given I have a PHP class matching pattern "*Interface"
        When I check the class structure
        Then the class should be interface
        And the class should not be final
        And the class should not be abstract

    Scenario: Event classes should have specific attributes
        Given I have a PHP class matching pattern "*Event"
        When I examine the class attributes
        Then the class should have attribute "AsEventListener"

    Scenario: Command classes should not have forbidden methods
        Given I have a PHP class matching pattern "*Command"
        When I inspect the class methods
        Then the class should not have methods named "get*"
        And the class should not have methods named "set*"