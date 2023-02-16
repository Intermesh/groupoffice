go.customfields.FieldSetDialog = Ext.extend(go.form.Dialog, {
	stateId: 'custom-field-set-dialog',
	title: t('Field set'),
	entityStore: "FieldSet",
	width: dp(1000),
	height: dp(800),
	autoScroll: true,

	initComponent : function() {
		go.customfields.FieldSetDialog.superclass.initComponent.call(this);

		this.formPanel.on("setvalues", (form, v) => {
			this.fieldSetCombo.store.setFilter("default", {
				entities: [v.entity],
				exclude: this.currentId ? [this.currentId] : undefined,
				isTab: true
			});

			this.fieldSetCombo.setDisabled(v.isTab);
		});
	},
	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel(
			{
				addLevel: go.permissionLevels.write
			}
		));
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					},{
						xtype: "hidden",
						name: "entity"
					}, {
						xtype: "checkbox",
						name: 'isTab',
						hideLabel: true,
						boxLabel: t("Show as tab"),

						listeners: {
							check:function(cb, checked) {
								this.fieldSetCombo.setDisabled(checked);
							},
							scope: this
						}
					}, {
						xtype: "checkbox",
						name: 'collapseIfEmpty',
						hideLabel: true,
						boxLabel: t("Collapse when empty"),
						hint: t('Show this fieldset collapsed when all of its field have the initial value'),
					},
					this.fieldSetCombo = new go.customfields.FieldSetCombo({
						disabled: true,
						hiddenName: "parentFieldSetId",
						fieldLabel: t("Show on tab"),
						emptyText: t("Default")
					}),

					{
						xtype: "textarea",
						name: "description",
						fieldLabel: t("Description"),
						anchor: "100%",
						grow: true,
						hint: t("This description will show in the edit form")
					}	,{
						xtype:'gonumberfield',
						name: 'columns',
						fieldLabel: t("Columns"),
						value: 2,
						decimals: 0
					}
				]
			}
		];
	}
});
