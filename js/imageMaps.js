/**
Implemented by Chatzimichali Eleni-Anthippi

Developers can modify the contents of this script in order to extend the functionality of the Image Maps

*/

var counter= 1;
var select3d= 0;

jQuery(document).ready(function()
{
	/* Add a CSS file */
	/* jQuery("head").append('<link href="css/style.css" rel="stylesheet" type="text/css"/>'); */
    
	/* onClick events for the image map */
	jQuery("map area").click(function(e) {
		var title = jQuery(this).attr("name");
		alert(title);
	});
	
	/* onMouseOver and onMouseOut events for the image map */
	jQuery("map area").mouseover(function(e) {  
		var tip = jQuery(this).attr("name");      
		jQuery(".tipBody").html(tip);  
          
		//Set the X and Y axis of the tooltip  
		jQuery("#tooltip").css('top', e.pageY + 2 );  
		jQuery("#tooltip").css('left', e.pageX + 2);  
          
		//Show the tooltip with faceIn effect  
		jQuery("#tooltip").fadeIn('100');  
		jQuery("#tooltip").fadeTo('10',0.8); 
                
		setTimeout(function(){ jQuery("#tooltip").hide(); }, 3000);
		
	}).mouseout(function() {  
      	   jQuery('#tooltip').hide();    
	});
	
});

/* Alternate between 2D and 3D graphic */

function alternate(temp)
{
	if (temp == 0)
	{
		select3d=0;
		jQuery('#imageMap3d').hide();
		jQuery('#imageMap2d').show();
	}
	else
	{
		select3d=1;
		jQuery('#imageMap3d').show();
		jQuery('#imageMap2d').hide();
	}
}
