iBarPlot <- function(height, fileName, directory, ...) UseMethod("iBarPlot")


iBarPlot.default <- function(height, fileName, directory, fpng="barplot", dataPoints=NULL, width=1, space=0.2, horiz=FALSE, names.arg=NULL, beside=FALSE, axis=FALSE, ...)
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
		
		rowNames = rownames(as.matrix(height))
		rowNum = seq(1:nrow(as.matrix(height)))
		
		if(is.null(fpng))
		{
			fpng= "barplot"
		}
		
		if(is.null(dataPoints))
		{
			if((is.null(names.arg)) == FALSE)
			{
				dataPoints = names.arg
			}
#			else if(is.null(rowNames))
#			{
#				dataPoints = rowNum
#			}else
#			{
#				dataPoints = rowNames
#			}
		}
		
		if(is.null(space))
		{
			space=0.2
		}
		
		if(is.null(width))
		{
			width=1
		}
		
		
		fraction = space*(mean(width))
		
		if(length(width) == 1)
		{
			width = rep.int(width, length(height))
			
		}else
		{
			if(is.vector(height))
			{
				iterations= length(height) - length(width)
				
				newWidth = rep.int(width, iterations)
				width = c(width, newWidth)
				
			}else if(is.matrix(height))
			{
				newWidth=mat.or.vec(0,1)
				
				k= 1
				
				for(i in 1:ncol(height))
				{
					for (j in 1:nrow(height))
					{
						if (is.na(width[j]))
						{
							newWidth[k]=1
							
						}else
						{
							newWidth[k]=width[j]
						}
						
						k= k+1
					}
				}
				width=newWidth
			}
		}
		
		cx = fraction
		cy = as.vector(height)
		cyy = rep.int(0, length(height)) 
		
		
		if(is.vector(height))
		{
			for (i in 1:(length(height)-1))
			{
				temp = cx[i]+width[i]+fraction
				cx = c(cx,temp)
			}
		}else if(is.matrix(height))
		{
			if(beside == FALSE)
			{
#				cx = mat.or.vec(0,1)
#				cy = as.vector(height)
#				
#				k=1 
#				
#				#cyy =0
#				
#				for (j in 1:ncol(height))
#				{
#					cx[k] = fraction
#					
#					for (i in 1:nrow(height))
#					{
#						temp = width[i]+fraction
#						cx = c(cx,temp)
#						k = k+1
#					}
#					k = k+1
#				}
#				
#				for (j in 1:ncol(height))
#				{
#					for (i in 1:(nrow(height)-1))
#					{
#						temp = cx[k]+ width[k]
#						cx = c(cx,temp)
#						k = k+1
#					}
#					temp = cx[k]+fraction+width[k]
#					cx = c(cx,temp)
#					k = k+1
#				}
				
			}else
			{
				fraction = space*(mean(width))*(nrow(height))
				cx=fraction
				cy = as.vector(height)
				
				k=1
				
				for (j in 1:ncol(height))
				{
					for (i in 1:(nrow(height)-1))
					{
						temp = cx[k]+ width[k]
						cx = c(cx,temp)
						k = k+1
					}
					temp = cx[k]+fraction+width[k]
					cx = c(cx,temp)
					k = k+1
				}
				cx = cx[-length(cx)]
			}
		}
		
		cxx = rowSums(cbind(cx, width))
		
			  
		fpng = paste(fpng,".png", sep="")
		png(paste(directory, fpng, sep="/"), width=680, height=470 , bg="transparent")
#barplot(height, width=width, space=space, horiz=horiz, names.arg=names.arg, beside=TRUE, ...)
		barplot(height, beside=TRUE, width=width, ...)
		if(axis == TRUE)
		{
			axis(1)
		}
		
		if (horiz == FALSE)
		{
			cx2d = grconvertX(cx , "user", "device")
			cy2d = grconvertY(cy , "user", "device")
			cxx2d = grconvertX(cxx , "user", "device")
			cyy2d = grconvertY(cyy, "user", "device")
			coords2d = cbind(cx2d, cy2d, cxx2d, cyy2d)
			
		}else
		{
			cx2d = grconvertY(cx , "user", "device")
			cy2d = grconvertX(cy , "user", "device")
			cxx2d = grconvertY(cxx, "user", "device")
			cyy2d = grconvertX(cyy , "user", "device")
			coords2d = cbind(cy2d, cx2d, cyy2d, cxx2d)
		}

		dev.off()
		
		id = seq(1:nrow(coords2d))
		
		if(is.null(dataPoints))
		{
			dataPoints=id
		}
		
		con = openHtmlPage(paste(directory, fileName, sep="/"), title = "Interactive Barplot")
		
		writeLines("<div id='main'>",con)
		writeLines("<div id='content'>",con)
		
		writeLines("<div class='imageMap' id='iBarplot'>",con)
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