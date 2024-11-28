import {Main, CalendarList} from "@intermesh/community/calendar";

// const origRender = Main.prototype.render;
//
// Main.prototype.render = function(parentEl, insertBefore) {
//
// 	alert("DavClient override is working!");
// 	return origRender.call(this, parentEl, insertBefore)
// }

const origCheckboxRender = CalendarList.prototype.checkboxRenderer;

CalendarList.prototype.checkboxRenderer = function(data, r,l,si) {
	console.log("checkboxes are rendered", this, data);
	return origCheckboxRender.call(this, data, r,l,si);
}