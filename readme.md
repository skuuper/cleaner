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
* *Use alternative chunking from LF Aligner*: If you want to invoke chunking (paragraph to sentence splitting) process from LF Aligner instead of finely tuned Skuuper process. It's in test  mode and can be useful for alignment comparison.
* *Chunk Chinese sentences into words with LDC Segmenter*: Break a Chinese text into words by invoking LDC Mandarin Segmenter. It will put spaces between sequences of characters recognized as words. If no words are detected, all the characters will be separated. It slightly increases Chinese alignment accuracy.

2. Then click **"Create TMX"** button.
![Opening documents](http://cleaner.skuuper.com/assets/img/tutorial_open.png "Opening documents")
A table with results of automatic alignment appears on the new page. The left column contains chunks from the *Source language* text, the right column shows the Destination language text. Each line corresponds to a pair of chunks.
The resulting alignment can be downloaded as a TMX file without editing by clicking on the blue "Download% SourceLanguageFileName\_Date\_Time%.tmx" button. In case you would like to edit the alignment, press the green **"Align generated TMX"** button. A new web page will open.

![Aligning process](http://cleaner.skuuper.com/assets/img/tutorial_align.png "Aligning process")
This is how **Aligner editor** looks like. It's the same double-column interface allowing to make edits. Hovering a mouse over a chunk invokes a menu in the lower right corner.

The content is editable.
There are four icons there:
* ![Add chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_add.png "Add Chunk below button") - append an empty chunk below
* ![Duplicate chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_dupe.png "Duplicate button") - duplicate current chunk and append it to the current one
* ![Split chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_split.png "Split chunk button") - split current chunk at the cursor position
* ![Delete chunk button](http://cleaner.skuuper.com/assets/img/tutorial_btn_delete.png "Delete a chunk") - delete selected chunk

There is also *merging mode*. 
First, you have to select chunks by clicking the _text_ of the chunk (not the white area) with a mouse pointer with `Shift` pressed. Selection order is important: if you select Chunk2 and Chunk1, you will have a resulting text "Chunk2_text Chunk1_text".
Then, click the ![Merge button](http://cleaner.skuuper.com/assets/img/tutorial_btn_merge.png "Merge chunks"), a fifth icon appearing in the hover menu when at least one chunk is selected.
You can deselect by clicking on the text in the chunk in the same way (one chunk at a time) or by clicking "Clear selection" in the upper right corner of the page.
![Merging chunks](http://cleaner.skuuper.com/assets/img/tutorial_merge.png "Merging chunks")

**Attention!** The "Merge chunks" icon also appears in menu for chunks not included in the selection (for moth language). If it is pressed outside the selection area, merging is not performed correctly, resulting in crazy result or misplaced output text. Thus, click "Merge" button within selection only.
![Wrong merging process](http://cleaner.skuuper.com/assets/img/tutorial_nomerge.png "Do not do like that!")

Current progress can be saved by clicking "Save TMX" button on the side. To cancel the changes, it is enough to update the page (`F5` or `Cmd+R`) - the alignment will return to the version of the last save (if any, or to the version generated after uploading the source documents).
![Save the TMX](http://cleaner.skuuper.com/assets/img/tutorial_save_current.png "Saves the document at server")

After completing the editing, you can save the changes locally by downloading the updated TMX. Click the green *"Download% SourceLanguageFileName\_Date\_Time%.tmx"* button located at the bottom of the page.
![Download the TMX](http://cleaner.skuuper.com/assets/img/tutorial_save.png "Downloads the result")

Good luck with editing!