/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SelectFile.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Twan Verhofstad
 */


GO.bookmarks.SelectFile = Ext.extend(Ext.form.TriggerField,{

	triggerClass : 'fs-form-file-select',
	filesFilter : '',
	root_folder_id : 0,
	files_folder_id : 0,
	dialog : false,

	onTriggerClick : function(){

		var thumb_id = this.dialog.formPanel.form.baseParams.id;
		if (!thumb_id) thumbtitle='Example';
		if(!this.thumbsDialog){
			this.thumbsDialog = new GO.bookmarks.ThumbsDialog({
				thumb_id:thumb_id,
				iconfield:this,
				pubicon:this.dialog.formPanel.baseParams.public_icon,
				dialog:this.dialog
			});
		}
		this.thumbsDialog.thumb_id=thumb_id;
		this.thumbsDialog.is_publiclogo=this.dialog.formPanel.baseParams.public_icon;		
		this.thumbsDialog.show();
		this.thumbsDialog.setIcon(this.getValue(), this.thumbsDialog.is_publiclogo);
	}

});