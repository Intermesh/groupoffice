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
		return this.map(rec=>rec[name]);
	}
});