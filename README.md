# moodle-qformat_proforma

Import ProFormA questions into Moodle. 
For details of the ProFormA format see https://github.com/ProFormA/proformaxml.

This plugin requires qtype_proforma to be installed.

ProFormA questions can be uploaded as:

1. zip file containing a task.xml at the root folder
2. task xml file (can be any filename with extension xml)
3. zip file containing one or more task zip files (according to 1). 

## known limitations

Not yet implemented ProFormA features:

- embedded bin files
- attached text files
- regular expressions in submission restrictions (and other details of submission restrictions)
- only very simple grading hints with weights 
- external resources
- internationalization
