

/**
 * 
 * @example
 * 
 * {
					xtype: 'tbsearch',
					filters: [
						'q',
						'name', 
						'content',
						{name: 'modified', multiple: false},
						{name: 'created', multiple: false}						
					],
					listeners: {
						scope: this,
						search: function(btn, query, filters) {
							this.storeFilter.setFilter("tbsearch", filters);
							this.storeFilter.load();
						},
						reset: function() {
							this.storeFilter.setFilter("tbsearch", null);
							this.storeFilter.load();
						}
					}
				}
 */
go.toolbar.SearchButton = Ext.extend(Ext.Toolbar.Button, {

	iconCls: 'ic-search',	
	store: null,
	tooltip: t('Search'),
	searchToolBar: null,	
	
	//Specify all the remote jmap filters that can be used in the search box as an 
	//array. eg.
	//
	//filterNames: [
//						'name', 
//						'email', 
//						'country', 
//						'city', 
//						{name: 'modifiedsince', multiple: false}, 
//						{name: 'modifiedbefore', multiple: false}, 
//						{name: 'minage', multiple: false},
//						{name: 'maxage'}
//					] 
	//
	//Users can use: 
	//
	//name: "Merijn Schering" name: Merijn% email:%intermesh% 
	//
	//to search. This will convert into:
	//
	// {name: ["Merijn Schering", "Merijn%", email:["%intermesh%]}
	
	filters: null,
	
	
	constructor: function (config) {
		config = config || {};
		go.toolbar.SearchButton.superclass.constructor.call(this, config);
			
		this.filters = go.util.Filters.normalize(config.filters || ['q']);
		
		if(!this.store) {			
			//try to find store if this button it part of a grid.
			var grid = this.findParentByType('grid');
			if(grid) {
				this.store = grid.store;
				
				grid.getSelectionModel().on("rowselect", function() {
					this.back();
				}, this);
				
				grid.on("rowclick", function() {
					this.back();
				}, this);
			}
		}	
	
		//default filter on "q"
		if(this.store) {
			this.on({
				scope: this,
				search: function (tb, v, filters) {
					if(this.store instanceof go.data.Store || this.store instanceof go.data.GroupingStore) {
					
						this.store.setFilter('tbsearch', filters).load();
						
					} else {
						//params for old framework
						this.store.baseParams.query = v;
						this.store.load();
					}
					
					this.updateView();
				},
				reset: function() {
					if(this.store instanceof go.data.Store) {
						this.store.setFilter('tbsearch', null).load();
					} else {
						delete this.store.baseParams.query;
					}
					
					this.updateView();
					this.store.load();
				}
			});
		}


		var me = this;

		this.triggerField = new Ext.form.TriggerField({
			xtype: 'trigger',
			emptyText: t('Search'),
			validationEvent: false,
			validateOnBlur: false,
			triggerClass: 'x-form-search-trigger',
			listeners: {				
				specialkey: function (field, e) {					
					if (e.getKey() == Ext.EventObject.ENTER) {
						e.preventDefault(); //to prevent form submission
						this.search();
					}
				},
				scope: this
			},
			onTriggerClick: function () {
				me.search();
			},
			flex: 1
		});
	},
	
	
	
	/**
	 * Set correct class and update tooltip
	 */
	updateView : function(){
		if(this.hasActiveSearch()){
			this.addClass('raised');
			this.setTooltip(t("Change search condition"));
		} else {
			this.removeClass('raised');
			this.setTooltip(t("Search"));
		}
	},
	
	/**
	 * Check if there currently is an active search going on
	 * 
	 * @return {Boolean}
	 */
	hasActiveSearch : function(){
		
		var isActive = false;
		
		if(this.store instanceof go.data.Store || this.store instanceof go.data.GroupingStore) {
			isActive = !GO.util.empty(this.store.filters.tbsearch);
		} else {
			isActive = !GO.util.empty(this.store.baseParams.query);
		}

		return isActive;
	},
	
	/**
	 * Close the search toolbar
	 * 	 
	 */
	back : function(){
		this.backButton.findParentByType('toolbar').setVisible(false);
		this.fireEvent('close', this);
	},
	
	onRender : function(ct, position) {
		var items = this.initialConfig.tools || [];
		
		items.unshift(this.backButton = new Ext.Button({
			iconCls: 'ic-arrow-back',
			handler: function () {
				this.back();
			},
			scope: this
		}), 
		this.triggerField);
				
		items.push(this.resetButton = new Ext.Button({
			iconCls: 'ic-close',
			disabled: true,
			handler: function (b) {
				this.reset();
				this.back(b);
			},
			scope: this
		}));
		
		this.searchToolBar = new Ext.Toolbar({
			cls: 'x-searchbar',
			hidden: true, //default
			layout: 'hbox',
			layoutConfig: {align: 'middle'},
			items: items
		});
		var toolbar = this.findParentByType('toolbar');
		this.searchToolBar.render(toolbar.el);
		toolbar.ownerCt.on('resize', function (tb, adjWidth) {
			this.searchToolBar.setWidth(adjWidth);
		}, this);
		
		if(this.filters) {
			
			var names = Object.keys(this.filters).filter(function(name) {return name != 'q'});
			
			if(names.length) {
			
				var msg = t("You can use these keywords:<br /><br />") + names.join(", ") + "<br /><br />";

				msg += t("For example:<br /><br />" + names[0] + ": \"John Doe\" "+ names[0] + ": Foo%");

				if(names.indexOf('modified') > -1) {
					msg += " modified: >2019-01-31 23:59 modified: <2019-02-01";
				}

				Ext.QuickTips.register({
					target: this.triggerField.getEl(),
					title: t("Advanced search options"),
					text: msg,				
					dismissDelay: 10000 // Hide after 10 seconds hover
				});
			}
		}
		
		
		go.toolbar.SearchButton.superclass.onRender.call(this, ct, position);
	},
	
	getValue: function(){
		return this.triggerField.getValue();
	},
	setValue: function(v){
		return this.triggerField.setValue(v);
	},
	
	reset : function() {
		this.triggerField.setValue("");
		this.fireEvent('reset', this);
	},
	
	search : function() {
		var v = this.triggerField.getValue(), filters = null;
		this.resetButton.setDisabled(GO.util.empty(v));
		
		if(this.filters) {
			filters = go.util.Filters.parseQueryString(v, this.filters);	
		}
		
		this.fireEvent('search', this, v, filters);
		//this.onSearch.call(this.scope || this, v);
	},

	// search button handler
	handler: function () {
		this.searchToolBar.setVisible(true);
		this.searchToolBar.items.get(1).focus();
	}
});
Ext.reg('tbsearch', go.toolbar.SearchButton);

