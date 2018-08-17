go.modules.community.music.ArtistDialog = Ext.extend(go.form.Dialog, {
	// Change to true to remember state
	stateful: false,
	stateId: 'music-aritst-dialog',
	title: t('Artist'),

	//The dialog set's entities in an go.data.EntityStore. This store notifies all 
	//connected go.data.Store view stores to update.
	entityStore: go.Stores.get("Artist"),
	autoHeight: true,

	// return an array of form items here.
	initFormItems: function () {
		return [{
				// it's recommended to wrap all fields in field sets for consistent style.
				xtype: 'fieldset',
				title: t("Artist information"),
				items: [{
						// The go.form.FileField component can handle "blob" fields.
						xtype: "filefield",
						hideLabel: true,
						buttonOnly: true,
						name: 'photo',
						height: dp(120),
						cls: "avatar",
						autoUpload: true,
						buttonCfg: {
							text: '',
							width: dp(120)
						},
						setValue: function (val) {
							if (this.rendered && !Ext.isEmpty(val)) {
								this.wrap.setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
							}
							go.form.FileField.prototype.setValue.call(this, val);
						},
						accept: 'image/*'
					},

					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					}]
			},

			{
				xtype: "fieldset",
				title: t("Albums"),

				items: [
					{
						//For relational properties we can use the "go.form.FormGroup" component.
						//It's a sub form for the "albums" array property.
				
						xtype: "formgroup",
						name: "albums",
						hideLabel: true,
						
						// this will add dp(16) padding between rows.
						pad: true,
						
						//the itemCfg is used to create a component for each "album" in the array.
						itemCfg: {
							layout: "form",							
							defaults: {
								anchor: "100%"
							},
							items: [{
									xtype: "textfield",
									fieldLabel: t("Name"),									
									name: "name"									
								},
								
								{
									xtype: "datefield",
									fieldLabel: t("Release date"),
									name: "releaseDate"
								},
								
								{
									xtype: "genrecombo"
								}
							]
						}
					}
				]
			}
		];
	}
});

