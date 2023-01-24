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
	emptyText: '<div class="pad">' + t("No items found") + "</div>",
	trackOver: false,
	cls: 'go-nav',
	autoScroll: true,
	style: {'padding-top':dp(8)+'px'},
	singleSelect: true,

	//overClass:'x-view-over',
	itemSelector:'div.go-nav-item',
	tpl:'<tpl for=".">\
					<div class="go-nav-item {cls}"><i class="icon {iconCls}">{icon}</i>\
					<span>{name}</span>\
					<tpl if="values.badge">\
					<span class="badge right">{badge}</span>\
					</tpl>\
					</div>\
					</tpl>\
				</tpl>'
});

Ext.reg('navmenu', go.NavMenu);