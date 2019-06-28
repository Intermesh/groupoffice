go.modules.comments.Settings = Ext.extend(go.Window, {
	title: t("Labels"),
	maximizable:false,
	iconCls: 'ic-label',
	initComponent: function () {
		
		Ext.apply(this,{
			width:dp(300),
			height:dp(580),
			layout:'fit',
			closeAction:'hide',
			items: [
				this.labelGrid = new go.modules.comments.LabelGrid()
			],
			buttons:[{
				text: t('Save'),
				handler: this.submit,
				scope:this
			}]
		});
		
		go.modules.comments.Settings.superclass.initComponent.call(this);
		
	},

	show: function(){

		go.modules.comments.Settings.superclass.show.call(this);
				
		this.labelGrid.store.load();
	},
	
	submit : function(){
		var me = this;
		this.labelGrid.store.save().then(function() {
			me.close();
		});
		//go.Db.store('CommentLabel').set({update:items});
		// CommitChanges does not save to server????
		//this.labelGrid.store.commitChanges();

	}
});
