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
  protected $min_tile_size;
  protected $zoom;
  protected $output_dir;
  
  public function __construct(){
    $this->original_image=null;
    $this->original_image_path="";
    $this->original_image_mime="";
    $this->original_image_width=0;
    $this->original_image_height=0;
    $this->min_tile_size=256;
    $this->zoom=0;
    $this->output_dir="../../img/make_tiles";
  }
  
  public function from_image($original_image_path){
    if(!file_exists($original_image_path))return false; //file does not exist!?
    $this->original_image_path=$original_image_path;
    $result_array=getimagesize($original_image_path); 
    if($result_array===false)return false; //could not get imagesize&infos => no image!?
    $this->original_image_width=$result_array[0];
    $this->original_image_height=$result_array[1];
    $this->original_image_mime=$result_array["mime"];
    $this->create_image(); //create image object and make it square and increase to next multiple-min_tile_size
    $this->split_image(); //split down to min_tile_size
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
    //make it square to multiple-min_tile_size (min_tile_size*2)...
    $original_image_ratio=$this->original_image_width/$this->original_image_height; //1 square, >1 landscape, <1 portrait 
    if($original_image_ratio==1){ //it's already square, use original_image_width, check if resize needed
      $tile_size_ratio=ceil(sqrt($this->original_image_width/$this->min_tile_size)); //round up to next min_tile_size multiple (min_tile_size * 2 ^ tile_size_ratio)
      if($this->original_image_width==$this->min_tile_size*pow(2,$tile_size_ratio))return true; //no resize needed :)
    }else if($original_image_ratio>1){ //it's landscape, use original_image_width
      $tile_size_ratio=ceil(sqrt($this->original_image_width/$this->min_tile_size)); //round up to next min_tile_size multiple
      $width_gap=0;
      $height_gap=$this->min_tile_size*pow(2,$tile_size_ratio)-$this->original_image_height; //gap between multiple min_tile_size height
    }else{ //it's portrait, use original_image_height
      $tile_size_ratio=ceil(sqrt($this->original_image_height/$this->min_tile_size)); //round up to next min_tile_size multiple
      $width_gap=$this->min_tile_size*pow(2,$tile_size_ratio)-$this->original_image_width; //gap between multiple min_tile_size width
      $height_gap=0;
    }
    $resized_img=imagecreate($this->min_tile_size*pow(2,$tile_size_ratio),$this->min_tile_size*pow(2,$tile_size_ratio));
    $result=imagecopyresampled($resized_img,$this->original_image,$width_gap/2,$height_gap/2,0,0,$this->min_tile_size*pow(2,$tile_size_ratio),$this->min_tile_size*pow(2,$tile_size_ratio),$this->original_image_width,$this->original_image_height);
    if($result){
      $this->original_image=$resized_img; //set new resized image object
      $this->original_image_width=$this->original_image_height=$this->min_tile_size*pow(2,$tile_size_ratio); //set new resized width and height
      return true;
    }else{
      echo "something went wront in create_image()"; //hmm, need more to explain why!?
      return false;
    }
  }
  
  private function split_image($img=null,$size=0,$zoom=0){
    if($img==null && $this->original_image===null)return false; //there is no image_object to split => why!?
    if($img==null)$img=$this->original_image; //use original image_object => normally before recursive request
    if($size==0)$size=$this->original_image_width; //use original image width => normally before recursive request
    echo "[split|$img|$size|$zoom]<br />";
    $img_tile=imagecreate($this->min_tile_size,$this->min_tile_size); //create tile image object
    $max_split_step=pow(4,$zoom); // 1, 4, 16, 64, ...
    $runtime=microtime(true); //start measure runtime
    for($split_step_x=0;$split_step_x<sqrt($max_split_step);$split_step_x++){ //sqrt($max_split_step) split steps in x-direction from left to right
      $src_x=$split_step_x*$size;
      for($split_step_y=0;$split_step_y<sqrt($max_split_step);$split_step_y++){ //sqrt($max_split_step) split steps in x-direction from left to right
        $src_y=$split_step_y*$size;
        echo "($split_step_x*$split_step_y|$src_x*$src_y)";
        $result=imagecopyresampled($img_tile,$img,0,0,$src_x,$src_y,$this->min_tile_size,$this->min_tile_size,$size,$size); //resize tile to min_tile_size
        $this->save_tile($img_tile,$zoom,$split_step_x,$split_step_y); //save tile based folder/name scheme
      }
    }
    echo "<br />runtime: ".((microtime(true)-$runtime)*1000)." ms<hr />";
    if($size>$this->min_tile_size)$this->split_image($img,$size/2,$zoom+1); //recursive split_image => next zoom level
    return true;
  }
  
  private function save_tile($img,$zoom,$x,$y){
    //create folder structure...
    if(!file_exists("$this->output_dir/$zoom"))mkdir("$this->output_dir/$zoom");
    if(!file_exists("$this->output_dir/$zoom/$x"))mkdir("$this->output_dir/$zoom/$x");
    //save as png...
    imagepng($img,"$this->output_dir/$zoom/$x/$y.png");
    return true;
  }
}

?>