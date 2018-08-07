go.modules.community.files.ShareDialog = Ext.extend(go.form.Dialog, {
	stateId: 'files-shareDialog',
	title: t("Share"),
	entityStore: go.Stores.get("Node"),
	width: dp(680),
	height: 600,
	
	initComponent: function() {
		this.buttons = [
			'->', this.shareBtn = new Ext.Button({
				cls: "raised",
				text: t("Share"),
				handler: this.share,
				scope: this
			})];
		go.modules.community.files.ShareDialog.superclass.initComponent.call(this);
	},
	
	initFormItems: function () {
	
		return [{
				xtype: 'fieldset',
				autoHeight: true,
				items: [
					this.shareCbx = new go.form.Switch({
						boxLabel: t('Enable shareable link for public access'),
						hideLabel:true,
						anchor: '100%',
						listeners:{
							check: function(cbx,checked){
								this.shareLinkField[checked?'show':'hide']();
								this.expireField[checked?'show':'hide']();
								this.doLayout();
							},
							scope:this
						}
					}),
					this.shareLinkField = new Ext.form.TriggerField({
						name: 'link',
						hideLabel:true,
						anchor: '100%',
						hidden:true,
						style:{
							"padding-right":"80px"
						},
						triggerConfig: {
							tag: "button", 
							type: "button", 
							tabindex: "-1",
							style:{
								width:"80px"
							},
							cls: "x-form-text-trigger",
							html: t("Copy link")
						}				
					}),
					this.expireField = new Ext.form.CompositeField({
						hideLabel:true,
						hidden:true,
						items:[
							this.shareExpireCbx = new Ext.ux.form.XCheckbox({
								boxLabel: t('Expire at:'),
								hideLabel:true,
								anchor: '100%',
								listeners:{
									check: function(cbx,checked){


									},
									scope:this
								}
							}),
							this.shareExpireDate = new Ext.form.DateField({
								name : 'tokenExpiresAt',
								allowBlank : true
							})
						],
						anchor: '100%'
					})
				]
			},
			this.sharePanel = new go.modules.core.core.SharePanel({
				hideLabel: true,
				autoHeight: true,
			})
		];
	},
	/**
	 * Set the acl Id for the permissions panel/grid
	 * 
	 * @param int aclId
	 */
	setAcl : function(aclId) {
		this.sharePanel.load(aclId);
		return this;
	},
	
	share: function() {
		var node;
		if(node.isShared) {
			//just update ACL
			go.Stores.get('Acl').get(node.aclId).set({groups:this.sharePanel.groups})
		} else if(this.sharePanel.hasChanges()) {
			// create new ACL
			var newAclId = '#'+Ext.id(),
				 items = {};
			items[newAclId] = {groups:this.sharePanel.groups};
			go.Stores.get('Acl').set({create: items});
			// update node with new ACL ID
			node.aclId = newAclId;
		}
		if(this.shareCbx.getValue() == '1') {
			// save token
			node.token = '000000';
			if(this.shareExpireCbx.getValue() == 1) {
				node.tokenExpiresAt = this.shareExpireDate.getValue();
			}
		}
	}
		
});