/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ThumbsDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

 	
go.modules.community.bookmarks.ThumbsDialog = Ext.extend(Ext.Window, {
	initComponent: function() {
		var chosenlogo="";	// pad naar gekozen logo
		this.height=250;
		this.width=300;
		this.layout='border';
		this.title=t("Choose icon for bookmark");
		this.buttons=[{
			text: t("Ok"),
			handler: function(){
				this.iconfield.setValue(chosenlogo); // pad naar logo
				this.close();
			},
			scope: this
		}
		,{
			text: t("Close"),
			handler: function(){
				this.close();
			},
			scope:this
		}	
		];
		
		this.thumbExample = new Ext.Component({
			style: {
				marginLeft:'13px'
			}
		});
	
		// knoppen + voorbeeld in westPanel
		this.westPanel= new Ext.Panel({
			region: 'west',
			//layout: 'form',
			border: true,
			header: false,
			width: 215,
			items: [{
				// The go.form.FileField component can handle "blob" fields.
				xtype: "filefield",
				hideLabel: true,
				buttonOnly: true,
				name: 'photo',
				height: dp(120),
				cls: "chooseIcon",
				autoUpload: true,
				buttonCfg: {
						text: '',
						width: dp(120)
				},
				setValue: function (val) {
					chosenlogo = val;
						if (this.rendered && !Ext.isEmpty(val)) {
								this.wrap.setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
						}
						go.form.FileField.prototype.setValue.call(this, val);
				},
				accept: 'image/*'
		},
				this.thumbExample]
		})
	
		
		// public logo's in centerPanel'
		this.centerPanel= new Ext.Panel({
			region: 'center',
			autoScroll: true,
			items: []
		})
	
		this.items=[this.centerPanel,this.westPanel];
		go.modules.community.bookmarks.ThumbsDialog.superclass.initComponent.call(this);
		
	
		this.addEvents({
			'upload' : true
		 });
	},
});
