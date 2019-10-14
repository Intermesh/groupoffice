go.QRCodeComponent = Ext.extend(Ext.BoxComponent, {
	
	qrUrl: Ext.BLANK_IMAGE_URL,

	render: function(ct, position){
		this.el = ct.createChild({
			tag: 'img',
			src: this.qrUrl
		});
		
		go.QRCodeComponent.superclass.render.call(this,ct,position);
	},

	setQrBlobId: function(qrBlobId){
		this.setQRUrl(go.Jmap.downloadUrl(qrBlobId));
	},

	setQRUrl: function (url) {
		this.qrUrl = url;

		if (this.rendered) {
			this.getEl().dom.src = url;
		}
	},
	clearQRUrl: function () {
		this.setQRUrl(Ext.BLANK_IMAGE_URL);
	}
});
