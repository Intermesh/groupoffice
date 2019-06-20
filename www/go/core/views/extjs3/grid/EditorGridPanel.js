/**
 * GridPanel
 * 
 * We added this functionality:
 * 
 * 1. When columns have "resizable" = false. State won't be stored when using this property.
 * 2. Added a "navigate" (grid, rowIndex, record) event that can be used to navigate the router.
 * 3. When using a go.data.Store with entityStore it will auto load more results when scrolling.
 * 4. A delete function is added and mapped to the delete keyboard key.
 * 
 */

go.grid.EditorGridPanel = Ext.extend(Ext.grid.EditorGridPanel, {	

	loadMask: true,
	
	autoExpandMin: dp(200),
	
	initComponent: function () {
		go.grid.EditorGridPanel.superclass.initComponent.call(this);
		
		Ext.applyIf(this, go.grid.GridTrait);
		this.initGridTrait();
	}

});

(function() {
	var origGetState = Ext.grid.EditorGridPanel.prototype.getState;
	
	Ext.override(Ext.grid.EditorGridPanel, {
			
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
Ext.reg("goeditorgrid", go.grid.EditorGridPanel);
