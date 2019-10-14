/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

go.form.KeyValueEditorGrid = Ext.extend(Ext.grid.EditorGridPanel, {
	
	entityStore: null,
	
	initComponent: function(){
		
		if(!this.entityStore){
			console.error('No entitystore provided');
		}
		
		var actions = this.initRowActions();
		
		this.store = new go.data.Store({
			fields: ['id', {name:'name'}],
			entityStore: this.entityStore
		});
		
		Ext.apply(this,{
			plugins: [actions],
			autoExpandColumn:'name',
			autoScroll:true,
			clicksToEdit:1,
			columns: [{
				hidden: true,
				header: 'ID',
				width: 40,
				sortable: true,
				dataIndex: 'id'
			},
			{
				id: 'name',
				header: t('Name'),
				width: 175,
				sortable: true,
				dataIndex: 'name',
				editor: new Ext.form.TextField()
			},
			actions
			],

			viewConfig: {
				emptyText: 	'<p>' +t("No items to display") + '</p>'
			},
			loadMask:true,
			tbar: [{
				xtype:'tbtitle',
				text:this.title
			},'->',{
				iconCls: 'ic-add',
				tooltip: t('Add'),
				handler: function () {
					var r = new this.store.recordType({
						id: 0,
						name: ''
					});
					this.stopEditing();
					this.store.insert(0, r);
					this.startEditing(0, 1);
				},
				scope: this
			}]
			
		});
		
		this.title = null;
		
		go.form.KeyValueEditorGrid.superclass.initComponent.call(this);
	},
	
	getRowClass: function(row, index){
		if (!row.data.active) {
			return 'go-grid-row-inactive';
		}
	},
	
	afterRender: function () {
		this.store.load();
		go.form.KeyValueEditorGrid.superclass.afterRender.call(this);
	},
	
	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,

			actions: [{
					iconCls: 'ic-more-vert'
				}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				this.showMoreMenu(record, e);
			},
			scope: this
		});

		return actions;

	},
	
	showMoreMenu : function(record, e) {
		if(!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [{
					itemId:"delete",
					iconCls: 'ic-delete',
					text: t("Delete"),
					handler: function() {
						Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete the selected item?"), function (btn) {
							if (btn != "yes") {
								return;
							}
							this.doDelete(this.moreMenu.record,true);
						}, this);
					},
					scope: this						
				}]
			});
		}
		this.moreMenu.record = record;
		this.moreMenu.showAt(e.getXY());
	},
	
	/**
	 * 
	 * @param Record record
	 * @param boolean direct Remove directly from DB or only from the grid
	 */
	doDelete : function(record, direct) {
		
		direct?direct:false;
		
		if(!direct){
			this.store.remove(record);
		} else {		
			this.entityStore.set({
				destroy:  [record.id]
			});
		}
	}
});
