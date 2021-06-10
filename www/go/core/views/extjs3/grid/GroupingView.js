Ext.define('go.grid.GroupingView', {
	extend: 'Ext.grid.GroupingView',
	htmlEncode: true,
	totalDisplay: false,
	totalCount: 0,

	masterTpl: go.grid.GridView.prototype.masterTpl,
	initElements: go.grid.GridView.prototype.initElements,
	setTotalCount: go.grid.GridView.prototype.setTotalCount,
	initActionButton : go.grid.GridView.prototype.initActionButton,
	showActionButton : go.grid.GridView.prototype.showActionButton,
	onRowOver : go.grid.GridView.prototype.onRowOver,
	onRowOut : go.grid.GridView.prototype.onRowOut,
	destroy : function() {
		this.callParent(arguments);
		if(this.actionBtn) {
			this.actionBtn.destroy();
		}
	}
});