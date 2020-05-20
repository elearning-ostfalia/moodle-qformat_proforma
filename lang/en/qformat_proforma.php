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
 * Strings for component 'qformat_proforma', language 'en'
 *
 * @package    qformat_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

$string['pluginname'] = 'ProFormA format';
$string['pluginname_help'] = 'Programming Task for Formative Assessment (zip file).';
$string['pluginname_link'] = 'qformat/proforma';
$string['noproformafile'] =
        'Unable to find any proforma file in the zip archive. Please make sure your archive contains the task.xml file at root level.';
$string['filenamenotunique'] = 'Filename for attachment is not unique';
$string['importwarningquestion'] = 'Warning: ';
$string['missingfileintask'] = 'File \'{$a}\' is referenced in task but is not attached';
$string['notsupported'] = 'Sorry! The task file contains an unsupported ProFormA feature: ';
$string['inconsistenttest'] = 'The task file is inconsistent. could not find test id "{a}" in tests';
$string['invalidxml'] = 'The task file does not contain valid xml.';
$string['namespacenotfound'] = 'The task file does not contain a ProFormA task or the version of the ProFormA task is unsupported. Supported versions are 1.0.1, 2.0 and 2.0.1.';
$string['noproformafile'] = 'The file is not a ProFormA file (xml or zip).';
$string['complexgradinghints'] = 'Grading hints other than weighted sum are not supported.';
$string['morethanonemodelsolution'] = 'Task contains more than one model solution. None is imported!';
$string['privacy:metadata'] = 'The ProFormA question format plugin does not store any personal data.';

