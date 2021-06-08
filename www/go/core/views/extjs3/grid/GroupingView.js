Ext.define('go.grid.GroupingView', {
	extend: 'Ext.grid.GroupingView',
	htmlEncode: true,
	totalDisplay: false,
	totalCount: 0,

	masterTpl: go.grid.GridView.prototype.masterTpl,
	initElements: go.grid.GridView.prototype.initElements,
	setTotalCount: go.grid.GridView.prototype.setTotalCount,
	initActionButton : go.grid.GridView.prototype.initActionButton
});