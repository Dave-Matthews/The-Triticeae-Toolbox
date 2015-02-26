/**
* Copyright by Fabian Dill, 2010
* Licensed under the MIT License (http://www.opensource.org/licenses/mit-license.php).
* 
* This script was written by Fabian Dill and published
* at http://informationandvisualization.de
* 
* If you use it, it would be nice if you link to our page
* and/or drop us a line where you use it (for our interest only).
* to fabian.dill(at)googlemail.com
*/
var lowerWhisker;
var q1;
var median;
var q3;
var upperWhisker;
var mildOutliers;
var extremeOutliers;
var min;
var max;

function sortNumber(a, b) {
	return a - b;
}

// map the values onto a scale of fixed height
function mapValue(v, height) {
	return Math.round(height - (((v - min) / (max - min)) * height));
}

function calculateValues(data) {
	data.sort(sortNumber);
	var n = data.length;
	// lower quartile
	var q1Pos = (n * 0.25);
	if (q1Pos % 1 != 0) {
	    q1Pos = Math.floor(q1Pos);
	    q1 = data[q1Pos];
	} else {
	    q1Pos = Math.floor(q1Pos);
	    q1 = (data[q1Pos] + data[q1Pos-1]) / 2;
	}
	// median
	var medianPos = (n * 0.5);
	if (medianPos % 1 != 0) {
	    medianPos = Math.floor(medianPos);
	    median = data[medianPos];
	} else {
	    medianPos = Math.floor(medianPos);
	    median = (data[medianPos] + data[medianPos-1]) / 2;
	}
	// upper quartile
	var q3Pos = (n * 0.75);
	if (q3Pos % 1 != 0) {
	    q3Pos = Math.floor(q3Pos);
	    q3 = data[q3Pos];
	} else {
	    q3Pos = Math.floor(q3Pos);
	    q3 = (data[q3Pos] + data[q3Pos-1]) / 2;
	}	
	min = data[0];
	max = data[n - 1];
	
	var iqr = q3 - q1;
	mildOutliers = new Array();
	extremeOutliers = new Array();
	lowerWhisker = min;
	upperWhisker = max;
	if (min < (q1 - 1.5 * iqr)) {
		for (var i = 0; i < q1Pos; i++) {
			// we have to detect outliers
			if (data[i] < (q1 - 3 * iqr)) {
				extremeOutliers.push(data[i]);
			} else if (data[i] < (q1 - 1.5 * iqr)) {
				mildOutliers.push(data[i]);
			} else if (data[i] >= (q1 - 1.5 * iqr)) {
				lowerWhisker = data [i];
				break;
			}
		}
	}
	if (max > (q3 + (1.5 * iqr))) {
		for (i = q3Pos; i < data.length; i++) {
			// we have to detect outliers
			if (data[i] > (q3 + 3 * iqr)) {
				extremeOutliers.push(data[i]);
			} else if (data[i] > (q3 + 1.5 * iqr)) {
				mildOutliers.push(data[i]);
			} else if (data[i] <= (q3 + 1.5 * iqr)) {
				upperWhisker = data[i];
			}
		}
	}
}

function roundVal(val){
	var dec = 2;
	var result = Math.round(val*Math.pow(10,dec))/Math.pow(10,dec);
	return result;
}

