/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: WebFeedsGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Danny Wijffelaars <dwijffelaars@intermesh.nl>
 */
GO.summary.WebFeedsGrid = function(config){
	config = config || {};

	config.title = t("webfeeds", "summary");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;

	let hasManagePerms = false;
	const module = go.Modules.get("legacy", "summary");
	if (module.userRights.mayManage) {
		hasManagePerms = true;
	}

	const SummaryColumn = new GO.grid.CheckColumn({
		header: t("Summary", "summary"),
		dataIndex: 'summary',
		width: 75,
		disabled_field: ''
	}), allUsersColumn = new GO.grid.CheckColumn({
		header: t("All users", "summary"),
		dataIndex: 'allUsers',
		width: 75,
		disabled_field: '',
		hidden: !hasManagePerms,
		disabled: !hasManagePerms
	});


	var fields ={
		fields:['title', 'url', 'summary'],
		columns:[{
			header: t("Title"),
			dataIndex: 'title',
			editor: new Ext.form.TextField({
				 allowBlank: false
			})
		},
		{
			header: t("URL"),
			dataIndex: 'url',
			editor: new Ext.form.TextField({
				 allowBlank: false
//				 vtype: 'url'
			})
		},
		SummaryColumn,
		allUsersColumn
	]
	};
	config.store = new GO.data.JsonStore({
		url: GO.url('summary/rssFeed/store'),
		baseParams: {},
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields:['id', 'title', 'url', 'summary'],
		remoteSort: true
	});

	config.bbar = new Ext.PagingToolbar({
		cls: 'go-paging-tb',
		store: config.store,
		pageSize: parseInt(GO.settings['max_rows_list']),
		displayInfo: true,
		displayMsg: t("Displaying items {0} - {1} of {2}"),
		emptyMsg: t("No items to display")
	});

	config.cm = new Ext.grid.ColumnModel({
		defaults: {
			sortable: true
		},
		columns: fields.columns
	});

	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;

	config.clicksToEdit=1;
	config.plugins = [SummaryColumn];

	let arFlds = [
		{
			name: 'id', type: 'int'
		}, {
			name: 'title', type: 'string'
		}, {
			name: 'url', type: 'string'
		}, {
			name: 'summary', type: 'boolean'
		}
	];
	if (hasManagePerms) {
		arFlds.push({name: 'allUsers', type: 'boolean'});
	}

	const Feed = Ext.data.Record.create(arFlds);

	config.tbar=[{
		iconCls: 'btn-add',
		text: t("Add"),
		cls: 'x-btn-text-icon',
		handler: function(){
			var e = new Feed({
				id: '0'
			});
			this.stopEditing();
			this.store.insert(0, e);
			this.startEditing(0, 0);
		},
		scope: this
	},{
		iconCls: 'btn-delete',
		text: t("Delete"),
		cls: 'x-btn-text-icon',
		handler: function(){
			const selectedRows = this.selModel.getSelections();
			for (let i = 0, l = selectedRows.length; i < l; i++) {
				selectedRows[i].commit();
				this.store.remove(selectedRows[i]);
			}
		},
		scope: this
	}];

	GO.summary.WebFeedsGrid.superclass.constructor.call(this, config);
};
Ext.extend(GO.summary.WebFeedsGrid, GO.grid.EditorGridPanel,{

	getGridData : function(){
		var data = {};

		for (var i = 0; i < this.store.data.items.length;  i++)
		{
			var r = this.store.data.items[i].data;

			data[i]={};

			for(var key in r)
			{
				data[i][key]=r[key];
			}
		}
		return data;
	}
});
