# Installation

* Clone the project to the directory you want it to run
* Run 'composer update' to install PHP dependecies and configure autoloader
* Run 'bower install' to install Javascript dependenceies
* Chmod /downloads directory to be writable by web server
* In case of running the application under subdirectory (generally not recommended for Slim Framework), change the $base_uri variable in index.php
* If needed change the path for Python API in app/Service/NltkService $this->api_file variable

The Cleaner consists of the following parts:
* TMX Creator
* Aligner Editor
* Cleaner
 

# TMX Creator
This application is designed to create a TMX (Translation Memory eXchange format file, a subset of XML) from two documents which are translations of each other. TMX can be used to store aligned documents to create a corpus for translation, postediting or other purposes.

# Aligner Editor
This application allows to edit TMX by adding, removing and moving chunk boundaries to obtain perfect sentence-level alignment in TMX.

# Cleaner
This application allows user to prepare documents from raw TXT or plain text grabbed from HTML Web page to comply with Skuuper standards.


# Usage
The [tool](http://cleaner.skuuper.com) opens at TMX Creator page. You will ned two documents presumably being translations of each other to be aligned.

1. To begin work on alignment, select a pair of languages in the "Source language" and "Destination language" drop-down menus. After that click the "Select file" button on the right of each language to upload source files. In the window that appears, select the file in the appropriate file on your computer. After uploading, the file name should appear to the right of the "Select file".
If you miss language selection, the system will show a small popup message and won't allow you to continue.
There are two additional options:
* **Use alternative chunking from LF Aligner**: If you want to invoke chunking (paragraph to sentence splitting) process from LF Aligner instead of finely tuned Skuuper process. It's in test  mode and can be useful for alignment comparison.
* **Chunk Chinese sentences into words with LDC Segmenter**: Break a Chinese text into words by invoking LDC Mandarin Segmenter. It will put spaces between sequences of characters recognized as words. If no words are detected, all the characters will be separated. It slightly increases Chinese alignment accuracy.

Then click **"Create TMX"** button.
![Opening documents](http://cleaner.skuuper.com/assets/img/tutorial_open.png "Opening documents")

![Aligning process](http://cleaner.skuuper.com/assets/img/tutorial_align.png "Aligning process")
![Hovering menu](http://cleaner.skuuper.com/assets/img/tutorial_menu.png "Hover menu")

![Add chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_add.png "Add Chunk below button")
![Duplicate chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_dupe.png "Duplicate button")
![Split chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_split.png "Split chunk button")
![Delete chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_delete.png "Delete a chunk")
![Merge button](http://cleaner.skuuper.com/assets/img/tutorial_btn_merge.png "Merge chunks")

![Merging chunks](http://cleaner.skuuper.com/assets/img/tutorial_merge.png "Merging chunks")
![Wrong merging process](http://cleaner.skuuper.com/assets/img/tutorial_nomerge.png "Do not do like that!")

![Save the TMX](http://cleaner.skuuper.com/assets/img/tutorial_save_current.png "Saves the document at server")
![Download the TMX](http://cleaner.skuuper.com/assets/img/tutorial_save.png "Downloads the result")
