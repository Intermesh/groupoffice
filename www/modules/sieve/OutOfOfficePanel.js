GO.sieve.OutOfOfficePanel = Ext.extend(Ext.Panel,{
	
	title:GO.sieve.lang.outOfOffice,
	layout:'form',
	autoScroll:true,
	
	accountId:0,
		
	initComponent : function(config){
		
		this.scheduleText = new GO.form.HtmlComponent({
			html:GO.sieve.lang.scheduleText,
			style:'padding:5px 0px'
		});
		
		this.scheduleActivateField = new Ext.form.DateField({
			name : 'ooo_activate',
			format : GO.settings['date_format'],
			width: 180,
			allowBlank : false,
			fieldLabel: GO.sieve.lang.activateAt,
			isChanged:false,
			listeners : {
				
				focus : {
					fn : function(field){
						this.scheduleActivateField.setMinValue(new Date());
						this.scheduleActivateField.setValue(new Date());
					},
					scope : this
				},
				
				change : {
					fn : function(field,newVal,oldVal){
						this.scheduleActivateField.isChanged=true;
						this.scheduleDeactivateField.setValue(newVal);
						this.scheduleDeactivateField.setMinValue(newVal);
					},
					scope : this
				}
			}
		});
		
		this.scheduleDeactivateField = new Ext.form.DateField({
			name : 'ooo_deactivate',
			format : GO.settings['date_format'],
			width: 180,
			allowBlank : false,
			fieldLabel: GO.sieve.lang.deactivateAt,
			listeners : {
				focus : {
					fn : function(){
						if(!this.scheduleActivateField.isChanged){
							this.scheduleDeactivateField.setMinValue(new Date());
							this.scheduleDeactivateField.setValue(new Date());
						}
					},
					scope : this
				}
			}
		});
		
		this.scheduleFieldset = new Ext.form.FieldSet({
			title: GO.sieve.lang.schedule,
			height:130,
			border: true,
			collapsed: false,
			labelWidth: 180,
			items:[this.scheduleText,this.scheduleActivateField,this.scheduleDeactivateField],
			style: 'margin-right:10px; margin-bottom:5px;'
		});
		
		this.activateText = new GO.form.HtmlComponent({
			html:GO.sieve.lang.activateText,
			style:'padding:5px 0px'
		});
		
		this.activateCheck = new Ext.ux.form.XCheckbox({
				hideLabel: true,
				boxLabel: GO.sieve.lang.activate,
				name: 'ooo_script_active'
			});
		
		this.activateFieldset = new Ext.form.FieldSet({
			title: GO.sieve.lang.activate,
			height:130,
			border: true,
			collapsed: false,
			items:[this.activateText,this.activateCheck]
		});
		
//		this.subjectText = new GO.form.HtmlComponent({
//			html:GO.sieve.lang.subjectText,
//			style:'padding:5px 0px'
//		});
//		
//		this.subjectField = new Ext.form.TextArea({
//			name: 'ooo_subject',
//			allowBlank:false,
//			anchor:'100%',
//			height:20,
//			width: 300,
//			hideLabel: true
//		});
		
		this.messageText = new GO.form.HtmlComponent({
			html:GO.sieve.lang.messageText,
			style:'padding:5px 0px'
		});
		
		this.messageField = new Ext.form.TextArea({
			name: 'ooo_message',
			allowBlank:false,
			anchor:'100%',
			height:130,
			width: 300,
			hideLabel: true,
			setValue: function(v){
//				this.messageField.superclass.setValue.call(this,v);
				Ext.form.TextArea.prototype.setValue.call(this,GO.util.HtmlDecode(Ext.util.Format.htmlDecode(v)));
			}
		});
		
		this.messageFieldset = new Ext.form.FieldSet({
			title: GO.sieve.lang.message,
			autoHeight: true,
			border: true,
			collapsed: false,
			items:[
//				this.subjectText,this.subjectField,
				this.messageText,this.messageField]
		});
		
		
		this.aliassesText = new GO.form.HtmlComponent({
			html:GO.sieve.lang.aliassesText,
			style:'padding:5px 0px'
		});
		
		this.aliassesField = new Ext.form.TextArea({
			name: 'ooo_aliasses',
			allowBlank:true,
			anchor:'100%',
			height:40,
			width: 300,
			hideLabel: true
		});
			
		this.nDaysText = new GO.form.HtmlComponent({
			html:GO.sieve.lang.nDaysText,
			style:'padding:5px 0px'
		});
		
		this.nDaysField = new GO.form.NumberField({
			name: 'ooo_days',
			value: 3,
			allowBlank:false,
			width:70,
			decimals:0,
			fieldLabel:GO.sieve.lang.days
		});
		
		this.advancedFieldset = new Ext.form.FieldSet({
			title: GO.sieve.lang.advancedOptions,
			autoHeight: true,
			border: true,
			collapsed: true,
			collapsible: true,
			labelWidth: 180,
			items:[this.aliassesText,this.aliassesField,this.nDaysText,this.nDaysField]
		});
			
		this.scriptNameField = new Ext.form.Hidden({
			name: 'ooo_script_name',
		});
		
		this.ruleNameField = new Ext.form.Hidden({
			name: 'ooo_rule_name',
		});
					
		this.indexField = new Ext.form.Hidden({
			name: 'ooo_script_index',
		});
			
		this.items = [
			this.scriptNameField,
			this.ruleNameField,
			this.indexField,
			{
				layout:'column',
				defaults:{columnWidth:.5, cls: 'go-form-panel', padding:'10'},
				items:[
					this.scheduleFieldset,
					this.activateFieldset
				]
			},
			this.messageFieldset,
			this.advancedFieldset
		];

		GO.sieve.OutOfOfficePanel.superclass.initComponent.call(this,config);
	},
	
	disableFields : function(disable){
		this.scheduleActivateField.setDisabled(disable);
		this.scheduleDeactivateField.setDisabled(disable);
		this.messageField.setDisabled(disable);
		this.aliassesField.setDisabled(disable);
		this.scriptNameField.setDisabled(disable);
		this.ruleNameField.setDisabled(disable);
		this.activateCheck.setDisabled(disable);
		this.indexField.setDisabled(disable);
		this.nDaysField.setDisabled(disable);
//		this.subjectField.setDisabled(disable);
//
//
//		if(GO.config.){
//			this.daysFieldset.setVisible();
//		}

	},
		
	setAccountId : function(account_id){
		this.setDisabled(!account_id);
		this.accountId=account_id;
	}

});