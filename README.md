zp_picturefill
==============

 A plugin to responsively provide different image resolutions for different screen sizes as well as standard 
 and HiDPI ("Retina") counter parts
 
 An adaption of Picturefill by Scott Jehl - https://github.com/scottjehl/picturefill
 
 ##Responsive breakpoints
 These three are setup by default:
 Standard: For normal desktop usage (aka "large")
 Medium: For smaller "tablet" screens max-width 767px
 Small: For small "mobile" screens with max-width 479px
  
 I decided to only use these general ones so the function stays usable regarding parameters. 
 Also they match my own small responsive CSS framework Zenponsive. 
 Also the Picturefill author recommends not to use too many breakpoints because the DOM 
 otherwise gets easily too large and slow. 
 You should be able to adapt custom function with more or other sizes if you need them.
  
 ##HiDPI images ("Retina")
 The new HD screens provide a higher pixel density than normal screens ("Retina" as Apple calls them).
 and soon they will be most likely more widely used, especially on mobile devives.
 Normal images made for e.g. 200x200px often look blurry on those screens since they are actually displayed upscaled.
 Image that should look sharp on those screens need to be provided with at least the double resolution of e.g. 400x400px.
 There is no HTML standard for this yet so this needs to be done via JavaScript for now.  
 (Although Webkit recenlty has introduced the srcset atribute that probably becomes standard later on).
 
 The plugin also follows observations made for example on http://filamentgroup.com/lab/rwd_img_compression/ 
 that a high density image can be compressed much more without loosing actual display quality. 
 That way their file size is not that much bigger than standard images. The hires images are always compressed at 35% always 
 while the standard images use what ever you have set on the Zenphoto image quality options.
 
 The HiDPI image creation is optionally.
 
 ##Installation 
 Place the file and folder within your `/plugins` folder.
 Modify your theme to use the functions provided
  
 ##Usage:
 Please see the in file comments on the functions itself for more detailed usage information.
