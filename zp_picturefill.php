<?php
/**
 * A plugin to responsively provide different image resolutions for different screen sizes as well as standard 
 * and HiDPI ("Retina") counter parts
 * An adaption of Picturefill 2.x by Scott Jehl - https://github.com/scottjehl/picturefill
 * Read more about it on http://scottjehl.github.io/picturefill/
 *
 * Responsive breakpoints:
 * These three are setup by default:
 * Standard: For normal desktop usage (aka "large")
 * Medium: For smaller "tablet" screens max-width 767px
 * Small: For small "mobile" screens with max-width 479px
 * 
 * I decided to only use these general ones so the function stays usable regarding parameters. 
 * Also they match my own small responsive CSS framework Zenponsive. 
 * Also the Picturefill author recommends not to use too many breakpoints because the DOM 
 * otherwise gets easily too large and slow. 
 * You should be able to adapt custom function with more or other sizes if you need them.
 * 
 * HiDPI images ("Retina")
 * The new HD screens provide a higher pixel density than normal screens ("Retina" as Apple calls them).
 * and soon they will be most likely more widely used, especially on mobile devives.
 * Normal images made for e.g. 200x200px often look blurry on those screens since they are actually displayed upscaled.
 * Image that should look sharp on those screens need to be provided with at least the double resolution of e.g. 400x400px.
 * There is no HTML standard for this yet so this needs to be done via JavaScript for now.  
 * (Although Webkit recenlty has introduced the srcset atribute that probably becomes standard later on).
 *
 * Default image compression note
 * The plugin also follows observations made for example on http://filamentgroup.com/lab/rwd_img_compression/ 
 * that a high density image can be compressed much more without loosing actual display quality. 
 * That way their file size is not that much bigger than standard images. The hires images are always compressed at 35% by default
 * while the standard images use what ever you have set on the Zenphoto image quality options. You can set options to adjust
 *
 * The HiDPI image creation is optionally.
 * 
 * Usage:
 * Please see the in file comments on the functions itself for usage information below.
 *
 * @license GPL v3 
 * @author Malte Müller (acrylian)
 *
 * @package plugins
 * @subpackage media
 */
 
$plugin_is_filter = 9|THEME_PLUGIN;
$plugin_description = gettext('A plugin to provide higher resolution gallery images to hires screens.');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_version = '1.1';
$option_interface = 'zp_picturefill';
zp_register_filter('theme_head','picturefilljs');

class zp_picturefill {

	function __construct() {
		setOptionDefault('zp_picturefill_thumbquality', 35);
		setOptionDefault('zp_picturefill_imagequality', 35);
	}

