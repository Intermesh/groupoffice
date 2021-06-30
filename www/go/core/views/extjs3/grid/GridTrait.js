go.grid.GridTrait = {
//	/**
//	 * If the end of the list is within this number of pixels it will request the next page	
//	 */
//	scrollBoundary: 300,
//	
//	pageSize: 20,
	
	//scrollUp: false,  // set to true when you need to loadMore when scrolling up
	
	/**
	 * Init scrollloader for loading more records when scrolling
	 */
	scrollLoader: true,

	multiSelectToolbarEnabled: true,

	moveDirection: 'up',

	lastSelectedIndex: false,
	currentSelectedIndex: false,

	initGridTrait : function() {
		if (!this.keys)
		{
			this.keys = [];
		}
	
		this.initDeleteKey();		
		if(this.getSelectionModel().getSelected) {
			this.initNav();
		}
		
		this.initScrollOffset();
		
		this.initHeaderMenu();

		if(this.scrollLoader) {
			Ext.applyIf(this, go.panels.ScrollLoader);
			this.initScrollLoader();
		}
		
		this.initTotalDisplay();

		// Handle invalid sort state which may happen when a (custom) column has been removed.
		this.store.on("exception", function(store, type, action, options, response, arg ) {
			if(response.message == "unsupportedSort") {
				console.warn("Clearing invalid sort state:", store.sortInfo);
				store.sortInfo = {};
				//caused infinite loop while developing
				if(!GO.debug) {
					store.reload();
				}

				//cancel further exception handling
				return false;
			}
		}, this);

		if(this.multiSelectToolbarEnabled && this.getTopToolbar() && !this.getSelectionModel().singleSelect) {
			this.initMultiSelectToolbar();
		}

		//select row when action button is clicked
		if(this.getView().actionConfig) {

			this.on('viewready', function(){
				this.getView().actionBtn.on('click', function(btn) {
					this.getSelectionModel().selectRow(btn.rowIndex);
				}, this);
			}, this, {single: true});
		}
	},

	initMultiSelectToolbar : function() {

		this.getSelectionModel().on('selectionchange', function(sm) {

			if(!sm.getSelections) {
				return;
			}
			var selections = sm.getSelections();

			selections.length > 1 ? this.showMultiSelectToolbar(selections) : this.hideMultiSelectToolbar();
		}, this);
	},

	getMultiSelectToolbarItems : function() {
		var items = [

			{
				iconCls: 'ic-arrow-back',
				tooltip: t("Clear selection"),
				handler: function() {
					this.getSelectionModel().clearSelections();
				},
				scope: this
			},
			this.selectedLabel,
			'->',
			{
				iconCls: 'ic-delete',
				tooltip: t("Delete"),
				handler: function() {
					this.deleteSelected();
				},
				scope: this
			}
		];

		if(this.multiSelectToolbarItems) {
			var args = [3,0].concat(this.multiSelectToolbarItems);
			Array.prototype.splice.apply(items, args);
		}

		return items;
	},

	showMultiSelectToolbar : function(selections) {
		if(!this.multiSelectToolBar ) {
			this.selectedLabel = new Ext.form.Label({});
			this.multiSelectToolBar = new Ext.Toolbar({
				cls: 'go-multiselect-toolbar',
				hidden: true, //default
				items: this.getMultiSelectToolbarItems()
			});
			this.multiSelectToolBar.render(this.getTopToolbar().getEl());
		}

		this.multiSelectToolBar.setWidth(this.getTopToolbar().getWidth());
		this.multiSelectToolBar.setVisible(true);
		this.selectedLabel.setText(t("{count} selected").replace('{count}', selections.length));
	},

	hideMultiSelectToolbar : function() {
		if(this.multiSelectToolBar) {
			this.multiSelectToolBar.hide();
		}
	},

	initTotalDisplay: function() {

		this.store.on("beforeload", function(store, options) {
			if(!this.getView().totalDisplay) {
				return;
			}
			if((options.params.limit || store.baseParams && store.baseParams.limit) && go.util.empty(options.params.position)) {
				//only calculate total on first load.
				options.params.calculateTotal = true;
			}
		}, this);

		this.store.on("load", function(store, records, o){
			if(!this.getView().totalDisplay || (o.params && !go.util.empty(o.params.position))) {
				return;
			}
			this.getView().setTotalCount(store.getTotalCount());
		
		}, this);
	},
	
	initHeaderMenu : function() {
		if(this.enableHdMenu === false) {
			return;
		}
		this.enableHdMenu = false;
		this.on('render',function() {			
			// header menu
			this.addClass("go-grid");
			this.headerBtnWrap = this.el.child(".x-grid3-header");
			if (this.headerBtnWrap) {// && this.enableHdMenu) {
				this.headerBtn = new Ext.Component({
					cls: "go-grid-hd-btn",
					renderTo: this.headerBtnWrap
				});
				//this.headerBtnWrap.on('click', function(e){ console.log(e.target) });
				this.headerBtn.el.on("click", this.onHeaderBtnClick, this);
			}
		}, this);
	},
	
	//Always enforce room for scrollbar so last column in resizable because of our custom header button.
	initScrollOffset : function() {
		
		if(this.autoHeight || this.getView().scrollOffset === 0) {
			return;
		}
		
		this.getView().scrollOffset = Ext.getScrollBarWidth();
		
	},
	
	initCustomFields : function() {

		if(Ext.isObject(this.store) && !this.store.events) {
			this.store = Ext.create(this.store, 'store');
		}

		if (!this.columns || !this.store || !this.store.entityStore || !this.store.entityStore.entity.customFields) {
			return;
		}
		var customFldColumns = go.customfields.CustomFields.getColumns(this.store.entityStore.entity.name);

		if (this.autoExpandColumn && this.autoExpandColumn.indexOf('custom-field-') === 0) {
			var autoExpandColumnName = this.autoExpandColumn.substring(13);
			var restOfCustomColumns = [], arClmn = [];
			customFldColumns.forEach(function (col) {
				if (col.dataIndex === 'customfields.' + autoExpandColumnName) {
					arClmn.push(col);
				} else {
					restOfCustomColumns.push(col);
				}
			});
			this.columns = arClmn.concat(restOfCustomColumns, this.columns);
		} else {
			this.columns = this.columns.concat(customFldColumns);
		}
	},
	
	//The navigate can be used in modules to track row selections for navigation.
	//It buffers keyboard actions and it doesn't fire when ctrl or shift is used for multiselection
	initNav : function() {
		this.addEvents({navigate: true});

		this.getSelectionModel().on('rowselect', function (sm, rowIndex, record) {
			if(this.currentSelectedIndex != this.lastSelectedIndex) {
				this.lastSelectedIndex = this.currentSelectedIndex;
			}
			this.currentSelectedIndex = rowIndex;
		}, this);

		this.on('rowclick', function(grid, rowIndex, e){			

			if(!e.ctrlKey && !e.shiftKey)
			{
				var record = this.getSelectionModel().getSelected();
				if(record) {
					this.fireEvent('navigate', this, rowIndex, record);				
				}
			}
			
		}, this);
		
		
		this.on("keydown",function(e) {
			if(!e.ctrlKey && !e.shiftKey)
			{
				var record = this.getSelectionModel().getSelected();
				if(record) {
					this.fireEvent('navigate', this, this.store.indexOf(record), record);				
				}
			}			
		}, this, {
			buffer: 300
		});
	},
	
	initDeleteKey : function() {

		function onDeleteKey(key, e){
			//sometimes there's a search input in the grid, so dont delete when focus is on an input
			if(e.target.tagName!='INPUT')
				this.deleteSelected();
		}

		this.keys.push({
			key: Ext.EventObject.DELETE,
			fn: onDeleteKey,
			scope:this
		});

		this.keys.push({
			key: Ext.EventObject.BACKSPACE,
			ctrl: true,
			fn: onDeleteKey,
			scope:this
		});
	},

	deleteSelected: function () {

		var selectedRecords = this.getSelectionModel().getSelections(), count = selectedRecords.length, strConfirm;

		switch (count)
		{
			case 0:
				return;
			case 1:
				strConfirm = t("Are you sure you want to delete the selected item?");
				break;

			default:
				strConfirm = t("Are you sure you want to delete the {count} items?").replace('{count}', count);
				break;
		}

		Ext.MessageBox.confirm(t("Confirm delete"), t(strConfirm), function (btn) {

			if (btn != "yes") {
				return;
			}
			
			this.doDelete(selectedRecords);
			
		}, this);
	},

	selectNextAfterDelete : function() {

		var index = -1;

		index = this.moveDirection == 'up' ? this.currentSelectedIndex - 1 : this.currentSelectedIndex;

		if(index > -1 && index < this.store.getCount()) {
			this.getSelectionModel().selectRow(index);
		} else
		{
			this.moveDirection == 'up' ? this.getSelectionModel().selectFirstRow() : this.getSelectionModel().selectLastRow();
		}

		//make sure moveDirections stays the same after delete
		if(this.moveDirection == 'up') {
			this.lastSelectedIndex = this.currentSelectedIndex + 1;
		} else
		{
			this.lastSelectedIndex = this.currentSelectedIndex - 1;
		}

		var record = this.getSelectionModel().getSelected();

		if(record) {
			var rowIndex = this.store.indexOf(record);
			this.fireEvent('navigate', this, rowIndex, record);
		}
	},


	
	doDelete : function(selectedRecords) {

		var me = this;
		this.getEl().mask(t("Deleting..."));

		//set to first record to make navigation work properly after delete
		this.moveDirection = this.lastSelectedIndex !== false && this.lastSelectedIndex < this.currentSelectedIndex ? 'down' : 'up';
		selectedRecords.forEach(function(r) {
			var rowIndex =  this.getStore().indexOf(r);
			// console.warn(r, rowIndex);
			if(rowIndex < this.currentSelectedIndex) {
				this.currentSelectedIndex = rowIndex;
			}
		}, this);

		var prom = this.getStore().entityStore.set({
			destroy:  selectedRecords.column("id")
		}).then(function(result){

			setTimeout(function() {
				me.selectNextAfterDelete();
			});

			if(!result.notDestroyed) {
				return;
			}

			var msg = "";
			for(var id in result.notDestroyed) {
				msg += id + ": " + result.notDestroyed[id].description + "<br />";
			}
						
			Ext.MessageBox.alert(t("Error"), t("Could not delete some items: <br /><br />" + msg));

		})
		.catch(function(reason) {
			GO.errorDialog.show(t( 'Sorry, an unexpected error occurred: ' + reason.message));
		})
		.finally(function() {
			me.getEl().unmask();			
		});
	},
	
	handleHdMenuItemClick: function(item) {
		var cm = this.getColumnModel()
		  , id = item.getItemId()
		  , column = cm.getIndexById(id.substr(4));
		if (column !== -1) {
			if (item.checked && cm.getColumnsBy(function(c) {return !c.hidden }, this).length <= 1) {
				 return
			}
			cm.setHidden(column, item.checked)
		}
	},
	onHeaderBtnClick: function(event, el, object) {
		var i, cm = this.getColumnModel(), column, item;
		if (!this.headerMenu) {
			this.headerMenu = new Ext.menu.Menu({
				 items: []
			});
			this.headerMenu.on("itemclick", this.handleHdMenuItemClick, this);
		}
		this.headerMenu.removeAll();
		for (i = 0; i < cm.getColumnCount(); i++) {
			column = cm.getColumnAt(i);
			if (column.hideable !== false) {
				item = new Ext.menu.CheckItem({
					 text: cm.getOrgColumnHeader(i),
					 itemId: "col-" + cm.getColumnId(i),
					 checked: !cm.isHidden(i),
					 hideOnClick: false,
					 htmlEncode: column.headerHtmlEncode
				});
				this.headerMenu.add(item)
			}
		}
		//Sort menu alphabetically
		this.headerMenu.items.sort("ASC", function(a, b){
			// Use toUpperCase() to ignore character casing
			var colA = a.text.toUpperCase();
			var colB = b.text.toUpperCase();

			var comparison = 0;
			if (colA > colB) {
				comparison = 1;
			} else if (colA < colB) {
				comparison = -1;
			}
			return comparison;
		});
		this.headerMenu.show(el, "tr-br?")
	}
}
