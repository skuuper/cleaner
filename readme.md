# Installation

* Clone the project to the directory you want it to run
* Run 'composer update' to install PHP dependecies and configure autoloader
* Run 'bower install' to install Javascript dependenceies
* Chmod /downloads directory to be writable by web server
* In case of running the application under subdirectory (generally not recommended for Slim Framework), change the $base_uri variable in index.php
* If needed change the path for Python API in app/Service/NltkService $this->api_file variable
