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
 * PHPUnit tests for the SimpleXMLElement wrapper class.
 *
 * @package    qformat_proforma
 * @copyright  2020 Ostfalia University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/format/proforma/classes/ProformaXMLElement.php');

/**
 * Unit tests for the Proforma question importer.
 *
 * @copyright 2019 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class proformaxmlelement_test extends question_testcase {
    private $testxml1 = '<?xml version="1.0"?>
<p:task xmlns:p="urn:proforma:v2.0" lang="de" uuid="66646c04-cf6d-4b4e-aa2c-b2fdbe3f6035" 
                           xmlns:u="urn:proforma:tests:unittest:v1.0">
    <p:title>Task Title</p:title>
    <p:description><![CDATA[Write a program that ....]]></p:description>
    <p:proglang version="1.8">java</p:proglang>
    <p:submission-restrictions max-size="10000">
        <p:file-restriction required="true" pattern-format="none">Solution.java</p:file-restriction>
    </p:submission-restrictions>
    <p:files>
        <p:file id="junit" used-by-grader="true" visible="no">
            <p:attached-bin-file>JunitTest.java</p:attached-bin-file>
        </p:file>
        <p:file id="checkstyle" used-by-grader="true" visible="no">
            <p:attached-bin-file encoding="UTF-8">checkstyle.xml</p:attached-bin-file>
        </p:file>
        <p:file id="solution" used-by-grader="false" visible="delayed">
            <p:attached-bin-file>Solution.java</p:attached-bin-file>
        </p:file>
    </p:files>
    <p:model-solutions>
        <p:model-solution id="ms">
            <p:filerefs>
                <p:fileref refid="solution"></p:fileref>
            </p:filerefs>
        </p:model-solution>
    </p:model-solutions>
    <p:tests>
        <p:test id="1">
              <p:title>Compilation</p:title>
              <p:test-type>java-compilation</p:test-type>
              <p:test-configuration></p:test-configuration>
        </p:test>
    <p:test id="2">
        <p:title>JUnit Test</p:title>
        <p:test-type>unittest</p:test-type>
        <p:test-configuration>
            <p:filerefs>
                <p:fileref refid="junit"></p:fileref>
            </p:filerefs>
            <u:unittest framework="junit" version="5">
                <u:entry-point>JunitTest</u:entry-point>
            </u:unittest>
        </p:test-configuration>
    </p:test>
    </p:tests>
</p:task>';


    public function test_elements() {
        $xmltask = new SimpleXMLElement($this->testxml1);
        $xmlelement = new ProformaXMLElement($xmltask, "urn:proforma:v2.0");

        // first level element
        $title = (string)$xmlelement->title;
        $this->assertEquals('Task Title', $title);
        // second level element
        $restrictions = $xmlelement->{'submission-restrictions'}->{'file-restriction'};
        $this->assertEquals('Solution.java', (string)$restrictions);
    }

    public function test_attributes() {
        $xmltask = new SimpleXMLElement($this->testxml1);
        $xmlelement = new ProformaXMLElement($xmltask, "urn:proforma:v2.0");

        // first level
        $proglang = $xmlelement->proglang['version'];
        $this->assertEquals('1.8', (string)$proglang);

        // second level with -
        $id = $xmlelement->{'model-solutions'}->{'model-solution'}['id'];
        $this->assertEquals('ms', (string)$id);
    }


    public function test_forloop_with_many_elements() {
        $xmltask = new SimpleXMLElement($this->testxml1);
        $xmlelement = new ProformaXMLElement($xmltask, "urn:proforma:v2.0");

        $allfiles = array();
        $allids = array();
        foreach ($xmlelement->files->file as $file) {
            $allfiles[] = (string)$file->{'attached-bin-file'};
            $allids[] = (string)$file['id'];
        }

        $this->assertEquals('JunitTest.java', $allfiles[0]);
        $this->assertEquals('checkstyle.xml', $allfiles[1]);
        $this->assertEquals('Solution.java', $allfiles[2]);
        $this->assertEquals(3, count($allfiles));

        $this->assertEquals('junit', $allids[0]);
        $this->assertEquals('checkstyle', $allids[1]);
        $this->assertEquals('solution', $allids[2]);
        $this->assertEquals(3, count($allids));

    }

    public function test_forloop_with_one_element() {
        $xmltask = new SimpleXMLElement($this->testxml1);
        $xmlelement = new ProformaXMLElement($xmltask, "urn:proforma:v2.0");

        $allids = array();
        foreach ($xmlelement->{'model-solutions'}->{'model-solution'}->filerefs->{'fileref'} as $fileref) {
            $allids[] = (string)$fileref['refid'];
        }
        $this->assertEquals('solution', $allids[0]);
        $this->assertEquals(1, count($allids));
    }
}