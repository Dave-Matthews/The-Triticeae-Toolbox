iScatterPlots <- function(x, y, z= NULL, fileName, directory, ...) UseMethod("iScatterPlots")


iScatterPlots.default <- function(x, y, z= NULL, fileName, directory, fpng2d="2Dplot", fpng3d="3Dplot", dataPoints=NULL, ...)
{	
	if((missing(directory)) || (is.null(directory)))
	{
		warning("You have to provide a valid path or directory.")
		
	} else if((missing(fileName)) || (is.null(fileName)))
	{
		warning("You have to provide a file name (without the extention \".html\").")
		
	} else
	{
		initialDir = getwd()
		dir.create(directory, recursive = TRUE, mode = "0777", showWarnings = FALSE)
		setwd(directory)
		fullDir = getwd()
		setwd(initialDir)
		
		x = as.matrix(x)
		
		if(is.null(fpng2d))
		{
			fpng2d= "2Dplot"
		}
		
		if(is.null(fpng3d))
		{
			fpng3d= "3Dplot"
		}
		
		
		fpng2d = paste(fpng2d,".png",sep="")
		png(paste(directory, fpng2d,sep="/"), width=680, height=470 , bg="transparent")
		plot(x, y, type="p", ...)
		cx2d = grconvertX(x , "user", "device")
		cy2d = grconvertY(y , "user", "device")
		dev.off()
		
		fpng3d = paste(fpng3d,".png",sep="")
		png(paste(directory, fpng3d,sep="/"), bg="transparent",  width=680, height=470)
		s3d = scatterplot3d(x, y, z, type="p", ...)
		A=s3d$xyz.convert(cbind(x,y,z))
		cx3d = grconvertX(A$x , "user", "device")
		cy3d = grconvertY(A$y , "user", "device")
		dev.off()
		
		
		coords2d = cbind(cx2d, cy2d, cx2d+3, cy2d+3)
		coords3d = cbind(cx3d, cy3d, cx3d+3, cy3d+3)
		
		id = seq(1:nrow(coords2d))
		
		con = openHtmlPage(paste(directory, fileName, sep="/"), title="Interactive Scatterplots")
		
		writeLines("<div id='main'>",con)
		writeLines("<div id='content'>",con)
		
		writeLines("<div id='banner'>",con)
		writeLines("<button type='button' onclick='alternate(0);' onmouseover='this.style.cursor=\"pointer\";'>Load 2D Plot</button>",con)
		writeLines("<button type='button' onclick='alternate(1);' onmouseover='this.style.cursor=\"pointer\";'>Load 3D Plot</button>",con)
		writeLines("</div>",con)
		
		
		writeLines("<div class='imageMap' id='imageMap2d'>",con)
		imageMap(coords2d, con, list(ID=id, NAME=dataPoints, HREF=paste("#", dataPoints, sep="")), fpng2d)
		writeLines("</div>",con)
		
		writeLines("<div class='imageMap' id='imageMap3d' style='display: none'>",con)
		imageMap(coords3d, con, list(ID=id, NAME=dataPoints, HREF=paste("#", dataPoints, sep="")), fpng3d)
		writeLines("</div>",con)
		
		writeLines("<form id='selection'></form>",con)
		
		writeLines("<div id='tooltip'>",con)
		writeLines("<div class='tipHeader'>Data Point</div>",con)
		writeLines("<div class='tipBody'></div>",con)
		writeLines("<div class='tipFooter'></div>",con)
		writeLines("</div>",con)
		
		writeLines("</div>",con)
		writeLines("</div>",con)
		
		
		writeLines("<script src='js/jquery.js'></script>", con)
		writeLines("<script src='js/interactiveMaps.js'></script>", con)
		writeLines("<script src='js/imageMaps.js'></script>", con)
		closeHtmlPage(con)
		
		file.copy(system.file("js", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		file.copy(system.file("css", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		
		message(paste("The generated folder is located at ", fullDir, "",sep="\""))
	}
}