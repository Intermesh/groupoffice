/* global go, Ext */

/**
 * Allow grids to be sortable
 * @param {function} callback
 * 
 * Called with:
 * 
 * sortable, selections, dragData, dd, e
 * 
 * For an example see www/go/modules/core/customfields/views/extjs3/SystemSettingsPanel.js
 * 
 * @param {object} scope
 * @parab {object} dropTargetConfig Config options for https://docs.sencha.com/extjs/3.4.0/#!/api/Ext.dd.DropTarget
 * @returns {go.grid.plugin.Sortable}
 */
go.grid.plugin.Sortable = function (callback, scope, isDropAllowed) {
	this.callback = callback;
	this.scope = scope || this;
	this.isDropAllowed = isDropAllowed;
};

go.grid.plugin.Sortable.prototype.init = function (grid) {
	
	this.grid = grid;
	
	var ddGroup = Ext.id();
	Ext.apply(grid, {
		enableDragDrop: true,
		ddGroup: ddGroup
	});

	grid.on("afterrender", function (grid) {
		this.dropTarget = new Ext.dd.DropTarget(grid.getView().mainBody,
						{
							ddGroup: ddGroup,
							copy: false,
							notifyDrop: this.notifyDrop.createDelegate(this),
							notifyOver: this.notifyOver.createDelegate(this)
						
						});

	}, this);
};


go.grid.plugin.Sortable.prototype.notifyDrop = function (dd, e, data)
{
	
	if (!this.copy) {
		data.grid.store.remove(data.selections);
	}

	var dragData = dd.getDragData(e);
	var cindex = dragData.rowIndex;
	if (cindex == 'undefined')
	{
		cindex = data.grid.store.data.length - 1;
	}
	
	dragData.dropRecord = data.grid.store.getAt(cindex);
	
	if(this.isDropAllowed && !this.isDropAllowed(data.selections, dragData.dropRecord)) {
		return false;
	}	

	for (var i = 0, l = data.selections.length; i < l; i++)
	{
		data.grid.store.insert(cindex + 1, data.selections[i]);
	}

	if(this.callback) {
		this.callback.call(this.scope, this, data.selections, dragData, dd, e);
	}

	return true;

};

go.grid.plugin.Sortable.prototype.notifyOver = function (dd, e, data)
{
	if(!this.isDropAllowed) {
		return this.dropAllowed;
	}
	
	var dragData = dd.getDragData(e);
	var cindex = dragData.rowIndex;
	if (cindex == 'undefined')
	{
		cindex = data.grid.store.data.length - 1;
	}
	var overRecord = data.grid.store.getAt(cindex);
	
	if(!this.isDropAllowed(data.selections, overRecord)) {
		return dd.dropNotAllowed;
	} else
	{
		return dd.dropAllowed;
	}
	

};

