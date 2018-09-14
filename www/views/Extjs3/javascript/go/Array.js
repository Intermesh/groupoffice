
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
		return this.map(function(i) {
			return i[name];
		});
//		var r = [];
//		for(var i = 0, l = this.length; i < l; i++) {
//			r.push(this[i][name]);
//		}
//		
//		return r;
	},
	
	/**
	 * Return new array with unique values
	 * 
	 * @returns {Array}
	 */
	unique : function() {				
		return this.filter(function(value, index, self) { 
			return self.indexOf(value) === index;
		});
	},
	
	/**
	 * Get array of all values that are not present in the given array
	 * 
	 * @param {Array} a
	 * @returns {Array}
	 */
	diff : function(a) {
    return this.filter(function(i) {
			return a.indexOf(i) === -1;
		});
	}
});