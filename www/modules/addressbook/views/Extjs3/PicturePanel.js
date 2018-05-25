GO.addressbook.PicturePanel = Ext.extend(Ext.Panel, {
	
	title : t("Photo", "addressbook"),
	layout: 'form',
	border:false,	
	autoScroll:true,
	cls: 'go-form-panel',
	labelAlign:'top',
	parentPanel:null,
	originalPhotoUrl : null,
	contactPhoto: null,
	
	initComponent: function() {

		this.contactPhoto = new Ext.Panel({
			width: 336, 
			height: 252,			
			style: 'background-size: cover',
			tbar: {style:{'background-color': 'rgba(255,255,255,.24)'},
				items:[this.deleteImageCB = new Ext.form.Checkbox({
					name: 'delete_photo',
					boxLabel: t("Delete image", "addressbook"),
					iconCls:'ic-delete',
					enableToggle:true,
					hidden:true,
				}),'->',{
					iconCls:'ic-more-vert', 
					menu:[
				this.downloadBtn = new Ext.menu.Item({
					text: t("Download full image", "addressbook"),
					iconCls: 'ic-file-download',
					hidden: true,
					handler: function() {
						if(this.originalPhotoUrl) {
							window.open(this.originalPhotoUrl,'_blank');
						}
					},
					scope:this
				}),{
					text:t("Search for images", "addressbook"),
					iconCls: 'ic-search',
					scope:this,
					handler:function(){
						var name = this.getName();
						var sUrl = 'http://www.google.com/search?tbm=isch&q="'+encodeURIComponent(name)+'"';
						window.open(sUrl);
					}
				}
			]}
			]},
			setPhotoSrc : function(url){
				this.el.setStyle('background-image', !url ? null : 'url("'+ url + '")');
			}
		});
		
		Ext.applyIf(this, {
			items: [{
					xtype:'fieldset',
					items:[this.contactPhoto,{
					fieldLabel: t("Download photo URL", "addressbook"),
					xtype:'textfield',
					width: 336,
					name:'download_photo_url',
				},this.uploadFile = new GO.form.UploadFile({
					fieldLabel: t("or upload from your computer", "addressbook"),
					inputName : 'image',
					iconCls: 'ic-upload-file',
					max: 1
				})]
			}
			]
		});
	
		GO.addressbook.PicturePanel.superclass.initComponent.call(this);
	},
	setPhoto : function(url, orig_url) {
		this.uploadFile.clearQueue();
		this.originalPhotoUrl = orig_url || "";
		this.contactPhoto.setPhotoSrc(url);
		this.deleteImageCB.setValue(false);
		this.deleteImageCB.setVisible(!!url);
		this.downloadBtn.setVisible(!!url);
	},
	getName : function() {
		// override this is the name to search for
		return '';
	}
});
