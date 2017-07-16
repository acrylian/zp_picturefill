<?php
/**
 * A plugin to responsively provide different image resolutions for different screen sizes as well as standard 
 * and HiDPI ("Retina") counter parts
 *
 * An adaption of the polyfill Picturefill 2.x by Scott Jehl - https://github.com/scottjehl/picturefill
 * Read more about it on http://scottjehl.github.io/picturefill/
 *  
 * Note that this plugin does only solve some basic usage scenarios of both img srcset and picture. 
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
 * The HiDPI image creation is optionally although it does not really make sense to leave it out for most functions
 * 
 * Usage:
 * a) Simple standard and high density images using <img src="img_sd.jpg" srcset="img_sd.jpg 1x, img_hd.jpg 2x">
 * Template functions for simple normal and high density default images using for detailed usage information 
 * - get/printHDDefaultSizedImage() 
 * - get/printHDImageThumb()
 * - printHDAlbumThumbImage()
 * - get/printHDCustomSizedImage()
 *
 * There are standard functions to use these with on gallery images as well:
 * - get/printHDSrcsetImage()
 *
 * b) "Art direction" responsive images using <picture>
 * These template functions are for custom sized images with normal and high density image and additional versions for responsive breakpoints
 * - get/printResponsiveCustomSizedImage()
 * Use these to pass specific sizes to the above manually:
 * - getHDCustomSizedImage()
 * - getHDCustomSizedImageMaxSpace()
 *
 * These are the base function to create/print the <picture> setup for the responsive image itself.
 * You can use these with static non gallery images on your theme as well, given you have ready made images-
 * - get/printResponsiveImage()
 *
 * Responsive breakpoints:
 * These three are setup by default:
 * Standard: For desktop usage (aka "large") - Always set at least this!
 * Medium: For smaller "tablet" screens max-width 767px
 * Small: For small "mobile" screens with max-width 479px
 * 
 * I decided to only use these general ones so the function stays usable regarding parameters. 
 * Also they match my own small responsive CSS grid Zenponsive. 
 * Also the Picturefill author recommends not to use too many breakpoints because the DOM 
 * otherwise gets easily too large and slow. You should be able to adapt custom function with more or other sizes if you need them.
 * 
 * Please see the in file comments on each function itself for detailed usage information below.
 * 
 * @author Malte Müller (acrylian) <info@maltem.de>
 * @copyright 2014 Malte Müller
 * @license GPL v3 or later
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 9 | THEME_PLUGIN;
$plugin_description = gettext('A plugin to provide higher resolution gallery images to hires screens.');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_version = '1.3.2';
$option_interface = 'zp_picturefill';
zp_register_filter('theme_head', 'picturefilljs');

class zp_picturefill {

  function __construct() {
    setOptionDefault('zp_picturefill_thumbquality', 35);
    setOptionDefault('zp_picturefill_imagequality', 35);
  }

  function getOptionsSupported() {
    $options = array(gettext('HD thumb quality') => array('key' => 'zp_picturefill_thumbquality', 'type' => OPTION_TYPE_TEXTBOX,
            'order' => 2,
            'desc' => gettext('It is recommended to set the HiDPI image to a lower compression as because its resolution it will not be as noticable and will have file size advantages. Default is 35 percent.')),
        gettext('HD image quality') => array('key' => 'zp_picturefill_imagequality', 'type' => OPTION_TYPE_TEXTBOX,
            'order' => 2,
            'desc' => gettext('It is recommended to set the HiDPI image to a lower compression as because its resolution it will not be as noticable and will have file size advantages. Default is 35 percent.'))
    );
    return $options;
  }

}

function picturefilljs() {
  ?>
  <script>
  // Picture element HTML5 shiv
    document.createElement("picture");
  </script>
  <script type="text/javascript" src="<?php echo FULLWEBPATH . '/' . USER_PLUGIN_FOLDER; ?>/zp_picturefill/picturefill.min.js"></script>
  <?php
}

/* **************************
 * Picturefill functions 
 * ************************** */

