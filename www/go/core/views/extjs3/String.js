String.prototype.ucFirst = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
}


String.prototype.lcFirst = function() {
	return this.charAt(0).toLowerCase() + this.slice(1);
}

/**
 * Explode comma separated values and respect quoted strings
 * 
 * @link https://stackoverflow.com/questions/11456850/split-a-string-by-commas-but-ignore-commas-within-double-quotes-using-javascript
 * @returns {Array}
 * 
 */
String.prototype.splitCSV = function() {
	var arr = this.match(/(".*?"|[^",]+)(?=\s*,|\s*$)/g);
	return arr || [];
}

String.prototype.escapeRegExp = function() {
	return this.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}