	function getOptionsSupported() {
		array(gettext('HD thumb quality')				 => array('key'		 => 'zp_picturefill_thumbquality', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext('It is recommended to set the HiDPI image to a lower compression as because its resolution it will not be as noticable and will have file size advantages. Default is 35 percent.'),
					gettext('HD image quality')				 => array('key'		 => 'zp_picturefill_imagequality', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext('It is recommended to set the HiDPI image to a lower compression as because its resolution it will not be as noticable and will have file size advantages. Default is 35 percent.')
		return $options;											
	}
}


function picturefilljs() {
	?>
	<script>
	// Picture element HTML5 shiv
	document.createElement( "picture" );
	</script>
	<script type="text/javascript" src="<?php echo FULLWEBPATH .'/'.USER_PLUGIN_FOLDER; ?>/zp_picturefill/picturefill.min.js"></script>
	<?php
}

/***************************
* Picturefill functions 
****************************/

	/**
	 * Returns the Picturefill html setup for standard, medium and small images, both with optional standard and high density. 
	 * Since it takes img urls directly, it can be used for non gallery static images on the theme pages, too.
	 *
	 * @param string $standard_sd Url to the normal image for desktop screens in single density
	 * @param string $standard_hd Url to the normal image for desktop screens in high density
	 * @param string $medium_sd Url to the medium image for smaller screens (max-width: 767px) in single density
	 * @param string $medium_hd Url to the medium image for smaller screens (max-width: 767px) in high density
	 * @param string $small_sd Url to the small image for small screens (max-width: 479px) in single density
	 * @param string $small_hd Url to the small image for small screens (max-width: 479px) in high density
	 * @param string $class optional class attribute
	 * @param string $id optional id attribute
	 * @param string $alt alt text
	 */
	function getResponsiveImage($standard_sd=NULL, $standard_hd=NULL, $medium_sd=NULL,$medium_hd=NULL,$small_sd=NULL,$small_hd=NULL,$class=NULL, $id=NULL, $alt=NULL) {
		$imgclass = '';
		$imgid = '';
		if(!is_null($class)) $imgclass = ' class="'.$class.'"';
		if(!is_null($id)) $imgid = ' id="'.$id.'"';
		//main wrapper
		$html = '<picture'.$imgclass.$imgid.' data-picture data-alt="'.html_encode($alt).'">'."\n";
		
		//IE bug workaround
		$html = '<!--[if IE 9]><video style="display: none;"><![endif]-->';
		
		//standard desktop size
		if(!is_null($standard_sd) && !is_null($standard_hd)) {
			$standard_source = html_encode(pathurlencode($standard_sd)).', '.html_encode(pathurlencode($standard_hd)).' 2x';
		} else if(!is_null($standard_sd)) {
			$source_standard_sd = $standard_sd;
			$standard_source = html_encode(pathurlencode($standard_sd));
		} else if(!is_null($standard_hd)) { 
			$standard_source = html_encode(pathurlencode($standard_hd)).' 2x';
		} 
		$html .= '<source class="image_standard" srcset="'.$standard_source.'">';
	
		//medium "tablet" size
		if(!is_null($medium_sd) && !is_null($medium_hd)) {
			$html .= '<source class="image_medium" srcset="'.html_encode(pathurlencode($medium_sd)).', '.$medium_hd.' 2x" media="(max-width: 767px)">';
		} else if(!is_null($standard_sd)) {
			$html .= '<source class="image_medium" srcset="'.html_encode(pathurlencode($medium_sd)).'" media="(max-width: 767px)">';
		} else if(!is_null($standard_hd)) { 
			$html .= '<source class="image_medium" srcset="'.html_encode(pathurlencode($medium_hd)).' 2x" media="(max-width: 767px)">';
		} 
		
		//small "mobile" size
		if(!is_null($small_sd) && !is_null($small_hd)) {
			$html .= '<source class="image_small" srcset="'.html_encode(pathurlencode($small_sd)).', '.html_encode(pathurlencode($small_hd)).' 2x" media="(max-width: 479px)">';
		} else if(!is_null($standard_sd)) {
			$html .= '<source class="image_small" srcset="'.html_encode(pathurlencode($small_sd)).'" media="(max-width: 767px)">';
		} else if(!is_null($standard_hd)) { 
			$html .= '<source class="image_small" srcset="'.html_encode(pathurlencode($small_hd)).' 2x" media="(max-width: 767px)">';
		} 
		
		//fall backs for old IEs
		$html .= '<!--[if IE 9]></video><![endif]-->'."\n";
		$html .= '<img srcset="'.$standard_source.'" alt="'.$alt.'">';
		
		$html .= '</picture>'."\n".'
		';
		return $html;
	}
	
	/**
	 * Helper function to get the quality option setting for the HD images
	 * @param bool $thumb true for thumb quality, false for sized image quality
	 */
	function getHDQuality($thumb=true) {
		if($thumb) {
			$quality = getOption('zp_picturefill_thumbquality');
		} else {
			$quality = getOption('zp_picturefill_imagequality');
		}
		if(empty($quality) {
			$quality = 35;
		}
		return $quality
	}

	/**
	 * Prints the Picturefill html setup for standard, medium and small images, both with optional standard and high density. 
	 * Since it takes img urls directly, it can be used for non gallery static images on the theme pages, too.
	 *
	 * @param string $standard_sd Url to the normal image for desktop screens in single density
	 * @param string $standard_hd Url to the normal image for desktop screens in high density
	 * @param string $medium_sd Url to the medium image for smaller screens (max-width: 767px) in single density
	 * @param string $medium_hd Url to the medium image for smaller screens (max-width: 767px) in high density
	 * @param string $small_sd Url to the small image for small screens (max-width: 479px) in single density
	 * @param string $small_hd Url to the small image for small screens (max-width: 479px) in high density
	 * @param string $class optional class attribute
	 * @param string $id optional id attribute
	 * @param string $alt alt text
	 */
	function printResponsiveImage($standard_sd=NULL, $standard_hd=NULL, $medium_sd=NULL,$medium_hd=NULL,$small_sd=NULL,$small_hd=NULL,$class=NULL, $id=NULL, $alt=NULL) {
		echo getResponsiveImage($standard_sd, $standard_hd, $medium_sd,$medium_hd,$small_sd,$small_hd,$class, $id, $alt);
	}
		
	/**
	 * Gets custom sized image with repsonsive breakpoints for standard, medium and small images and optionally with HiDPI counterparts
	 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
	 *
	 * You need to pass the image sizes for the standard, medium and small images via the nested array $imgsettings. 
	 * Naturally at least the type "standard" must be set. The parameters itself in follow those e.g. getHDCustomSizedImage() uses: 
	 * a) $maxpace = false
	 * $imgsettings = array(
	 *	'standard' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
	 *	'medium' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
	 *	'small' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy)
	 * );
	 * b) $maxspace = true
	 * $imgsettings = array(
	 *	'standard' => array($width, $height),
	 *	'medium' => array($width, $height),
	 *	'small' => array($width, $height)
	 * );
	 * All parameters must be set for each type used at least to NULL.
	 *
	 * @param obj $imgobj Image object
	 * @param array $imgsettings Array containing the imagesettings for the standard, medium and small images. 
	 * @param bool $hd Set to true if you want the high density counterpart (Note they are generated actually
	 * @param bool $maxspace Set to true if the maxspace mode (fit un-cropped within the width & height parameters given) should be used
	 * @param bool $thumbStandin set to true to treat as thumbnail
	 * @param bool $effects image effects (e.g. set gray to force grayscale) 
	 */
	function getResponsiveCustomSizedImage($imgobj = null, $imgsettings, $hd=false, $maxspace=false, $thumbStandin = false, $effects = NULL) {
		global $_zp_current_image;
		if(!is_object($imgobj)) {
			$imgobj = $_zp_current_image;
		}
		$images = array();
		if(is_array($imgsettings) && (array_key_exists('standard',$imgsettings) || array_key_exists('medium',$imgsettings) || array_key_exists('small',$imgsettings))) {
			foreach($imgsettings as $key=>$val) {
				if($maxspace) {
					$img = getHDCustomSizedImageMaxSpace($imgobj, $hd, $val[0], $val[1], $thumbStandin, $effects);
				} else {
					$img = getHDCustomSizedImage($imgobj, $hd, $val[0], $val[1], $val[2], $val[3], $val[4], $val[5], $val[6], $thumbStandin, $effects);
				}
				$array = array($key => array($img[0],$img[1]));
				$images = array_merge($array,$images);
			}
		}
		// add NULL values for missing keys so the handling in picturefill is easier
		if(!array_key_exists('standard',$images)) {
			$array = array('standard' => array(NULL,NULL));
			$images = array_merge($array,$images);
		}
		if(!array_key_exists('medium',$images)) {
			$array = array('medium' => array(NULL,NULL));
			$images = array_merge($array,$images);
		}
		if(!array_key_exists('small',$images)) {
			$array = array('small' => array(NULL,NULL));
			$images = array_merge($array,$images);
		}
		return $images;
	}
	
 /**
	 * Print custom sized images with repsonsive breakpoints for standard, medium and small images and optionally with HiDPI counterparts
	 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
	 *
	 * Notes on cropping:
	 *
	 * The $crop* parameters determine the portion of the original image that will be incorporated
	 * into the final image. The w and h "sizes" are typically proportional. That is you can set them to
	 * values that reflect the ratio of width to height that you want for the final image. Typically
	 * you would set them to the fincal height and width.
	 *
	 * You need to pass the image sizes for the standard, medium and small images via the nested array $imgsettings. 
	 * Naturally at least the type "standard" must be set. The parameters itself in follow those e.g. getHDCustomSizedImage() uses: 
	 *
	 * a) $maxpace false
	 * $imgsettings = array(
	 *	'standard' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
	 *	'medium' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
	 *	'small' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy)
	 * );
	 * b) $maxspace true
	 * $imgsettings = array(
	 *	'standard' => array($width, $height),
	 *	'medium' => array($width, $height),
	 *	'small' => array($width, $height)
	 * );
	 * All parameters must be set for each type used at least to NULL.
	 *
	 * @param obj $imgobj Image object If NULL the current image is used
	 * @param bool $hd Set to true if you want the high density counterparts
	 * @param bool $maxspace Set to true if the maxspace mode (fit un-cropped within the width & height parameters given) should be used
	 * @param string $alt Alt text for the url
	 * @param array $imgsettings Array containing the imagesettings for the standard, medium and small images. 
	 * @param string $class Optional style class
	 * @param string $id Optional style id
	 * @param bool $thumbStandin set to true to treat as thumbnail
	 * @param bool $effects image effects (e.g. set gray to force grayscale)
	 * @return array
	 */
	function printResponsiveCustomSizedImage($imgobj, $hd = false, $maxspace = false, $alt, $imgsettings, $class = NULL, $id = NULL, $thumbStandin = false, $effects = NULL) {
		$images =	getResponsiveCustomSizedImage($imgobj, $imgsettings, $hd, $maxspace, $thumbStandin, $effects);
		$standard_sd = $images['standard'][0];
		$standard_hd = $images['standard'][1];
		$medium_sd = $images['medium'][0];
		$medium_hd = $images['medium'][1];
		$small_sd = $images['small'][0];
		$small_hd = $images['small'][1];
		printResponsiveImage($standard_sd, $standard_hd, $medium_sd, $medium_hd, $small_sd, $small_hd, $class, $id, $alt);
	}

	/**
	 * Get custom sized image optionally with HiDPI counterpart
	 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
	 *
	 * Notes on cropping:
	 *
	 * The $crop* parameters determine the portion of the original image that will be incorporated
	 * into the final image. The w and h "sizes" are typically proportional. That is you can set them to
	 * values that reflect the ratio of width to height that you want for the final image. Typically
	 * you would set them to the fincal height and width.
	 *
	 * @param obj $imgobj Image object If NULL the current image is used
	 * @param string $alt Alt text for the url
	 * @param int $size size
	 * @param int $width width
	 * @param int $height height
	 * @param int $cropw crop width
	 * @param int $croph crop height
	 * @param int $cropx crop x axis
	 * @param int $cropy crop y axis
	 * @param bool $thumbStandin set to true to treat as thumbnail
	 * @param bool $effects image effects (e.g. set gray to force grayscale)
	 * @return array
	 */
	function getHDCustomSizedImage($imgobj, $hd=false, $size, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = NULL, $thumbStandin = false, $effects = NULL) {
		global $_zp_current_image;
		if(!is_object($imgobj)) {
			$imgobj = $_zp_current_image;
		}
		$img_sd = $imgobj->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects);
		$img_hd = NULL;
		if($hd) {
			$s2 = $size*2;
			$w2 = $width*2;
			$h2 = $height*2;
			$cw2 = $cropw*2;
			$ch2 = $croph*2;
			$cx2 = $cropx*2;
			$cy2 = $cropy*2;
			setOption('image_quality', getHDQuality(false),false); // more compression for the hires to save file size
			$img_hd = $imgobj->getCustomImage($s2, $w2, $h2, $cw2, $ch2, $cx2, $cy2, $thumbStandin, $effects);
		}
		return array($img_sd, $img_hd);
	}
	

	/**
	 * Creates images which will fit un-cropped within the width & height parameters given optionally with HiDPI counterpart
	 * Note: Does not work with  non image files like video or audio. Exclude them on your theme!
	 *
	 * @param obj $imgobj Image object If NULL the current image is used
	 * @param bool $hd Set to true if the HiDPI counterpart should be generated
	 * @param int $width width
	 * @param int $height height
	 * @param bool $thumb set to true to treat as thumbnail
	 * @param bool $effects image effects (e.g. set gray to force grayscale)
	 */
	function getHDCustomSizedImageMaxSpace($imgobj, $hd=false, $width, $height,$thumb=false,$effects = null) {
		global $_zp_current_image;
		if(!is_object($imgobj)) {
			$imgobj = $_zp_current_image;
		}
		getMaxSpaceContainer($width, $height, $imgobj);
		$img_sd = $imgobj->getCustomImage(NULL, $width, $height, NULL, NULL, NULL, NULL, $thumb, $effects);
		$img_hd = NULL;
		if($hd) {
			$w2 = $width * 2;
			$h2 = $height * 2;
			getMaxSpaceContainer($w2, $h2, $imgobj);
			if($thumb) { // more compression for the hires to save file size
				setOption('thumb_quality', getHDQuality(true),false); 
			} else {
				setOption('image_quality', getHDQuality(false),false); 
			}
			$img_hd = $imgobj->getCustomImage(NULL, $w2, $h2, NULL, NULL, NULL, NULL, $thumb, NULL);
		}
		return array ($img_sd, $img_hd);
	}
	
	
/*************************************************
* Template functions for normal and high density 
* default images - only standard sizes
**************************************************/

	/**
	 * Standard sized image as set on the options
	 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
	 *
	 * @param obj $imgobj Image object If NULL the current image is used
	 * @return array
	 */
	function getHDDefaultSizedImage($imgobj) {
		global $_zp_current_image;
		if(!is_object($imgobj)) {
			$imgobj = $_zp_current_image;
		}
		//standard
		$size = getOption('image_size');
		$img_sd = $imgobj->getSizedImage($size);
	  //hires
	  $img_hd = NULL;
		if($hd) {
			$size2 = $size*2;
			setOption('image_quality', getHDQuality(false), false); // more compression for the hires to save file size
			$img_hd = $imgobj->getSizedImage($size2);
		}
		return array($img_sd, $img_hd);
	}
	
	/**
	 * Standard sized image as set on the options
	 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
	 *
	 * @param obj $imgobj Image object If NULL the current image is used
	 * @param string $alt Alt text
	 * @param string $class Optional style class
	 * @param string $id Optional style id
	 */
	function printHDDefaultSizedImage($imgobj,$alt, $class=NULL, $id=NULL ) {
		$img = getHDDefaultSizedImage($imgobj);
		printResponsiveImage($img[0], $img[1], NULL,NULL,NULL,NULL,$class, $id, $alt);
	}

	/** 
	 * Standard thumb as set on the options
	 *
	 * @param obj $imgobj Image object If NULL the current image is used
	 * @return array
	 */
	function getHDImageThumb($imgobj=null) {
		global $_zp_current_image;
		if(!is_object($imgobj)) {
			$imgobj = $_zp_current_image;
		}
		//standard
		$cropw = getOption('thumb_crop_width');
		$croph = getOption('thumb_crop_height');
		$thumbsize = getOption('thumb_size');
		$img_sd = $imgobj->getThumb();
		//hires
		$img_hd = NULL;
		$cropw2 = setOption('thumb_crop_width',$cropw*2,false);
		$croph2 = setOption('thumb_crop_height',$croph2*2,false);
		$thumbsize2 = setOption('thumb_size',$thumbsize2*2,false);
		setOption('thumb_quality', getHDQuality(true),false); // more compression for the hires to save file size
		$img_hd = $imgobj->getThumb();
		return array($img_sd, $img_hd);
	}

	/**
	 * Standard thumb as set on the options
	 *
	 * @param obj $imgobj Image object 
	 * @param string $alt Alt text
	 * @param string $class optional class tag
	 * @param string $id optional id tag
	 */
	function printHDImageThumb($alt, $class=NULL, $id=NULL,$imgobj=null) {
		$img = getHDImageThumb($imgobj);
		printResponsiveImage($img[0], $img[1], NULL,NULL,NULL,NULL,$class, $id, $alt);
	}
?>