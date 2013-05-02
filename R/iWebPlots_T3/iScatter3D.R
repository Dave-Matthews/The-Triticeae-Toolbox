iScatter3D <- function(x, y, z, fileName, directory, ...) UseMethod("iScatter3D")


iScatter3D.default <- function(x, y=NULL, z = NULL, fileName, directory, fpng3d="3Dplot", dataPoints=NULL, ...)
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
		
		
		if(is.null(fpng3d))
		{
			fpng3d= "3Dplot"
		}
		
		if(is.null(dataPoints))
		{
			x = as.matrix(x)
			
			if(is.null(rownames(x)))
			{
				dataPoints = seq(1:nrow(x))
			}else
			{
				dataPoints = rownames(x)
			}
		}
		
		
		fpng3d = paste(fpng3d,".png",sep="")
		png(paste(directory, fpng3d,sep="/"), bg="transparent",  width=680, height=470)
		s3d = scatterplot3d(x, y, z, type="p", ...)
		A=s3d$xyz.convert(cbind(x,y,z))
		cx3d = grconvertX(A$x , "user", "device")
		cy3d = grconvertY(A$y , "user", "device")
		dev.off()
		
		coords3d = cbind(cx3d, cy3d, cx3d+3, cy3d+3)
		
		id = seq(1:nrow(coords3d))
		
		con = openHtmlPage(paste(directory, fileName, sep="/"), title = "Interactive 3D plot")
		
		writeLines("<div id='main'>",con)
		writeLines("<div id='content'>",con)
		
		writeLines("<div class='imageMap' id='imageMap3d'>",con)
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