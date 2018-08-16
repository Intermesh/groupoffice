go.grid.GridPanel = Ext.extend(Ext.grid.GridPanel, {	

	initComponent: function () {
		go.grid.GridPanel.superclass.initComponent.call(this);
		
		Ext.applyIf(this, go.grid.GridTrait);
		this.initGridTrait();
	}

});

Ext.reg("gogrid", go.grid.GridPanel);
