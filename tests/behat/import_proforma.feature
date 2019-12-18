@qtype @qtype_proforma @qformat_proforma
Feature: IMPORT (ProFormA format)
  Test importing ProFormA questions
  As a teacher
  In order to use ProFormA questions
  I need to import them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript @_file_upload
  Scenario: import Java question.
    When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_proforma" to "1"
    And I upload "question/format/proforma/tests/fixtures/javaTask2.zip" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. checks whether a given string is a palindrom"
    And I press "Continue"
    And I should see "is palindrom"

    When I click on "Edit" "link" in the "is palindrom" "table_row"
    Then the following fields match these values:
      | Question name            | is palindrom              |
      | Question text            | checks whether a given string is a palindrom |
      | Default mark             | 1                              |
      | General feedback         |        |
      | Response format          | editor                         |
      | Input box size           | 15 lines                       |
      | Comment                  |        |
      | Penalty for each incorrect try  | 10%                     |
      | Aggregation strategy     | Weighted sum  |

    # tests
    And the field "testtitle[0]" matches value "Compiler Test"
    And the field "testweight[0]" matches value "0"
    And the field "testid[0]" matches value "1"
    And the field "testtype[0]" matches value "java-compilation"
    And the field "testdescription[0]" matches value ""

    And the field "testtitle[1]" matches value "Junit Test ostfalia/zell/isPalindromTask/PalindromTest"
    And the field "testweight[1]" matches value "1"
    And the field "testid[1]" matches value "2"
    And the field "testtype[1]" matches value "unittest"
    And the field "testdescription[1]" matches value ""

    # static fields
    And I should see "de/ostfalia/zell/isPalindromTask/MyString.java "
    # grader settings
    And I should see "c3c1a32a-b33b-4034-bf6f-6fbfe1efe3fc"
    And I should see "javaTask2.zip"
    And I should see "2.0"

    # multiline fields
    And the field "Response template" starts with "package de.ostfalia.zell.isPalindromTask;"

    And I press "Cancel"