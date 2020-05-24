# MAKE_TILES
PHP class which makes squared image tiles from original images for use e.g. in map applications.

## Installation
* checkout repository (files with folder structure)
* you need a PHP installation with GD2 extension enabled (https://www.php.net/manual/de/image.installation.php)
* make sure, your webserver or user has write permissions to the default tiles output folder img/make_tiles
* run the test script php/make_tiles/make_tiles.php in your browser or at console

## Implement in your application
* link class file with "require_once('path/to/classes/MAKE_TILES.inc.php');
* create object instance with "$MAKE_TILES=new MAKE_TILES();"
* make tiles with "$MAKE_TILES->from_image("path/to/original_image");
* the default output folder for tiles is img/make_tiles

You can change some class attributes:
* t.b.d
