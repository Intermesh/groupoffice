/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ThumbsDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */

GO.bookmarks.thumbTpl = new Ext.XTemplate('<div class="thumb-wrap" >'+
		'<div class="thumb">'+
		'<div id="dialog_thumb" class="thumb-name"'+
		' style="background-image:url({logo})"><h1>{title}</h1>{description}</div></div>'
		+'</div>');


GO.bookmarks.ThumbsDialog = function(config){

	this.chosenlogo="";													// pad naar gekozen logo
	
	if(!config)
	{
		config = {};
	}

	config.closeAction='hide';
	config.height=350;
	config.width=800;
	config.layout='border';
	config.title=GO.bookmarks.lang.chooseIcon;
	config.buttons=[{
		text: GO.lang['cmdOk'],
		handler: function(){
			
			this.iconfield.setValue(this.chosenlogo); // pad naar logo
			
			//config.dialog.formPanel.baseParams.public_icon=this.is_publiclogo; // public logo
			config.dialog.setIcon(this.chosenlogo, this.is_publiclogo);
			this.hide();
		},
		scope: this
	}
	,{
		text: GO.lang['cmdClose'],
		handler: function(){
			this.hide();
		},
		scope:this
	}	
	];

 	// laat alle public logo's op deze manier zien, in centerPanel van de dialog
	
	this.logolist  = new Ext.XTemplate(
		'<tpl for=".">',
		'<div class="icons">', '<img src="'+BaseHref+'modules/bookmarks/icons/{filename}"/> </div>',
		'</tpl>',
		'<div style="clear:both"></div>');
	
	this.publiclogoView = new Ext.DataView({
		store: GO.bookmarks.thumbstore,
		tpl: this.logolist,
		cls: 'thumbnails',
		itemSelector:'div.icons',
		multiSelect: false,
		singleSelect: false,
		//trackOver:true,
		border: false,
		style: {
			marginLeft: '13px',
			marginTop: '13px',
			marginRight: '6px'
		}
	});
	
	// verander voorbeeld thumb als er een logo in centerPanel word aangeklikt.

	this.publiclogoView.on('click',function(DV, index, node, e) {
		var record = this.publiclogoView.getRecord(node); // waar hebben we op geklikt?
		this.is_publiclogo=1;
		this.chosenlogo="icons/" + record.data.filename;
		this.setIcon(this.chosenlogo, this.is_publiclogo);		
	},this)

	// voorbeeld thumb, in westPanel
	
	this.thumbExample = new Ext.Component({
		style: {
			marginLeft:'13px'
		}
	});

	// upload logo button in westPanel

	this.uploadFile = new GO.form.UploadFile({ // aangepast met fileAdded event.
		style: {
			marginTop: '6px'
		},
		border: false,
		inputName : 'attachments',
		addText : GO.bookmarks.lang.uploadLogo,
		max: 1		// maar 1 tegelijk, overwrite event word meteen ge-fired.
	});


	// knoppen in een form, voor upload submit
	this.uploadForm = new Ext.form.FormPanel({
		border: false,
		cls : 'go-form-panel',
		fileUpload : true,
		waitMsgTarget : true,
		autoScroll:true,
		baseParams: {
		},
		items : [this.uploadFile]
	});


	// knoppen + voorbeeld in westPanel
	this.westPanel= new Ext.Panel({
		region: 'west',
		//layout: 'form',
		border: true,
		header:false,
		width: 215,
		items: [this.uploadForm,this.thumbExample]
	})

	
	// public logo's in centerPanel'
	this.centerPanel= new Ext.Panel({
		region: 'center',
		autoScroll: true,
		items: [this.publiclogoView]
	})

	Ext.apply(config, {
		listeners:{
			render:function(){
				GO.bookmarks.thumbstore.load();
			}
		}
	});

	// uploadknop fired upload event
	this.uploadFile.on('fileAdded',function(){
		this.is_publiclogo=0; // ge-uploade logo's zijn niet public
		this.uploadHandler(); // fired upload event
	},this )


	config.items=[this.centerPanel,this.westPanel];
	GO.bookmarks.ThumbsDialog.superclass.constructor.call(this, config);
	

	this.addEvents({
		'upload' : true
	 });
}

Ext.extend(GO.bookmarks.ThumbsDialog, Ext.Window, {
	setIcon : function(icon, pub){

		var now = new Date();
		var url = GO.bookmarks.getThumbUrl(icon, pub);
		if(pub==0){
			url += '&amp;time='+now.format('U');
		}

		this.thumbExample.getEl().update(GO.bookmarks.thumbTpl.apply({
				logo:url,
				title:GO.bookmarks.lang.title,
				description:GO.bookmarks.lang.description
			}));
	},

  // kopie / aanpassing uit Files module
	uploadHandler : function(){
		this.uploadForm.form.submit({
			url:GO.url("bookmarks/bookmark/upload"),
			waitMsg : GO.lang.waitMsgUpload,
			params:{
				thumb_id:   this.thumb_id,
				folder_id : this.folder_id
			},
			success:function(form, action){
				this.uploadFile.clearQueue();
				this.fireEvent('upload', action);
				
				this.chosenlogo=action.result.logo;
				this.is_publiclogo=0;

				this.setIcon(this.chosenlogo, this.is_publiclogo);
			},
			failure:function(form, action)
			{
				var error = '';
				if(action.failureType=='client')
				{
					error = GO.lang['strErrorsInForm'];
				}else
				{
					error = action.result.feedback;
				}

				Ext.MessageBox.alert(GO.lang['strError'], error);
			},
			scope: this
		});
	}
});