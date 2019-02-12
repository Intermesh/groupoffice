Ext.override(Ext.Panel, {	
	stateEvents: ['collapse', 'expand'],
	getState: function () {
		return {
			collapsed: this.collapsed
		};
	}
});
