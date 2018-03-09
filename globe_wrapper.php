<?php
/*
dependency : php GD

I created the pasteInto function to simplify the GD param, because 
our case some param values are repeats of other params.
when copy into, our case always paste into center etc etc

*/


//init with arbitrary value for demo
//replaced with argv param for commandline use, or $_POST data in web use
$inc=1; //better be plural
$segments=6;


$currDir=dirname(__FILE__)."/";
$filename = $currDir .'Jupiter.jpg';

//get dimension of source image
list($width, $height) = getimagesize($filename);

global $src_image;
global $dst_image;

//to simplify calculation, we let the destinate image same size as souce image
//resize it AFTER all done
$src_image = imagecreatefromjpeg($filename);
$dst_image = imagecreatetruecolor($width, $height);

//to do: add aspect ratio check, src must 2:1
//resize src and notify user

//fill image all white
$backgroundColor = imagecolorallocate($dst_image, 255, 255, 255);
imagefill($dst_image, 0, 0, $backgroundColor);

$src_centerPoint=array( "x" => 0, "y" => 0);
 
// note: $i starts at $height/2, process goes in both up and down directions
// (since other than the y value, other values are the same )

//to do: may not divisible, with extra mod 
$segments_width = $width/$segments;


for( $i=($height/2) ; $i>$inc; $i=$i - $inc){
	$segments_center_offset = $segments_width/2;
	
	//when $i goes up the perimeter changes
	$curr_perimeter = get_new_perimeter( $i, $height );
	
	//this value is same for every segment in same loop, but
	//to do: may not divisible, with extra mod. compensate that in the last loop?
	$segment_des_width = $curr_perimeter / $segments;
	
	//work on segments, centerpoint x changes while y stays
	for( $x=1; $x<=$segments; $x++){	 	 
		//going up
		$src_centerPoint["x"]= $x * $segments_width - $segments_center_offset;			
		$src_centerPoint["y"]= $i- $inc/2;
		
		$src_w= $segments_width;
		$src_h=$inc;
 		$dst_w = $segment_des_width;
		
		pasteInto( $src_centerPoint, $src_w, $src_h, $dst_w );
		
		//going down, only y changes
		$src_centerPoint["y"]= $height - $i- $inc/2;
		pasteInto( $src_centerPoint, $src_w, $src_h, $dst_w );	
		
	}	
 	
}

// Output
imagejpeg( $dst_image, "/Users/michael/testoutput2.jpg");

function get_new_perimeter( $v_position, $full_height ){
	echo $v_position . "---" . $full_height . "\n";
	//$full_height covers 180 degree, $v_position is current pos, which is within $v_position
	
	//$full_height * 2 => radian = 2 pi()
	$seg_size = ( $full_height/2) - $v_position;
	$seg_ratio = $seg_size / ($full_height * 2);
	//$seg_ratio range from near 0 to 1/4
	 
	$radian = 2 * pi() * $seg_ratio;

	$parameter =$full_height*2;
	$radius = ($full_height*2)/(2*pi());

	$shrinked_radius = cos( $radian) * $radius;
	
	$shrinked_parameter = 2 * pi() * $shrinked_radius;
	
	
	return $shrinked_parameter;
	/*
		$full_height*2 = 2 * pi() * $radius
		$full_height = pi() * $radius
	*/
	
}


function pasteInto(  $src_centerPoint, $src_w, $src_h, $dst_w ){
	
	global $src_image;
	global $dst_image;
	
	//some param is fixed in this special use function.
	//note that $src_image, $dst_image are of same dimension
	$dst_h = $src_h;
	$des_centerPoint =$src_centerPoint; //$src_centerPoint is an array with 2 items
	
	
	//deduce other var from $src_centerPoint, $des_centerPoint
	$dst_x= $des_centerPoint["x"] - $dst_w/2;
	$dst_y= $des_centerPoint["y"]  + $dst_h/2;
	
	$src_x=$src_centerPoint["x"]  - $src_w/2;
	$src_y=$src_centerPoint["y"]  + $src_h/2;
	
	
	imagecopyresized ( $dst_image , $src_image , $dst_x , $dst_y , $src_x , $src_y , $dst_w , $dst_h , $src_w , $src_h );
}
?>
