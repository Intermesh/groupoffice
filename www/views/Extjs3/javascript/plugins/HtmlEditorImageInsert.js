/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: HtmlEditorImageInsert.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.plugins.HtmlEditorImageInsert = function(config) {
    
	config = config || {};
    
	Ext.apply(this, config);
    
	this.init = function(htmlEditor) {
		this.editor = htmlEditor;
		this.editor.on('render', this.onRender, this);
	};

	this.filesFilter='jpg,png,gif,jpeg,bmp';
	this.addEvents({
		'insert' : true
	});
}

Ext.extend(GO.plugins.HtmlEditorImageInsert, Ext.util.Observable, {


	root_folder_id : 0,
	folder_id : 0,
	
	isTempFile : true,
	
	onRender :  function() {

		var element={};

		element.itemId='htmlEditorImage';
		element.cls='x-btn-icon go-edit-insertimage';
		element.enableToggle=false;
		element.scope=this;
		element.clickEvent='mousedown';
		element.tabIndex=-1;
		element.tooltip={
			title:t("Image"),
			text:t("Insert image in the text")
		}
		element.overflowText=t("Insert image in the text");
		
							
		this.uploadForm = new GO.UploadPCForm();

		this.uploadForm.on('upload', function(e, files)
		{
			this.selectTempImage(files[0]);
		},this);

		var menuItems = [
		this.uploadForm
		];

		if(go.Modules.isAvailable("legacy", "files")){
			menuItems.push({
				iconCls:'btn-groupoffice',
				text : t("Add from Group-Office", "email").replace('{product_name}', GO.settings.config.product_name),
				handler : function()
				{
					this.showFileBrowser();
				},
				scope : this
			});
		}

		this.menu = element.menu = new Ext.menu.Menu({
			items:menuItems
		});
		
		
		this.editor.tb.add(element);
	},
	
	showFileBrowser : function (){
	

		GO.files.createSelectFileBrowser();

		GO.selectFileBrowser.setFileClickHandler(this.selectImage, this);

		GO.selectFileBrowser.setFilesFilter(this.filesFilter);
		GO.selectFileBrowser.setRootID(this.root_folder_id, this.files_folder_id);
		GO.selectFileBrowserWindow.show();

		GO.selectFileBrowserWindow.show.defer(200, GO.selectFileBrowserWindow);
	},

	selectTempImage : function(path)
	{
		
		var token = GO.base.util.MD5(path);
		
		this.selectedUrl = GO.url("core/downloadTempFile", {path:path, token: token});

		this.selectedPath = path;	
		

		var html = '<img src="'+this.selectedUrl+'" border="0" />';

		this.fireEvent('insert', this,  this.selectedPath, true, token);
		
		this.menu.hide();

		this.editor.focus();
		this.editor.insertAtCursor(html);		
	},
	
	selectImage : function(r){	
		

		this.selectedRecord = r;
		this.selectedPath = r.data.path;
		
		var token = GO.base.util.MD5(r.data.name);
		
		//filename is added as parameter. This is only for matching the url in the body of the html in GO\\Base\\Mail\\Message::handleEmailFormInput with preg_match.
		this.selectedUrl = GO.url("files/file/download",{id:r.data.id,token:token});
						
		var html = '<img src="'+this.selectedUrl+'" border="0" />';
								
		this.fireEvent('insert', this, this.selectedPath, false, token);

		this.editor.focus();
			
		this.editor.insertAtCursor(html);
		
		GO.selectFileBrowserWindow.hide();
	}
	
});