/**
 * Returns the <picture> html setup for standard, medium and small images, both with optional standard and high density. 
 * It can be used for non gallery static images on the theme pages, too.
 * Note each standard/medium/small sd/hd parameter requires an multidimensional array as returnd by the plugin's getXXX functions:
 *
 *    array(
 *      'url' => '<full url of the image', 
 *      'width' => '<width in px>', 
 *      'height' => '<height in px>'
 *    )
 *
 * @param array $standard_sd Array for the normal image for desktop screens in single density - Always set at least this!
 * @param array $standard_hd Array for the normal image for desktop screens in high density
 * @param array $medium_sd Array for the medium image for smaller screens (max-width: 767px) in single density
 * @param array $medium_hd Array for the medium image for smaller screens (max-width: 767px) in high density
 * @param array $small_sd Array for the small image for small screens (max-width: 479px) in single density
 * @param array $small_hd Array for the small image for small screens (max-width: 479px) in high density
 * @param string $class optional class attribute
 * @param string $id optional id attribute
 * @param string $alt alt text
 * @param string $imagetype Type of the image for the html filter support
 *									'custom_image' for custom sized images (default)
 *									'custom_image_thumb' for custom sized images treated as thumbs
 *									'custom_album_thumb' for custom sized images
 *									'standard_image' for default sized images
 *									'standard_image_thumb' for default sized thumbs
 *									'standard_album_thumb' for default sized album thumbs
 *									'none' for static images not part of the gallery
 */
function getResponsiveImage($standard_sd = null, $standard_hd = null, $medium_sd = null, $medium_hd = null, $small_sd = null, $small_hd = null, $class = null, $id = null, $alt = null, $imagetype = 'custom_image') {
  $imgclass = '';
  $imgid = '';
  if (!is_null($class)) {
    $imgclass = ' class="' . $class . '"';
  }
  if (!is_null($id)) {
    $imgid = ' id="' . $id . '"';
  }
  //main wrapper
  $html = '<picture' . $imgclass . $imgid . ' data-picture data-alt="' . html_encode($alt) . '">' . "\n";

  //IE bug workaround
	$html .= '<!--[if IE 9]><video style="display: none;"><![endif]-->';
	
	//standard desktop size
	if (!is_null($standard_sd) && !is_null($standard_hd)) {
		$standard_source = html_encode(pathurlencode($standard_sd['url'])) . ', ' . html_encode(pathurlencode($standard_hd['url'])) . ' 2x';
	} else if (!is_null($standard_sd)) {
		$standard_source = html_encode(pathurlencode($standard_sd['url']));
	} else if (!is_null($standard_hd['url'])) {
		$standard_source = html_encode(pathurlencode($standard_hd['url'])) . ' 2x';
	}
	$html .= '<source srcset="' . $standard_source . '">';

	//medium "tablet" size
	if (!is_null($medium_sd) && !is_null($medium_hd)) {
		$html .= '<source srcset="' . html_encode(pathurlencode($medium_sd['url'])) . ', ' . html_encode(pathurlencode($medium_hd['url'])) . ' 2x" media="(max-width: 767px)">';
	} else if (!is_null($standard_sd)) {
		$html .= '<source srcset="' . html_encode(pathurlencode($medium_sd['url'])) . '" media="(max-width: 767px)">';
	} else if (!is_null($standard_hd)) {
		$html .= '<source srcset="' . html_encode(pathurlencode($medium_hd['url'])) . ' 2x" media="(max-width: 767px)">';
	}

	//small "mobile" size
	if (!is_null($small_sd) && !is_null($small_hd)) {
		$html .= '<source srcset="' . html_encode(pathurlencode($small_sd['url'])) . ', ' . html_encode(pathurlencode($small_hd['url'])) . ' 2x" media="(max-width: 479px)">';
	} else if (!is_null($standard_sd)) {
		$html .= '<source srcset="' . html_encode(pathurlencode($small_sd['url'])) . '" media="(max-width: 479px)">';
	} else if (!is_null($standard_hd)) {
		$html .= '<source srcset="' . html_encode(pathurlencode($small_hd['url'])) . ' 2x" media="(max-width: 479px)">';
	}

	//fall backs for old IEs
	$html .= '<!--[if IE 9]></video><![endif]-->' . "\n";
	$html .= '<img srcset="' . html_encode(pathurlencode($standard_sd['url'])) . '" width="'.$standard_sd['width'].'" height="'.$standard_sd['height'].'" alt="' . $alt . '">';

  $html .= '</picture>' . "\n";
  $html = applyImageFilter($html, $imagetype);
  return $html;
}

