go.grid.GridPanel = Ext.extend(Ext.grid.GridPanel, {
	
	/**
	 * If the end of the list is within this number of pixels it will request the next page	
	 */
	scrollBoundary : 300,

	initComponent: function () {
		
		go.grid.GridPanel.superclass.initComponent.call(this);
		
		if(!this.keys)
		{
			this.keys=[];
		}
		this.keys.push({
			key: Ext.EventObject.DELETE,
			fn: function(key, e){
				console.log("DELETE");
				this.deleteSelected();
			},
			scope:this
		});
		
		this.on("bodyscroll", this.loadMore, this, {buffer: 100});
		
		this.on("rowcontextmenu", function(grid, rowIndex, e) {
			e.stopEvent();
			var sm =this.getSelectionModel();
			if(sm.isSelected(rowIndex) !== true) {
				sm.clearSelections();
				sm.selectRow(rowIndex);
			}
		}, this);
	},
	
	afterRender: function() {
		go.grid.GridPanel.superclass.afterRender.call(this);
		this.addClass("go-grid");
		this.headerBtnWrap = this.el.child(".x-grid3-header");
		if (this.headerBtnWrap && this.enableHdMenu) {
			this.headerBtn = new Ext.Component({
				cls: "go-grid-hd-btn",
				renderTo: this.headerBtnWrap
			});
			this.headerBtn.el.on("click", this.onHeaderBtnClick, this);
		}
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
		this.headerMenu.show(el, "tr-br?")
	},
	
	deleteSelected : function() {
	
		var selectedRecords = this.getSelectionModel().getSelections(), ids = [];
		
		selectedRecords.forEach(function(r) {
			ids.push(r.data.id);
		});
		
		this.store.destroy(ids);
	},

	/**
	 * Loads more data if the end off the scroll area is reached
	 * @returns {undefined}
	 */
	loadMore: function () {
		var store = this.getStore();

		if (store.getCount() == store.getTotalCount()) {
			return;
		}

		store.lastOptions.params = store.lastOptions.params || {};

		var limit = store.lastOptions.params.limit || store.getCount(),
						pos = store.lastOptions.params.position || 0,
						scroller = this.getView().scroller.dom,
						body = this.getView().mainBody.dom;


		if ((scroller.offsetHeight + scroller.scrollTop + this.scrollBoundary) >= body.offsetHeight) {

			var p = Ext.apply(store.lastOptions, {
				add: true,
				params: {
					limit: limit,
					position: pos + limit
				}
			});
			store.load(p);
		}
	}

});

Ext.reg("gogrid", go.grid.GridPanel);
