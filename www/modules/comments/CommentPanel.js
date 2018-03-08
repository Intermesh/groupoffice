/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CommentPanel.js 19019 2015-04-22 09:46:46Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.comments.CommentPanel = Ext.extend(GO.DisplayPanel,{
	
	model_name : "GO\\Comments\\Model\\Comment",

	stateId : 'co-comment-panel',

	editGoDialogId : 'comment',
	
	editHandler : function(){
		GO.comments.showCommentDialog(this.model_id, {model_name:this.data.model_name,model_id:this.data.model_id,action_date:this.actionDate});
	},	
	
	initComponent : function(){	
		
		this.loadUrl=('comments/comment/display');
		
		
		
		this.template = 
				'{[this.collapsibleSectionHeader(GO.comments.lang.comment+": "+ values.short, "commentpane-"+values.panelId, "name")]}'+
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="commentpane-{panelId}">'+
					'<tr>'+
						'<td valign="top">'+GO.comments.lang.comment+'</td>'+
						'<td valign="top" colspan="2">{comments}</td>'+
					'</tr>' +
					
					'<tpl if="!GO.util.empty(category_name)">'+
					'<tr>'+
						'<td valign="top">'+GO.comments.lang.category+'</td>'+
						'<td valign="top" colspan="2">{category_name}</td>'+
					'</tr>' +
					'</tpl>'+
					
					'<tr>'+
						'<td valign="top">'+GO.lang.strDate+'</td>'+
						'<td valign="top" colspan="2">{ctime}</td>'+
					'</tr>' +
					'<tr>'+
						'<td valign="top">'+GO.comments.lang.parent+'</td>'+
						//'<td valign="top"><a href="#" onclick="GO.linkHandlers[\'{values.parent.model_type}\'].call(this, {values.parent.model_id});">{values.parent.name}</a></td>'+
						'<td style="width:16px;"><div class="display-panel-link-icon go-model-icon-{[this.replaceWithUnderscore(values.parent.model_type)]}"></div></td>'+
						'<td valign="top"><a href="#" onclick="GO.linkHandlers[\'{[this.addSlashes(values.parent.model_type)]}\'].call(this, {values.parent.model_id});">{values.parent.name}</a></td>'+
					'</tr>' +
				'</table>';
			
		Ext.apply(this.templateConfig, {
			addSlashes : function(str)
			{
				str = GO.util.html_entity_decode(str, 'ENT_QUOTES');
				str = GO.util.add_slashes(str);
				return str;
			},
			
			replaceWithUnderscore: function(str){
				if(!GO.util.empty(str)){
					str = str.replace(/\\/g,"_");
				}
				return str;
			}
			
		});
		
		Ext.apply(this.templateConfig, GO.linksTemplateConfig);

		GO.comments.CommentPanel.superclass.initComponent.call(this);

	},
	
	createTopToolbar : function(){
		var tbar = GO.comments.CommentPanel.superclass.createTopToolbar.call(this);
		return tbar;
	},
	
	setData : function(data)
	{
		GO.comments.CommentPanel.superclass.setData.call(this, data);
//		this.newMenuButton.menu.taskShowConfig= {comment_id:this.data.id};
	}	
});			