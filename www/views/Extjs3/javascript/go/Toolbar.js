Ext.namespace('go.toolbar');

go.toolbar.SearchButton = Ext.extend(Ext.Toolbar.Button, {

	iconCls: 'ic-search',	
	store: null,
	tooltip: t('Search'),
	searchToolBar: null,
	constructor: function (config) {
		go.toolbar.SearchButton.superclass.constructor.call(this, config);
		
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
					if(this.store instanceof go.data.Store) {
						
						this.store.baseParams.filter.q = v;
						
						this.store.load();
						return
					}
					//params for old framework
					this.store.baseParams.query = v;
					this.store.load();
				},
				reset: function() {
					if(this.store instanceof go.data.Store) {
						delete this.store.baseParams.filter.q;
					} else
					{
						delete this.store.baseParams.query;
					}
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
	
	onRender : function(ct, position) {
		var items = this.initialConfig.tools || [];
		
		items.unshift({
			iconCls: 'ic-arrow-back',
			handler: function (b) {
				b.findParentByType('toolbar').setVisible(false);
				this.reset();
				this.fireEvent('close', this);
			},
			scope: this
		}, 
		this.triggerField);
		
		items.push(this.resetButton = new Ext.Button({
			iconCls: 'ic-close',
			disabled: true,
			handler: function (b) {
				this.reset();
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
