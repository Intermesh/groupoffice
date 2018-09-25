go.modules.community.files.OpenWithDialog = Ext.extend(go.form.Dialog, {
	stateId: 'files-openWithDialog',
	title: t("Open with"),
	entityStore: go.Stores.get("Node"),
	width: 450,
	height: 150,
	
	initFormItems: function () {
		var items = [
			
		];
		return items;
	},
	
	show: function() {
		
		//Get the blobInfo and load the list based on the blob type
		
		console.log(this.entityStore);
		
		go.modules.community.files.OpenWithDialog.superclass.show.call(this);
	}
});