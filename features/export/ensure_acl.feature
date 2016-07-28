@javascript
Feature: Ensures acl are respected on the export profile tabs
  In order to give more access to export configuration (content part) for Julia, without giving her all the settings access (Properties tab)
  As peter
  I would like to manage permissions on the export profile tabs

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "Catalog manager" role page
    And I visit the "Permissions" tab

  Scenario: Should not see the general properties tab
    Given I revoke rights to resources Show an export profile general properties
    And I grant rights to resources Show an export profile content
    And I save the role
    When I am on the "csv_footwear_product_export" export job page
    Then I should be on the "Content" tab

  Scenario: Should not see the general properties and content tab
    Given I revoke rights to resources Show an export profile general properties
    And I revoke rights to resources Show an export profile content
    And I save the role
    When I am on the "csv_footwear_product_export" export job page
    Then I should be on the "History" tab

  Scenario: Should not be able to edit the general properties job profile
    Given I revoke rights to resources Edit an export profile general properties
    And I grant rights to resources Edit an export profile content
    And I save the role
    When I am on the "csv_footwear_product_export" export job edit page
    Then I should be on the "Content" tab

  Scenario: Should not be able to edit the general properties and content tab
    Given I revoke rights to resources Edit an export profile general properties
    And I revoke rights to resources Edit an export profile content
    And I save the role
    When I am on the "csv_footwear_product_export" export job edit page
    Then I should be on the "History" tab
