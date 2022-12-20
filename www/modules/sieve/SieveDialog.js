/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SieveDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @author WilmarVB <wilmar@intermesh.nl>
 */
GO.sieve.SieveDialog = function(config) {
	if (!config) {
		config = {};
	}
	
	this.rgMethod = new Ext.form.RadioGroup({
		fieldLabel: '<b>'+t("For incoming emails", "sieve")+'</b>',
		columns: 1,
		vertical: true,
		anchor: '100%',
		value:'anyof',
		items: [
				{
					boxLabel: t("that meet the following criteria", "sieve"), name: 'join', inputValue: 'allof'
				},
				{
					boxLabel: t("that meet at least one of the following criteria", "sieve"), name: 'join', inputValue: 'anyof'
				},
				{
					boxLabel: t("all incoming emails", "sieve"), name: 'join', inputValue: 'any'
				}
		],
		listeners:{
			scope:this,
			change:function(){
				if(this.rgMethod.getValue()){
					if(this.rgMethod.getValue().inputValue == 'any')
					{
						this.criteriaLabel.hide();
						this.criteriumGrid.hide();
					}
					else
					{
						if(this.criteriumGrid.store.getCount() > 0)
						{
							if(this.criteriumGrid.store.getAt(0).data.test == 'true')
							{
								this.criteriumGrid.store.removeAll();
							}
						}
						this.criteriaLabel.show();
						this.criteriumGrid.show();
					}
				}
			}
		}
	})

	this.nameField = new Ext.form.TextField({
		fieldLabel:t("Name"),
		name:'rule_name',
		width: 360,
		allowBlank:false
	});

	this.formPanel = new Ext.FormPanel({
		style:'padding:' + dp(16) + "px",
		autoHeight:true,
		border:false,
		labelWidth:200,
		url: GO.url('sieve/sieve/rule'),
		baseParams:{},
		items:[this.nameField,{
				name:'active',
				checked:true,
				xtype:'checkbox',
				boxLabel:t("Activate this filter", "sieve")
			},
			this.rgMethod,
			this.criteriaLabel = new Ext.form.Label({text: '...'+t("meeting these criteria", "sieve")+':',	width:'100%',	style: 'padding-bottom: 10px; font-weight:bold;'})
		]
	});

	// Make tests Grid and Panel
	this.criteriumGrid = new GO.sieve.CriteriumGrid();

	// Make action Grid and Panel
	this.actionGrid = new GO.sieve.ActionGrid();
	this.actionGrid.on('rowdblclick', function(grid, index, e){
//		var record = this.actionGrid.store.getAt(index);
		this.actionGrid.showActionCreatorDialog(index,this._accountId);
	},this);
	
	this.currentScriptName = '';
	this.currentRuleName = '';
	this.currentScriptIndex = 0;
	this.currentAccountId = 0;

	config.items = {
		autoScroll:true,
		layout:'anchor',
		items:[
				this.formPanel,
				this.criteriumGrid,
				new Ext.form.Label({text:t("...execute the following actions", "sieve"), width:'100%', style: 'padding-bottom: 10px; margin: 5px; font-weight:bold;'}),
				this.actionGrid
			]
		};
			
	config.collapsible = true;
	config.maximizable = true;
	config.layout = 'fit';
	config.modal = false;
	config.resizable = true;
	config.width = 700;
	config.height = 640;
	config.closeAction = 'hide';
	config.title = t("Rules", "sieve");
	config.buttons = [{
		text : t("Save changes", "sieve"),
		handler : function() {
			if(this.actionGrid.store.getCount() == 0 || (this.criteriumGrid.store.getCount() == 0 && this.rgMethod.getValue().inputValue != 'any'))
				alert(t("One or both grids are empty.", "sieve"));
			else
				this.saveAll();
		},
		scope : this
	}, {
		text : t("Cancel"),
		handler : function() {
			this.hide();
		},
		scope : this
	}];

	GO.sieve.SieveDialog.superclass.constructor.call(this, config);
	
	this.addEvents({
		'save' : true
	});
}
Ext.extend(GO.sieve.SieveDialog, GO.Window, {

	_accountId : 0,

	focus : function(){
		this.nameField.focus();
	},

	show : function(script_index,script_name, account_id) {

			GO.sieve.SieveDialog.superclass.show.call(this);
			
			this.formPanel.baseParams.script_index = script_index;
			this._accountId = this.formPanel.baseParams.account_id = account_id;
			this.formPanel.baseParams.script_name = script_name;

			if(script_index > -1)
			{	
				this.title = t("Edit rule", "sieve");
	
				this.formPanel.load({
					success:function(form, action)
					{
						this.rgMethod.setValue(action.result.data.join);
						this.actionGrid.store.loadData(action.result);
						this.criteriumGrid.store.loadData(action.result);
					},
					failure:function(form, action)
					{
						GO.errorDialog.show(action.result.feedback)
					},
					scope: this
				});		
			} 
			else
			{
				this.title = t("New rule", "sieve");
				this.formPanel.form.setValues({
					'rule_name' : '',
					'disabled' : false
				});
				this.resetGrids();
				this.rgMethod.setValue('anyof');
				
				GO.request({
					url: 'sieve/sieve/accountAliases',
					params: {
						'account_id' : account_id
					},
					success:function(options, response, result) {
						this.actionGrid.accountAliasesString = result.data.aliases;
					},
					scope: this
				});
				
			}
	},
	
	saveAll : function() {

		var criteriaData = this.criteriumGrid.getGridData();
		var actionData = this.actionGrid.getGridData();
		
		// Check for spam
		if(this.checkIsSpamRule(criteriaData,actionData)){
			
			// Check if a STOP is already applied.
			if(!this.checkHasStopAction(actionData)){
				//Add a STOP action at the end
				var stopAction = {
					addresses:'',
					days:'',
					id:1,
					reason:'',
					target:'',
					text:'Stop',
					type:'stop',
				};
				
				actionData.push(stopAction);
			}
			
		}

		this.formPanel.form.submit({
			url: GO.url('sieve/sieve/submitRules'),
			params : {
				'criteria' : Ext.encode(criteriaData),
				'actions' : Ext.encode(actionData)
			},
			success : function(form, action) {
					this.hide();
					this.body.unmask();
			},
			failure: function(form, action) {
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));			
				} else {
					Ext.MessageBox.alert(t("Error"), action.result.feedback);
				}
				this.body.unmask();
			},
			scope : this
		});
	},
	
	/**
	 * Check if the current rule is a spam message test
	 * 
	 * @param array criteria
	 * @param array action
	 * @returns {Boolean}
	 */
	checkIsSpamRule : function(criteria,action){
		var isSpam = false;
		
		for(var i=0, tot=criteria.length; i < tot; i++) {
			if(criteria[i].test == 'header' && criteria[i].type == 'contains' && criteria[i].arg1 == 'X-Spam-Flag'){
				isSpam = true;
			}
			if(criteria[i].test == 'header' && criteria[i].type == 'contains' && criteria[i].arg1 == 'Subject' && criteria[i].arg2 == 'spam'){
				isSpam = true;
			}
		}
		return isSpam;
	},
	
	/**
	 * Check if the current action has a "STOP"
	 * 
	 * @param array action
	 * @returns {Boolean} 
	 */
	checkHasStopAction : function(action){
		
		var hasStop = false;
		
		for(var i=0, tot=action.length; i < tot; i++) {
//			if(action[i].text == "Stop" && action[i].type == "stop"){
			if(action[i].type == "stop"){
				hasStop = true;
			}
		}
				
		return hasStop;
	},
	

	resetGrids : function(){
		this.actionGrid.store.removeAll();
		this.criteriumGrid.store.removeAll();   
	}	
});
