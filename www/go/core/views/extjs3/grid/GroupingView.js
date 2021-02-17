Ext.define('go.grid.GroupingView', {
  	extend: 'Ext.grid.GroupingView',

	totalDisplay: false,
	totalCount: 0,

	masterTpl: go.grid.GridView.prototype.masterTpl,
	initElements: go.grid.GridView.prototype.initElements,
	setTotalCount: go.grid.GridView.prototype.setTotalCount
});