/**
 * Prints the <picture> html setup for standard, medium and small images, both with optional standard and high density. 
 * It can be used for non gallery static images on the theme pages, too.
 * Note each standard/medium/small sd/hd parameter requires an multidimensional array as returnd by the plugin's getXXX functions:
 *
 *    array(
 *      'url' => '<full url of the image', 
 *      'width' => '<width in px>', 
 *      'height' => '<height in px>'
 *    )
 *
 * @param array $standard_sd Array for the normal image for desktop screens in single density - Always set at least this!
 * @param array $standard_hd Array for the normal image for desktop screens in high density
 * @param array $medium_sd Array for the medium image for smaller screens (max-width: 767px) in single density
 * @param array $medium_hd Array for the medium image for smaller screens (max-width: 767px) in high density
 * @param array $small_sd Array for the small image for small screens (max-width: 479px) in single density
 * @param array $small_hd Array for the small image for small screens (max-width: 479px) in high density
 * @param string $class optional class attribute
 * @param string $id optional id attribute
 * @param string $imagetype Type of the image for the html filter support
 *									'custom_image' for custom sized images (default)
 *									'custom_image_thumb' for custom sized images treated as thumbs
 *									'custom_album_thumb' for custom sized images
 *									'standard_image' for default sized images
 *									'standard_image_thumb' for default sized thumbs
 *									'standard_album_thumb' for default sized album thumbs
 *									'none' for static images not part of the gallery
 */
function printResponsiveImage($standard_sd = null, $standard_hd = null, $medium_sd = null, $medium_hd = null, $small_sd = null, $small_hd = null, $class = null, $id = null, $alt = null, $imagetype = 'custom_image') {
  echo getResponsiveImage($standard_sd, $standard_hd, $medium_sd, $medium_hd, $small_sd, $small_hd, $class, $id, $alt, $imagetype);
}

/**
 * Gets custom sized images with repsonsive breakpoints for standard, medium and small images and optionally with HiDPI counterparts
 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
 *
 * You need to pass the image sizes for the standard, medium and small images via the nested array $imgsettings. 
 * Naturally at least the type "standard" must be set. The parameters itself in follow those e.g. getHDCustomSizedImage() uses: 
 * a) $maxpace = false
 * $imgsettings = array(
 * 	'standard' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
 * 	'medium' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
 * 	'small' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy)
 * );
 * b) $maxspace = true
 * $imgsettings = array(
 * 	'standard' => array($width, $height),
 * 	'medium' => array($width, $height),
 * 	'small' => array($width, $height)
 * );
 * All parameters must be set for each type used at least to NULL. Always set at least the standard size!
 *
 * @param obj $imgobj Image object
 * @param array $imgsettings Array containing the imagesettings for the standard, medium and small images. 
 * @param bool $hd Set to true if you want the high density counterpart (Note they are generated actually
 * @param bool $maxspace Set to true if the maxspace mode (fit un-cropped within the width & height parameters given) should be used
 * @param bool $thumbStandin set to true to treat as thumbnail
 * @param bool $effects image effects (e.g. set gray to force grayscale) 
 */
