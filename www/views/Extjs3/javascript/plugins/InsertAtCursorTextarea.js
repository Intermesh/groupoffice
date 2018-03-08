/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: InsertAtCursorTextarea.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

 
GO.plugins.InsertAtCursorTextareaPlugin = function (){
	return {
		init : function(textarea){
			textarea.insertAtCursor = function(v) {
				if (Ext.isIE) {
					this.el.focus();
					var sel = document.selection.createRange();
					sel.text = v;
					sel.moveEnd('character',v.length);
					sel.moveStart('character',v.length);
				}else
				{
					var startPos = this.el.dom.selectionStart;
					var endPos = this.el.dom.selectionEnd;
					this.el.dom.value = this.el.dom.value.substring(0, startPos)
					+ v
					+ this.el.dom.value.substring(endPos, this.el.dom.value.length);

					this.el.focus();				
					this.el.dom.setSelectionRange(endPos+v.length,endPos+v.length);
				}
			}
		}
	}
};