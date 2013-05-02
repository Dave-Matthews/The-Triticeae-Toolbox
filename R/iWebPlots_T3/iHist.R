iHist <- function(x, breaks, fileName, directory, ...) UseMethod("iHist")


iHist.default <- function(x, breaks, fileName, directory, fpng="histogram", dataPoints=NULL, freq=NULL, prob=!freq, ...)
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
		
		if(is.null(fpng))
		{
			fpng= "histogram"
		}
		
		if((missing(breaks)) || (is.null(breaks)))
		{
			breaks = "Sturges"
		}
		
		if((missing(freq)) || (is.null(freq)))
		{
			freq = TRUE
		}
		
		obj = hist(x, breaks= breaks, plot=FALSE)
		
		if ((obj$equidist == FALSE) || (prob == TRUE))
		{
			freq = FALSE
			cx = obj$breaks	
			cy = obj$density
		}else
		{
			freq = TRUE
			cx = obj$breaks	
			cy = obj$counts
		}
		
		
		fpng = paste(fpng,".png", sep="")
		png(paste(directory, fpng, sep="/"), width=680, height=470 , bg="transparent")
		hist(x, breaks=breaks, freq, ...)
		
		cx2d = grconvertX(cx , "user", "device")
		cy2d = grconvertY(cy , "user", "device")
		cxy= grconvertY(0, "user", "device")
		dev.off()
		
		
		diff = mat.or.vec(0,1)
		
		for (i in 2:length(cx2d))
		{
			temp = cx2d[i]-cx2d[i-1]
			diff[i-1] = temp
		}
		
		cx2d = cx2d[-length(cx2d)] 
		
		cff = rep.int(cxy, length(cy2d))
		coords2d = cbind(cx2d, cy2d, rowSums(cbind(cx2d, diff)), cff)
		
		
		id = seq(1:nrow(coords2d))
		
		if(is.null(dataPoints))
		{
			dataPoints = id
		}
		
		con = openHtmlPage(paste(directory, fileName, sep="/"), title = "Interactive Histogram")
		
		writeLines("<div id='main'>",con)
		writeLines("<div id='content'>",con)
		
		writeLines("<div class='imageMap' id='iHist'>",con)
		imageMap(coords2d, con, list(ID=id, NAME=dataPoints, HREF=paste("#", dataPoints, sep="")), fpng)
		writeLines("</div>",con)
		
		writeLines("<form id='selection'></form>",con)
		
		writeLines("<div id='tooltip'>",con)
		writeLines("<div class='tipHeader'>Data point</div>",con)
		writeLines("<div class='tipBody'></div>",con)
		writeLines("<div class='tipFooter'></div>",con)
		writeLines("</div>",con)
		
		writeLines("</div>",con)
		writeLines("</div>",con)
		
		writeLines("<script src='js/jquery.js'></script>", con)
		writeLines("<script src='js/imageMaps.js'></script>", con)
		closeHtmlPage(con)
		
		file.copy(system.file("js", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		file.copy(system.file("css", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		
		message(paste("The generated folder is located at ", fullDir, "",sep="\""))
	}
}