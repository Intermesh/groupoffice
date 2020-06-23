/**
 * File upload field
 * 
 * @example
 * ```
 * this.avatarComp = new go.form.FileField({
 * 			hideLabel: true,
 * 			buttonOnly: true,
 * 			name: 'photoBlobId',
 * 			height: dp(120),
 * 			cls: "avatar",
 * 			autoUpload: true,
 * 			buttonCfg: {
 * 				text: '',
 * 				width: dp(120)
 * 			},
 * 			setValue: function (val) {
 * 				if (this.rendered && !Ext.isEmpty(val)) {
 * 					this.wrap.setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
 * 				}
 * 				go.form.FileField.prototype.setValue.call(this, val);
 * 			},
 * 			accept: 'image/*'
 * 		});
 *  * ```
 * 
 */
go.form.ImageField = Ext.extend(Ext.BoxComponent, {

	/**
	 * @cfg {Object} buttonCfg A standard {@link Ext.Button} config object.
	 */

	// private
	readOnly: true,

	/**
	 * @hide
	 * @method autoSize
	 */
	autoSize: Ext.emptyFn,

	cls: 'avatar',

	style: "cursor: pointer",

	height: dp(120),

	width: dp(120),

	hideLabel: true,

	autoUpload: false,

	accept: '*/*',

	value: null,

	// private
	initComponent: function () {
		go.form.ImageField.superclass.initComponent.call(this);

		this.menu = new Ext.menu.Menu({
			items: [{

				iconCls: 'ic-computer',
				text: t("Upload"),
				handler: function () {
					go.util.openFileDialog({
						multiple: false,
						accept: "image/*",
						directory: false,
						autoUpload: true,
						listeners: {
							upload: function (response) {
								this.setValue(response.blobId);
							},
							scope: this
						}
					});
				},
				scope: this
			}, {
				iconCls: 'ic-link',
				text: t("From URL"),
				handler: function () {
					Ext.MessageBox.prompt(t("Set Image From URL"), t("Enter URL"), function(btn, url) {

						
						if(btn != "ok" || !url) {
							return;
						}


						Ext.Ajax.request({
							url: go.User.uploadUrl + "?url=" + encodeURIComponent(url),
							method: "GET",
							success: function(response) {
								data = Ext.decode(response.responseText);
								this.setValue(data.blobId);								
							},
							scope: this
						});

					}, this);
				},
				scope: this
			},
			{
				iconCls: 'ic-delete',
				text: t("Clear"),
				handler: function () {
					this.setValue(null);
				},
				scope: this
			}
			]
		});
	},

	// private
	onRender: function (ct, position) {
		go.form.ImageField.superclass.onRender.call(this, ct, position);

		this.getEl().on('click', this.onClick, this);
	},

	onClick: function (e) {
		var XY = new Array(e.getPageX(), e.getPageY());
		this.menu.showAt(XY);
	},

	name: null,

	isFormField: true,
	getName: function () {
		return this.name;
	},

	reset: function () {
		this.value = null;
	},

	isDirty: function () {
		return this.originalValue != this.value;
	},

	setValue: function (value) {
		this.value = value;

		if (this.rendered) {
			if (!Ext.isEmpty(value)) {
				this.el.setStyle('background-image', 'url(' + go.Jmap.thumbUrl(value, {w: 120, h: 120, zc: 1})  + ')');
			} else {
				this.el.setStyle('background-image', null);
			}
		}
	},

	getValue: function () {
		return this.value;
	},
	markInvalid: function (msg) {
		this.getEl().addClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.mark(this, msg);
	},
	clearInvalid: function () {
		this.getEl().removeClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.clear(this);
	},

	validate: function() {
		return true;
	},

	isValid: function(preventMark) {
		return true;
	}

});

Ext.reg('imagefield', go.form.ImageField);
