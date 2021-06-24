/**
 * 
 * Vertical navigation menu.
 * 
 * @example
 * ```
 * var filterPanel = new go.NavMenu({
 * 		region:'north',
 * 		store: new Ext.data.ArrayStore({
 * 			fields: ['name', 'icon', 'inputValue'], //icon, iconCls and cls are supported.
 * 			data: [
 * 				[t("Active", "tasks"), 'content_paste', 'active'],
 * 				[t("Due in seven days", "tasks"), 'filter_7', 'sevendays'],
 * 				[t("Overdue", "tasks"), 'schedule', 'overdue'],
 * 				[t("Incomplete tasks", "tasks"), 'assignment_late', 'incomplete'],
 * 				[t("Completed", "tasks"), 'assignment_turned_in', 'completed'],
 * 				[t("Future tasks", "tasks"), 'assignment_return', 'future'],
 * 				[t("All", "tasks"), 'assignment', 'all'],
 * 			]
 * 		}),
 * 		listeners: {
 * 			selectionchange: function(view, nodes) {	
 * 				var record = view.store.getAt(nodes[0].viewIndex);
 * 				this.gridPanel.store.baseParams['show']=record.data.inputValue;
 * 				this.gridPanel.store.load();
 * 			},
 * 			scope: this
 * 		}
 * 	});
 * ```
 */
go.NavMenu = Ext.extend(Ext.DataView,{
	
	initComponent: function() {
		
		Ext.applyIf(this,{
			trackOver: false,
			cls: 'go-nav',
			autoScroll: true,
			style: {'padding-top':dp(8)+'px'},
			store:this.store,
			singleSelect: true,
			//overClass:'x-view-over',
			itemSelector:'div',
			tpl:'<tpl for=".">\
					<div class="{cls}"><i class="icon {iconCls}">{icon}</i>\
					<span>{name}</span></div>\
					</tpl>\
				</tpl>',
			columns: [{dataIndex:'name'}]
		});
		
		go.NavMenu.superclass.initComponent.call(this);
	}
	//separater messed up select() function because of mismathing index

	// addSeparator : function() {
	// 	this.store.add(new Ext.data.Record({
	// 		name: "-"
	// 	}));
	// }
});

Ext.reg('navmenu', go.NavMenu);