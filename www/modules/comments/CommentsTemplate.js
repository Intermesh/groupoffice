GO.comments.displayPanelTemplate =
	//'{[this.collapsibleSectionHeader(GO.comments.lang.recentComments+\' (<a href="#" onclick="GO.comments.browseComments(\'+values.id+\', \'+values.link_type+\');" class="normal-link">\'+GO.comments.lang.browseComments+\'</a>)\', "comments-"+values.panelId, "comments")]}'+
'<tpl if="values.comments && values.comments.length">'+
'{[this.collapsibleSectionHeader(GO.comments.lang.recentComments, "comments-"+values.panelId, "comments")]}'+
	
			'<table cellpadding="0" cellspacing="0" border="0" class="display-panel" id="comments-{panelId}">'+
				'<tr><td colspan="2"><div id="newCommentForModelDiv_{model_name_underscores}_{id}"></div></td></tr>'+
				'<tr><td colspan="2"><hr /></td></tr>'+
				'<tpl if="!comments.length">'+
					'<tr><td colspan="3">'+GO.lang.strNoItems+'</td></tr>'+
				'</tpl>'+
				'<tpl for="comments">'+					
					'<tr>'+
						'<td><i>{user_name}</i> ({categoryName})</td>'+										
						'<td style="text-align:right"><b>{ctime}</b></td>'+
					'</tr>'+
					'<tr>'+
						'<td colspan="2" style="padding-left:5px" id="comment-td-{id}">'+
							'{[GO.comments.commentsAccordion(values.id,values.comments)]}'+
							'<hr />'+
						'</td>'+
					'</tr>'+
				'</tpl>'+

				'<tr><td colspan="4"><a class="display-panel-browse" href="#" onclick="GO.comments.browseComments({id}, \'{model_name}\',\'{action_date}\');">'+GO.lang.browse+'</a></td></tr>'+

			'</table>'+
	'</tpl>';

GO.comments.closeComment = function(commentId) {
	Ext.get('comment-'+commentId).setDisplayed(false);
	Ext.get('shortComment-'+commentId).setDisplayed(true);
}

GO.comments.openComment = function(commentId) {
	Ext.get('comment-'+commentId).setDisplayed(true);
	Ext.get('shortComment-'+commentId).setDisplayed(false);
}

GO.comments.commentsAccordion = function(id,commentsText) {
	
	var maxLength = 200;
	if (commentsText.length<maxLength || GO.comments.enableReadMore == "0") {
		return '<div id="comment-'+id+'" class="comment-div print-always">'+commentsText+'</div>';
	} else {
		return '<div id="comment-'+id+'" class="comment-div print-always" style="display:none;">'+
//				'<a href="javascript:GO.comments.closeComment('+id+');"><img src="views/Extjs3/themes/Default/images/elbow-end-minus-nl.gif" style="margin-bottom:-4px;margin-right:3px;" /></a>'+
					commentsText+'</div>'+
					'<div id="shortComment-'+id+'" class="shortComment-div print-never">'+				
					cutHtmlString(commentsText,maxLength)+'...'+
//					Ext.util.Format.ellipsis(commentsText,maxLength)+
					'&nbsp;<a href="javascript:GO.comments.openComment('+id+');">'+GO.comments.lang.readMore+'</a>'+
					'</div>';
					
	}
}