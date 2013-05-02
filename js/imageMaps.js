/**
Implemented by Chatzimichali Eleni-Anthippi

Developers can modify the contents of this script in order to extend the functionality of the Image Maps

*/

var counter= 1;
var select3d= 0;

$(document).ready(function()
{
	/* Add a CSS file */
	$("head").append('<link href="css/style.css" rel="stylesheet" type="text/css"/>');
    
	/* onClick events for the image map */
	$("map area").click(function(e) {
		var title = $(this).attr("name");
		alert(title);
	});
	
	/* onMouseOver and onMouseOut events for the image map */
	$("map area").mouseover(function(e) {  
		var tip = $(this).attr("name");      
		$(".tipBody").html(tip);  
          
		//Set the X and Y axis of the tooltip  
		$("#tooltip").css('top', e.pageY + 2 );  
		$("#tooltip").css('left', e.pageX + 2);  
          
		//Show the tooltip with faceIn effect  
		$("#tooltip").fadeIn('100');  
		$("#tooltip").fadeTo('10',0.8); 
                
		setTimeout(function(){ $("#tooltip").hide(); }, 3000);
		
	}).mouseout(function() {  
      	   $('#tooltip').hide();    
	});
	
});

/* Alternate between 2D and 3D graphic */

function alternate(temp)
{
	if (temp == 0)
	{
		select3d=0;
		$('#imageMap3d').hide();
		$('#imageMap2d').show();
	}
	else
	{
		select3d=1;
		$('#imageMap3d').show();
		$('#imageMap2d').hide();
	}
}