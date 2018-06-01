/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: HtmlEditorLinkInsert.js 10290 2012-05-02 08:08:30Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.site.HtmlEditorLinkInsert = function(config) {
    
	config = config || {};
    
	Ext.apply(this, config);
    
	this.init = function(htmlEditor) {
		this.editor = htmlEditor;
		this.editor.on('render', this.onRender, this);
	};
	
	this.addEvents({
		'insert' : true
	});
};

Ext.extend(GO.site.HtmlEditorLinkInsert, Ext.util.Observable, {
	toolbarPosition : 17,
	toolbarSeparatorBefore : false,
	toolbarSeparatorAfter : false,
	onRender :  function() {

		var element={};

		element.itemId='htmlEditorLink';
		element.cls='x-btn-icon go-edit-insertlink';
		
		element.enableToggle=false;
		element.scope=this;
		element.clickEvent='mousedown';
		element.tabIndex=-1;
		element.tooltip={
			title:t("Insert link", "site"),
			text:t("Insert link", "site")
		};
		element.overflowText=t("Insert link", "site");

		element.handler = function(){
			this.showLinkDialog();
		};

		if(this.toolbarSeparatorBefore)
			this.editor.tb.insert(this.toolbarPosition,'-');
		
		this.editor.tb.insert(this.toolbarPosition,element);
		
		if(this.toolbarSeparatorAfter)
			this.editor.tb.insert((this.toolbarPosition+1),'-');
	},
	
	setSiteId : function(site_id){
		this.id = site_id;
	},
	
	getSelectedText : function() {
		
		var frame = this.editor.iframe;
    var frameWindow = frame.contentWindow;
    var frameDocument = frameWindow.document;

    if (frameDocument.getSelection) 
        return frameDocument.getSelection().toString();
    else if (frameDocument.selection)
        return frameDocument.selection.createRange().text;
	},
	
	showLinkDialog : function(id,path){
	
		var selection = this.getSelectedText();

		
		if(!selection){
			Ext.Msg.alert('Selecteer', 'Geen tekst in de editor geselecteerd.');
			return;
		}
		
		if(!this.linkDialog){
			this.linkDialog = new GO.site.HtmlEditorLinkDialog();
			
			this.linkDialog.on('insert', function(){
				//var html = '<site:link id="'+r.data.id+'" href="'+this.selectedPath+'"></site:link>';
				var html = this.linkDialog.getTag();

				if(html){
					this.editor.focus();
					
					var selection = this.getSelectedText();
					
					html = html.replace('{selectedEditorText}',selection);

					this.editor.insertAtCursor(html);
				}
			},this);
		}
		
		var dialogconfig = [];
		dialogconfig.site_id = this.id;
	
		this.linkDialog.show(dialogconfig);
	}
});
