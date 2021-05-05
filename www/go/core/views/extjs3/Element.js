/**
 * Determine if element is displayed in the view port
 * @return {boolean}
 */
Ext.Element.prototype.isInViewport = function () {

	var rect = this.dom.getBoundingClientRect();

	return (
		rect.top >= 0 &&
		rect.left >= 0 &&
		rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
		rect.right <= (window.innerWidth || document.documentElement.clientWidth)
	);
}