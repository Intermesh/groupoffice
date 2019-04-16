
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

go.users.UserGroupGrid = Ext.extend(go.grid.GridPanel, {
	title: t("Groups"),
	iconCls: 'ic-group',
	selectedGroups: null,
	name: 'groups',
	initComponent: function () {	
		
		var checkColumn = new GO.grid.CheckColumn({
			dataIndex: 'selected',
			listeners: {
				change: this.onCheckChange,
				scope: this
			}
		});
		
		var me = this;
		
		this.store = new go.data.Store({
			fields: [
				'id', 
				'name',
				'isUserGroupFor',
				{name: 'members', type: go.data.types.User, key: 'users.userId'},
				{
					name: 'selected', 
					type: {
						convert: function (v, data) {
							return me.selectedGroups.indexOf(data.id) > -1;
						}
					}
				}
//				{name: 'user', type: go.data.types.User, key: 'isUserGroupFor'},
			],
			entityStore: "Group"
		});

		
		this.store.setFilter("base", {
					excludeEveryone: true,
					hideUsers: true
				});		

		Ext.apply(this, {		
			plugins: [checkColumn],
			tbar: [ '->', 
				{
					xtype: 'tbsearch',
					filters: [
						'text'					
					]
				}				
			],
			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'displayName',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var user = record.get("user"),
							style = user && user.avatarId ?  'background-image: url(' + go.Jmap.downloadUrl(record.get("user").avatarId) + ')"' : "",						
							max = 5,
							members = record.get('members').slice(0, max).column('displayName'),						
							memberStr = members.join(", "),								
							more = members.length - max;
							
						if(more > 0) {
							memberStr += t(" and {count} more").replace('{count}', more);
						}
						
						return '<div class="user"><div class="avatar" style="'+style+'"></div>' +
							'<div class="wrap">'+
								'<div class="displayName">' + record.get('name') + '</div>' +
								'<small class="username">' + memberStr + '</small>' +
							'</div>'+
							'</div>';
					}
				},
				checkColumn
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			}
			// config options for stateful behavior
//			stateful: true,
//			stateId: 'users-grid'
		});

		go.users.UserGroupGrid.superclass.initComponent.call(this);		
	},	
	
	onCheckChange : function(record, newValue) {
		if(newValue) {
			this.selectedGroups.push(record.id);
		} else
		{
			this.selectedGroups.splice(this.selectedGroups.indexOf(record.id), 1);
		}
		this._isDirty = true;
	},
	
//	onLoadComplete : function (user) {
//		this.user = user;
//		
//		var me = this;
//		this.selectedGroups =[];
//		this.user.groups.forEach(function(group) {
//			me.selectedGroups.push(group.groupId);
//		});
//		
//		
//		if(this.rendered) {
//			this.store.load();
//		} else if(!this.loading)
//		{
//			this.loading = true;
//			this.on('render', function() {
//				this.loading = false; 
//				this.store.load();
//			}, this, {single: true});
//		}
//	}
//	
	
	isFormField: true,

	getName: function() {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	},

	setValue: function (groups) {
		
		this._isDirty = false;
		
		var me = this;
		this.selectedGroups =[];
		groups.forEach(function(group) {
			
			if(!group.groupId) {
				throw "Invalid value given";
			}
			me.selectedGroups.push(group.groupId);
		});		
		
		if(this.rendered) {
			this.store.load();
		} else if(!this.loading)
		{
			this.loading = true;
			this.on('render', function() {
				this.loading = false; 
				this.store.load();
			}, this, {single: true});
		}
	},
	
	getValue: function () {				
		var groups = [];
		this.selectedGroups.forEach(function(groupId) {
			groups.push({
				groupId: groupId
			});
		});
		return groups;
	},

	markInvalid: function (msg) {
		this.getEl().addClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.mark(this, msg);
	},
	clearInvalid: function () {
		this.getEl().removeClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.clear(this);
	},
	
	validate : function() {
		return true;
	}
	
});


