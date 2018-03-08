/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: UsersInGroup.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

 /**
 * @class GO.dialog.UsersInGroup
 * @extends Ext.Window
 * A window to show the users in a group.
 *
 * @cfg {Function} handler A function called when the Add or Ok button is clicked. The grid will be passed as argument.
 * @cfg {Object} scope The scope of the handler
 *
 * @constructor
 * @param {Object} config The config object
 */

GO.dialog.UsersInGroup = function(config){

	Ext.apply(this, config);

	this.store = new GO.data.JsonStore({
    url: GO.url('groups/group/getUsers'),
    baseParams: {id:0},
    root: 'results',
		totalProperty: 'total',
		id: 'id',
		fields: ['id','name','username'],
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
	       	header: t("Name"),
					dataIndex: 'name',
					sortable: true
		    	},{
	       	header: t("Username"),
					dataIndex: 'username',
					sortable: true
		    	}],
		    sm: new Ext.grid.RowSelectionModel(),
		    tbar: [
	            t("Search")+': ', ' ',
	            this.searchField
	        ]
		});

	var focusSearchField = function(){
		this.searchField.focus(true);
	};

	GO.dialog.UsersInGroup.superclass.constructor.call(this, {
    layout: 'fit',
		modal:false,
		height:350,
		width:550,
		closeAction:'hide',
		focus: focusSearchField.createDelegate(this),
		title:t("Users in group"),
		items: this.grid,
		buttons: [
			{
				text: t("Close"),
				handler: function(){this.hide();},
				scope: this
			}
		]
	});
};

Ext.extend(GO.dialog.UsersInGroup, Ext.Window, {

	setGroupId : function(groupId){
		this.grid.store.setBaseParam('id',groupId);
		this.grid.store.load();
	},

	show : function(){
		if(!this.grid.store.loaded)
		{
			this.grid.store.load();
		}
		GO.dialog.UsersInGroup.superclass.show.call(this);
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


