go.grid.EditorGridPanel = Ext.extend(Ext.grid.EditorGridPanel, {	

	initComponent: function () {
		go.grid.EditorGridPanel.superclass.initComponent.call(this);
		
		Ext.apply(this, go.grid.GridTrait);
		this.initGridTrait();
	}

});

Ext.reg("goeditorgrid", go.grid.EditorGridPanel);
