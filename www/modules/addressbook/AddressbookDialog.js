GO.addressbook.AddressbookDialog = Ext.extend(GO.dialog.TabbedFormDialog, {

	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'name',
			title:GO.addressbook.lang.addressbook,
			formControllerUrl: 'addressbook/addressbook',
			width:800,
			height:540
			//fileUpload:true
		});
		
		GO.addressbook.AddressbookDialog.superclass.initComponent.call(this);	
	},
	
	afterSubmit : function(action){
		var modelCreated = action.result.id>0;
		this.importPanel.setDisabled(!modelCreated);
	},
	
	beforeSubmit : function(params) {		
		this.formPanel.baseParams.importBaseParams = Ext.encode({'addressbook_id':this.remoteModelId});
		
		GO.addressbook.AddressbookDialog.superclass.beforeSubmit.call(this);	
	},
	buildForm : function(){
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.addressbook.lang.cmdPanelProperties,
			layout: 'form',
			labelWidth: 140,
			defaultType: 'textfield',
			border: false,
			bodyStyle:'padding:5px',
			defaults: {anchor:'100%'},
			//cls:'go-form-panel',
			items:[
			{
				fieldLabel: GO.lang['strName'],
				name: 'name',
				allowBlank: false
			},
			this.selectUser = new GO.form.SelectUser({
				fieldLabel: GO.lang['strUser'],
				disabled : !GO.settings.has_admin_permission,
				allowBlank: false
			}),{
				xtype:'panel',
				border:false,
				layout:'column',
				items:[{
					border:false,
					layout:'form',
					columnWidth:.8,
					items:{
						xtype:'textfield',
						fieldLabel: GO.addressbook.lang['defaultSalutation'],
						name: 'default_salutation',
						allowBlank: false,
						anchor:'99%',
						value:GO.addressbook.lang.defaultSalutationExpression
					}
				},{
					columnWidth:.2,
					border:false,
					items:{
						xtype:'button',
						handler:function(){this.propertiesPanel.form.findField('default_salutation').setValue(GO.addressbook.lang.defaultSalutationExpression);},
						scope:this,
						text:GO.lang.cmdReset
					}
				}]
			},
			{
				xtype: "xcheckbox",
				fieldLabel: 'files_module',
				boxLabel: GO.addressbook.lang['create_folder_contact_or_company'],
				name: "create_folder"
			},
			{
				xtype:'fieldset',
				title:GO.addressbook.lang.explanationVariables,
				border:true,
				layout:'column',
				autoHeight:true,
				items:[{
					border:false,
					columnWidth:.2,
					html:	'['+GO.addressbook.lang.cmdSir+'/'+GO.addressbook.lang.cmdMadam+']<br />'+
							'{title}<br />'+
							'{initials}<br />'+
							'{first_name}<br />'+
							'{middle_name}<br />'+
							'{last_name}'
				},{
					columnWidth:.8,
					border:false,
					html:	GO.addressbook.lang.explanationSex+
							'<br />'+GO.lang.strTitle+
							'<br />'+GO.lang.strInitials+
							'<br />'+GO.lang.strFirstName+
							'<br />'+GO.lang.strMiddleName+
							'<br />'+GO.lang.strLastName
				}]
			},{
				xtype:'panel',
				border:false,
				items:[this.deleteAllItemsButton = new Ext.Button({
					xtype:'button',
					text:GO.lang.deleteAllItems,
					handler:function(){
						Ext.Msg.show({
							title: GO.lang.deleteAllItems,
							icon: Ext.MessageBox.WARNING,
							msg: GO.lang.deleteAllItemsAreYouSure,
							buttons: Ext.Msg.YESNO,
							scope:this,
							fn: function(btn) {
								if (btn=='yes') {
									GO.request({
										timeout:300000,
										maskEl:Ext.getBody(),
										url:'addressbook/addressbook/truncate',
										params:{
											addressbook_id:this.remoteModelId
										},
										scope:this
									});
								}
							}
						});
					},
					scope:this
				}),
				this.removeDuplicatesButton =new Ext.Button({
					style:'margin-top:10px',
					xtype:'button',
					text:GO.lang.removeDuplicates,
					handler:function(){

						window.open(GO.url('addressbook/addressbook/removeDuplicates',{addressbook_id:this.remoteModelId}))

					},
					scope:this
				})]
			}
			]
		});
		
		this.addPanel(this.propertiesPanel);
		
		this.importDialogs = {};
		
		this.addPanel(this.importPanel = new Ext.Panel({
			title:GO.lang.cmdImport,
			layout: 'form',
			items: [],
			defaults: {anchor:'100%'},
			border: false,
			labelWidth: 150,
			toolbars: [],
			cls:'go-form-panel',
			items: [
				this.fileTypeCB = new GO.form.ComboBox({
					hiddenName: 'fileType',
					fieldLabel: GO.addressbook.lang.cmdFormLabelFileType,
					store: new Ext.data.ArrayStore({
						storeId: 'fileTypeStore',
						idIndex: 0,
						fields:['value','label'],
						data: [
							['CSV','CSV (Comma Separated Values)'],
							['VCard','VCF (vCard)'],
							['XLS','XLS(X)']
						]
					}),
					valueField:'value',
					displayField:'label',
					mode:'local',
					editable:false,
					allowBlank: false,
					triggerAction: 'all',
					value: 'CSV'
				}), this.controllerNameCB = new GO.form.ComboBox({
					hiddenName: 'controller',
					fieldLabel: GO.lang.cmdImport,
					store: new Ext.data.ArrayStore({
						storeId: 'controllersStore',
						idIndex: 0,
						fields:['value','label'],
						data: [
							['GO\\Addressbook\\Controller\\Contact',GO.addressbook.lang.contacts],
							['GO\\Addressbook\\Controller\\Company',GO.addressbook.lang.companies]
						]
					}),
					valueField:'value',
					displayField:'label',
					mode:'local',
					editable:false,
					allowBlank: false,
					triggerAction: 'all',
					value: 'GO\\Addressbook\\Controller\\Company'
				}),new Ext.Panel({
					layout: 'form',
					border: false,
					items: [
						new Ext.Button({
							text: GO.lang.cmdContinue,
							width: '20%',
							handler: function(){
								var controllerName = this.controllerNameCB.getValue();		
								var fileType = this.fileTypeCB.getValue();
								if (!GO.util.empty(controllerName) && !GO.util.empty(fileType)) {
									if ( !this.importDialogs[fileType] )
										this.importDialogs[fileType] = {};
									if ( !this.importDialogs[fileType][controllerName] ) {
											this.importDialogs[fileType][controllerName] = new GO.base.model.ImportDialog({
												importBaseParams : { addressbook_id : this.remoteModelId },
												controllerName : controllerName,
												fileType: fileType,
												excludedAttributes : ['ctime','mtime','user_id', 'contact_name','link_id','files_folder_id',
													'user_id','email_allowed','go_user_id'],
												modelContainerIdName : 'addressbook_id',
												possibleUpdateFindAttributes : ['email']
											});
										}
									this.importDialogs[fileType][controllerName].show(this.remoteModelId);
								}
							},
							scope: this
						})
					]
				})
			]
		}));
		
		this.fileTypeCB.on('select',function(combo,record,index){
			if (record.id=='VCard')
				this.controllerNameCB.setValue('GO\\Addressbook\\Controller\\Contact');
			this.controllerNameCB.setDisabled(record.id!='CSV' && record.id!='XLS');
		},this);
		
