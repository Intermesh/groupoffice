/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 */
 
GO.customfields.ManageBlockDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'id',
			goDialogId:'block',
			title:GO.customfields.lang['block'],
			formControllerUrl: 'customfields/block',
			height:200
		});
		
		GO.customfields.ManageBlockDialog.superclass.initComponent.call(this);	
	},
	buildForm : function () {

		this.propertiesPanel = new Ext.Panel({
			border: false,
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype: 'textfield',
			  name: 'name',
				anchor: '100%',
			  allowBlank:false,
			  fieldLabel: GO.lang.strName
			}, this.selectCustomfieldBox = new GO.form.ComboBox({
				fieldLabel: GO.customfields.lang['customfield'],
				hiddenName:'field_id',
				anchor:'-20',
				store: new GO.data.JsonStore({
					url:GO.url('customfields/blockField/selectStore'),
					totalProperty: "total",
					root: "results",
					id: "id",
					fields:[
						'id',
						'full_info',
						'name',
						'datatype',
						'extends_model'
					]
				}),
				valueField:'id',
				displayField:'full_info',
				mode: 'remote',
				triggerAction: 'all',
				editable: false,
				selectOnFocus:true,
				forceSelection: true
			}),this.datatypeField = new GO.form.PlainField({
				name: 'datatype',
				fieldLabel: GO.customfields.lang['cfDatatype']
			}),this.extendsModelField = new GO.form.PlainField({
				name: 'extends_model',
				fieldLabel: GO.customfields.lang['cfUsedIn']
			})]
		});

		this.selectCustomfieldBox.on('change',function(combo,newValue,oldValue){
			var record = combo.store.getById(newValue);
			this._updateModelNames(record);
		}, this);

		this.selectCustomfieldBox.store.on('load', function(){
			var fieldId = this.selectCustomfieldBox.getValue();
			if (fieldId > 0)
				var record = this.selectCustomfieldBox.store.getById(fieldId);
			if (!GO.util.empty(record))
				this._updateModelNames(record);
		}, this);

		this.addPanel(this.propertiesPanel);	
 
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.selectCustomfieldBox.store.load();
	},

	_updateModelNames : function(customFieldRecord) {
		this.datatypeField.setValue(GO.customfields.lang[customFieldRecord.data['datatype']]);
		this.extendsModelField.setValue(GO.customfields.lang[customFieldRecord.data['extends_model']]);
	}
});