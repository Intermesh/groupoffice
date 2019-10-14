/**
 * var aclId = 1;
 * var dlg = new go.permissions.ShareWindow();
 * dlg.load(aclId).show();
 */
go.permissions.ShareWindow = Ext.extend(go.form.Dialog, {
	title: t('Share'),
	entityStore: null,
	height: dp(600),
	width: dp(1000),
	modal: true,
	showLevels: true,
	
	initComponent : function() {
		this.buttons = [
			'->', this.shareBtn = new Ext.Button({
				cls: "raised",
				text: t("Share"),
				handler: function() {this.submit();},
				scope: this
			})];
		
		go.permissions.ShareWindow.superclass.initComponent.call(this);
	},
	initFormItems: function () {
		return [
			this.sharePanel = new go.permissions.SharePanel({
				title: false,
				anchor: '100% -' + dp(32),
				hideLabel: true,
				name: "acl",
				showLevels: this.showLevels
			})
		];
	},
	onSubmit : function() {
		//don't route to page
	}
});