//		this.addPanel( this.importPanel = new GO.base.model.ImportPanel({
//			filetypes:[
//				['csv','CSV (Comma Separated Values)'],
//				['vcf','VCF (vCard)']
//			],
//			controllers:[
//				['GO_Addressbook_Controller_Contact',GO.addressbook.lang.contacts],
//				['GO_Addressbook_Controller_Company',GO.addressbook.lang.companies]
//			],
//			importBaseParams:[
//				{'addressbook_id':this.remoteModelId}
//			]
//		}));
		
		this.addPermissionsPanel(new GO.grid.PermissionsPanel());
		
		if(GO.customfields){
			this.disableContactsCategoriesPanel = new GO.customfields.DisableCategoriesPanel({
				title:GO.addressbook.lang.contactCustomFields
			});
			this.addPanel(this.disableContactsCategoriesPanel);
			
			this.disableCompaniesCategoriesPanel = new GO.customfields.DisableCategoriesPanel({
				title:GO.addressbook.lang.companyCustomFields
			});
			this.addPanel(this.disableCompaniesCategoriesPanel);
			
			this.enableBlocksPanel = new GO.customfields.EnableBlocksPanel();
			this.addPanel(this.enableBlocksPanel);
		}
	},
	
	beforeLoad : function(remoteModelId, config){
		this.importPanel.setDisabled(!(remoteModelId>0));
	},
	
	setRemoteModelId : function(remoteModelId){
		
		if(GO.customfields){
			this.disableContactsCategoriesPanel.setModel(remoteModelId, "GO\\Addressbook\\Model\\Contact");
			this.disableCompaniesCategoriesPanel.setModel(remoteModelId, "GO\\Addressbook\\Model\\Company");
			this.enableBlocksPanel.setModel(remoteModelId,"GO\\Addressbook\\Model\\Addressbook");
		}
		
		this.removeDuplicatesButton.setDisabled(!remoteModelId);
		this.deleteAllItemsButton.setDisabled(!remoteModelId);
		
		return GO.addressbook.AddressbookDialog.superclass.setRemoteModelId.call(this, remoteModelId);
	}
});
