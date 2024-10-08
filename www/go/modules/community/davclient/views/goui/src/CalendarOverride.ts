import {Main, test} from "@intermesh/community/calendar";

const origRender = Main.prototype.render;

Main.prototype.render = function(parentEl, insertBefore) {

	alert("DavClient override is working!");
	return origRender.call(this, parentEl, insertBefore)
}