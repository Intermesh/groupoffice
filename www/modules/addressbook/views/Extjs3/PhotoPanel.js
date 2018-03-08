/**
 * 
 * !! NOTE: this panel is only used for the Settings Dialog !!
 * The contact dialog has its own photopanel build in.
 */
GO.addressbook.PhotoPanel = Ext.extend(Ext.Panel, {
	
	originalPhotoUrl : null,
	contactPhoto: null,
	
	initComponent : function(){
		
		var cPhoto = Ext.extend(Ext.BoxComponent, {
			autoEl : {
					tag: 'img',
					cls:'ab-photo',
					src:Ext.BLANK_IMAGE_URL
				},

			setPhotoSrc : function(url)
			{
				var now = new Date();
				if (this.el)
					this.el.set({
						src: GO.util.empty(url) ? Ext.BLANK_IMAGE_URL : url
					});
				this.setVisible(true);
			}
		});

		this.uploadFile = new GO.form.UploadFile({
			inputName : 'image',
			max: 1
		});

		this.contactPhoto = new cPhoto();
		this.deleteImageCB = new Ext.form.Checkbox({
			boxLabel: GO.addressbook.lang.deleteImage,
			labelSeparator: '',
			name: 'delete_photo',
			allowBlank: true,
			hideLabel:true,
			disabled:true
		});

		Ext.apply(this, {
			title : GO.addressbook.lang.photo,
			layout: 'form',
			border:false,
			cls : 'go-form-panel',		
			autoScroll:true,
			labelAlign:'top',
			items:[
				{

					xtype:'textfield',
					fieldLabel:GO.addressbook.lang.downloadPhotoUrl,
					name:'download_photo_url',
					anchor:'100%'
				},{
					style:'margin-top:15px;margin-bottom:10px;',
					html:GO.addressbook.lang.orBrowseComputer+':',
					xtype:'htmlcomponent'
				},
				this.uploadFile,
				{
					style:'margin-top:15px',
					html:GO.addressbook.lang.currentImage+':',
					xtype:'htmlcomponent'
				},
				this.contactPhoto,
				this.deleteImageCB,
				new Ext.Button({
					text:GO.addressbook.lang.downloadFullImage,
					disabled:false,
					handler:function(){
						window.open(this.originalPhotoUrl,'_blank');
					},
					scope:this
				})
			]
		});
		
		GO.addressbook.PhotoPanel.superclass.initComponent.call(this);
	},
	
	onLoadSettings : function(action) {
		
		// in contact this is done after successfull submit but there
		// is no on success triggered by the personal settings pannel.
		//this.uploadFile.clearQueue(); 
		
		if(!GO.util.empty(action.result.data.original_photo_url))
			this.setOriginalPhoto(action.result.data.original_photo_url);
		else
			this.setOriginalPhoto("");
		
		if(!GO.util.empty(action.result.data.photo_url))
			this.setPhoto(action.result.data.photo_url);
		else
			this.setPhoto("");
	},

	setOriginalPhoto : function(url){
		this.originalPhotoUrl = url;
	},
	setPhoto : function(url)
	{
		this.contactPhoto.setPhotoSrc(url);
		this.deleteImageCB.setValue(false);
		this.deleteImageCB.setDisabled(url=='');
	}
	
});