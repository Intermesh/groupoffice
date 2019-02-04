Ext.namespace('go.toolbar');


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
	
	filterNames: null,
	constructor: function (config) {
		go.toolbar.SearchButton.superclass.constructor.call(this, config);
		
		this.initFilterNames(config);
		
		if(!this.store) {			
			//try to find store if this button it part of a grid.
			var grid = this.findParentByType('grid');
			if(grid) {
				this.store = grid.store;
			}
		}	
	
		//default filter on "q"
		if(this.store) {
			this.on({
				scope: this,
				search: function (tb, v) {
					if(this.store instanceof go.data.Store || this.store instanceof go.data.GroupingStore) {
						
						var filter = go.util.parseSearchQuery(v, this.filterNames[0].name), me = this;
						this.filterNames.forEach(function(cfg) {
							delete me.store.baseParams.filter[cfg.name];
							if(filter[cfg.name]) {
								me.store.baseParams.filter[cfg.name] = cfg.multiple ? filter[cfg.name] : filter[cfg.name][0];
							}
						});
						
					} else {
						//params for old framework
						this.store.baseParams.query = v;
					}
					
					this.updateView();
					this.store.load();
				},
				reset: function() {
					if(this.store instanceof go.data.Store) {
						var me = this;
						this.filterNames.forEach(function(cfg) {
							delete me.store.baseParams.filter[cfg.name];							
						});
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
	
	initFilterNames: function(config) {
		this.filterNames = config.filterNames || ['q'];
		for(var i = 0, l = this.filterNames.length; i < l; i++) {
			if(!Ext.isObject(this.filterNames[i])) {
				this.filterNames[i] = {
					name: this.filterNames[i],
					multiple: true
				}
			}
		}
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
			isActive = !GO.util.empty(this.store.baseParams.filter.q);
		} else {
			isActive = !GO.util.empty(this.store.baseParams.query);
		}

		return isActive;
	},
	
	/**
	 * Close the search toolbar
	 * 
	 * @param {Button} b
	 */
	back : function(b){
		b.findParentByType('toolbar').setVisible(false);
		this.fireEvent('close', this);
	},
	
	onRender : function(ct, position) {
		var items = this.initialConfig.tools || [];
		
		items.unshift({
			iconCls: 'ic-arrow-back',
			handler: function (b) {
				this.back(b);
			},
			scope: this
		}, 
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
		
		if(this.filterNames.length) {
			
			var names = this.filterNames.map(function(f) {return f.name;});
			
			var msg = t("You can use these keywords:<br /><br />") + names.join(", ") + "<br /><br />";
			
			msg += t("For example:<br /><br />" + names[0] + ": \"John Doe\" "+ names[0] + ": Foo%");
			
			if(names.indexOf('modifiedsince')) {
				msg += " modifiedsince: 2019-01-31 23:59 modifiedbefore 2019-02-01";
			}
			
			Ext.QuickTips.register({
				target: this.triggerField.getEl(),
				title: t("Advanced search options"),
				text: msg,				
				dismissDelay: 10000 // Hide after 10 seconds hover
			});
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
		var v =this.triggerField.getValue();
		this.resetButton.setDisabled(GO.util.empty(v));
		this.fireEvent('search', this, v);
		//this.onSearch.call(this.scope || this, v);
	},

	// search button handler
	handler: function () {
		this.searchToolBar.setVisible(true);
		this.searchToolBar.items.get(1).focus();
	}
});
Ext.reg('tbsearch', go.toolbar.SearchButton);


go.toolbar.TitleItem = Ext.extend(Ext.Toolbar.Item, {
	/**
	 * @cfg {String} text The text to be used as innerHTML (html tags are accepted)
	 */

	constructor: function (config) {
		go.toolbar.TitleItem.superclass.constructor.call(this, Ext.isString(config) ? {text: config} : config);
	},

	// private
	onRender: function (ct, position) {
		this.autoEl = {cls: 'xtb-title', html: this.text || ''};
		go.toolbar.TitleItem.superclass.onRender.call(this, ct, position);
	},

	/**
	 * Updates this item's text, setting the text to be used as innerHTML.
	 * @param {String} t The text to display (html accepted).
	 */
	setText: function (t) {
		if (this.rendered) {
			this.el.update(t);
		} else {
			this.text = t;
		}
	}
});
Ext.reg('tbtitle', go.toolbar.TitleItem);
