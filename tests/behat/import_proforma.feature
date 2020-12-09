@qformat @qtype_proforma @qformat_proforma
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

    # When I click on "Edit" "link" in the "is palindrom" "table_row"
    When I choose "Edit question" action for "is palindrom" in the question bank
    Then the following fields match these values:
      | Question name            | is palindrom              |
      | Question text            | checks whether a given string is a palindrom |
      | Default mark             | 1                              |
      | General feedback         |        |
      | Response format          | editor                         |
      | Response filename          | de/ostfalia/zell/isPalindromTask/MyString.java |
      | Input box size           | 15 lines                       |
      | Comment                  |        |
      | Penalty for each incorrect try  | 10%                     |
      | Aggregation strategy     | Weighted sum  |
      | UUID                     | 679c8796-97cc-41fc-8825-8b4d70cf79c2     |
      | ProFormA Version         | 2.0                        |

    And I should see "3" elements in "Downloadable files" filemanager
    And I should see "palindrom.txt"
    And I should see "samples.txt"
    # filename of download file with complex path is not completely visible
    #And I should see "de/ostfalia/zell/isPalindromTask/MyStringTemplate.java"

    # check for incorrect filename in model solution file
    And I should not see "deostfaliazellisPalindromTaskMyString.java"

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

    And following "de/ostfalia/zell/isPalindromTask/MyString.java" should download file with between "244" and "250" bytes
    # grader settings
    And I should see "javaTask2.zip"
    # zip file cannot be downloaded that way anymore (filemanager). 
    # But I do not know how.    
    # And following "javaTask2.zip" should download file with between "1828" and "1829" bytes
    # multiline fields
    And the field "Response template" starts with "package de.ostfalia.zell.isPalindromTask;"

    And I press "Cancel"

    # check for download link in "proforma-003"
    When I choose "Preview" action for "is palindrom" in the question bank
    And I switch to "questionpreview" window
    # check content of editor (response template)
    Then I should see "package de.ostfalia.zell.isPalindromTask;"
    Then I should see "palindrom.txt"
    Then I should see "samples.txt"
    Then I should see "template.txt"
    Then I should not see "code.txt"
    And following "palindrom.txt" should download file with between "224" and "232" bytes
    And following "samples.txt" should download file with between "96" and "103" bytes
    And following "template.txt" should download file with between "137" and "150" bytes
    And following "de/ostfalia/zell/isPalindromTask/MyStringTemplate.java" should download file with between "146" and "150" bytes

    And I switch to the main window


    # OK, let's switch from editor to filepicker
    When I choose "Edit question" action for "is palindrom" in the question bank
    And  I set the following fields to these values:
      | Response format          | filepicker                         |
    And I press "id_submitbutton"

    When I choose "Preview" action for "is palindrom" in the question bank
    And I switch to "questionpreview" window
    # no editor
    Then I should not see "package de.ostfalia.zell.isPalindromTask;"
    Then I should see "palindrom.txt"
    Then I should see "samples.txt"
    Then I should not see "template.txt"
    And I should see "de/ostfalia/zell/isPalindromTask/MyStringTemplate.java"
    # only one reponse template
    Then I should not see "code.txt"
    And following "palindrom.txt" should download file with between "224" and "232" bytes
    And following "samples.txt" should download file with between "96" and "103" bytes
    And following "de/ostfalia/zell/isPalindromTask/MyStringTemplate.java" should download file with between "146" and "150" bytes
    # And following "template.txt" should download file with between "137" and "150" bytes
    And I switch to the main window
