function disabletextselect(i)
{
	return false;
}
function renabletextselect()
{
	return true;
}
//if IE4+
document.onselectstart = new Function ("return false");
//if NS6+
if (window.sidebar) {
	document.onmousedown = disabletextselect;
	document.onclick = renabletextselect;
}