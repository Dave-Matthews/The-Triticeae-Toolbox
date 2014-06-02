source(common_code)

message("MADII design")

num.entries <- length(trt)
num.checks <- length(trt2)
outdesign<- MADIIdgn(entries=trt, num.entries, num.rows=num_row, num.cols=num_col, chk.names=trt2, designID="tester1", annoy=T)

