GO.comments.SettingsPanel = function(config) {
	if (!config) 
		config = {};

	config.autoScroll = true;
	config.border = false;
	config.hideLabel = true;
	config.title = GO.comments.lang.comments;
	config.hideMode = 'offsets';
	config.layout = 'form';
	config.bodyStyle = 'padding:5px';
	config.labelWidth=150;
	
	config.items=[
		{
			xtype:'fieldset',
			title:GO.comments.lang.readMore,
			autoHeight:true,
			items:[
				this.useReadmore = new Ext.ux.form.XCheckbox({
					boxLabel:GO.comments.lang.enableReadMore,
					hideLabel:true,
					checked:GO.comments.enableReadMore,
					name:'comments_enable_read_more'
				})
			]
		},{
			xtype:'fieldset',
			title:GO.comments.lang.originalCommentTabs,
			autoHeight:true,
			items:[
				this.disableOrigContact = new Ext.ux.form.XCheckbox({
					boxLabel:GO.comments.lang.disableOriginalCommentsContact,
					hideLabel:true,
					checked:GO.comments.disableOriginalCommentsContact,
					name:'comments_disable_orig_contact',
					disabled:GO.comments.disabledOriginalCommentsContactInConfig
				}),
				this.disableOrigCompany = new Ext.ux.form.XCheckbox({
					boxLabel:GO.comments.lang.disableOriginalCommentsCompany,
					hideLabel:true,
					checked:GO.comments.disableOriginalCommentsCompany,
					name:'comments_disable_orig_company',
					disabled:GO.comments.disabledOriginalCommentsCompanyInConfig
				})
			]
		}
		
	];

	GO.comments.SettingsPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.comments.SettingsPanel, Ext.Panel, {
	onLoadSettings : function(action) {		
		this.useReadmore.setValue(action.result.data.comments_enable_read_more);
		this.disableOrigContact.setValue(action.result.data.comments_disable_orig_contact);
		this.disableOrigCompany.setValue(action.result.data.comments_disable_orig_company);
	}
});

GO.mainLayout.onReady(function() {
	GO.moduleManager.addSettingsPanel('comments',
		GO.comments.SettingsPanel);
});

GO.linkPreviewPanels["GO\\Comments\\Model\\Comment"]=function(config){
	config = config || {};
	return new GO.comments.CommentPanel(config);
}

/**
 * Returns if the original tab needs to be hidden or not.
 * True = hide, False = show
 * 
 * @param string panelId
 * @returns {Boolean}
 */
GO.comments.hideOriginalTab = function(panelId){

	if(panelId == 'contact' && GO.comments.disableOriginalCommentsContact && GO.comments.disableOriginalCommentsContact == "1"){
		 return true;
	}

	if(panelId == 'company' &&  GO.comments.disableOriginalCommentsCompany && GO.comments.disableOriginalCommentsCompany == "1"){
		 return true;
	}

	return false;
};
