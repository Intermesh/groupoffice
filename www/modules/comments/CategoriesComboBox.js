/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

GO.comments.CategoriesComboBox = Ext.extend(GO.form.ComboBox, {
	initComponent : function(){
		Ext.apply(this, {
			fieldLabel : GO.comments.lang.category,	
//			hideTrigger : !GO.settings.modules.comments.write_permission,
			hiddenName : 'category_id',
			store:GO.comments.categoriesStore,
			valueField:'id',
			displayField:'name',
			mode: 'remote',
			triggerAction: 'all',
			editable: false,
			selectOnFocus:true,
			forceSelection: GO.comments.categoryRequired,
			allowBlank: !GO.comments.categoryRequired
//					,
//					pageSize: parseInt(GO.settings.max_rows_list),
//					disabled:!GO.settings.modules.projects.write_permission
		});

		Ext.form.TwinTriggerField.prototype.initComponent.call(this);
		
	},
	
	render : function( container, position ) {
		GO.comments.CategoriesComboBox.superclass.render.call(this,container,position);
		if (!GO.settings.modules.comments.write_permission)
			this.triggers[0].hide();
	},
	
	getTrigger : Ext.form.TwinTriggerField.prototype.getTrigger,
	initTrigger : Ext.form.TwinTriggerField.prototype.initTrigger,
	trigger1Class : 'x-form-edit-trigger',
	//hideTrigger1 : true,
	onViewClick : Ext.form.ComboBox.prototype.onViewClick.createSequence(function() {
		//this.triggers[0].setDisplayed(true);
	}),
	onTrigger2Click : function() {
		this.onTriggerClick();
	},
	onTrigger1Click : function() {
		if(!this.disabled) {//} && GO.settings.modules.comments.write_permission){

			if(!GO.comments.manageCategoriesDialog){
				GO.comments.manageCategoriesDialog = new GO.comments.ManageCategoriesDialog();
				GO.comments.manageCategoriesDialog.on('save', function(){
						GO.comments.categoriesStore.reload();
					}, this);
			}

			GO.comments.manageCategoriesDialog.show();
		}
	},
	setValue : function(v){
		GO.form.ComboBoxReset.superclass.setValue.call(this, v);
		if(this.rendered)
		{
			//this.triggers[0].setDisplayed(v!='');
		}
	},
	afterRender:function(){
		GO.form.ComboBoxReset.superclass.afterRender.call(this);
		if (Ext.isIE8) this.el.setTop(1);
	}
});