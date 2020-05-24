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
* @version $Id$
*/
 
//load class...
require_once("../../inc/make_tiles/MAKE_TILES.inc.php");

//create instance...
$MAKE_TILES=new MAKE_TILES();

//make tiles from image...
$MAKE_TILES->from_image("../../img/make_tiles/tiles_4096.png");
?>