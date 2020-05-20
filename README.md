# moodle-qformat_proforma

Import ProFormA tasks (version 1.0.1, 2.0 and 2.0.1) into Moodle. 
For details of the ProFormA format see https://github.com/ProFormA/proformaxml.

This plugin requires qtype_proforma to be installed.

ProFormA tasks can be uploaded as:

1. zip file containing a task.xml at the root folder
2. task xml file (can be any filename with extension xml)
3. zip file containing one or more task zip files (according to 1). 


## Known limitations

Not yet implemented ProFormA features:

- embedded bin files
- attached text files
- regular expressions in submission restrictions (and other details of submission restrictions)
- grading hints other than weighted sum 
- external resources
- internationalization
