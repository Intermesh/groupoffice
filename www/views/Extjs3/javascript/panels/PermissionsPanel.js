/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PermissionsPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author WilmarVB <wilmar@intermesh.nl>
 */

/**
 * @class GO.grid.PermissionsPanel
 * @extends Ext.Panel
 * 
 * A panel that can be used to set permissions for a Group-Office ACL. It will
 * use an anchor layout with 100% width and 100% height automatically.
 * 
 * @constructor
 * @param {Object}
 *            config The config object
 */

GO.grid.PermissionsPanel = Ext.extend(Ext.Panel, {

	fieldName:'acl_id',
	
	changed : false,
	loaded : false,
	managePermission : false,
	levelLabels : null,
	
	isOverwritable: false,
	isOverwritten: false,
	
	cls:'go-permissions-panel',

	// private
	initComponent : function() {

		if(!this.title){
			this.title=t("Permissions");
		}
		
		var levelData = [];

		this.levelLabels = this.levelLabels || {};
		
		if(!this.levelLabels[GO.permissionLevels.read])
			this.levelLabels[GO.permissionLevels.read] =t("Read only");
		if(!this.levelLabels[GO.permissionLevels.create])
			this.levelLabels[GO.permissionLevels.create] =t("Read and Create only");
		if(!this.levelLabels[GO.permissionLevels.write])
			this.levelLabels[GO.permissionLevels.write] =t("Write");
		if(!this.levelLabels[GO.permissionLevels.writeAndDelete])
			this.levelLabels[GO.permissionLevels.writeAndDelete] =t("Write and delete");
		if(!this.levelLabels[GO.permissionLevels.manage])
			this.levelLabels[GO.permissionLevels.manage] =t("Manage");
		
		if(!this.levels){
			this.levels=[
				GO.permissionLevels.read,
				GO.permissionLevels.create,
				GO.permissionLevels.write,
				GO.permissionLevels.writeAndDelete,
				GO.permissionLevels.manage
			];
		}
		
		for(var i=0;i<this.levels.length;i++){			
			if(!this.levelLabels[this.levels[i]]){
				alert('Warning: you must define a label for permission level: '+this.levels[i]);
			}else
			{
				levelData.push([this.levels[i],this.levelLabels[this.levels[i]]]);
			}
		}
		

		this.showLevel = (this.hideLevel) ? false : true;			

		var permissionLevelConfig ={
					store : new Ext.data.SimpleStore({
						id:0,
						fields : ['value', 'text'],
						data : levelData
					}),
					valueField : 'value',
					displayField : 'text',
					mode : 'local',
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					forceSelection : true
				};
				
		
		if(!this.addLevel)
			this.addLevel = GO.permissionLevels.read;

//		var selectUsersPermissionLevel = new GO.form.ComboBox(permissionLevelConfig);
		var selectGroupsPermissionLevel = new GO.form.ComboBox(permissionLevelConfig);

		this.header = false;
		this.layout = 'fit';
		this.border = false;
		this.disabled = true;

		var renderLevel = function(v){
			var r = permissionLevelConfig.store.getById(v);
			return r ? r.get('text') : v;
		}

		var groupColumns = [{
			header : t("Name"),
			dataIndex : 'name',
			menuDisabled:true,
			sortable: true,
			renderer: function(name, meta, record) {
				var icon = record.data.isUserGroupFor ? 'person' : 'people';
				return '<i class="icon">' + icon + '</i>&nbsp;&nbsp;' + name;
			}
		}];
		if(this.showLevel)
		{
			groupColumns.push({
				header : t("Level"),
				dataIndex : 'level',
				menuDisabled:true,
				editor : selectGroupsPermissionLevel,
				renderer:renderLevel,
				sortable: true
			});
		}

		var action = new Ext.ux.grid.RowActions({
			header : '-',
			autoWidth:true,
			align : 'center',
			actions : [{
				iconCls : 'ic-group',
				qtip: t("Users")
			}]
		});

		groupColumns.push(action);


		this.aclGroupsGrid = new GO.base.model.multiselect.panel({
//			title:t("Authorized groups"),
			plain:true,
//			style:'margin:4px',
//			anchor: '100% 50%',
			region: "center",
			forceLayout:true,
			autoExpandColumn:'name',
			url:'aclGroup',
			columns: groupColumns,
			plugins: action,
			addAttributes:{level:this.addLevel},
			selectColumns:[{
				header : t("Name"),
				dataIndex : 'name',
				menuDisabled:true,
				sortable: true
			},{
				header : t("Display name","users","core"),
				dataIndex : 'displayName',
				menuDisabled:true,
				sortable: true
			}
		],
			fields:['id','name','displayName','level','isUserGroupFor'],
			model_id: this.acl_id//GO.settings.user_id
		});

		this.aclGroupsGrid.store.on("load", function(){
			this.managePermission = this.aclGroupsGrid.store.reader.jsonData.manage_permission;
			this.aclGroupsGrid.getTopToolbar().setDisabled(!this.isEditable());
			
			
			this.overWriteCheckBox.setDisabled(!this.managePermission);
		}, this);

		action.on({
			scope:this,
			action:function(grid, record, action, row, col) {

				this.aclGroupsGrid.getSelectionModel().selectRow(row);

				switch(action){
					case 'ic-group':
						this.showUsersInGroupDialog(record.data.id);
						break;
				}
			}
		}, this);

		this.aclGroupsGrid.on('beforeedit', function(e) {
			return this.isEditable();
		},this);

	
		this.items =[];
		this.overWriteCheckBox = new Ext.ux.form.XCheckbox({
			hideLabel: true,
			name: 'acl_overwritten',
			boxLabel: t("Overwrite default permissions for this item (click apply to activate)")
		});
		if(this.isOverwritable) {
			this.layout="border";
			this.items.push({
				xtype: "fieldset",
				region: "north",
				autoHeight: true,
				items:[this.overWriteCheckBox],
				layout: "form"
			});
		}
		this.items.push(this.aclGroupsGrid);
		
		GO.grid.PermissionsPanel.superclass.initComponent.call(this);
	},
	
	isEditable : function() {
		return this.managePermission && (!this.isOverwritable || this.isOverwritten);
	},

	/**
	 * Sets Access Control List to load in the panel
	 * 
	 * @param {Number}
	 *            The Group-Office acl ID.
	 */
	setAcl : function(acl_id) {
		if(this.isOverwritable)
			this.isOverwritten = this.overWriteCheckBox.getValue();
		this.acl_id = acl_id || 0;
		this.aclGroupsGrid.setModelId(acl_id, this.isVisible());
		this.setDisabled(GO.util.empty(acl_id));	
	},

	onShow : function() {

		GO.grid.PermissionsPanel.superclass.onShow.call(this);

		if(this.isOverwritable)
			this.isOverwritten = this.overWriteCheckBox.getValue();

		if (!this.aclGroupsGrid.loaded) {
			this.loadAcl();
		}

	},

	loadAcl : function(){
		if(this.acl_id>0){
			this.aclGroupsGrid.store.load();
		
		}
	},

	afterRender : function() {

		GO.grid.PermissionsPanel.superclass.afterRender.call(this);

		var v = this.isVisible();

		if (v && !this.aclGroupsGrid.loaded) {
			this.loadAcl();
		}
	},

	// private
	showUsersInGroupDialog : function(groupId) {
		if (!this.usersInGroupDialog) {
			this.usersInGroupDialog = new GO.dialog.UsersInGroup();
		}
		this.usersInGroupDialog.setGroupId(groupId);
		this.usersInGroupDialog.show();
	}

});
