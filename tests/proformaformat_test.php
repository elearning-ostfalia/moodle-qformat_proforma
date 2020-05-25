<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * PHPUnit tests for the ProFormA format question importer.
 *
 * @package    qformat_proforma
 * @copyright  2019 Ostfalia University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/proforma/format.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');


/**
 * Unit tests for the Proforma question importer.
 *
 * @copyright 2019 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_proforma_test extends question_testcase {
    private function prepare_test() {
        // because we need to change the user it is necessary to
        // reset the test at the end in order to avoid an error message
        $this->resetAfterTest(true);
        // draft file storage needs a current user id. So this test must
        // be associated with a user!
        $this->setAdminUser();
    }

    public function test_import_java_task_1_zip() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        $result = $importer->readdata(__DIR__ . '/fixtures/javaTask1.zip');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $this->assertEquals(1, count($questions));

        $this->assert_java_task_1($questions[0]);
    }

    public function test_import_java_task_2_zip() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        $result = $importer->readdata(__DIR__ . '/fixtures/javaTask2.zip');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $this->assertEquals(1, count($questions));

        $this->assert_java_task_2($questions[0], 'cc1a0ff4-8550-49a9-b33b-4bc3cc30613f');
    }

    public function test_import_java_task_2_xml() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        $result = $importer->readdata(__DIR__ . '/fixtures/javaTask2.xml');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $this->assertEquals(1, count($questions));

        $this->assert_java_task_2($questions[0]);
    }

    /**
     * test:
     * - embedded bin file
     */
    public function test_import_embedded_bin_file_xml() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/javaTaskEmbeddedBinFile.xml');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Error importing question', $output);
        $this->assertContains('The task file contains an unsupported ProFormA feature: embedded binary files', $output);

        $this->assertEquals(false, $questions);
    }

    /**
     * invalid ProFormA version
     */
    public function test_import_invalid_version_xml() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/javaTask2Version1.5.xml');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Error importing question', $output);
        $this->assertContains('The task file does not contain a ProFormA task or the version of the ProFormA task is unsupported. Supported versions are', $output);

        $this->assertEquals(false, $questions);
    }


    public function test_import_invalid_xml_xml() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/javaTask2invalidXml.xml');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Error importing question', $output);
        $this->assertContains('The task file does not contain valid xml.', $output);

        $this->assertEquals(false, $questions);
    }


    public function test_import_no_extension() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/javaTask2');
        $this->assertEquals(false, $result);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Error importing question', $output);
        $this->assertContains('The file is not a ProFormA file (xml or zip).', $output);
    }


    public function test_import_java_archive() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        $result = $importer->readdata(__DIR__ . '/fixtures/javaArchive.zip');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(2, count($result));
        $questions = $importer->readquestions($result);
        $this->assertEquals(2, count($questions));

        $this->assert_java_task_1($questions[1]);
        $this->assert_java_task_2($questions[0]);
    }


    public function test_import_java_archive_with_wrong_task() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/archiveWithWrongTask.zip');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(2, count($result));
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Error importing question', $output);
        $this->assertContains('File \'info.txt\' is referenced in task but is not attached', $output);

        $this->assertEquals(1, count($questions));

        $this->assert_java_task_1($questions[0]);
    }

    /**
     * tests: missing attached files referenced in task
     */
    public function test_read_missing_attached_file() {
        $this->prepare_test();

        $importer = new qformat_proforma();

        // The importer echoes some errors, so we need to capture and check that.
        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/attachedFileMissing.zip');
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Error importing question', $output);
        $this->assertContains('File \'info.txt\' is referenced in task but is not attached', $output);

        // No question  have been imported.
        $this->assertEquals(false, $questions);
    }

    /**
     * @param $questions
     */
    protected function assert_java_task_1($question) {

        $expectedq = (object) array(
                'questiontextformat' => FORMAT_HTML,
                'generalfeedback' => '',
                'generalfeedbackformat' => FORMAT_MOODLE,
                'qtype' => 'proforma',
                'defaultmark' => 1,
                'penalty' => 0.1,
                'length' => 1,
        );

        $this->assert(new question_check_specified_fields_expectation($expectedq), $question);
        $this->assertEquals($question->name, 'Sample Java Task');
        $this->assertEquals($question->questiontext, '<b>Task</b> Description ');
        $this->assertEquals($question->qtype, 'proforma');

        $this->assertEquals($question->uuid, '46d4e650-8e98-4736-b0d1-d1aa2c64ff82');
        $this->assertEquals($question->programminglanguage, 'java');

        $this->assertEquals('package reverse_task;

public class MyString 
{
	static public String flip( String aString)
	{	
		StringBuilder sb = new StringBuilder();
		
		for (int i = 0; i < aString.length(); i++)
			sb.append(aString.charAt(aString.length()-1-i));

		return sb.toString();
	}
}
'
                , $question->modelsolution);
        $this->assertEquals('reverse_task/MyString.java', $question->responsefilename);
        $this->assertEquals('class MyClass {
    
    // write your code here...
    
}',
                $question->responsetemplate);
        $this->assertEquals('reverse_task/MyString.java', $question->responsefilename);
        $this->assertEquals('info.txt', $question->downloads);
        $this->assertEquals('code.txt', $question->templates);
        $this->assertEquals('reverse_task/MyString.java', $question->modelsolfiles);
        $this->assertEquals('<grading-hints><root function="sum"><test-ref weight="0" ref="1"><title>Compiler Test</title><test-type>java-compilation</test-type><description>compile code without errors?</description></test-ref><test-ref weight="2" ref="2"><title>Junit Test MyStringTest</title><test-type>unittest</test-type><description>simple JUNIT test</description></test-ref></root></grading-hints>',
                $question->gradinghints);
        $this->assertEquals(2, $question->aggregationstrategy);
        $this->assertEquals(10240, $question->maxbytes);
        $this->assertEquals('.java', $question->filetypes);
        $this->assertEquals('editor', $question->responseformat);
        $this->assertEquals('15', $question->responsefieldlines);
        $this->assertEquals(0, $question->attachments);
        $this->assertEquals(1, $question->taskstorage);
        $this->assertEquals('2.0', $question->proformaversion);
        // todo: test files in file storage
    }

    /**
     * @param $questions
     */
    protected function assert_java_task_2($question, $uuid = null) {
        $expectedq = (object) array(
                'questiontextformat' => FORMAT_HTML,
                'generalfeedback' => '',
                'generalfeedbackformat' => FORMAT_MOODLE,
                'qtype' => 'proforma',
                'defaultmark' => 1,
                'penalty' => 0.1,
                'length' => 1,
        );

        $this->assert(new question_check_specified_fields_expectation($expectedq), $question);
        $this->assertEquals('is palindrom', $question->name);
        $this->assertEquals('checks whether a given string is a palindrom', $question->questiontext);
        $this->assertEquals('proforma', $question->qtype);

        $this->assertEquals('679c8796-97cc-41fc-8825-8b4d70cf79c2', $question->uuid);
        $this->assertEquals('java', $question->programminglanguage);

        $this->assertEquals('package de.ostfalia.zell.isPalindromTask;

public class MyString {
	
	static public Boolean isPalindrom(String aString) 
	{
		String reverse = new StringBuilder(aString).reverse().toString();

		return (aString.equalsIgnoreCase(reverse));
	}
}
'
                , $question->modelsolution);
        $this->assertEquals('de/ostfalia/zell/isPalindromTask/MyString.java', $question->responsefilename);
        $this->assertEquals('package de.ostfalia.zell.isPalindromTask;

public class MyString {
	
	static public Boolean isPalindrom(String aString) 
	{
		// ...
	}
}
',
                $question->responsetemplate);
        $this->assertEquals('de/ostfalia/zell/isPalindromTask/MyString.java', $question->responsefilename);
        $this->assertEquals('palindrom.txt,samples.txt,de/ostfalia/zell/isPalindromTask/MyStringTemplate.java', $question->downloads);
        $this->assertEquals('code.txt', $question->templates);
        $this->assertEquals('de/ostfalia/zell/isPalindromTask/MyString.java', $question->modelsolfiles);
        $this->assertEquals('<grading-hints><root function="sum"><test-ref weight="0" ref="1"><title>Compiler Test</title><test-type>java-compilation</test-type></test-ref><test-ref weight="1" ref="2"><title>Junit Test ostfalia/zell/isPalindromTask/PalindromTest</title><test-type>unittest</test-type></test-ref></root></grading-hints>',
                $question->gradinghints);
        $this->assertEquals(2, $question->aggregationstrategy);
        $this->assertEquals(0, $question->maxbytes);
        $this->assertEquals('', $question->filetypes);
        $this->assertEquals('editor', $question->responseformat);
        $this->assertEquals('15', $question->responsefieldlines);
        $this->assertEquals(0, $question->attachments);
        $this->assertEquals(1, $question->taskstorage);
        $this->assertEquals('2.0', $question->proformaversion);
        // todo: test files in file storage
    }

    /**
     * tests:
     * - ProFormA version 2.0.1
     * - complex grading hints
     * - more than one model solution
     * @throws coding_exception
     */
    public function test_import_task_2_0_1() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        // The importer echoes some errors, so we need to capture and check that.
        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/task_2.01_prefix.xml');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Task contains more than one model solution. None is imported!', $output);
        $this->assertContains('Grading hints other than weighted sum are not supported.', $output);

        $this->assertEquals(1, count($questions));

        $expectedq = (object) array(
                'questiontextformat' => FORMAT_HTML,
                'generalfeedback' => '',
                'generalfeedbackformat' => FORMAT_MOODLE,
                'qtype' => 'proforma',
                'defaultmark' => 1,
                'penalty' => 0.1,
                'length' => 1,
        );

        $question = $questions[0];
        $this->assert(new question_check_specified_fields_expectation($expectedq), $question);
        $this->assertEquals($question->name, 'Task 2.0.1');
        $this->assertEquals($question->questiontext, 'description of the task');
        $this->assertEquals($question->qtype, 'proforma');

        $this->assertEquals($question->uuid, '9a95419c-d12f-4e2b-9109-d498de235e86');
        $this->assertEquals($question->programminglanguage, 'java');

        $this->assertEquals(null, $question->modelsolution);
        $this->assertEquals('Solution.java', $question->responsefilename);
        $this->assertEquals('', $question->responsetemplate);
        $this->assertEquals('', $question->downloads);
        $this->assertEquals('', $question->templates);
        $this->assertEquals('', $question->modelsolfiles);
        $this->assertEquals(1, $question->aggregationstrategy);
        $this->assertEquals(10240, $question->maxbytes);
        $this->assertEquals('.java', $question->filetypes);
        $this->assertEquals('editor', $question->responseformat);
        $this->assertEquals('15', $question->responsefieldlines);
        $this->assertEquals(0, $question->attachments);
        $this->assertEquals(1, $question->taskstorage);
        $this->assertEquals('2.0.1', $question->proformaversion);

        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<grading-hints>
 <root function="sum">
  <test-ref ref="compile" weight="1">
   <title>Compilation</title>
   <test-type>java-compilation</test-type>
  </test-ref>
  <test-ref ref="junit" weight="1">
   <title>JUnit test case</title>
   <test-type>unittest</test-type>
  </test-ref>
  <test-ref ref="checkstyle" weight="1">
   <title>Checkstyle</title>
   <test-type>java-checkstyle</test-type>
  </test-ref>
 </root>
