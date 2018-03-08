/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SelectUsers.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
/**
 * @class GO.dialog.SelectUsers
 * @extends Ext.Window
 * A window to select a number of User-Office user Users.
 * 
 * @cfg {Function} handler A function called when the Add or Ok button is clicked. The grid will be passed as argument.
 * @cfg {Object} scope The scope of the handler
 * 
 * @constructor
 * @param {Object} config The config object
 */
 
GO.dialog.SelectUsers = function(config){
	
	Ext.apply(this, config);
	

	this.store = new GO.data.JsonStore({
		url: GO.url('core/users'),
		fields: ['id','name', 'email'],
		remoteSort: true
	});
    
	this.searchField = new GO.form.SearchField({
		store: this.store,
		width:320
	});
    
   
    
	
	this.grid = new GO.grid.GridPanel({
		paging:true,
		border:false,
		store: this.store,
		view: new Ext.grid.GridView({
			autoFill: true,
			forceFit: true
		}),
		columns: [{
			header: GO.lang['strName'],
			dataIndex: 'name',					
			sortable: true
		},{
			header: GO.lang['strEmail'],
			dataIndex: 'email',					
			sortable: true
		}],
		sm: new Ext.grid.RowSelectionModel(),
		tbar: [
		GO.lang['strSearch']+': ', ' ',
		this.searchField
		]		
	});
		
	this.grid.on('rowdblclick', function(){
		this.callHandler(true);
	}, this);
		
	var focusSearchField = function(){
		this.searchField.focus(true);
	};
		
	GO.dialog.SelectUsers.superclass.constructor.call(this, {
		layout: 'fit',
		modal:false,
		height:400,
		width:600,
		closeAction:'hide',
		focus: focusSearchField.createDelegate(this),
		title:GO.lang['strSelectUsers'],
		items: this.grid,
		buttons: [
		{
			text: GO.lang['cmdOk'],
			handler: function (){
				this.callHandler(true);
			},
			scope:this
		},
		{
			text: GO.lang['cmdAdd'],
			handler: function (){
				this.callHandler(false);
			},
			scope:this
		},
		{
			text: GO.lang['cmdClose'],
			handler: function(){
				this.hide();
			},
			scope: this
		}
		]
	});
};

Ext.extend(GO.dialog.SelectUsers, GO.Window, {

	show : function(){
		if(!this.grid.store.loaded)
		{
			this.grid.store.load();
		}
		GO.dialog.SelectUsers.superclass.show.call(this);
	},
	//private
	callHandler : function(hide){
		if(this.handler)
		{
			if(!this.scope)
			{
				this.scope=this;
			}
			
			var handler = this.handler.createDelegate(this.scope, [this.grid]);
			handler.call();
		}
		if(hide)
		{
			this.hide();
		}
	}	
	
});


