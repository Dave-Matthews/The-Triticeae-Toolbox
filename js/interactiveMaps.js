/**
Implemented by Chatzimichali Eleni-Anthippi
This script handles the clickable areas in the image maps.
Therefore, it should NOT be altered.

*/

jQuery(document).ready(function()
{
	var timestamp = new Date().getTime();
        var imageName = "";
	if (jQuery("#imageMap2d > img").length > 0)
	{
		imageName = jQuery("#imageMap2d > img").attr("src");
		jQuery("#imageMap2d > img").attr("src", imageName+"?"+timestamp);
	}
	
	if (jQuery("#imageMap3d > img").length > 0)
	{
		imageName = jQuery("#imageMap3d > img").attr("src");
		jQuery("#imageMap3d > img").attr("src", imageName+"?"+timestamp);
	}
	
	/* Create circular clickable areas in the image map */
	jQuery("map area").each(function() {
		var coord = jQuery(this).attr("coords");
		coord = coord.split(",",2)+",4";
		jQuery(this).attr({shape:"circle", coords: coord});
	});
});



