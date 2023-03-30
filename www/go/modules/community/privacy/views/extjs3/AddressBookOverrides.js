GO.moduleManager.onModuleReady('addressbook',function() {
	Ext.override(go.modules.community.addressbook.ContactDetail, {
		initComponent: go.modules.community.addressbook.ContactDetail.prototype.initComponent.createSequence(function () {
			// TODO: Display warning when a contact is to be removed
			this.privacyPanel = new Ext.Panel({
				title: t("Privacy"),
				onLoad: (detailView) => {
					this.privacyPanel.hide();
					if(detailView.data.deletionDate) {
						this.privacyPanel.show();
					}
				},
				tpl: '<div class="icons">' +
					'<p class="s6">' +
					'<i class="icon label">security</i>' +
					'<span>{[go.util.Format.date(values.deletionDate?.deleteAt)]}</span>	' +
					'<label>'+ t("Delete at") + '</label>' +
					'</p>'+
					'</div>'
			})

			this.insert(2, this.privacyPanel);
		})
	});

	Ext.override(go.modules.community.addressbook.ContactDialog, {
		initComponent: go.modules.community.addressbook.ContactDialog.prototype.initComponent.createSequence(function () {
			this.privacyFieldset = new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [new go.form.DateField({
					flex: 1,
					xtype: "datefield",
					allowBlank: true,
					name: "deletionDate.deleteAt",
					setFocus: true,
					fieldLabel: t("Delete at"),
				})],
				title: t("Privacy")
			});
			this.mainPanel.insert(1,this.privacyFieldset);
		})
	});
});
