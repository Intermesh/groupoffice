

if(!Array.hasOwnProperty('unique')) {
	Object.defineProperty(Array.prototype, 'unique', {
		/**
		 * Return new array with unique values
		 *
		 * @returns {Array}
		 */
		value: function() {
			return this.filter(function(value, index, self) {
				return self.indexOf(value) === index;
			});
		},
		enumerable: false
	});
}


if(!Array.hasOwnProperty('diff')) {
	Object.defineProperty(Array.prototype, 'diff', {

		/**
		 * Get array of all values that are not present in the given array
		 * Opposite of intersect()
		 *
		 * @param {Array} a
		 * @returns {Array}
		 */
		value: function(a) {
			return this.filter(function(i) {
				return a.indexOf(i) === -1;
			});
		},
		enumerable: false
	});
}


if(!Array.hasOwnProperty('intersect')) {
	Object.defineProperty(Array.prototype, 'intersect', {

		/**
		 * Get array of all values that are present in the given array
		 * Opposite of diff()
		 *
		 * @param {Array} a
		 * @returns {Array}
		 */
		value:  function(a) {
			return this.filter(function(i) {
				return a.indexOf(i) !== -1;
			});
		},
		enumerable: false
	});
}


if(!Array.hasOwnProperty('column')) {
	Object.defineProperty(Array.prototype, 'column', {

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
		 *
		 */
		value:  function(name) {
			return this.map(function(i) {
				return i[name];
			});
		},
		enumerable: false
	});
}




if(!Array.hasOwnProperty('columnSort')) {
	Object.defineProperty(Array.prototype, 'columnSort', {

		/**
		 * Sort array of objects by column values.
		 *
		 * eg.
		 *
		 * var arr = [{foo: 2}, {foo: 1}]
		 *
		 * arr.columnSort("foo") == [{foo: 1}, {foo: 2}]
		 *
		 * @param {string} col
		 * @param {boolean} asc
		 * @returns {Array}
		 *
		 */
		value:  function(col, asc) {

			if (!Ext.isDefined(asc)) {
				asc = true;
			}

			return this.sort(function compare(a, b) {
				// Use toUpperCase() to ignore character casing
				var colA = Ext.isString(a[col]) ? a[col].toUpperCase() : a[col];
				var colB = Ext.isString(b[col]) ? b[col].toUpperCase() : b[col];

				var comparison = 0;
				if (colA > colB) {
					comparison = asc ? 1 : -1;
				} else if (colA < colB) {
					comparison = asc ? -1 : 1;
				}
				return comparison;
			})
		},
		enumerable: false
	});
}


if(!Array.hasOwnProperty('indexOfLoose')) {
	Object.defineProperty(Array.prototype, 'indexOfLoose', {

		/**
		 * Same as indexOf but not strict. eg. "2" will be found in [2].
		 *
		 * @param {type} v
		 * @returns {Number}
		 */
		value:  function(v) {
			for(var i = 0, l = this.length; i < l; i++) {
				if(this[i] == v) {
					return i;
				}
			}

			return -1;
		},
		enumerable: false
	});
}
