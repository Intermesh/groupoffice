GO.customfields.DisableCategoriesPanel = Ext.extend(Ext.Panel, {
	initComponent : function(){
		
		this.formPanel = new Ext.Panel({
			region:'north',
			layout:'form',
			cls:'go-form-panel',
			height:40,
			items:this.enableCB = new Ext.form.Checkbox({				
				name:'enabled_customfield_categories',
				hideLabel:true,
				boxLabel:t("Enable the display of selected categories only", "customfields"),
				listeners:{
					scope:this,
					render:function(cb){
						
						cb.getEl().on('click',function(){
							GO.request({
								url:"customfields/category/enableDisabledCategories",
								params:{
									enabled:cb.getValue(),
									model_id:this.model_id,
									model_name:this.model_name
								},
								success:function(response, options, result){
									this.categoriesGrid.setDisabled(!cb.getValue());
								},
								scope:this
							});
						}, this);
					}
				}
			})
		});
		
		var store = new GO.data.JsonStore({
			url: GO.url("customfields/category/enabled"),
			baseParams:{
				model_id:0,
				model_name:""
			},
			fields:['id', 'name', 'checked'],
			remoteSort: true
		});
		
		this.categoriesGrid= new GO.grid.MultiSelectGrid({
			region:'center',
			loadMask:true,
			store: store,
			width: 210,
			split:true,
			disabled:true,
			allowNoSelection:true,
			listeners:{
				scope:this,
				change:function(grid, ids, records){
					GO.request({
						url:"customfields/category/setEnabled",
						params:{
							categories:Ext.encode(ids),
							model_id:this.model_id,
							model_name:this.model_name
						},
						success:function(response, options, result){
							
						}
					});
				}
			}
		});
		
		
		Ext.apply(this, {
			layout:'border',		
			disabled:true,
			items:[this.formPanel, this.categoriesGrid],
			listeners:{
				scope:this,
				show:function(){
					this.loadPanel();
				}
//				render:function(){
//					console.log('ka1');
//					this.loadPanel();
//				}
			}			
		});		
		
		if(!this.title)
			this.title=t("Enabled customfields", "customfields");
		
		GO.customfields.DisableCategoriesPanel.superclass.initComponent.call(this);
	},
	
	loadPanel : function(){
		//if(this.isVisible()){
			this.categoriesGrid.store.load({
				scope:this,
				callback:function(){
					//this.enableCB.suspendEvents();
					this.enableCB.setValue(!GO.util.empty(this.categoriesGrid.store.reader.jsonData.enabled_customfield_categories));
					this.categoriesGrid.setDisabled(GO.util.empty(this.categoriesGrid.store.reader.jsonData.enabled_customfield_categories));
					//this.enableCB.resumeEvents();
				}
			});
		//}
	},
	
	/**
	 * Set the model to edit.
	 * 
	 * @param int model_id
	 * @param string model_name The name of the model that controls the disabled categories. eg. GO\Addressbook\Model\Addressbook controls them for GO\Addressbook\Model\Contact
	 */
	setModel : function(model_id, model_name){
		this.setDisabled(!model_id);
		this.model_id=this.categoriesGrid.store.baseParams.model_id=model_id;
		this.model_name=this.categoriesGrid.store.baseParams.model_name=model_name;
	}
});
