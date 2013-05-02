openHtmlFile = function(name) {
    name = gsub("\\.html$", "", name)
    con = file(paste(name, ".html", sep=""), open="wt")
    return(con)
}

openHtmlPage = function(name, title="") {
    name = gsub("\\.html$", "", name)
    con = file(paste(name, ".html", sep=""), open="wt")
    writeLines(paste("<html><head><title>", title,
                     "</title></head><body style=\"font-family: ",
                     "helvetica,arial,sans-serif;\">", sep=""), con)
    return(con)
}

closeHtmlPage = function(con) {
    writeLines("</body></html>", con)
    close(con)
}

