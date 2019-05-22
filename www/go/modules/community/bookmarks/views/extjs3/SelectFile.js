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

go.modules.community.bookmarks.SelectFile = Ext.extend(Ext.form.TriggerField,{
	triggerClass : 'fs-form-file-select',
	filesFilter : '',
	root_folder_id : 0,
	files_folder_id : 0,
	dialog : false,

	onTriggerClick : function(){
		if(!this.thumbsDialog) {
			this.thumbsDialog = new go.modules.community.bookmarks.ThumbsDialog({
				iconfield: this,
				dialog: this.dialog,
				listeners:{
					close:function(){
						// the dialog is closed 
						this.thumbsDialog = false;
					},
					scope:this
				}
			});
		}
		this.thumbsDialog.show();
	}

});