function getResponsiveCustomSizedImage($imgobj = null, $imgsettings, $hd = true, $maxspace = false, $thumbStandin = false, $effects = null) {
  global $_zp_current_image;
  if (!is_object($imgobj)) {
    $imgobj = $_zp_current_image;
  }
  $images = array();
  if (is_array($imgsettings) && (array_key_exists('standard', $imgsettings) || array_key_exists('medium', $imgsettings) || array_key_exists('small', $imgsettings))) {
    foreach ($imgsettings as $key => $val) {
      if ($maxspace) {
        $img = getHDCustomSizedImageMaxSpace($imgobj, $hd, $val[0], $val[1], $thumbStandin, $effects);
      } else {
        $img = getHDCustomSizedImage($imgobj, $hd, $val[0], $val[1], $val[2], $val[3], $val[4], $val[5], $val[6], $thumbStandin, $effects);
      }
      $array = array($key => array('img_sd' => $img['img_sd'], 'img_hd' => $img['img_hd']));
      $images = array_merge($array, $images);
    }
  }
  // add NULL values for missing keys so the handling in picturefill is easier
  if (!array_key_exists('standard', $images)) {
    $array = array('standard' => array(null, null));
    $images = array_merge($array, $images);
  }
  if (!array_key_exists('medium', $images)) {
    $array = array('medium' => array(null, null));
    $images = array_merge($array, $images);
  }
  if (!array_key_exists('small', $images)) {
    $array = array('small' => array(null, null));
    $images = array_merge($array, $images);
  }
  return $images;
}

/**
 * Print custom sized images with repsonsive breakpoints for standard, medium and small images and optionally with HiDPI counterparts
 * This function also supports password checks and displaying the lock image if being used for albums thumbs.
 *
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
 * 	'standard' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
 * 	'medium' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy),
 * 	'small' => array($size, $width, $height, $cropw, $croph, $cropx, $cropy)
 * );
 * b) $maxspace true
 * $imgsettings = array(
 * 	'standard' => array($width, $height),
 * 	'medium' => array($width, $height),
 * 	'small' => array($width, $height)
 * );
 * All parameters must be set for each type used at least to NULL. Always set at least the standard size!
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
 * @param bool $albumobj If an album object is passed this is treated as the album thumb and enables 
 *												password checks and prints the lock image instead of the real thumb if needed (neither responsive nor HiDPI)
 * @return array
 */
function printResponsiveCustomSizedImage($imgobj = null, $hd = true, $maxspace = false, $alt, $imgsettings, $class = null, $id = null, $thumbStandin = false, $effects = null,$albobj = null) {
	if(is_object($albobj) && getClass($albumobj) == 'Album') {
		$is_albumthumb = true;
		$imagetype = 'custom_album_thumb';
	} else {
		$is_albumthumb = false;
		if($thumbStandin) {
			$imagetype = 'custom_image_thumb';
		} else {
			$imagetype = 'custom_image';
		}
	}
	if($is_albumthumb) {
		if (!$albobj->getShow()) {
			$class .= " not_visible";
		}
		$pwd = $albobj->getPassword();
		if (!empty($pwd)) {
			$class .= " password_protected";
		}
	}
	if ($is_albumthumb && (!getOption('use_lock_image') || $albobj->isMyItem(LIST_RIGHTS) || empty($pwd))) {
  	$images = getResponsiveCustomSizedImage($imgobj, $imgsettings, $hd, $maxspace, $thumbStandin, $effects);
  	$standard_sd = $images['standard']['img_sd'];
  	$standard_hd = $images['standard']['img_hd'];
  	$medium_sd = $images['medium']['img_sd'];
  	$medium_hd = $images['medium']['img_hd'];
  	$small_sd = $images['small']['img_sd'];
  	$small_hd = $images['small']['img_hd'];
  	printResponsiveImage($standard_sd, $standard_hd, $medium_sd, $medium_hd, $small_sd, $small_hd, $class, $id, $alt,$imagetype);
	} else {
		echo getPasswordProtectImage(null);
	}
}

/**
 * Returns the <img src="img_sd.jpg" srcset="img_sd.jpg 1x, img_hd.jpg 2x"> html setup for standard and high density image.
 * It can be used for non gallery static images on the theme pages, too.
 * Note each sd and hd parameter requires an multidimensional array as returnd by the plugin's getHDXXX functions:
 *
 *    array(
 *      'url' => '<full url of the image', 
 *      'width' => '<width in px>', 
 *      'height' => '<height in px>'
 *    )
 * Naturally it does not makes sense to pass only one img parameter.
 *
 * @param array $standard_sd Array for the normal image for desktop screens in single density - Always set at least this!
 * @param array $standard_hd Array for the normal image for desktop screens in high density
 * @param string $class optional class attribute
 * @param string $id optional id attribute
 * @param string $imagetype Type of the image for the html filter support
 *									'custom_image' for custom sized images (default)
 *									'custom_image_thumb' for custom sized images treated as thumbs
 *									'custom_album_thumb' for custom sized images
 *									'standard_image' for default sized images
 *									'standard_image_thumb' for default sized thumbs
 *									'standard_album_thumb' for default sized album thumbs
 *									'none' for static images not part of the gallery
 */
