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
* @version $Id: MAKE_TILES.inc.php 3671 2020-05-25 00:11:42Z OSt $

* e.g. min tile size = 256 px
* e.g. resize original 3000x4096 => 4096x4096 (make square first, center horizontal)
* max possible zoom is 4 (4096/256/4)
* tiles/0/0/0.png => orignal.png (but reszed to 256x256 px)
* tiles/{z}/{x}/{y}.png (folder/name scheme)
* z: zoom level (0 = max zoomed out)
* x: collumn (tiles from left to right)
* y: row (tiles from top to bottom)

tiles/1/0/0.png   tiles/1/1/0.png
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|sector |-|-| |-|-|sector |-|-]
[-|-| 0/0   |-|-| |-|-| 1/0   |-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]

tiles/1/0/1.png   tiles/1/1/1.png
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|sector |-|-| |-|-|sector |-|-]
[-|-| 0/1   |-|-| |-|-| 1/1   |-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
[-|-|-|-|-|-|-|-| |-|-|-|-|-|-|-|-]
*/

class MAKE_TILES{
  protected $original_image;
  protected $original_image_path;
  protected $original_image_mime;
  protected $original_image_width;
  protected $original_image_height;
  protected $tile_size;
  protected $max_zoom;
  protected $output_dir;
  private $debug;
  
  public function __construct(){
    $this->original_image=null;
    $this->original_image_path="";
    $this->original_image_mime="";
    $this->original_image_width=0;
    $this->original_image_height=0;
    $this->tile_size=256; //default tile size e.g. for maps
    $this->max_zoom=0;
    $this->output_dir="../../img/make_tiles";
    $this->debug=false; //no default debug output
  }
  
  /**
   * MAKE_TILES::set_debug()
   * set debug to true or false 
   * @param bool $debug
   * @return bool
   */
  public function set_debug($debug){
    $this->debug=$debug;
    return true;
  }
  
  /**
   * MAKE_TILES::set_tile_size()
   * set tile size in even pixel (e.g. 128, 256, 512 px ...)
   * @param mixed $tile_size
   * @return
   */
  public function set_tile_size($tile_size){
    $this->tile_size=$tile_size;
    return true;
  }
  
  /**
   * MAKE_TILES::set_output_dir()
   * set tiles output directory
   * @param string $output_dir
   * @return bool
   */
  public function set_output_dir($output_dir){
    $this->output_dir=$output_dir;
    return true;
  }
  
  /**
   * MAKE_TILES::from_image()
   * make tiles from image $original_image_path
   * @param string $original_image_path
   * @return bool
   */
  public function from_image($original_image_path){
    if(!file_exists($original_image_path))return false; //file does not exist!?
    $this->original_image_path=$original_image_path;
    $result_array=getimagesize($original_image_path); 
    if($result_array===false)return false; //could not get imagesize&infos => no image!?
    $this->original_image_width=$result_array[0];
    $this->original_image_height=$result_array[1];
    $this->original_image_mime=$result_array["mime"];
    $this->create_image(); //create image object and make it square and increase to next multiple-tile_size
    $this->split_image(); //split down to tile_size
    return true;
  }
  
  private function create_image(){
    //create image object...
    if($this->original_image_mime=="image/png"){
      $this->original_image=imagecreatefrompng($this->original_image_path); //create image object from PNG source
    }else if($this->original_image_mime=="image/jpeg"){
      $this->original_image=imagecreatefromjpeg($this->original_image_path); //create image object from JPEG source
    }else{
      return false; //mime not supported
    }
    //make it square to multiple-tile_size (tile_size*2)...
    $original_image_ratio=$this->original_image_width/$this->original_image_height; //1 square, >1 landscape, <1 portrait 
    if($original_image_ratio==1){ //it's already square, use original_image_width, check if resize needed
      $this->max_zoom=ceil(sqrt($this->original_image_width/$this->tile_size)); //round up to next tile_size multiple tile_size * (2 ^ max_zoom)
      if($this->original_image_width==$this->tile_size*pow(2,$this->max_zoom))return true; //no resize needed :)
      $new_width=$this->tile_size*pow(2,$this->max_zoom);
      $new_height=round($new_width*$original_image_ratio);
      $width_gap=$height_gap=0;
    }else if($original_image_ratio>1){ //it's landscape, long side is original_image_width
      $this->max_zoom=ceil(sqrt($this->original_image_width/$this->tile_size)); //round up to next tile_size multiple
      $new_width=$this->tile_size*pow(2,$this->max_zoom);
      $new_height=round($new_width/$original_image_ratio);
      $width_gap=0; //because the new width will be full resized
      $height_gap=$new_width-$new_height; //gap between multiple tile_size height      
    }else{ //it's portrait, long side is original_image_height
      $this->max_zoom=ceil(sqrt($this->original_image_height/$this->tile_size)); //round up to next tile_size multiple
      $new_height=$this->tile_size*pow(2,$this->max_zoom);
      $new_width=round($new_height*$original_image_ratio);
      $width_gap=$new_height-$new_width; //gap between multiple tile_size width
      $height_gap=0; //because the new height will be full resized
    }
    if($this->debug)echo "resize from ".$this->original_image_width."x".$this->original_image_height." to ".$this->tile_size*pow(2,$this->max_zoom)."x".$this->tile_size*pow(2,$this->max_zoom)." (new width/height: $new_width/$new_height | gaps: $width_gap/$height_gap)<br />";
    $resized_img=imagecreatetruecolor($this->tile_size*pow(2,$this->max_zoom),$this->tile_size*pow(2,$this->max_zoom)); //create new squared image by tile_size * (2 ^ max_zoom)
    $result=imagecopyresampled($resized_img,$this->original_image,$width_gap/2,$height_gap/2,0,0,$new_width,$new_height,$this->original_image_width,$this->original_image_height);
    if($this->debug)imagepng($resized_img,"$this->output_dir/resized.png");
    if($result){
      $this->original_image=$resized_img; //set new resized image object
      $this->original_image_width=$this->original_image_height=$this->tile_size*pow(2,$this->max_zoom); //set new resized width and height
      return true;
    }else{
      if($this->debug)echo "something went wront in create_image()"; //hmm, need more to explain why!?
      return false;
    }
  }
  
  private function split_image($img=null,$size=0,$zoom=0){
    if($img==null && $this->original_image===null)return false; //there is no image_object to split => why!?
    if($img==null)$img=$this->original_image; //use original image_object => normally before recursive request
    if($size==0)$size=$this->original_image_width; //use original image width => normally before recursive request
    if($this->debug)echo "[split|$img|$size|$zoom]<br />";
    $img_tile=imagecreatetruecolor($this->tile_size,$this->tile_size); //create tile image object
    $max_split_step=pow(4,$zoom); // 1, 4, 16, 64, ...
    $runtime=microtime(true); //start measure runtime
    for($split_step_x=0;$split_step_x<sqrt($max_split_step);$split_step_x++){ //sqrt($max_split_step) split steps in x-direction from left to right
      $src_x=$split_step_x*$size;
      for($split_step_y=0;$split_step_y<sqrt($max_split_step);$split_step_y++){ //sqrt($max_split_step) split steps in x-direction from left to right
        $src_y=$split_step_y*$size;
        if($this->debug){
          echo "($split_step_x*$split_step_y|$src_x*$src_y)";
          flush();
        }
        $result=imagecopyresampled($img_tile,$img,0,0,$src_x,$src_y,$this->tile_size,$this->tile_size,$size,$size); //resize tile to tile_size
        $this->save_tile($img_tile,$zoom,$split_step_x,$split_step_y); //save tile based folder/name scheme
      }
    }
    if($this->debug)echo "<br />runtime: ".((microtime(true)-$runtime)*1000)." ms<hr />";
    if($size>$this->tile_size)$this->split_image($img,$size/2,$zoom+1); //recursive split_image => next zoom level
    return true;
  }
  
  private function save_tile($img,$zoom,$x,$y){
    //create folder structure...
    if(!file_exists("$this->output_dir/$zoom"))mkdir("$this->output_dir/$zoom");
    if(!file_exists("$this->output_dir/$zoom/$x"))mkdir("$this->output_dir/$zoom/$x");
    //save as png ...
    imagepng($img,"$this->output_dir/$zoom/$x/$y.png");
    return true;
  }
}

?>