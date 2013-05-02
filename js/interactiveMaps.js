/**
Implemented by Chatzimichali Eleni-Anthippi
This script handles the clickable areas in the image maps.
Therefore, it should NOT be altered.

*/

$(document).ready(function()
{
	var timestamp = new Date().getTime();
	if ($("#imageMap2d > img").length > 0)
	{
		var imageName = $("#imageMap2d > img").attr("src");
		$("#imageMap2d > img").attr("src", imageName+"?"+timestamp)
	}
	
	if ($("#imageMap3d > img").length > 0)
	{
		var imageName = $("#imageMap3d > img").attr("src");
		$("#imageMap3d > img").attr("src", imageName+"?"+timestamp)
	}
	
	/* Create circular clickable areas in the image map */
	$("map area").each(function() {
		var coord = $(this).attr("coords");
		coord = coord.split(",",2)+",4";
		$(this).attr({shape:"circle", coords: coord});
	});
});