function getHDSrcsetImage($standard_sd = null, $standard_hd = null, $class, $id, $alt, $imagetype) {
	$imgclass = '';
  $imgid = '';
  if (!is_null($class)) {
    $imgclass = ' class="' . $class . '"';
  }
  if (!is_null($id)) {
    $imgid = ' id="' . $id . '"';
  }
	$sd = html_encode(pathurlencode($standard_sd['url']));
	$hd = html_encode(pathurlencode($standard_hd['url']));
	$sd_srcset = $sd.' 1x';
	if(!empty($hd)) {
		$hd_srcset = ','.$hd.' 2x';
	}
	$html = '<img src="'.$sd.'" srcset="'.$sd_srcset.$hd_srcset.'" alt="'. html_encode($alt).'"'.$imgclass.$imgid.' width="'.$standard_sd['width'].'" height="'.$standard_sd['height'].'">';
	$html = applyImageFilter($html, $imagetype);
	return $html;
}

/**
 * Prints the <img src="img_sd.jpg" srcset="img_sd.jpg 1x, img_hd.jpg 2x"> html setup for standard and high density image.
 * It can be used for non gallery static images on the theme pages, too. 
 * Note each sd and hd parameter requires an multidimensional array as returnd by the plugin's getHDXXX functions:
 *
 *    array(
 *      'url' => '<full url of the image', 
 *      'width' => '<width in px>', 
 *      'height' => '<height in px>'
 *    )
 * Naturally it does not makes sense to pass only one img parameter.
 *
 * @param array $standard_sd Array for the normal image for desktop screens in single density - Always set at least this!
 * @param array $standard_hd Array for the normal image for desktop screens in high density
 * @param string $class optional class attribute
 * @param string $id optional id attribute
 * @param string $imagetype Type of the image for the html filter support
 *									'custom_image' for custom sized images (default)
 *									'custom_image_thumb' for custom sized images treated as thumbs
 *									'custom_album_thumb' for custom sized images
 *									'standard_image' for default sized images
 *									'standard_image_thumb' for default sized thumbs
 *									'standard_album_thumb' for default sized album thumbs
 *									'none' for static images not part of the gallery
 */
function printHDSrcsetImage($standard_sd = null, $standard_hd = null, $class, $id, $alt, $imagetype) {
	echo getHDSrcsetImage($standard_sd, $standard_hd, $class, $id, $alt, $imagetype);
}

