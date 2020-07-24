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
go.form.FileField = Ext.extend(Ext.form.TextField, {
	/**
	 * @cfg {String} buttonText The button text to display on the upload button (defaults to
	 * 'Browse...').  Note that if you supply a value for {@link #buttonCfg}, the buttonCfg.text
	 * value will be used instead if available.
	 */
	buttonText: 'Browse...',
	/**
	 * @cfg {Boolean} buttonOnly True to display the file upload field as a button with no visible
	 * text field (defaults to false).  If true, all inherited TextField members will still be available.
	 */
	buttonOnly: false,
	/**
	 * @cfg {Number} buttonOffset The number of pixels of space reserved between the button and the text field
	 * (defaults to 3).  Note that this only applies if {@link #buttonOnly} = false.
	 */
	buttonOffset: 3,
	/**
	 * @cfg {Object} buttonCfg A standard {@link Ext.Button} config object.
	 */

	// private
	readOnly: true,

	height: dp(32),

	/**
	 * @hide
	 * @method autoSize
	 */
	autoSize: Ext.emptyFn,
	
	cls: '',

	autoUpload: true,
	
	accept: '*/*',

	// private
	initComponent: function () {
		go.form.FileField.superclass.initComponent.call(this);

		this.addEvents(
				  /**
					* @event fileselected
					* Fires when the underlying file input field's value has changed from the user
					* selecting a new file from the system file selection dialog.
					* @param {go.form.FileField} this
					* @param {String} value The file value returned by the underlying file input field
					*/
				  'fileselected',
				  
				  'uploadComplete',
				  'uploadStart'
				  );
	},

	// private
	onRender: function (ct, position) {
		go.form.FileField.superclass.onRender.call(this, ct, position);

		this.wrap = this.el.wrap({
			cls: 'x-form-field-wrap x-form-file-wrap '+this.cls,
			height: this.height || 'auto'
		});
		this.el.addClass('x-form-file-text');
		this.el.dom.removeAttribute('name');
		this.createFileInput();

		var btnCfg = Ext.applyIf(this.buttonCfg || {}, {
			text: this.buttonText
		});
		this.button = new Ext.Button(Ext.apply(btnCfg, {
			renderTo: this.wrap,
			height: btnCfg.height || this.height || 'auto',
			cls: btnCfg.cls+' x-form-file-btn' + (btnCfg.iconCls ? ' x-btn-icon' : '')
		}));

		if (this.buttonOnly) {
			this.el.hide();
			this.wrap.setWidth(this.button.getEl().getWidth());
		}

		this.bindListeners();
		this.resizeEl = this.positionEl = this.wrap;
	},

	bindListeners: function () {
		this.fileInput.on({
			scope: this,
			mouseenter: function () {
				this.button.addClass(['x-btn-over', 'x-btn-focus'])
			},
			mouseleave: function () {
				this.button.removeClass(['x-btn-over', 'x-btn-focus', 'x-btn-click'])
			},
			mousedown: function () {
				this.button.addClass('x-btn-click')
			},
			mouseup: function () {
				this.button.removeClass(['x-btn-over', 'x-btn-focus', 'x-btn-click'])
			},
			change: function () {
				var v = this.fileInput.dom.files;
				//this.setValue(v);
				this.fireEvent('fileselected', this, v);
				if (v && this.autoUpload) {
					this.startUpload(v);
				}
			}
		});
	},

	startUpload: function (files) {
		for (var f in files) {
			var file = files[f];
			this.fireEvent('uploadStart', this, file);
			go.Jmap.upload(file, {
				success: function(data, file, options) {
					if (data.blobId) {
						this.fireEvent('change', this, data.blobId, this.value);
						this.setValue(data.blobId);
						this.originalValue = '';
					}
					this.fireEvent('uploadComplete', data, file, options);
				},
				failure: function(data, file, options) {
					this.fireEvent('uploadFailed', data, file, options);
				},
				scope:this
			});
			break; // single file upload support for now
		}
	},

	createFileInput: function () {
		var style = {},
			height = this.buttonCfg && this.buttonCfg.height ? this.buttonCfg.height : this.height;
		if(height) {
			var style = {height: height+'px'};
		}
		this.fileInput = this.wrap.createChild({
			id: this.getFileInputId(),
			name: this.name || this.getId(),
			cls: 'x-form-file',
			tag: 'input',
			type: 'file',
			style: style,
			accept: this.accept,
			size: 1
		});
	},

	reset: function () {
		if (this.rendered) {
			this.fileInput.remove();
			this.createFileInput();
			this.bindListeners();
		}
		go.form.FileField.superclass.reset.call(this);
	},
	getValue : function() {
		var v = go.form.FileField.superclass.getValue.call(this);
		return v ? v : null;
	},

	// private
	getFileInputId: function () {
		return this.id + '-file';
	},

	// private
	onResize: function (w, h) {
		go.form.FileField.superclass.onResize.call(this, w, h);
		this.wrap.setWidth(w);

		if (!this.buttonOnly) {
			var w = this.wrap.getWidth() - this.button.getEl().getWidth() - this.buttonOffset;
			this.el.setWidth(w);
		} else {
			this.wrap.setWidth(this.button.getEl().getWidth());
		}
	},

	// private
	onDestroy: function () {
		go.form.FileField.superclass.onDestroy.call(this);
		Ext.destroy(this.fileInput, this.button, this.wrap);
	},

	onDisable: function () {
		go.form.FileField.superclass.onDisable.call(this);
		this.doDisable(true);
	},

	onEnable: function () {
		go.form.FileField.superclass.onEnable.call(this);
		this.doDisable(false);

	},

	// private
	doDisable: function (disabled) {
		this.fileInput.dom.disabled = disabled;
		this.button.setDisabled(disabled);
	},

	// private
	preFocus: Ext.emptyFn,

	// private
	alignErrorIcon: function () {
		this.errorIcon.alignTo(this.wrap, 'tl-tr', [2, 0]);
	}

});

Ext.reg('filefield', go.form.FileField);
