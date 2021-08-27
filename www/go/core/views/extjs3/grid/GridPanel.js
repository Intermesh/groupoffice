/**
 * GridPanel
 * 
 * We added this functionality:
 * 
 * 1. When columns have "resizable" = false. State won't be stored when using this property.
 * 2. Added a "navigate" (grid, rowIndex, record) event that can be used to navigate the router.
 * 3. When using a go.data.Store with entityStore it will auto load more results when scrolling.
 * 4. A delete function is added and mapped to the delete keyboard key.
 * 5. When using "columns" to define the colModel custom fields will be added automatically.
 * 
 */

go.grid.GridPanel = Ext.extend(Ext.grid.GridPanel, {	

	autoExpandMin: dp(200),

	loadMask: true,
	
	initComponent: function () {
		
		Ext.applyIf(this, go.grid.GridTrait);
		this.initCustomFields();
		this.initShowMore();
		go.grid.GridPanel.superclass.initComponent.call(this);	
		
		this.initGridTrait();		
	},

	getView : function() {
		if (!this.view) {
				this.view = new go.grid.GridView(this.viewConfig);
		}
			
		return this.view;
	}

});

Ext.reg("gogrid", go.grid.GridPanel);


(function() {
	var origGetState = Ext.grid.GridPanel.prototype.getState;
	
	Ext.override(Ext.grid.GridPanel, {
			
			getState : function() {

				var o = origGetState.call(this);

				for(var i = 0, c; (c = this.colModel.config[i]); i++){
					if(c.resizable === false) {
						delete o.columns[i].width;
					}            
				}

				return o;
			}
	});

})();

