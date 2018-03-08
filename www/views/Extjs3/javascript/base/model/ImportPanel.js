// TODO: Search for the parent to be a form and set it to uploadFiles = true

GO.base.model.ImportPanel = Ext.extend(Ext.Panel, {
	
	importBaseParams: {},
	fileTypes:[],
	controllers:[],
	form:undefined,
	
	initComponent : function(){
		Ext.apply(this, {
			title:GO.lang.cmdImport,
			layout: 'form',
			items: [],
			defaults: {anchor:'100%'},
			border: false,
			labelWidth: 150,
			toolbars: [],
			cls:'go-form-panel',
			listeners:{
				render : function() {
					this.form = this.findParentByType(Ext.form.FormPanel);
					this.form.baseParams.importBaseParams = Ext.encode(this.importBaseParams);
					this.form.form.fileUpload = true;
					
					this.form.form.on('actioncomplete', function(form, action){
						if(action.type=='submit'){
							this.fileSelector.clearQueue();
						}						
					},this);
					
					this.cmbController.selectFirst();
					this.cmbFileType.selectFirst();
					
					this.cmbFileType.setVisible(this.fileTypeStore.getCount() > 1);
					this.cmbController.setVisible(this.controllersStore.getCount() > 1);
				},
				show : function(){
					this.fileSelector.clearQueue();
				},
				scope:this
			}
		});
		
		this.fileTypeStore = new Ext.data.ArrayStore({
			storeId: 'fileTypeStore',
			idIndex: 0,
			fields:['value','label'],
			data: this.filetypes
		});
				
		this.controllersStore = new Ext.data.ArrayStore({
			storeId: 'controllersStore',
			idIndex: 0,
			fields:['value','label'],
			data: this.controllers
		});
		
		this.txtDelimiter = new Ext.form.TextField({
			name: 'delimiter',
			fieldLabel: GO.addressbook.lang.cmdFormLabelValueSeperated,
			allowBlank: false,
			value: GO.settings.list_separator
		});
		
		this.txtEnclosure = new Ext.form.TextField({
			name: 'enclosure',
			fieldLabel: GO.addressbook.lang.cmdFormLabelValueIncluded,
			allowBlank: false,
			value: GO.settings.text_separator
		});
		
		this.cmbFileType = new GO.form.ComboBox({
			hiddenName: 'fileType',
			fieldLabel: GO.addressbook.lang.cmdFormLabelFileType,
			store: this.fileTypeStore,
			valueField:'value',
			displayField:'label',
			mode:'local',
			allowBlank: false,
			triggerAction: 'all'
		});
		
		this.cmbController = new GO.form.ComboBox({
			hiddenName: 'controller',
			fieldLabel: GO.lang.cmdImport,
			store: this.controllersStore,
			valueField:'value',
			displayField:'label',
			mode:'local',
			allowBlank: false,
			triggerAction: 'all'
		});
		
		this.fileSelector = new GO.form.UploadFile({
			inputName: 'files',
			fieldLabel: GO.lang.upload,
			max:1
		});

		this.items.push(this.txtDelimiter);
		this.items.push(this.txtEnclosure);
		this.items.push(this.cmbFileType);
		this.items.push(this.cmbController);
		this.items.push(this.fileSelector);
		
		Ext.Panel.superclass.initComponent.call(this);
	}
});