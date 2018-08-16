
Ext.applyIf(Array.prototype, {
	/**
	 * Turn an array of objects into an array of object property values.
	 * 
	 * eg. 
	 * 
	 * var arr = [{foo: 1}, {foo: 2}]
	 * 
	 * arr.column("foo") == [1, 2]
	 * 
	 * @param {type} name
	 * @returns {Array}
	 */
	column : function(name) {
		var r = [];
		for(var i = 0, l = this.length; i < l; i++) {
			r.push(this[i][name]);
		}
		
		return r;
	}
});