GO.sieve.OutOfOfficePanel = Ext.extend(Ext.Panel,{
	
	title:t("Out of office", "sieve"),
	layout:'form',
	autoScroll:true,
	hideMode: "offsets",
	
	accountId:0,
		
	initComponent : function(config){
		
		this.scheduleText = new GO.form.HtmlComponent({
			html:t("In here you can schedule when the \"Out of office\" message needs to be activated.", "sieve"),
			style:'padding:5px 0px'
		});
		
		this.scheduleActivateField = new Ext.form.DateField({
			name : 'ooo_activate',
			format : GO.settings['date_format'],
			width: 180,
			allowBlank : true,
			fieldLabel: t("Activate at", "sieve"),
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
			allowBlank : true,
			fieldLabel: t("Deactivate after", "sieve"),
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
			title: t("Schedule", "sieve"),
			height:130,
			collapsed: false,
			labelWidth: 180,
			items:[this.scheduleText,this.scheduleActivateField,this.scheduleDeactivateField]
		});
		
		this.activateText = new GO.form.HtmlComponent({
			html:t("Activate this filter by checking the checkbox below.", "sieve"),
			style:'padding:5px 0px'
		});
		
		this.activateCheck = new Ext.ux.form.XCheckbox({
				hideLabel: true,
				boxLabel: t("Activate filterset", "sieve"),
				name: 'ooo_script_active'
			});
		
		this.activateFieldset = new Ext.form.FieldSet({
			title: t("Activate filterset", "sieve"),
			collapsed: false,
			items:[this.activateText,this.activateCheck]
		});
		
//		this.subjectText = new GO.form.HtmlComponent({
//			html:t("Fill in the subject of the response in the field below.", "sieve"),
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
			html:t("Fill in your message in the field below.", "sieve"),
			style:'padding:5px 0px'
		});
		
		this.messageField = new Ext.form.TextArea({
			name: 'ooo_message',
			allowBlank:true,
			anchor:'100%',
			height: 300,
			grow: true,
			hideLabel: true,
			setValue: function(v){
//				this.messageField.superclass.setValue.call(this,v);
				Ext.form.TextArea.prototype.setValue.call(this,GO.util.HtmlDecode(Ext.util.Format.htmlDecode(v)));
			}
		});
		
		this.messageFieldset = new Ext.form.FieldSet({
			title: t("Message", "sieve"),
			autoHeight: true,
			collapsed: false,
			items:[
//				this.subjectText,this.subjectField,
				this.messageText,this.messageField]
		});
		
		
		this.aliassesText = new GO.form.HtmlComponent({
			html:t("Fill in the aliasses on which this message also needs to apply to. If you have multiple aliasses, then separate each alias with a comma (,).", "sieve"),
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
			
		this.Senders will only be notified periodically. You can set the number of days below. = new GO.form.HtmlComponent({
			html:t("Senders will only be notified periodically. You can set the number of days below.", "sieve"),
			style:'padding:5px 0px'
		});
		
		this.nDaysField = new GO.form.NumberField({
			name: 'ooo_days',
			value: 3,
			allowBlank:false,
			width:70,
			decimals:0,
			fieldLabel:t("Reply every x days", "sieve")
		});
		
		this.advancedFieldset = new Ext.form.FieldSet({
			title: t("Advanced options", "sieve"),
			autoHeight: true,
			border: true,
			collapsed: true,
			collapsible: true,
			labelWidth: 180,
			items:[this.aliassesText,this.aliassesField,this.Senders will only be notified periodically. You can set the number of days below.,this.nDaysField]
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
