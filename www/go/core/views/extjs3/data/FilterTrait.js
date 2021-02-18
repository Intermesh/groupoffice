go.data.FilterTrait = {
	/**
	 * Same as set filter but keeps existing filter values if set
	 *
	 * @param cmpId
	 * @param filter
	 * @returns {go.data.StoreTrait}
	 */
	patchFilter: function (cmpId, filter) {
		var f = this.getFilter(cmpId);
		if (!f) {
			f = {};
		}
		return this.setFilter(cmpId, Ext.apply(f, filter));
	},

	/**
	 * Set a filter object for a component
	 *
	 * @param {string} cmpId
	 * @param {object} filter if null is given it's removed
	 * @returns {this}
	 */
	setFilter: function (cmpId, filter) {
		if (filter === null) {
			delete this.filters[cmpId];
		} else {
			this.filters[cmpId] = filter;
		}

		var conditions = [];
		for (var cmpId in this.filters) {
			conditions.push(this.filters[cmpId]);
		}

		switch (conditions.length) {
			case 0:
				delete this.baseParams.filter;
				break;
			case 1:
				this.baseParams.filter = conditions[0];
				break;
			default:
				this.baseParams.filter = {
					operator: "AND",
					conditions: conditions
				};
				break;
		}

		return this;
	},

	getFilter: function (name) {
		return this.filters[name];
	},

	initFilters: function () {
		//JMAP remote filters. Used by setFilter()
		if (this.filters) {
			for (var name in this.filters) {
				this.setFilter(name, this.filters[name]);
			}
		} else {
			this.filters = {};
		}
	}
}