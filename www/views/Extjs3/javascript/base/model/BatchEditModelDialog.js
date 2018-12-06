GO.base.model.BatchEditModelDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
	grid: false,
	editors : [],
	width: 800,
	
	initComponent : function(){
		
		Ext.apply(this, {
			title:t("Batch edit"),
			formControllerUrl: 'batchEdit',
			loadOnNewModel:false
		});
		
		GO.base.model.BatchEditModelDialog.superclass.initComponent.call(this);	
	},

	setModels : function(model_name, keys, primaryKey, editors,exclude){
		
		this.store.baseParams.primaryKey=primaryKey;
		this.store.baseParams.keys = Ext.encode(keys);
		
		this.formPanel.baseParams.model_name=model_name;
		this.store.baseParams.model_name=model_name;
		//this.formPanel.baseParams.exclude=exclude;
		this.store.baseParams.exclude=exclude;
		this.formPanel.baseParams.keys=Ext.encode(keys);
		this.formPanel.baseParams.primaryKey=primaryKey;
		this.editors = editors;
	},
	
	show : function(){
		this.store.load();
		GO.base.model.BatchEditModelDialog.superclass.show.call(this);	
	},
	
	setEditor : function(record){
		var col = this.editGrid.getColumnModel().getColumnById('value');
		var config ={};
		if(!GO.util.empty(record.get('regex')))
			config = {regex: new RegExp(record.get('regex'),record.get('regex_flags'))};
		
		var colName = record.get('name');
		if (this.editors[colName]) {
			var editor = new this.editors[colName](config);
			col.setEditor(editor);
		}else {
			var field = GO.base.form.getFormFieldByType(record.get('gotype'), record.get('name'), config);
			col.setEditor(field);
		}		
		
	},
	
	afterSubmit : function(action){
		console.log(action);
	},
	
	getSubmitParams : function(){
		return {data:Ext.encode(this.editGrid.getGridData())}
	},
	
	buildForm : function(){
		
		var checkColumn = new GO.grid.CheckColumn({
			header:t("Replace"),
			id:'replace',
			dataIndex: 'replace',
			width: 80,
			sortable:false,
			hideable:false,
			disabled_field: 'mergeable',
			isDisabled: function(record) {
				return !record.get(this.disabled_field);
			}
		});
		
	
		var fields ={
			fields:['name','label','replace','value','gotype','regex','regex_flags', 'replace', 'mergeable', 'has_data', 'multiselect', 'customfieldtype'],
			columns:[
				checkColumn,
				{
					header:t("Label"),
					dataIndex: 'label',
					sortable:false,
					hideable:false,
					editable:false,
					id:'label',
					width: 200
				},{
					header:t("Value"),
					dataIndex: 'value',
					sortable:false,
					hideable:false,
					editable:true,
					width: 300,
					editor: new Ext.form.TextField({}),
					renderer: {
						fn: function(value, metaData, record, rowIndex, colIndex, store) {

							//get Column
							var col = this.editGrid.getColumnModel().getColumnAt(colIndex);

							var editor =col.getEditor();
//							if(editor.field) {
//								editor = editor.field;
//							}
							// check of column is set and has an editor with a displayField
							if(editor.queryValuesDelimiter) {
								//superbox select
								value = value.replace(col.getEditor().queryValuesDelimiter, ', ');
							}else	if(!GO.util.empty(col) && !GO.util.empty(editor.displayField) && !GO.util.empty(value)) {
								var editorRec = editor.getStore().query(editor.valueField,value);
								value = editorRec.items[0].get(editor.displayField);
							} else if(record.get('has_data') && value == ""){
								value = '<span class="x-item-disabled" >'+ t("Has data") +'<span>';
							}
							return value;
						},
						scope: this
					},
					id:'value'
				}
			]
		};
		
		this.store = new GO.data.JsonStore({
			url: GO.url('batchEdit/attributesStore'),
			baseParams:{
				model_name: '' // config.modelType example: GO\\Addressbook\\Model\\Company
			},
			fields: fields.fields,
			//fields: ['name','label','edit','value','gotype'],
			remoteSort: true
		});
	
		this.store.on('load', function() {
			
		}, this)
	
		
		var columnModel =  new Ext.grid.ColumnModel({
			defaults:{
				sortable:true
			},
			columns:fields.columns
		});

		this.editGrid = new GO.grid.EditorGridPanel({
			fields:fields.fields,
			store:this.store,
			cm:columnModel,
			view:new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: t("No items to display")
			}),
			sm:new Ext.grid.RowSelectionModel(),
			loadMask:true,
			clicksToEdit:1,
			listeners:{
				beforeedit:function(e){			
					this.setEditor(e.record);
					return true;
				},scope:this,
				afteredit:function(e) {
					var t = e.record.get('gotype');

					e.record.set('replace',true);
					
					if(t=='date' || t=='unixtimestamp' || t=='unixdate')
						e.record.set(e.field,e.value.format(GO.settings.date_format));
				}
			}
		});	
			
		this.addPanel(this.editGrid);
	}
});

GO.base.model.showBatchEditModelDialog=function(model_name, keys, primaryKey, editors,exclude,title){
	
	if (keys.length<=0) {
			Ext.Msg.alert(t("You didn't select anything"), t("Select at least one item"));
			return false;
	}
	
	if(!GO.base.model.batchEditModelDialog){
		GO.base.model.batchEditModelDialog = new GO.base.model.BatchEditModelDialog();
	}
	
	if(title){
		GO.base.model.batchEditModelDialog.setTitle(title);
	}
	
	GO.base.model.batchEditModelDialog.setModels(model_name, keys, primaryKey, editors,exclude);
	GO.base.model.batchEditModelDialog.show();
	return GO.base.model.batchEditModelDialog;
}
