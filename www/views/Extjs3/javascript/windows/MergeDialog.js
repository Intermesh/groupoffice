GO.dialog.MergeWindow = Ext.extend(GO.Window,{
	

	initComponent : function(){
		
		this.store = new GO.data.JsonStore({
			url: GO.url('search/store'),
			baseParams: {
				model_names:Ext.encode([this.displayPanel.model_name]),
				permissionLevel:GO.permissionLevels.writeAndDelete
			},
			fields: ['icon','id', 'model_name','name','model_type_id','type','mtime','model_id','module', 'description', 'name_and_type', 'model_name_and_id'],
			remoteSort: true
		});
	
		this.searchField = new GO.form.SearchField({
			store: this.store,
			width:320
		});
		
		
		var gridConfig = {
			border:true,
			region:'center',
			layout:'fit',
			tbar:[
			GO.lang['strSearch']+': ', ' ',this.searchField
			],
			store:this.store,
			columns:[{
				header:'ID',
				dataIndex:'model_id',
				width:40
			},{
				id:'name',
				header: GO.lang['strName'],
				dataIndex: 'name',
				css: 'white-space:normal;',
				sortable: true,
				renderer:function(v, meta, record){
					return '<div class="go-grid-icon go-model-icon-'+record.data.model_name+'">'+v+'</div>';
				}
			},{
				header: GO.lang['strType'],
				dataIndex: 'type',
				sortable:true,
				width:100
			},{
				header: GO.lang['strMtime'],
				dataIndex: 'mtime',
				sortable:true,
				width:110
			}],
			autoExpandMax:2500,
			autoExpandColumn:'name',
			paging:20,
			view:new Ext.grid.GridView({
				enableRowBody:true,
				showPreview:true,			
				emptyText:GO.lang.strNoItems,	
				getRowClass : function(record, rowIndex, p, store){
					if(this.showPreview && record.data.description.length){
						p.body = '<div class="go-links-panel-description">'+record.data.description+'</div>';
						return 'x-grid3-row-expanded';
					}
					return 'x-grid3-row-collapsed';
				}
			}),
			loadMask:{
				msg: GO.lang['waitMsgLoad']
			},
			sm:new Ext.grid.RowSelectionModel()
		};
	
		this.searchGrid = new GO.grid.GridPanel(gridConfig);
		this.layout='border';
		
		this.title=GO.lang.merge;
		
		this.items = [this.formPanel = new Ext.FormPanel({
			region:'north',
			height:60,
			cls:'go-form-panel',
			items:[{
				hideLabel:true,
				xtype:'checkbox',
				name:'delete_merge_models',
				boxLabel:GO.lang.mergeDeleteItems,
				checked:true
			},{
				hideLabel:true,
				xtype:'checkbox',
				name:'merge_attributes',
				boxLabel:GO.lang.mergeAttributes,
				checked:true
			}]
		}),this.searchGrid];
	
		this.width=600;
		this.height=500;
		
		this.buttons=[{
			text: GO.lang['cmdOk'],
			handler: function(){							
				this.merge();
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
		
		GO.dialog.MergeWindow.superclass.initComponent.call(this);
	},
	focus:function(){
		this.searchField.focus();
	},

	merge: function(){
		
		var selectionModel = this.searchGrid.getSelectionModel();
		var records = selectionModel.getSelections();

		var merge_models = [];

		for (var i = 0;i<records.length;i++)
		{
			merge_models.push({
				'model_id' : records[i].data['model_id'], 
				'model_name' : records[i].data['model_name']
				});
		}
				
		var params = this.formPanel.form.getValues();
		params.model_name=this.displayPanel.model_name;
		params.target_model_id=this.displayPanel.data.id;
		params.merge_models=Ext.encode(merge_models);
				
		if(confirm("Are you sure you want to do this?")){
			GO.request({
				url:'addressbook/company/merge',
				params:params,
				success:function(){
					this.displayPanel.reload();
				},
				scope:this
			});
		}
						
		this.hide();
	}
	
});