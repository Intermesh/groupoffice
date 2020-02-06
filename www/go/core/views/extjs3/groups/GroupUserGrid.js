
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

go.groups.GroupUserGrid = Ext.extend(go.grid.GridPanel, {
	/**
	 * The form field name
	 */
	name: "users",
	
	initComponent: function () {
		
		this.selectedUsers = [];
		
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
					'username',
					'displayName',
					'avatarId',
					'loginCount',
					{name: 'createdAt', type: 'date'},
					{name: 'lastLogin', type: 'date'},
					{
						name: 'selected',
						type: {
							convert: function (v, data) {
								return me.selectedUsers.indexOf(data.id) > -1;
							}
						},
						sortType: function (checked) {
							return checked ? 1 : 0;
						}
					}
				],
				sortInfo: {
					field: 'displayName',
					direction: 'ASC'
				},
				entityStore: "User"
			});

		Ext.apply(this, {		
			plugins: [checkColumn],
			tbar: [{
					xtype: 'tbtitle',
					text: t("Members")
			},
			'->', 
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
					sortable: false,
					dataIndex: 'displayName',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						
						var style = record.get('avatarId') ?  'background-image: url(' + go.Jmap.thumbUrl(record.get("avatarId"), {w: 40, h: 40, zc: 1}) + ')"' : "";						
						
						return '<div class="user"><div class="avatar" style="' + style + '"></div>' +
							'<div class="wrap">'+
								'<div class="displayName">' + value + '</div>' +
								'<small class="username">' + Ext.util.Format.htmlEncode(record.get('username')) + '</small>' +
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
		});		
	
		this.store.on("beforeload", this.onBeforeStoreLoad, this);

		go.groups.GroupUserGrid.superclass.initComponent.call(this);

	},

	
	onCheckChange : function(record, newValue) {
		if(newValue) {
			this.selectedUsers.push(record.id);
		} else
		{
			this.selectedUsers.splice(this.selectedUsers.indexOf(record.id), 1);
		}
		
		this._isDirty = true;
	},
	
	isFormField: true,

	getName: function() {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	},

	setValue: function (users) {
		this._isDirty = false;
		this.selectedUsers = users;
		this.store.load().catch(function(){});
	},
	
	onBeforeStoreLoad : function(store, options) {
		//don't add selected on search, or when they are already loaded or when gridpanel is trying to fill the page.
		if(this.store.filters.tbsearch || options.selectedLoaded || options.paging) {
			return true;
		}

		go.Db.store("User").get(this.selectedUsers, function(entities) {
			entities.columnSort('displayName', true);

			this.store.loadData({records: entities}, true);
			// this.store.sortData();

			this.store.setFilter('exclude', {
				exclude: this.selectedUsers
			});

			var me = this;

			this.store.load({
				add: true,
				selectedLoaded: true
			}).then(function() {
				//when reload is called by SSE we need this removed.
				delete me.store.lastOptions.selectedLoaded;
			});

		}, this);

		return false;
	},
	
//	onStoreLoad : function() {
//		this.store.sortData();
//	},
	
	getValue: function () {
		return this.selectedUsers;
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


