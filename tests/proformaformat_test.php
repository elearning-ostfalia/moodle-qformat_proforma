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

    public function test_import_java_task_1() {
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

    public function test_import_java_task_2() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        $result = $importer->readdata(__DIR__ . '/fixtures/javaTask2.zip');
        $this->assertNotEquals(false, $result);
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);
        $this->assertEquals(1, count($questions));

        $this->assert_java_task_2($questions[0]);
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
    protected function assert_java_task_2($question) {
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
        $this->assertEquals($question->name, 'is palindrom');
        $this->assertEquals($question->questiontext, 'checks whether a given string is a palindrom');
        $this->assertEquals($question->qtype, 'proforma');

        $this->assertEquals($question->uuid, 'c3c1a32a-b33b-4034-bf6f-6fbfe1efe3fc');
        $this->assertEquals($question->programminglanguage, 'java');

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
        $this->assertEquals('', $question->downloads);
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
}

