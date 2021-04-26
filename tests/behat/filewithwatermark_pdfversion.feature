@mod @mod_filewithwatermark @_file_upload
Feature: Teacher can specify different display options for the resource
  In order to provide more information about a file
  As a teacher
  I need to be able to show size, type and modified date

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  @javascript
  Scenario Outline: Specifying different display options for a file resource
    When I add a "File with watermark" to section "1"
    And I set the following fields to these values:
      | Name                      | Myfile     |
      | Display                   | Open       |
      | Show size                 | 0          |
      | Show type                 | 0          |
      | Show upload/modified date | 0          |
    And I upload "<file>" file to "Select files" filemanager
    And I press "Save and display"
    And I <seeerror> see "Docupub"
    And I log out

    Examples:
      | file                                                                       | seeerror   |
      | mod/filewithwatermark/tests/generator/filewithwatermark_test_file.pdf      | should not |
      | mod/filewithwatermark/tests/generator/filewithwatermark_test_file_v1.7.pdf | should     |

