
go.form.FileButtonField = Ext.extend(Ext.form.CompositeField,{
	
	uploadButtonText: t('Upload'),
	
	clearButtonText: t('Clear'),
	
	showFileName:true,
	
	showClearButton:true,
	
	showSize:true,
	
	autoUpload: true,
	
	idOnly: false,
	
	accept: '*/*',
	
	blob:null,
	
	submit: true,
	
	dirty:false,

	labelShouldFloat: function() {
		return true;
	},
	
	initComponent: function () {
		
		this.hiddenField = new Ext.form.Hidden({
			name:this.name+'.id',
			submit: false
		});
		
		var uploadBtnCfg = Ext.applyIf(this.uploadBtnCfg || {}, {
			text: this.uploadButtonText,
			handler: function(){

				if(this.blob) {
					go.util.downloadFile(go.Jmap.downloadUrl(this.blob.id));
				} else {
					go.util.openFileDialog({
						multiple: false, // We do not yet support multiple file upload
						accept: this.accept,
						directory: false, // We do not yet support directories
						autoUpload: this.autoUpload,
						listeners: {
							upload: this.onUpload,
							uploadComplete: this.onUploadComplete,
							select: this.onSelect,
							scope: this
						}
					});
				}
			},
			scope:this
		});
		this.uploadButton = new Ext.Button(uploadBtnCfg);
		
		var clearBtnCfg = Ext.applyIf(this.clearBtnCfg || {}, {
			iconCls: "ic-delete",
			tooltip: this.clearButtonText,
			handler: function(){
				this.clearField();
			},
			scope:this
		});
		this.clearButton = new Ext.Button(clearBtnCfg);
				
		this.items = [this.hiddenField];
	
		this.items.push(this.uploadButton);
		
		if(this.showClearButton){
			this.items.push(this.clearButton);
		}

		go.form.FileButtonField.superclass.initComponent.call(this);
	},
	
	clearField: function(){
		this.setValue(null);
		this.dirty = true;
	},
	
	onUpload:function(response){
		if(response.blobId){
			response.id = response.blobId; // Setvalue expects id instead of blobId
			this.setValue(response);
			this.dirty = true;
		}
	},
	
	setFileNameField : function(name, size){
		if(this.showFileName){

			if(name.length > 20) {
				name = name.substring(0, 20) + "...";
			}

			if(this.showSize && size){



				name += ' ('+go.util.humanFileSize(size,true)+')';
			}

			this.uploadButton.setText(name);
		}
	},
	
	onSelect: function(files){
		console.log('onUploadComplete');
		console.log(files);
	},
	
	onUploadComplete:function(){
		console.log('onUploadComplete');
	},
	
	isDirty: function(){
		return this.dirty;
	},
	
	setValue : function(blob){
		this.blob = blob;
		if(blob !== null){
			this.hiddenField.setValue(blob.id);
			this.clearButton.enable();
			this.setFileNameField(blob.name,blob.size);
		} else {
			this.clearButton.disable();
			this.hiddenField.setValue(null);
			this.uploadButton.setText(t("Upload"));
		}

		this.fireEvent("setvalue", this, this.getRawValue());
	},
	
	getValue: function(){
		return this.getRawValue();
	},
	
	setRawValue : function(blob){
		this.setValue(blob);
	},
	
	getRawValue: function(){
		return !this.idOnly ? this.blob : this.hiddenField.getValue() || null;
	},
	
	disable : function(silent){
		this.filenameField.disable(silent);
		this.clearButton.disable(silent);
		this.uploadButton.disable(silent);
		
		go.form.FileButtonField.superclass.disable.call(this,silent);
	},
	enable : function(){
		
		this.filenameField.enable();
		this.clearButton.enable();
		this.uploadButton.enable();
		
		go.form.FileButtonField.superclass.enable.call(this);
	}
	
});

Ext.reg('filebuttonfield', go.form.FileButtonField);
