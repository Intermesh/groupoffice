
go.toolbar.SearchField = Ext.extend(GO.form.ComboBox, {
	sep: ' ',
	name: 'text',
	valueField: 'value',
	displayField: 'display',
	mode: 'local',
	typeAhead: false,
	autoSelect: false,
	getCursorPosition: function(){

		if (document.selection) { // IE
			var r = document.selection.createRange();
			if(!r)
				return false;

			var d = r.duplicate();

			if(!this.el.dom)
				return false;

			d.moveToElementText(this.el.dom);
			d.setEndPoint('EndToEnd', r);
			return d.text.length;
		}
		else {
			return this.el.dom.selectionEnd;
		}
	},

	getActiveRange: function(){
		var s = this.sep;
		var p = this.getCursorPosition();
		var v = this.getRawValue();
		var left = p;
		while (left > 0 && v.charAt(left) != s) {
			--left;
		}
		if (left > 0) {
			left++;
		}
		return {
			left: left,
			right: p
		};
	},

	getActiveEntry: function(){
		var r = this.getActiveRange();
		return this.getRawValue().substring(r.left, r.right).trim();//.replace(/^s+|s+$/g, '');
	},

	replaceActiveEntry: function(value){
		var r = this.getActiveRange();
		var v = this.getRawValue();
		if (this.preventDuplicates && v.indexOf(value) >= 0) {
			return;
		}
		var pad =  (this.sep == ' ' ? '' : ' ');
		this.setValue(v.substring(0, r.left) + (r.left > 0 ? pad : '') + value + this.sep + pad + v.substring(r.right));

		var p = r.left + value.length + 2 + pad.length;
		//this.selectText.defer(200, this, [p, p]);
	},


	onSelect: function(record, index){
		if (this.fireEvent('beforeselect', this, record, index) !== false) {
			var value = Ext.util.Format.htmlDecode(record.data[this.valueField || this.displayField]);
			this.replaceActiveEntry(value);
			this.collapse();
			this.fireEvent('select', this, record, index);
		}
	},

	getValue : function() {
		return this.getRawValue();
	},

	initQuery: function(){
		if(this.getEl().id === document.activeElement.id)
			this.doQuery(this.getActiveEntry() );

//    	if(this.focused)
//        this.doQuery(this.sep ? this.getActiveEntry() : this.getRawValue());
	},
	onLoad : function(){
		if(!this.hasFocus){
			return;
		}
		if(this.store.getCount() > 0 || this.listEmptyText){
			this.expand();
			this.restrictHeight();
			if(this.lastQuery == this.allQuery){

				if(this.autoSelect !== false && !this.selectByValue(this.value, true)){
					this.select(0, true);
				}
			}else{
				if(this.autoSelect !== false){
					this.selectNext();
				}
				if(this.typeAhead && this.lastKey != Ext.EventObject.BACKSPACE && this.lastKey != Ext.EventObject.DELETE){
					this.taTask.delay(this.typeAheadDelay);
				}
			}
		}else{
			this.collapse();
		}

	}
});

/**
 * Search button
 *
 * When used inside a go.grid.GridPanel component it will lookup the store and bind the entity automatically.
 * The entity filters can be used using the advanced filter syntax
 *
 * @example
 * 
 * {
					xtype: 'tbsearch',
					tools: [
						new Ext.Button({iconCls: 'star'})//extra tool button inside search toolbar
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
	query: "",
	
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
			
		//this.filters = go.util.Filters.normalize(config.filters || ['text']);
		
		if(this.onwnerCt) {
			this.lookupStore();
		} else{
			this.on('added', function() {
				this.lookupStore();
			}, this);
		}

		this.on('destroy', function() {
			if(this.searchToolBar) {
				this.searchToolBar.destroy();
			}
		}, this);


		var me = this;

		this.triggerField = new go.toolbar.SearchField({
			validationEvent: false,
			validateOnBlur: false,
			spellCheck: false,
			triggerClass: 'x-form-search-trigger',
			value: this.query,
			store: new Ext.data.ArrayStore({
				fields: ['display', 'value'],
				id: 'value'
			}),
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
	

	lookupStore : function() {
		if(!this.store) {			
			//try to find store if this button it part of a grid.
			var grid = this.findParentByType('grid');
			if(grid) {
				this.store = grid.store;			
			}
		}

		this.bindStore(this.store);
	

	},


	bindStore : function(store) {

		this.store = store;
		//default filter on 'text'
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


				},
				reset: function() {
					if(this.store instanceof go.data.Store || this.store instanceof go.data.GroupingStore) {
						this.store.removeAll();
						this.store.setFilter('tbsearch', null).load();
					} else {
						delete this.store.baseParams.query;
					}

					this.store.load();
				}
			});
		}
	},
	
	
	/**
	 * Set correct class and update tooltip
	 */
	updateView : function(){
		if(this.hasActiveSearch()){
			this.addClass('raised');
			this.addClass('accent');
			this.setTooltip(t("Change search condition"));
		} else {
			this.removeClass('raised');
			this.removeClass('accent');
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
			items: items,
			listeners: {
				scope: this,

				render : function(tb) {
					tb.getEl().set({tabindex: 0});
					tb.getEl().on("focusout", function(e) {

						//hide toolbar if clicked outside. To allow a menu button we check if the target is not a menuy
						if(!e.browserEvent.relatedTarget || (!e.browserEvent.relatedTarget.classList.contains('x-menu-focus') && !this.searchToolBar.getEl().dom.contains(e.browserEvent.relatedTarget))) {
							this.back();
						}
					}, this);
				}
			}
		});
		var toolbar = this.findParentByType('toolbar');
		this.searchToolBar.render(toolbar.el);
		
		// toolbar.ownerCt.on('resize', function (tb, adjWidth) {
		// 	this.searchToolBar.setWidth(adjWidth);
		// }, this);
		
		if(this.store && this.store.entityStore) {
			
			var names =  [], f;
			
			for ( var name in this.store.entityStore.entity.filters) {
				f = this.store.entityStore.entity.filters[name];
				if(f.name != 'text' && !f.customfield) {
					names.push(name);
				}

				this.triggerField.store.loadData([[f.title, name + ":"]], true);

			}


			if(names.length) {
			
				var msg = t("You can use these keywords:<br /><br />") + names.join(", ") + "<br /><br />";

				msg += t("And any custom field by 'databaseName'.") + "<br /><br />";

				msg += t("For example:<br /><br />modifiedBy: \"John Doe\" modifiedBy: Foo%");

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

		if(this.query) {
			this.search();
			this.showSearchBar();
		}
	},
	
	getValue: function(){
		return this.triggerField.getValue();
	},
	setValue: function(v){
		return this.triggerField.setValue(v);
	},
	
	reset : function() {
		this.triggerField.setValue("");
		this.triggerField.setDisabled(false);
		this.fireEvent('reset', this);
		this.updateView();
	},
	
	search : function() {
		var v = this.triggerField.getValue(), filters = null;
		this.resetButton.setDisabled(!v);
		
		if(this.store &&this.store.entityStore) {
			filters = go.util.Filters.parseQueryString(v, this.store.entityStore.entity.filters);	
		}
		
		this.fireEvent('search', this, v, filters);
		this.updateView();
		//this.onSearch.call(this.scope || this, v);
	},

	// search button handler
	showSearchBar: function () {
		this.searchToolBar.setWidth(this.ownerCt.getWidth());
		this.searchToolBar.setVisible(true);		
		this.searchToolBar.items.get(1).focus(); 
	},

	handler: function() {
		this.showSearchBar();
	}
});
Ext.reg('tbsearch', go.toolbar.SearchButton);