/**
 * Print custom sized image optionally with HiDPI counterpart
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
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
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
function printHDCustomSizedImage($imgobj = null, $hd = true, $size, $width = null, $height = null, $cropw = null, $croph = null, $cropx = null, $cropy = null, $thumbStandin = false, $effects = null) {
	$img = getHDCustomSizedImage($imgobj, $hd, $size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects);
  printHDSrcsetImage($img['img_sd'], $img['img_hd'] = null, $class, $id, $alt, 'custom_image');
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
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
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
function getHDCustomSizedImage($imgobj = null, $hd = true, $size, $width = null, $height = null, $cropw = null, $croph = null, $cropx = null, $cropy = null, $thumbStandin = false, $effects = null) {
  global $_zp_current_image;
  if (is_null($imgobj)) {
    $imgobj = $_zp_current_image;
  }
  $img_sd_size = getSizeCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $imgobj);
  $imgs['img_sd'] = array(
              'url' => $imgobj->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects),
              'width' => $img_sd_size[0],
              'height' => $img_sd_size[1]
  );
  $imgs['img_hd'] = null;
  $imagequality = getOption('image_quality');
  if ($hd) {
    $s2 = $size * 2;
    $w2 = $width * 2;
    $h2 = $height * 2;
    $cw2 = $cropw * 2;
    $ch2 = $croph * 2;
    $cx2 = $cropx * 2;
    $cy2 = $cropy * 2;
    setOption('image_quality', getHDQuality(false), false); // more compression for the hires to save file size
    $img_hd_size = getSizeCustomImage($s2, $w2, $h2, $cw2, $ch2, $cx2, $cy2, $imgobj);
    $imgs['img_hd'] = array(
        'url' => $imgobj->getCustomImage($s2, $w2, $h2, $cw2, $ch2, $cx2, $cy2, $thumbStandin, $effects),
        'width' => $img_hd_size[0],
        'height' => $img_hd_size[1]
    );
    setOption('image_quality', $imagequality, false); // reset for standard images
  }
  return array($img_sd, $img_hd);
}

/**
 * Prints images which will fit un-cropped within the width & height parameters given optionally with HiDPI counterpart
 * Note: Does not work with  non image files like video or audio. Exclude them on your theme!
 *
 * @param obj $imgobj Image object If NULL the current image is used
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
 * @param int $width width
 * @param int $height height
 * @param bool $thumb set to true to treat as thumbnail
 * @param bool $effects image effects (e.g. set gray to force grayscale)
 */
