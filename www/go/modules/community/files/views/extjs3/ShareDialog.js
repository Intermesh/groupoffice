go.modules.community.files.ShareDialog = Ext.extend(go.form.FormWindow, {
	stateId: 'files-shareDialog',
	title: t("Share"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		
		this.shareCbx = new Ext.ux.form.XCheckbox({
			boxLabel: t('Make shareable link available for external users'),
			hideLabel:true,
			anchor: '100%',
			listeners:{
				check: function(cbx,checked){


				},
				scope:this
			}
		});
		
		this.shareExpireCbx = new Ext.ux.form.XCheckbox({
			boxLabel: t('Expire link on:'),
			hideLabel:true,
			anchor: '100%',
			listeners:{
				check: function(cbx,checked){


				},
				scope:this
			}
		});
		
		this.shareExpireDate = new Ext.form.DateField({
			name : 'tokenExpiresAt',
			width : 120,
			format : GO.settings['date_format'],
			allowBlank : true
		});
		
		this.shareLinkField = new Ext.form.TriggerField({
			name: 'link',
			hideLabel:true,
			anchor: '100%',
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
				cls: "x-form-trigger x-form-text-trigger",
				html: t("Copy link")
			}				
		});
		
		this.aclPanel = new GO.grid.PermissionsPanel();
		
		var items = [{
				xtype: 'fieldset',
				title: t("External users"),
				autoHeight: true,
				items: [
					this.shareCbx,
					this.shareLinkField,
					{
						xtype: 'compositefield',
						hideLabel:true,
						items:[
							this.shareExpireCbx,
							this.shareExpireDate
						],
						anchor: '100%'
					}
				]
			},{
				xtype: 'fieldset',
				title:t("Group Office users"),
				autoHeight: true,
				items: [
					this.aclPanel
				]
			}
		];

		return items;
	},
	/**
	 * Set the acl Id for the permissions panel/grid
	 * 
	 * @param int aclId
	 */
	setAcl : function(aclId) {
		this.aclPanel.setAcl(aclId);
	}
		
});