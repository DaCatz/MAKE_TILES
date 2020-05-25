<?php

/**
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
* @author Oliver Strecke
* @copyright 2020
* @version $Id: make_tiles.php 3671 2020-05-25 00:11:42Z OSt $
*/
 
//load class...
require_once("../../inc/make_tiles/MAKE_TILES.inc.php");

//create instance...
$MAKE_TILES=new MAKE_TILES();
$MAKE_TILES->set_debug(true);
$MAKE_TILES->set_output_dir("../../img/make_tiles");
$MAKE_TILES->set_tile_size(256);

//make tiles from image...
#$MAKE_TILES->from_image("../../img/make_tiles/tiles_4096.png"); //test with already squared and size fitted image
#$MAKE_TILES->from_image("../../img/make_tiles/tiles_3000.png"); //test with already squared image but differenz size (for 256 tiles)
#$MAKE_TILES->from_image("../../img/make_tiles/tiles_3065x4096.png"); //test with portrait image
#$MAKE_TILES->from_image("../../img/make_tiles/tiles_4096x3065.png"); //test with landscape image
$MAKE_TILES->from_image("../../img/make_tiles/tiles_2040x3070.png"); //test with portrait image and none fitted size
?>