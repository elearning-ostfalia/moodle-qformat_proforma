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

    public function test_import_java_task() {
        $this->prepare_test();
        // create proforma importer
        $importer = new qformat_proforma();

        $result = $importer->readdata(__DIR__ . '/fixtures/sampleJavaTask.zip');
        $this->assertEquals(1, count($result));
        $questions = $importer->readquestions($result);

        $expectedq = (object) array(
            'name' => 'Sample Java Task',
            'questiontext' => '<b>Task</b> Description ',
            'questiontextformat' => FORMAT_HTML,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'proforma',
            'defaultmark' => 1,
            'penalty' => 0.1,
            'length' => 1,
        );

        $this->assertEquals(1, count($questions));
        $this->assert(new question_check_specified_fields_expectation($expectedq), $questions[0]);
        $this->assertEquals('proforma', $questions[0]->qtype);

        $this->assertEquals('46d4e650-8e98-4736-b0d1-d1aa2c64ff82', $questions[0]->uuid);
        $this->assertEquals('java', $questions[0]->programminglanguage);

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
                , $questions[0]->modelsolution);
        $this->assertEquals('reverse_task/MyString.java', $questions[0]->responsefilename);
        $this->assertEquals('class MyClass {
    
    // write your code here...
    
}',
                $questions[0]->responsetemplate);
        $this->assertEquals('reverse_task/MyString.java', $questions[0]->responsefilename);
        $this->assertEquals('reverse_task/MyString.java', $questions[0]->responsefilename);
        $this->assertEquals('info.txt', $questions[0]->downloads);
        $this->assertEquals('code.txt', $questions[0]->templates);
        $this->assertEquals('reverse_task/MyString.java', $questions[0]->modelsolfiles);
        $this->assertEquals('<grading-hints><root function="sum"><test-ref weight="0" ref="1"><title>Compiler Test</title><test-type>java-compilation</test-type><description>compile code without errors?</description></test-ref><test-ref weight="2" ref="2"><title>Junit Test MyStringTest</title><test-type>unittest</test-type><description>simple JUNIT test</description></test-ref></root></grading-hints>',
                $questions[0]->gradinghints);
        $this->assertEquals(2, $questions[0]->aggregationstrategy);
        $this->assertEquals(10240, $questions[0]->maxbytes);
        $this->assertEquals('.java', $questions[0]->filetypes);
        $this->assertEquals('editor', $questions[0]->responseformat);
        $this->assertEquals('15', $questions[0]->responsefieldlines);
        $this->assertEquals(0, $questions[0]->attachments);
        $this->assertEquals(1, $questions[0]->taskstorage);
        $this->assertEquals('2.0', $questions[0]->proformaversion);
        // todo: test files in file storage
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
}