function printHDCustomSizedImageMaxSpace($imgobj = null, $hd = true, $width, $height, $thumb = false, $effects = null) {
	$img = getHDCustomSizedImageMaxSpace($imgobj, $hd, $width, $height, $thumb, $effects);
	$imagetype = 'standard_image';
	if($thumb) {
		$imagetype = 'standard_image_thumb';
	} 
	printHDSrcsetImage($img['img_sd'], $img['img_hd'], $class, $id, $alt, $imagetype);
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
function getHDCustomSizedImageMaxSpace($imgobj = null, $hd = true, $width, $height, $thumb = false, $effects = null) {
  global $_zp_current_image;
  if (is_null($imgobj)) {
    $imgobj = $_zp_current_image;
  }
  getMaxSpaceContainer($width, $height, $imgobj);
  $imgs['img_sd'] = array(
              'url' => $imgobj->getCustomImage(null, $width, $height, null, null, null, null, $thumb, $effects),
              'width' => $width,
              'height' => $height
  );
  $imgs['img_hd'] = null;
  $thumbquality = getOption('thumb_quality');
  $imagequality = getOption('image_quality');
  if ($hd) {
    $w2 = $width * 2;
    $h2 = $height * 2;
    getMaxSpaceContainer($w2, $h2, $imgobj);
    if ($thumb) { // more compression for the hires to save file size
      setOption('thumb_quality', getHDQuality(true), false);
    } else {
      setOption('image_quality', getHDQuality(false), false);
    }
    $imgs['img_hd'] = array(
                'url' => $imgobj->getCustomImage(null, $w2, $h2, null, null, null, null, $thumb, $effects),
                'width' => $w2,
                'height' => $h2
    );
    // reset for standard images
    setOption('thumb_quality',$thumbquality,false);
    setOption('image_quality',$imagequality,false);
  }
  return $imgs;
}

/* * ***********************************************
 * Template functions for normal and high density 
 * default images - only standard sizes
 * ************************************************ */

/**
 * Standard sized image with normal and HiDPI resolution as set on the options
 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
 *
 * @param obj $imgobj Image object If NULL the current image is used
 * @param bool $hd Set to true if the HiDPI counterpart should be generated 
 * @return array
 */
function getHDDefaultSizedImage($imgobj = null, $hd = true) {
  global $_zp_current_image;
  if (is_null($imgobj)) {
    $imgobj = $_zp_current_image;
  }
  //standard
  $size = getOption('image_size');
  $img_sd_size = getSizeDefaultImage($size, $imgobj);
  $imgs['img_sd'] =
      array(
          'url' => $imgobj->getSizedImage($size),
          'width' => $img_sd_size[0],
          'height' => $img_sd_size[1]
      );
  $imagequality = getOption('image_quality');
  //hires
  $imgs['img_hd'] = null;
  if ($hd) {
    $size2 = $size * 2;
    setOption('image_quality', getHDQuality(false), false); // more compression for the hires to save file size
    $img_hd_size = getSizeDefaultImage($size2, $imgobj);
    $imgs['img_hd'] = array(
        'url' => $imgobj->getSizedImage($size2),
        'width' => $img_hd_size[0],
        'height' => $img_hd_size[1]
    );
    setOption('image_quality', $imagequality, false); // reset for standard images
  }
  return $imgs;
}

/**
 * Standard sized image with normal and HiDPI resolution as set on the options
 * Note: Does not work with  non image files like video or audio. Exclude them on your the theme!
 *
 * @param string $alt Alt text
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param obj $imgobj Image object If NULL the current image is used
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
 */
function printHDDefaultSizedImage($alt = null, $class = null, $id = null, $imgobj = null, $hd = true) {
	global $_zp_current_image;
  if (is_null($imgobj)) {
    $imgobj = $_zp_current_image;
  }
  $img = getHDDefaultSizedImage($imgobj, $hd);
  printHDSrcsetImage($img['img_sd'], $img['img_hd'], $class, $id, $alt, 'standard_image');
  //printResponsiveImage($img['img_sd'], $img['img_hd'], null, null, null, null, $class, $id, $alt, 'standard_image');
}

/**
 * Standard thumb with normal and HiDPI resolution as set on the options
 *
 * @param obj $imgobj Image object If NULL the current image is used
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
 * @return array
 */
function getHDImageThumb($imgobj = null, $hd = true) {
  global $_zp_current_image;
  if (is_null($imgobj)) {
    $imgobj = $_zp_current_image;
  }
 
  //standard
  $cropw = getOption('thumb_crop_width');
  $croph = getOption('thumb_crop_height');
  $thumbsize = getOption('thumb_size');
  $thumbquality = getOption('thumb_quality');
  $img_sd_size = getSizeDefaultThumb($imgobj);
  $imgs['img_sd'] = array(
              'url' => $imgobj->getThumb(),
              'width' => $img_sd_size[0],
              'height' => $img_sd_size[1]
  );
  //hires
  $imgs['img_hd'] = null;
  if ($hd) {
    setOption('thumb_crop_width', $cropw * 2, false);
    setOption('thumb_crop_height', $croph * 2, false);
    setOption('thumb_size', $thumbsize * 2, false);
    setOption('thumb_quality', getHDQuality(true), false); // more compression for the hires to save file size
    $img_hd_size = getSizeDefaultThumb($imgobj);
    $imgs['img_hd'] = array(
        'url' => $imgobj->getThumb(),
        'width' => $img_hd_size[0],
        'height' => $img_hd_size[1]
    );
    // reset for standard images
    $cropw = setOption('thumb_crop_width', $cropw, false);
    $croph = setOption('thumb_crop_height', $croph, false);
    $thumbsize = setOption('thumb_size', $thumbsize, false);
    $thumbquality = setOption('thumb_quality', $thumbquality, false);
  }
  return $imgs;
}

/**
 * Standard thumb as set on the options
 *
 * @param string $alt Alt text
 * @param string $class optional class tag
 * @param string $id optional id tag
 * @param string $imgobj Image object 
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
 */
function printHDImageThumb($alt = null, $class = null, $id = null, $imgobj = null, $hd = true) {
  $img = getHDImageThumb($imgobj, $hd);
  printHDSrcsetImage($img['img_sd'], $img['img_hd'], $class, $id, $alt, 'standard_image_thumb');
  //printResponsiveImage($img['img_sd'], $img['img_hd'], null, null, null, null, $class, $id, $alt, 'standard_image_thumb');
}

/**
 * Standard album thumb with normal and HiDPI resolution as set on the options
 * If the album is protected for the viewer and the lock image option is set it prints this lock image
 * plainly without any sizes or HiDPI support
 *
 * @param string $alt Alt text
 * @param string $class optional class tag
 * @param string $id optional id tag
 * @param string $albobj Album object optionally
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
 */
function printHDAlbumThumbImage($alt = null, $class = null, $id = null, $albobj = null, $hd = true) {
	global $_zp_current_album;
	if(is_null($albobj)) {
		$imgobj = $_zp_current_album->getAlbumThumbImage();
		$albobj = $_zp_current_album;
	} else {
		$imgobj = $albobj->getAlbumThumbImage();
	}
	if (!$_zp_current_album->getShow()) {
		$class .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (!empty($pwd)) {
		$class .= " password_protected";
	}
	$class = trim($class);
  if (!getOption('use_lock_image') || $albobj->isMyItem(LIST_RIGHTS) || empty($pwd)) {
  	$img = getHDImageThumb($imgobj, $hd);
  	printHDSrcsetImage($img['img_sd'], $img['img_hd'], $class, $id, $alt, 'standard_album_thumb');
		//printResponsiveImage($img['img_sd'], $img['img_hd'], null, null, null, null, $class, $id, $alt, 'standard_album_thumb');
	} else {
		echo getPasswordProtectImage(null);
	}
}


/**
 * Prints a link to a custom sized thumbnail with normal and HiDPI resolution of the current album
 *
 * See getCustomImageURL() for details.
 *
 * @param string $alt Alt atribute text
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw cropwidth
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param string $class css class
 * @param string $id css id
 * @param string $albobj Album object optionally
 * @param bool $hd Set to true if the HiDPI counterpart should be generated
 *
 * @return string
 */
function printHDCustomAlbumThumbImage($alt = null, $size, $width = null, $height = null, $cropw = null, $croph = null, $cropx = null, $cropy = null, $class = null, $id = null, $albobj = null, $hd = true) {
	global $_zp_current_album;
	if(is_null($albobj)) {
		$imgobj = $_zp_current_album->getAlbumThumbImage();
		$albobj = $_zp_current_album;
	} else {
		$imgobj = $albobj->getAlbumThumbImage();
	}
	if (!$_zp_current_album->getShow()) {
		$class .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (!empty($pwd)) {
		$class .= " password_protected";
	}
	$class = trim($class);
  if (!getOption('use_lock_image') || $albobj->isMyItem(LIST_RIGHTS) || empty($pwd)) {
  	$img = getHDCustomSizedImage($imgobj, $hd, $size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects);
  	printHDSrcsetImage($img['img_sd'], $img['img_hd'], $class, $id, $alt, 'custom_album_thumb');
		//printResponsiveImage($img['img_sd'], $img['img_hd'], null, null, null, null, $class, $id, $alt, 'custom_album_thumb');
	} else {
		echo getPasswordProtectImage(null);
	}
}

/*************************
* HELER FUNCTIONS
*************************/

/**
 * Helper function to get the quality option setting for the HD images - Used internally only
 * @param bool $thumb true for thumb quality, false for sized image quality
 */
function getHDQuality($thumb = true) {
  if ($thumb) {
    $quality = getOption('zp_picturefill_thumbquality');
  } else {
    $quality = getOption('zp_picturefill_imagequality');
  }
  if (empty($quality)) {
    $quality = 35;
  }
  return $quality;
}

/**
 * Helper function to apply the image filter - Used internally only
 * @param string $html as passed within printResponsiveImage() and printSrcsetImage()
 * @param string $imagetpye image type as passed within printResponsiveImage() and printSrcsetImage()
 */
function applyImageFilter($html, $imagetype) {
	switch($imagetype) {
  	default:
  	case 'custom_image':
  		$html = zp_apply_filter('custom_image_html', $html, false);
  		break;
  	case 'custom_image_thumb':
  		$html = zp_apply_filter('custom_image_html', $html, true);
  		break;
  	case 'custom_album_thumb':
  		$html = zp_apply_filter('custom_album_thumb_html', $html);
  		break;
  	case 'standard_image': 
  		$html = zp_apply_filter('standard_image_html', $html);
  		break;
  	case 'standard_image_thumb':
  		$html = zp_apply_filter('standard_image_thumb_html', $html);
  		break;
  	case 'standard_album_thumb':
  		$html = zp_apply_filter('standard_album_thumb_html', $html);
  		break;
  }
  return $html;
}
?>