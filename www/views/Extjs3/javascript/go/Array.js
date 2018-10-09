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


// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
  Object.defineProperty(Array.prototype, 'find', {
    value: function(predicate) {
     // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this);

      // 2. Let len be ? ToLength(? Get(O, "length")).
      var len = o.length >>> 0;

      // 3. If IsCallable(predicate) is false, throw a TypeError exception.
      if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function');
      }

      // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
      var thisArg = arguments[1];

      // 5. Let k be 0.
      var k = 0;

      // 6. Repeat, while k < len
      while (k < len) {
        // a. Let Pk be ! ToString(k).
        // b. Let kValue be ? Get(O, Pk).
        // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
        // d. If testResult is true, return kValue.
        var kValue = o[k];
        if (predicate.call(thisArg, kValue, k, o)) {
          return kValue;
        }
        // e. Increase k by 1.
        k++;
      }

      // 7. Return undefined.
      return undefined;
    }
  });
}