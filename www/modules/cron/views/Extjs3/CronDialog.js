GO.cron.CronDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'cronjob',
			title:GO.cron.lang.job,
			formControllerUrl: 'core/cron',
			updateAction : 'update',
			createAction : 'create',
			height:395,
			width:350,
			tools: [{
				id:'help',
				qtip: GO.cron.lang.exampleFormats+
					'<table>'+
					'<tr><td>*</td><td>'+GO.cron.lang.exampleFormat1Explanation+'</td></tr>'+
					'<tr><td>1</td><td>'+GO.cron.lang.exampleFormat2Explanation+'</td></tr>'+
					'<tr><td>1-5</td><td>'+GO.cron.lang.exampleFormat3Explanation+'</td></tr>'+
					'<tr><td>0-23/2</td><td>'+GO.cron.lang.exampleFormat4Explanation+'</td></tr>'+
					'<tr><td>1,2,3,13,22</td><td>'+GO.cron.lang.exampleFormat5Explanation+'</td></tr>'+
					'<tr><td>0-4,8-12</td><td>'+GO.cron.lang.exampleFormat6Explanation+'</td></tr>'+
					'<table>'
			}],
			select: false
		});
		
		GO.cron.CronDialog.superclass.initComponent.call(this);	
	},
	 	
	buildForm : function () {
			
		this.usersPanel = new GO.base.model.multiselect.panel({
      title:GO.cron.lang.users,	
      url:'cron/cronUser',
      columns:[{header: GO.cron.lang.user, dataIndex: 'name', sortable: true}],
      fields:['id','name'],
      model_id:this.remoteModelId
    });
		
		this.groupsPanel = new GO.base.model.multiselect.panel({
      title:GO.cron.lang.groups,	
      url:'cron/cronGroup',
      columns:[{header: GO.cron.lang.group, dataIndex: 'name', sortable: true}],
      fields:['id','name'],
      model_id:this.remoteModelId
    });
			
		this.nameField = new Ext.form.TextField({
			name: 'name',
			width:300,
			anchor: '100%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: GO.cron.lang.cronName
		});
		
		this.minutesField = new Ext.form.TextField({
			name: 'minutes',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: GO.cron.lang.minutes +' '+ GO.cron.lang.minutesExample
		});
		
		this.hoursField = new Ext.form.TextField({
			name: 'hours',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: GO.cron.lang.hours +' '+ GO.cron.lang.hoursExample
		});
		
		this.monthDaysField = new Ext.form.TextField({
			name: 'monthdays',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: GO.cron.lang.monthdays +' '+ GO.cron.lang.monthdaysExample
		});
		
		this.monthsField = new Ext.form.TextField({
			name: 'months',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: GO.cron.lang.months +' '+ GO.cron.lang.monthsExample
		});
		
		this.weekdaysField = new Ext.form.TextField({
			name: 'weekdays',
			width:300,
			anchor: '99%',
			maxLength: 100,
			allowBlank:false,
			fieldLabel: GO.cron.lang.weekdays +' '+ GO.cron.lang.weekdaysExample
		});
		
//		this.yearsField = new Ext.form.TextField({
//			name: 'years',
//			width:300,
//			anchor: '99%',
//			maxLength: 100,
//			allowBlank:false,
//			fieldLabel: GO.cron.lang.years +' '+ GO.cron.lang.yearsExample
//		});
//		
		this.activeCheckbox = new Ext.ux.form.XCheckbox({
			name: 'active',
			width:300,
			anchor: '100%',
			maxLength: 100,
//			allowBlank:false,
			boxLabel: GO.cron.lang.active
		});
		
		this.runOnceCheckbox = new Ext.ux.form.XCheckbox({
			name: 'runonce',
			width:300,
			anchor: '100%',
			maxLength: 100,
//			allowBlank:false,
			boxLabel: GO.cron.lang.runonce
		});
		
		this.jobCombo = new GO.form.ComboBox({
			hiddenName: 'job',
			fieldLabel: GO.cron.lang.job,
			store: GO.cron.jobStore,
			valueField:'class',
			displayField:'name',
			mode:'remote',
			anchor: '100%%',
			allowBlank: false,
			triggerAction: 'all',
			reloadOnExpand : true
		});
		
		this.jobCombo.on('select',function(combo, record, index ){
			if(record.data.selection && this.remoteModelId > 0){
				this.select = true;
				this.usersPanel.setDisabled(false);
				this.groupsPanel.setDisabled(false);
			}else{
				this.select = false;
				this.usersPanel.setDisabled(true);
				this.groupsPanel.setDisabled(true);
			}
		},this);
		
		
		this.timeFieldSet = new Ext.form.FieldSet({
			title: GO.cron.lang.timeFieldSetTitle,
			labelWidth: 140,
			autoHeight: true,
			border: true,
			collapsed: false,
			items:[
				this.minutesField,
				this.hoursField,
				this.monthDaysField,
				this.monthsField,
				this.weekdaysField
//				,
//				this.yearsField
			]
		});
			
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',
			layout:'form',
			labelWidth: 90,
			items:[
				this.nameField,
				this.jobCombo,
				this.timeFieldSet,
				this.activeCheckbox,
				this.runOnceCheckbox
      ]				
		});

		this.parameterPanel = new GO.cron.ParametersPanel();
	
    this.addPanel(this.propertiesPanel);
		this.addPanel(this.usersPanel);
		this.addPanel(this.groupsPanel);
		this.addPanel(this.parameterPanel);
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.usersPanel.setModelId(remoteModelId);
    this.groupsPanel.setModelId(remoteModelId);

		this.select = action.result.data.select || false;
		this.usersPanel.setDisabled(!this.select);
		this.groupsPanel.setDisabled(!this.select);

		this.parameterPanel.buildForm(action.result.data.paramsToSet);
		
	},
  afterSubmit: function(action){
    var noUserSelection = this.select; //this.usersPanel.disabled;
		var comboValue = this.jobCombo.getValue();
		var store = this.jobCombo.getStore();
		if(!store.loaded){
			store.load(function() {
				var record = store.getById(comboValue);
				noUserSelection = record.data.selection;
			});
		} else {
			var record = store.getById(comboValue);
			noUserSelection = record.data.selection;
		}
		
		this.usersPanel.setModelId(action.result.id);
		this.groupsPanel.setModelId(action.result.id);

		if(noUserSelection){
			this.select = true;
			this.usersPanel.setDisabled(false);
			this.groupsPanel.setDisabled(false);
		} else {
			this.select = false;
			this.usersPanel.setDisabled(true);
			this.groupsPanel.setDisabled(true);
		}
  }
});