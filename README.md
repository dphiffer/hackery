# Hackery
## WordPress theme developed for [ellieirons.com](http://ellieirons.com/)

### Media settings

*Works best in conjunction with [Media Press](https://github.com/dphiffer/media-press) plugin.*

* Thumbnail size: 316 x 195 (yes, crop it)
* Medium size: 482 x 0 (no limit on height)
* Large size: 648 x 0
* XL size: 980 x 0 (don’t crop, show in image insertion)
* XXL size: 1280 x 0 (don’t crop, show in image insertion)
* Enable 2x image sizes

### Page setup

Go to the __Pages__ section of the WordPress admin. This is where everything happens.

1. Create a new Page in WordPress, use the __Stack__ page template
2. In Settings &rarr; Reading, set your new Page to be the front page
3. Create children of the page
4. Use Page "order" number to define the order each sub-page appears

### Image format

Use the __Image__ format for a “full-bleed” image treatment

* Use the featured image to set the background
	
### Gallery format

Use the __Gallery__ format for the “projects” treatment

* Each child page of the gallery is represented by its featured image
* Use the caption text in the featured image to control how the project is
* If the caption text includes a link, that will be used instead of the page (useful linking to off-site URLs, just create a placeholder page with a featured image)

### Menu Template

* Use the __Menu__ template for the “videos” treatment
* Each child of the menu is represented in the list and loaded in dynamically

### Custom fields

* Use the `page_class` custom field for adding a CSS class to a page
* Set `page_class` to `dark` to invert the color scheme (like with the “videos” page)