</grading-hints>
',
                $question->gradinghints);
    }

    /**
     * tests:
     * - submission restriction with zip
     */
    public function test_import_sr_zip() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        // The importer echoes some errors, so we need to capture and check that.
        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/task_submn_restrcit_zip.xml');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(1, count($questions));

        $expectedq = (object) array(
                'questiontextformat' => FORMAT_HTML,
                'generalfeedback' => '',
                'generalfeedbackformat' => FORMAT_MOODLE,
                'qtype' => 'proforma',
                'defaultmark' => 1,
                'penalty' => 0.1,
                'length' => 1,
        );

        $question = $questions[0];
        $this->assert(new question_check_specified_fields_expectation($expectedq), $question);
        $this->assertEquals($question->name, 'Task 2.0.1');
        $this->assertEquals($question->questiontext, 'description of the task');
        $this->assertEquals($question->qtype, 'proforma');

        $this->assertEquals($question->uuid, '9a95419c-d12f-4e2b-9109-d498de235e86');
        $this->assertEquals($question->programminglanguage, 'java');

        $this->assertEquals('', $question->responsetemplate);
        $this->assertEquals('', $question->downloads);
        $this->assertEquals('', $question->templates);
        $this->assertEquals(1, $question->aggregationstrategy);
        $this->assertEquals(10240, $question->maxbytes);
        $this->assertEquals('.zip', $question->filetypes);
        $this->assertEquals('filepicker', $question->responseformat);
        $this->assertEquals(1, $question->attachments);
        $this->assertEquals(1, $question->taskstorage);
        $this->assertEquals('2.0', $question->proformaversion);

        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<grading-hints>
 <root function="sum">
  <test-ref ref="compile" weight="1">
   <title>Compilation</title>
   <test-type>java-compilation</test-type>
  </test-ref>
  <test-ref ref="junit" weight="1">
   <title>JUnit test case</title>
   <test-type>unittest</test-type>
  </test-ref>
  <test-ref ref="checkstyle" weight="1">
   <title>Checkstyle</title>
   <test-type>java-checkstyle</test-type>
  </test-ref>
 </root>
