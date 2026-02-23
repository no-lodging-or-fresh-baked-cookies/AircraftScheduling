<?PHP # $Id: image.php,v 1.3 2001/12/20 07:02:27 mbarclay Exp $

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

include "global_def.inc";
include "config.inc";

// $ret_type = thumb|normal
// $src = file_name
// $width
// $height

// maximum width for images
$image_width_limit = 576;

// get the input parameters if they have been passed in
$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
if(isset($rdata["src"])) $src = $rdata["src"];
if(isset($rdata["width"])) $width = $rdata["width"];
if(isset($rdata["height"])) $height = $rdata["height"];
if(isset($rdata["border"])) $border = $rdata["border"];

$src = $ImageRootPath . $src;

if(!is_file($src) or !is_readable($src)) exit;

$image_info = GetImageSize($src);
$image_width = $image_info[0];
$image_height = $image_info[1];

if(isset($height) and isset($width)) {
  ;  // Don't preserve the aspect ratio
} 
else if(isset($height)) 
{
	$width = $image_width * ($height / $image_height);
}
else if (isset($width)) 
{
	$height = $image_height * ($width / $image_width);
}
else 
{
    // limit the image height and width so that the
    // image size if reasonable
    if ($image_width > $image_width_limit)
    {
        // image is too big, limit the size
	    $width  = $image_width_limit;
	    $height = $image_height * ($width / $image_width);
    }
    else
    {
        // use the image as is
	    $height = $image_height;
	    $width  = $image_width;
    }
}

$dst_img=ImageCreateTrueColor($width, $height); 
  
switch($image_info[2]) {
	case 1: header("Content-Type: image/gif"); 
                $fn=fopen($src,"r"); 
                fpassthru($fn); 
		break;
	case 2: header("Content-type: image/jpeg");
		$src_img=ImageCreateFromJPEG($src);
		if($height != $image_height || $width != $image_width)
			ImageCopyResized($dst_img,$src_img,0,0,0,0,$width,$height,$image_width,$image_height);
		else	$dst_img = $src_img;
		ImageJpeg($dst_img);
		break;
	case 3: header("Content-type: image/png");
		$src_img=ImageCreateFromPNG($src);
		if($height != $image_height || $width != $image_width)
			ImageCopyResized($dst_img,$src_img,0,0,0,0,$width,$height,$image_width,$image_height);
		else	$dst_img = $src_img;
		ImagePNG($dst_img);
		break;
}

?>