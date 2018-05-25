String.prototype.ucFirst = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
}


String.prototype.lcFirst = function() {
	return this.charAt(0).toLowerCase() + this.slice(1);
}
