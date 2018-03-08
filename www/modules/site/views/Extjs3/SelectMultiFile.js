GO.site.SelectMultiFile = Ext.extend(Ext.form.TriggerField,{

	triggerClass : 'fs-form-site-multifile-select',

	multifileDialog : false,
	customfield: false,
	
	model_id:false,
	
	/**
	 * This object MUST be created inside a formPanel because the formPanel is 
	 * needed to retreive the correct id of the Model.
	 * The model also needs to have the column "id" as key.
	 */
	initComponent : function(){

		GO.site.SelectMultiFile.superclass.initComponent.call(this);
		
		this.on('render', function(){
			
			// Find the formpanel 
			var formPanel = this.findParentByType('form');

			formPanel.on('actioncomplete', function(form, action){
				this.model_id=action.result.data.id; // Checks for the "id" property in the result of the store load. This only works when the Model has the "id" field as key.
			}, this);
		}, this);
		
		
	},

	onTriggerClick : function(){
		
		if(!this.multifileDialog){
			this.multifileDialog = new GO.site.MultifileDialog();
			this.multifileDialog.on('hide',function(){
				this.setRawValue(GO.site.lang.multifileSelectValue.replace('%s', this.multifileDialog.multifileView.store.getCount()))
			},this);
		}
		this.multifileDialog.show(this.model_id,this.customfield.customfield_id);
	}

});

Ext.ComponentMgr.registerType('siteselectmultifile', GO.site.SelectMultiFile);