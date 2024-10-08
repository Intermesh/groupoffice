import {Main, test} from "@intermesh/community/calendar";

const origRender = Main.prototype.render;

test.foo = "bar";

debugger;

Main.prototype.render = function(parentEl, insertBefore) {

	alert("DavClient override is working!");
	return origRender.call(this, parentEl, insertBefore)
}