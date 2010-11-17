var isIE = /*@cc_on!@*/false;

function setup(){}

function getElmt(id)
{
	if (isIE) { return (document.all[id]); }
	else { return (document.getElementById(id)); }
}

function moveQuickLinks()
{
	var quickLinks = getElmt("quicklinks");
	var pos = 0;
	if (document.documentElement) { pos = 15 + document.documentElement.scrollTop; }
	else { pos = 15 + document.body.scrollTop; }
	if (pos < 141) { pos = 141; }
	quickLinks.style.top = pos + "px";
	setTimeout('moveQuickLinks()', 0);
}
setTimeout('moveQuickLinks()', 2000);

startList = function() {
	if (document.all&&document.getElementById) {
		navRoot = document.getElementById("nav");
		for (i=0; i<navRoot.childNodes.length; i++) {
			node = navRoot.childNodes[i];
			if (node.nodeName == "LI") {
				node.onmouseover = function() {
					this.className = "over";
  				};
				node.onmouseout = function() {
					this.className='';
				};
			}
		}
	}
};
window.onload=startList;


