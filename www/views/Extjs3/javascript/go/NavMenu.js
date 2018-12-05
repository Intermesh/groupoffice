go.NavMenu = Ext.extend(Ext.DataView,{
	
	initComponent: function() {
		
		Ext.applyIf(this,{
			cls: 'go-nav',
			autoScroll: true,
			style: {'padding-top':dp(8)+'px'},
			store:this.store,
			singleSelect: true,
			overClass:'x-view-over',
			itemSelector:'div',
			tpl:'<tpl for=".">\
					<tpl if="name == \'-\'"><hr /></tpl>\
					<tpl if="name != \'-\'">\
						<div><i class="icon {iconCls}">{icon}</i>\
					<span>{name}</span></div>\
					</tpl>\
				</tpl>',
			columns: [{dataIndex:'name'}]
		});
		
		go.NavMenu.superclass.initComponent.call(this);
	},
	
	addSeparator : function() {
		this.store.add(new Ext.data.Record({
			name: "-"
		}));
	}
});