zp_picturefill
==============

A [Zenphoto](http://www.zenphoto.org) plugin to responsively provide different image resolutions for different screen sizes as well as standard and HiDPI ("Retina") counter parts
 
An adaption of Picturefill 2.0 by Scott Jehl - https://github.com/scottjehl/picturefill
(Version 1.0 or older uses picturefill 1.x)

It supports only the `<picture>` implementation and not `<img srcset="">` currently.
 
Responsive breakpoints
-----------------------
These three are setup by default: 
- Standard: For normal desktop usage (aka "large")
- Medium: For smaller "tablet" screens max-width 767px
- Small: For small "mobile" screens with max-width 479px
  
I decided to only use these general ones so the function stays usable regarding parameters. Also they match my own small responsive CSS grid framework [Zenponsive](https://github.com/acrylian/zenponsive). 

Additionally the Picturefill author recommends not to use too many breakpoints because the DOM otherwise gets easily too large and slow. You should be able to adapt custom function with more or other sizes if you need them.
  
HiDPI images ("Retina")
----------------------
The new HD screens provide a higher pixel density than normal screens ("Retina" as Apple calls them) and soon they will be most likely more widely used, especially on mobile devives. The HiDPI image creation is optionally.

Normal images made for e.g. 200x200px often look blurry on those screens since they are actually displayed upscaled. Images that should look sharp on those screens need to be provided with at least the double resolution of e.g. 400x400px.

There is no HTML standard for this yet so this needs to be done via JavaScript for now.  (Although Webkit recently has introduced the srcset atribute that probably becomes standard later on).
 
The plugin also follows observations made for example on http://filamentgroup.com/lab/rwd_img_compression/ 
that a high density image can be compressed much more without loosing actual display quality. 

That way their file size is not that much bigger than standard images. The hires images are always compressed at 35% always 
while the standard images use what ever you have set on the Zenphoto image quality options.
 
Installation 
------------- 
Place the file `zp_picturefill.php` and the folder `zp_picturefill` within your `/plugins` folder.
Modify your theme to use the functions provided.
  
Usage
----- 

Template functions for normal and high density default images only

- `get/printHDDefaultSizedImage()` 
- `get/printHDImageThumb()`
- `printHDAlbumThumbImage()`
  
Template functions for custom sized image with normal and high density image and additional versions for responsive breakpoints

- `get/printResponsiveCustomSizedImage()`

Use these to pass specific sizes to the above manually:

- `getHDCustomSizedImage()`
- `getHDCustomSizedImageMaxSpace()`
 
These are the base function to create/print the `<picture>` setup for the responsive image itself. You can use these with static non gallery images on your theme as well, given you have ready made images-
- `get/printResponsiveImage()`
  
Please see the in file comments on the functions itself for more detail usage information.
  
License: GPL v3 
