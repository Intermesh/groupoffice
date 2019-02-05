/**
 * Store filter
 * 
 * A store filter is used to keep track of multiple components adjusting the 
 * store filters.
 * For example the address book has:
 * 
 * 1. a tree to select the address book / group
 * 2. An organization filter
 * 3. A search query field
 * 
 * All these components must modify the store filter but they must leave the
 * parameters of the other components alone.
 * 
 * See addressbook MainPanel for example usage
 * 
 */
go.data.StoreFilter = Ext.extend(Ext.util.Observable,{
	
	/**
	 * store is required
	 * 
	 * @param {type} config
	 * @returns {undefined}
	 */
	constructor: function(config) {		
		go.flux.Store.superclass.constructor.call(this);		
		
		config = config || {};
		Ext.apply(this, config);
		
		this.filters = {};
	},
	
	store: null,
	
	/**
	 * Set a filter obkect for a component
	 * 
	 * @param {string} cmpId
	 * @param {object} filter
	 * @returns {void}
	 */
	setFilter : function(cmpId, filter) {
		if(filter === null) {
			delete this.filters[cmpId];
		} else
		{
			this.filters[cmpId] = filter;
		}		
	},
	
	/**
	 * Applies the filter to the store baseParams and loads the store.
	 * @param {object} params
	 * @returns {undefined}
	 */
	load: function(params) {
		this.store.baseParams.filter = {
			operator: "AND",
			conditions: []
		};
		
		for(var cmpId in this.filters) {
			this.store.baseParams.filter.conditions.push(this.filters[cmpId]);
		}
		
		return this.store.load(params);	
	}	
});