function createBoxPlot(dataArray, height, divID) {
	calculateValues(dataArray);
	var overallID = "overall" + divID ;

	var mlowerWhisker = mapValue(lowerWhisker, height);
	var mq1 = mapValue(q1, height);
	var mmedian = mapValue(median, height);
	var mq3 = mapValue(q3, height);
	var mupperWhisker = mapValue(upperWhisker, height);
	var mmildOutliers = new Array(mildOutliers.length);
	for (i = 0; i < mildOutliers.length; i++) {
		mmildOutliers[i] = mapValue(mildOutliers[i], height);
	}
	var mextremeOutliers = extremeOutliers;
	for (i = 0; i < extremeOutliers.length; i++) {
		mextremeOutliers[i] = mapValue(extremeOutliers[i], height);
	}
  
  var overallDiv = document.createElement("div");
	overallDiv.style.height = height + "px";
	overallDiv.style.width = "56px";
	overallDiv.style.border = "none";
	overallDiv.style.borderRight = "1px dotted";
    // DEM added feb2015
    overallDiv.className = "boxplot-element";
	overallDiv.id = overallID;
	document.getElementById(divID).appendChild(overallDiv);

	var upperDiv = document.createElement("div");
	upperDiv.id = "upperBox" + divID;
	upperDiv.className = "boxplot-element";
    // DEM 
    upperDiv.style.background = "#fff";
	upperDiv.style.top = mq3 + "px";
	upperDiv.style.height = (mmedian - mq3) + "px";
	document.getElementById(overallID).appendChild(upperDiv);

	var lowerDiv = document.createElement("div");
	lowerDiv.id = "lowerBox" + divID;
	lowerDiv.className = "boxplot-element";
    // DEM 
    lowerDiv.style.background = "#fff";
	lowerDiv.style.top = mmedian + "px";
	lowerDiv.style.height = mq1 - mmedian + "px";
	document.getElementById(overallID).appendChild(lowerDiv);

	var lowerWhiskerDiv = document.createElement("div");
	lowerWhiskerDiv.id = "lowerWhisker" + divID;
	lowerWhiskerDiv.className = "boxplot-element";
	lowerWhiskerDiv.style.top = mlowerWhisker + "px";
	document.getElementById(overallID).appendChild(lowerWhiskerDiv);
	
	var upperWhiskerDiv = document.createElement("div");
	upperWhiskerDiv.id = "upperWhisker" + divID;
	upperWhiskerDiv.className = "boxplot-element";
	upperWhiskerDiv.style.top = mupperWhisker + "px";
	document.getElementById(overallID).appendChild(upperWhiskerDiv);

	for(i = 0; i < mildOutliers.length; i++) {
		var newDiv = document.createElement("div");
		newDiv.className = "boxplot-element";
		newDiv.style.width="4px";
		newDiv.style.height="4px";
		newDiv.style.top = mmildOutliers[i] + "px";
		// newDiv.style.left= "50px";
		newDiv.style.left= "54px";
		document.getElementById(overallID).appendChild(newDiv);
	}
	for(i = 0; i < extremeOutliers.length; i++) {
		var newDiv = document.createElement("div");
		newDiv.className = "boxplot-element";
		newDiv.style.background = "#666";
		newDiv.style.width="4px";
		newDiv.style.height="4px";
		newDiv.style.top = mextremeOutliers[i] + "px";
		// newDiv.style.left= "50px";
		newDiv.style.left= "54px";
		document.getElementById(overallID).appendChild(newDiv);
	}
	// labels
	var lowerLabel = document.createElement("div");
	lowerLabel.className = "boxplot-label";
	lowerLabel.innerHTML = "" + roundVal(lowerWhisker);
	lowerLabel.style.top = mlowerWhisker + "px";
	lowerLabel.style.left = "0px";
	document.getElementById(overallID).appendChild(lowerLabel);
	
	var q1Label = document.createElement("div");
	q1Label.className = "boxplot-label";
	q1Label.innerHTML = "" + roundVal(q1);
	q1Label.style.top = (mq1 - 9) + "px";
	q1Label.style.left = "80px";
	document.getElementById(overallID).appendChild(q1Label);
	
	var medianLabel = document.createElement("div");
	medianLabel.className = "boxplot-label";
	medianLabel.innerHTML = "" + roundVal(median);
	medianLabel.style.top = (mmedian - 9) + "px";
	medianLabel.style.left = "0px";
	document.getElementById(overallID).appendChild(medianLabel);
	
	var q3Label = document.createElement("div");
	q3Label.className = "boxplot-label";
	q3Label.innerHTML = "" + roundVal(q3);
	q3Label.style.top = (mq3 - 9) + "px";
	q3Label.style.left = "80px";
	document.getElementById(overallID).appendChild(q3Label);
	
	var upperLabel = document.createElement("div");
	upperLabel.className = "boxplot-label";
	upperLabel.innerHTML = "" + roundVal(upperWhisker);
	upperLabel.style.top = (mupperWhisker - 9) + "px";
	upperLabel.style.left = "0px";
	document.getElementById(overallID).appendChild(upperLabel);
	
	for (i = 0; i < mmildOutliers.length; i++) {
		var label = document.createElement("div");
		label.className = "boxplot-label";
		label.innerHTML = "" + roundVal(mildOutliers[i]);
		label.style.top = (mmildOutliers[i] - 9) + "px";
		if (i%2 == 0) {
			label.style.left = "20px";
		} else {
			label.style.left = "70px";
		}
		document.getElementById(overallID).appendChild(label);
	}	
}