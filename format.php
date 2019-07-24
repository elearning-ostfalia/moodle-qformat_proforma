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
 * ProFormA format question importer.
 *
 * @package    qformat_proforma
 * @copyright  2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');

class qformat_proforma extends qformat_default {

    const VERSION_V1 = 'V1';
    const VERSION_V2 = 'V2';

    /** @var string path to the temporary directory */
    public $tempdir = '';

    /** @var string ProFormA version */
    private $version = null;

    private $basefilename = '';

    private $xpath = null;

    public function provide_import() {
        global $CFG;
        // disable import if the ProFormA question type does not exist
        // return file_exists($CFG->dirroot.'/question/type/proforma/version.php');
        return true;
    }

    public function provide_export() {
        return false;
    }

    public function mime_type() {
        return mimeinfo('type', '.zip');
    }

    public function importpostprocess() {
        if ($this->tempdir != '') {
            fulldelete($this->tempdir);
        }
        return true;
    }

    public function export_file_extension() {
        return '.zip';
    }

    public function readdata($filename) {
        $uniquecode = time();
        $this->tempdir = make_temp_directory('proforma_import/' . $uniquecode);

        try {
            if (!is_readable($filename)) {
                throw new moodle_exception(get_string('cannotreaduploadfile', 'error'));
            }
            $this->basefilename = pathinfo($filename, PATHINFO_BASENAME);

            // create copy for extraction and extract
            if (!copy($filename, $this->tempdir . '/proforma.zip')) {
                throw new moodle_exception(get_string('cannotcopybackup', 'question'));
            }
            // overhead! todo use original file
            if (!copy($filename, $this->tempdir . '/' . $this->basefilename)) {
                throw new moodle_exception(get_string('cannotcopybackup', 'question'));
            }

            // extract zip content to $this->tempdir
            $packer = get_file_packer('application/zip');
            if (!$packer->extract_to_pathname($this->tempdir . '/proforma.zip', $this->tempdir)) {
                throw new moodle_exception(get_string('cannotunzip', 'question'));
            }
            // store task.xml in array $filenames if found
            // (all other names are discarded)
            $filenames = array();
            $iterator = new DirectoryIterator($this->tempdir);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile() && strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_BASENAME)) == 'task.xml') {
                    // if ($fileinfo->isFile() && strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION)) == 'xml') {
                    $filenames[] = $fileinfo->getFilename();
                }
            }
            if (!$filenames) {
                throw new moodle_exception(get_string('noproformafile', 'qformat_proforma'));
            }

            // return full path of task.xml file
            return file($this->tempdir . '/' . $filenames[0]);

        } catch (Exception $e) {
            fulldelete($this->tempdir);
            global $OUTPUT;
            // $OUTPUT->notification(get_string('noproformafile', 'qformat_proforma'));
            echo $OUTPUT->notification($e->getMessage());
            return false;
        }
    }

    protected function check_proforma_version($lines) {
        $xmldoc = new DOMDocument;
        if (!$xmldoc->loadXML($lines)) {
            debugging('invalid XML in proforma file');
            return false;
        }
        $this->xpath = new DOMXPath($xmldoc);

        $this->xpath->registerNamespace('dns1', 'urn:proforma:task:v1.0.1');
        $this->xpath->registerNamespace('dns2', 'urn:proforma:v2.0');

        if ($this->xpath->query('//dns2:task')->length > 0) {
            $this->version = self::VERSION_V2;
            return '2.0';
        } else if ($this->xpath->query('//dns1:task')->length > 0) {
            $this->version = self::VERSION_V1;
            return '1.0.1';
        }

        debugging('no task or no valid proforma namespace found');
        $this->error('no task or no valid proforma namespace found');
        return false;
    }

    protected function readquestions($lines) {
        // create one text
        $lines = implode('', $lines);
        $version = $this->check_proforma_version($lines);
        if (!$version) {
            return false;
        }

        $task = new SimpleXMLElement($lines);
        unset($lines); // No need to keep this in memory.
        $questions = array();
        $qo = $this->import_question($task);
        $qo->proformaversion = $version;

        // Stick the result in the $questions array.
        if ($qo) {
            $questions[] = $qo;
        }
        return $questions;
    }

    protected function store_download_file($content, $filename, &$list, &$draftitemid, $embedded) {
        // todo: throw exception!

        global $USER;

        if ($filename == -1) {
            $this->error('cannot create temporary file because of missing filename');
            throw new moodle_exception('cannot create temporary file because of missing filename');
        }

        if (in_array($filename, $list)) {
            $this->error(get_string('filenamenotunique', 'qformat_proforma') . ': "' .
                    $filename . '"');
            throw new moodle_exception(get_string('filenamenotunique', 'qformat_proforma') . ': "' .
                    $filename . '"');
        }
        $list[] = $filename;
        // $this->store_file($filename, /*base64_decode(*/$content/*)*/, $itemid);

        if (!isset($draftitemid)) {
            $draftitemid = file_get_unused_draft_itemid();
        }

        $fs = get_file_storage();

        if ($embedded) {
            // Prepare file record object
            $fileinfo = array(
                    'contextid' => context_user::instance($USER->id)->id, // ID of context
                    'component' => 'user',     // usually = table name
                    'filearea' => 'draft',     // usually = table name
                    'itemid' => $draftitemid,               // usually = ID of row in table
                    'filepath' => '/',           // any path beginning and ending in /
                    'filename' => $filename); // any filename

            /*$storedfile = */
            $fs->create_file_from_string($fileinfo, $content);
        } else {
            $filerecord = array(
                    'contextid' => context_user::instance($USER->id)->id,
                    'component' => 'user',
                    'filearea' => 'draft',
                    'itemid' => $draftitemid,
                    'filepath' => '/',
                    'filename' => $filename,
            );
            $fs->create_file_from_pathname($filerecord, $this->tempdir . '/' . $filename);

        }
    }

    protected function create_grading_hints_v1($qo, SimpleXMLElement $task) {
        // task version 1.0.1 does not contain grading hints
        $xw = new SimpleXmlWriter();
        $xw->openMemory();

        $xw->setIndent(1);
        $xw->setIndentString(' ');

        $xw->startDocument('1.0', 'UTF-8');

        $xw->startElement('grading-hints');
        // $xw->createAttribute('xmlns', 'urn:proforma:v2.0');

        $xw->startElement('root');
        $xw->create_attribute('function', 'sum');

        foreach ($task->tests->test as $test) {
            $xw->startElement('test-ref');
            $xw->create_attribute('ref', (string) $test['id']); // $id);
            $xw->create_attribute('weight', '-1');
            $xw->create_childelement_with_text('title', (string) $test->title);
            $xw->create_childelement_with_text('test-type', (string) $test->{'test-type'});
            $xw->endElement(); // test-ref
        }

        $xw->endElement(); // root
        $xw->endElement(); // grading-hints

        $xw->endDocument();
        $qo->gradinghints = $xw->outputMemory();
        $qo->aggregationstrategy = qtype_proforma::ALL_OR_NOTHING;
    }

    protected function import_grading_hints_v2($qo, SimpleXMLElement $task) {

        foreach ($task->{'grading-hints'}->root->{'test-ref'} as $test) {
            $id = (string) ($test['ref']);
            $tasktest = null;
            foreach ($task->tests->test as $actualtest) {
                if ($id === (string) $actualtest['id']) {
                    $tasktest = $actualtest;
                    break;
                }
            }

            // todo: what is wrong with that?
            // $tasktest = $task->xpath("//tests/test[@id='" . $id . "']");
            // $tasktest = $tasktest[0];

            if (!isset($tasktest)) {
                $this->error('task is inconsistent: could not find test id ' . $id . ' in tests');
            } else {
                // merge test data into grading hints
                $test->addChild("title", (string) $tasktest->title);
                $test->addChild("test-type", (string) $tasktest->{'test-type'});
                if (isset($tasktest->description)) {
                    $test->addChild("description", (string) $tasktest->description);
                }
                if (isset($tasktest->{'internal-description'})) {
                    $test->addChild("internal-description", (string) $tasktest->{'internal-description'});
                }
            }
        }

        $qo->gradinghints = (string) ($task->{'grading-hints'}->asXML());
        $qo->aggregationstrategy = qtype_proforma::WEIGHTED_SUM;
    }

    protected function import_files_v1($qo, SimpleXMLElement $task) {
        $downloads = array();
        $qo->downloadid = null;
        $templates = array();
        $qo->templateid = null;
        $modelsolfiles = array();
        $qo->modelsolid = null;

        foreach ($task->files->file as $file) {
            $fileid = (string) $file['id'];
            $fileclass = (string) $file['class'];
            $embedded = (string) $file['type'] === 'embedded';
            $content = (string) $file;
            $filename = (string) $file['filename'];
            switch ($fileclass) {
                case 'internal':
                case 'internal-library':
                    // check for model solution
                    foreach ($task->{'model-solutions'}->{'model-solution'}->filerefs->fileref as $msref) {
                        $msfileid = (string) $msref['refid'];
                        if ($fileid === $msfileid) {
                            // file belongs to model solution
                            $this->store_download_file($content, $filename, $modelsolfiles, $qo->modelsolid, $embedded);
                            if (empty($qo->modelsolution)) {
                                // first referenced file is found
                                if ($embedded) {
                                    $qo->modelsolution = $content;
                                } else {
                                    $qo->modelsolution = file_get_contents($this->tempdir . '/' . $filename);
                                }

                                $qo->responsefilename = $filename;
                            }
                        }
                    }
                    break;
                case 'template':
                    // store first template for editor, other template files
                    // are stored as normal files
                    if (empty($qo->responsetemplate)) {
                        if ($embedded) {
                            $qo->responsetemplate = $content;
                        } else {
                            $qo->responsetemplate = file_get_contents($this->tempdir . '/' . $filename);
                        }
                        $this->store_download_file($content, $filename, $templates, $qo->templateid, $embedded);
                    } else {
                        // store only first template as 'template', others as download
                        $this->store_download_file($content, $filename, $downloads, $qo->downloadid, $embedded);
                    }
                    break;
                case 'instruction':
                case 'library':
                    $this->store_download_file($content, $filename, $downloads, $qo->downloadid, $embedded);
                    break;
            }
        }

        $qo->downloads = implode(',', $downloads);
        $qo->templates = implode(',', $templates);
        $qo->modelsolfiles = implode(',', $modelsolfiles);
    }

    protected function import_files_v2($qo, SimpleXMLElement $task) {
        $downloads = array();
        $qo->downloadid = null;
        $templates = array();
        $qo->templateid = null;
        $modelsolfiles = array();
        $qo->modelsolid = null;

        foreach ($task->files->file as $file) {
            $fileid = (string) $file['id'];
            $usagebylms = (string) $file['usage-by-lms'];
            // $visible = (string)$file['visible'];

            $content = 'TBD: EXTERN';
            $filename = '???';
            $isembedded = null;
            $embedded = $file->{'embedded-txt-file'};
            if ($embedded) {
                $isembedded = true;
                $content = (string) $embedded;
                $filename = (string) $embedded['filename'];
            } else {
                $embedded = $file->{'embedded-bin-file'};
                if ($embedded) {
                    $this->error('Sorry! The task file contains embedded binary files. This is not supported!');
                    throw new moodle_exception('Sorry! The task file contains embedded binary files. This is not supported!');
                }

                $attached = $file->{'attached-bin-file'};
                if (!$attached) {
                    $attached = $file->{'attached-txt-file'};
                }
                if ($attached) {
                    $isembedded = false;
                    $filename = (string) $attached;
                }
            }

            switch ($usagebylms) {
                case 'edit':
                    // handle code snippet for editor
                    if (count($templates) === 0) {
                        // first code snippet can be changed in question editor by teacher
                        $this->store_download_file($content, $filename, $templates, $qo->templateid, $isembedded);
                        $qo->responsetemplate = $content;
                    } else {
                        // others must be downloaded
                        $this->store_download_file($content, $filename, $downloads, $qo->downloadid, $isembedded);
                    }
                    break;
                case 'display':
                case 'download':
                    $this->store_download_file($content, $filename, $downloads, $qo->downloadid, $isembedded);
                    break;

            }

            // todo: read content from attached file
            foreach ($task->{'model-solutions'}->{'model-solution'}->filerefs->fileref as $msref) {
                $msfileid = (string) $msref['refid'];
                if ($fileid === $msfileid) {
                    // file belongs to model solution
                    $this->store_download_file($content, $filename, $modelsolfiles, $qo->modelsolid, $isembedded);
                    if (empty($qo->modelsolution)) {
                        // first referenced file is found
                        if ($isembedded) {
                            $qo->modelsolution = $content;
                        } else {
                            $qo->modelsolution = file_get_contents($this->tempdir . '/' . $filename);
                        }
                        $qo->responsefilename = $filename;
                    }
                }
            }
        }

        $qo->downloads = implode(',', $downloads);
        $qo->templates = implode(',', $templates);
        $qo->modelsolfiles = implode(',', $modelsolfiles);
    }

    protected function import_question(SimpleXMLElement $xmltask) {
        // This routine initialises the question object.
        $qo = $this->defaultquestion();

        // description
        $qo->questiontext = (string) $xmltask->description;
        $qo->questiontextformat = FORMAT_HTML;

        // set default values for attributes that are not stored in ProFormA task
        $qo->defaultmark = 1;
        $qo->penalty = get_config('qtype_proforma', 'defaultpenalty'); // 0.1;
        $qo->taskrepository = '';
        $qo->taskpath = '';

        // Header parts particular to ProFormA.
        $qo->qtype = 'proforma';

        $qo->uuid = (string) $xmltask['uuid'];

        // lowercase programming language
        $qo->programminglanguage = strtolower((string) $xmltask->proglang);

        // read model solution and filename from first model solution entity
        $qo->modelsolution = '';

        $qo->responsefilename = '???';
        $qo->responsetemplate = '';

        switch ($this->version) {
            case self::VERSION_V1:
                // Question name.
                $qo->name = (string) $xmltask->{'meta-data'}->title;
                $this->import_files_v1($qo, $xmltask);
                // instead of grading hints import tests (for title)
                // for base of creating simple grading hints by teacher
                $this->create_grading_hints_v1($qo, $xmltask);
                $this->import_submission_restrictions_v1($qo, $xmltask);
                break;
            case self::VERSION_V2:
                $qo->name = (string) $xmltask->title;
                $this->import_files_v2($qo, $xmltask);
                $this->import_grading_hints_v2($qo, $xmltask);
                $this->import_submission_restrictions_v2($qo, $xmltask);
                break;
            default:
                throw coding_exception('no version found');
        }

        $qo->taskfilename = $this->basefilename;
        $qo->taskstorage = qtype_proforma::INTERNAL_STORAGE;
        $itemid = -1;
        $qo->taskfiledraftid = $this->store_task_file($qo->taskfilename, $itemid);
        $qo->taskpath = $qo->taskpath . '/' . $qo->taskfilename;

        $qo->comment = (string) $xmltask->{'internal-description'};
        $qo->commentformat = FORMAT_HTML;

        return $qo;
    }

    protected function store_task_file($filename) {
        global $USER;
        $fs = get_file_storage();
        $itemid = file_get_unused_draft_itemid();

        $filerecord = array(
                'contextid' => context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $itemid,
                'filepath' => '/',
                'filename' => $filename,
        );
        $fs->create_file_from_pathname($filerecord, $this->tempdir . '/' . $filename);
        return $itemid;
    }

    protected function import_submission_restrictions_v1($qo, SimpleXMLElement $task) {
        // read upload sizes (unit is kB)
        $maxbytes = (string) $task->{'submission-restrictions'}->{'regexp-restriction'}['max-size'];
        $qo->maxbytes = 0; // set default
        if ($maxbytes > 0) { // is set
            $maxbytes = $maxbytes * 1024;
            // determine matching value from choices array
            $this->set_max_bytes_choices($qo, $maxbytes);
        }

        $filetype = (string) $task->{'submission-restrictions'}->{'regexp-restriction'}['mime-type-regexp'];
        $pos = strpos($filetype, 'text');
        if ($pos === false) {
            // no text format => use filepicker but do not set mime type
            $qo->filetypes = '';
            $qo->responseformat = 'filepicker';
            $qo->attachments = 1;
        } else {
            // text format => use editor and set mime type
            $qo->filetypes = 'text/plain';
            $qo->responseformat = 'editor';
            $qo->responsefieldlines = 15;
            $qo->attachments = 0;
        }
    }

    protected function set_max_bytes_choices($qo, $maxbytes) {
        global $CFG, $COURSE;

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes,
                get_config('qtype_proforma', 'maxbytes'));
        foreach (array_keys($choices) as $choice) {
            if ($choice > 0 && $choice > $maxbytes) { // $choice = 0 means unlimited
                if ($qo->maxbytes == 0) {
                    $qo->maxbytes = $choice;
                } else {
                    if ($choice < $qo->maxbytes) {
                        $qo->maxbytes = $choice;
                    }
                }
            }
        }
    }

    protected function import_submission_restrictions_v2($qo, SimpleXMLElement $xmltask) {
        $maxbytes = $xmltask->{'submission-restrictions'}['max-size'];
        $qo->maxbytes = 0; // set default
        if (isset($maxbytes)) { // is set
            // determine matching value from choices array
            $this->set_max_bytes_choices($qo, (string) $maxbytes);
        }

        $extensions = array();
        $count = 0;
        foreach ($xmltask->{'submission-restrictions'}->{'file-restriction'} as $restriction) {
            $filename = (string) $restriction;
            if ($restriction['pattern-format'] === 'posix-ere') {
                // we cannot evaluate this format right now
                $qo->filetypes = '';
                $qo->responseformat = 'filepicker';
                $qo->attachments = 5;
                return;
            }
            // find extension
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (strlen($extension) > 0) { // ignore files with no extension
                if (array_search('.' . $extension, $extensions) === false) {
                    $extensions[] = '.' . $extension;
                }
                $count++;
            }
        }
        $qo->filetypes = implode(';', $extensions);

        if ($count > 5) {
            $this->error('more than 5 file uploads are not supported, use zip instead');
        }

        if ($count <= 1) {
            $qo->responseformat = 'editor';
            $qo->responsefieldlines = 15;
            $qo->attachments = 0;
        } else {
            $qo->responseformat = 'filepicker';
            $qo->attachments = $count;
        }
    }

    /**
     * show message but do not increment error counter => do not abort import
     *
     * @param $message
     * @param string $text
     * @param string $questionname
     */
    protected function warning($message, $text = '', $questionname = '') {
        $importwarningquestion = get_string('importwarningquestion', 'qformat_proforma');

        echo "<div class=\"importerror\">\n";
        echo "<strong>{$importwarningquestion} {$questionname}</strong>";
        if (!empty($text)) {
            $text = s($text);
            echo "<blockquote>{$text}</blockquote>\n";
        }
        echo "<strong>{$message}</strong>\n";
        echo "</div>";
    }

}
