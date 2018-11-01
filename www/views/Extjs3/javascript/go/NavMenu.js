go.NavMenu = Ext.extend(Ext.DataView,{
	
	initComponent: function() {
		
		Ext.applyIf(this,{
			cls: 'go-nav',
			style: {'padding-top':dp(8)+'px'},
			store:this.store,
			singleSelect: true,
			overClass:'x-view-over',
			itemSelector:'div',
			tpl:'<tpl for=".">\
				<div><i class="icon">{icon}</i>\
				<span>{name}</span></div>\
			</tpl>',
			columns: [{dataIndex:'name'}]
		});
		
		go.NavMenu.superclass.initComponent.call(this);
	}
});