</grading-hints>
',
                $question->gradinghints);
        $this->assertEquals('package de.ostfalia.zell.isPalindromTask;
public class MyString {
	
	static public Boolean isPalindrom(String aString) 
	{
		// ...
	}
}
', $question->modelsolution);
        $this->assertEquals('Solution.zip', $question->responsefilename);
        $this->assertEquals('correct.zip', $question->modelsolfiles);
    }

    /**
     * tests:
     * - zip as model solution => filepicker
     * @throws coding_exception
     */
    public function test_import_zip_solution() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        // The importer echoes some errors, so we need to capture and check that.
        ob_start();
        $result = $importer->readdata(__DIR__ . '/fixtures/task_zip_solution.zip');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $output = ob_get_contents();
        ob_end_clean();

        // complex grading hints
        $this->assertContains('Grading hints other than weighted sum are not supported.', $output);

        $this->assertEquals(1, count($questions));

        $expectedq = (object) array(
                'questiontextformat' => FORMAT_HTML,
                'generalfeedback' => '',
                'generalfeedbackformat' => FORMAT_MOODLE,
                'qtype' => 'proforma',
                'defaultmark' => 1,
                'penalty' => 0.1,
                'length' => 1,
        );

        $question = $questions[0];
        $this->assert(new question_check_specified_fields_expectation($expectedq), $question);
        $this->assertEquals($question->name, 'Task 2.0.1');
        $this->assertEquals($question->questiontext, 'description of the task');
        $this->assertEquals($question->qtype, 'proforma');

        $this->assertEquals($question->uuid, '9a95419c-d12f-4e2b-9109-d498de235e86');
        $this->assertEquals($question->programminglanguage, 'java');

        $this->assertEquals('correct.zip', $question->responsefilename);
        $this->assertEquals('', $question->responsetemplate);
        $this->assertEquals('', $question->downloads);
        $this->assertEquals('', $question->templates);
        $this->assertEquals('correct.zip', $question->modelsolfiles);
        $this->assertEquals(1, $question->aggregationstrategy);
        $this->assertEquals(10240, $question->maxbytes);
        $this->assertEquals('.zip', $question->filetypes);
        $this->assertEquals('filepicker', $question->responseformat);
        $this->assertEquals(1, $question->attachments);
        $this->assertEquals(1, $question->taskstorage);
        $this->assertEquals('2.0.1', $question->proformaversion);

        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<grading-hints>
 <root function="sum">
  <test-ref ref="compile" weight="1">
   <title>Compilation</title>
   <test-type>java-compilation</test-type>
  </test-ref>
  <test-ref ref="junit" weight="1">
   <title>JUnit test case</title>
   <test-type>unittest</test-type>
  </test-ref>
  <test-ref ref="checkstyle" weight="1">
   <title>Checkstyle</title>
   <test-type>java-checkstyle</test-type>
  </test-ref>
 </root>
</grading-hints>
',
                $question->gradinghints);
        $this->assertEquals('', $question->modelsolution);
    }

}

