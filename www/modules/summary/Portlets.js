/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Portlets.js 22337 2018-02-07 08:23:15Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.summary.portlets=[];

GO.mainLayout.onReady(function(){
	var rssTabPanel = new Ext.TabPanel({doLayoutOnTabChange:true, autoHeight: true});
	
	GO.summary.portlets['portlet-rss-reader']=new GO.summary.Portlet({
		id: 'portlet-rss-reader',
		//iconCls: 'rss-icon',
		title: t("News", "summary"),
		autoHeight: true,

		tools: [{
			id: 'gear',
			handler: function(){
				if(!this.manageWebFeedsWindow)
				{
					this.manageWebFeedsWindow = new Ext.Window({
						layout:'fit',
						items:this.WebFeedsGrid =  new GO.summary.WebFeedsGrid(),
						width:700,
						height:400,
						title:t("Rss Feeds", "summary"),
						closeAction:'hide',
						buttons:[{
							text: t("Save"),
							handler: function(){

								if(!this.WebFeedsGrid.isValid(true)){
									alert(t("You have errors in your form. The invalid fields are marked."));
									return false;
								}
								var params={
								};
								if(this.WebFeedsGrid.store.loaded){
									params['feeds']=Ext.encode(this.WebFeedsGrid.getGridData());
								}
								Ext.Ajax.request({
									url: GO.url('summary/rssFeed/saveFeeds'),
									params: params,
									callback: function(options, success, response){

										if(!success)
										{
											Ext.MessageBox.alert(t("Error"), t("Could not connect to the server. Please check your internet connection."));
										}else
										{
											var responseParams = Ext.decode(response.responseText);

											if(!responseParams.success) {
												Ext.MessageBox.alert(t("Error"), responseParams.feedback);
												return;
											}

											this.WebFeedsGrid.store.reload();
											this.manageWebFeedsWindow.hide();
											rssTabPanel.items.each(function(p){ // Walk through tabs
												if(!GO.util.empty(responseParams.data) && responseParams.data[p.id]==undefined) // Deleted feed
													rssTabPanel.remove(p);
												else // Feed already exists
												{
													var r = responseParams.data[p.id];

													if(p.feed != r.url || p.getView().showPreview != r.summary)
														p.loadFeed(r.url, r.summary);
													if(p.title != r.title)
														p.setTitle(r.title);
													delete responseParams.data[p.id]; //Remove id (don't create it again)
												}
											}, this);
											for(var i in responseParams.data) //For each new id
											{
												if(i != 'remove')
												{
													rssTabPanel.add(new GO.portlets.rssFeedPortlet({
														autoHeight: true,
														feed_id: responseParams.data[i].id,
														feed: responseParams.data[i].url,
														title: responseParams.data[i].title,
														showPreview:responseParams.data[i].summary,
														closable:false
													}));
												}
											}
											if(rssTabPanel.items.length == 0)
											{
												rssTabPanel.add(new Ext.Panel({
													title: '<br />',
													html: '<br />'+t("No RSS feeds have been added.<br />Click the settings button in the top right corner of this window to add feeds.", "summary"),
													cls: 'go-form-panel'
												}));
												rssTabPanel.setActiveTab(0);
											}
											if(rssTabPanel.getActiveTab() == null)
												rssTabPanel.setActiveTab(0)
										}
									},
									scope:this
								});
							},
							scope: this
						}],
						listeners:{
							show: function(){
								if(!this.WebFeedsGrid.store.loaded)
								{
									this.WebFeedsGrid.store.load();
								}
							},
							scope:this
						}
					});
				}
				this.manageWebFeedsWindow.show();     
			}
		},{
			id:'close',
			handler: function(e, target, panel){
				panel.removePortlet();
			}
		}],
		items: rssTabPanel
	});

	GO.summary.portlets['portlet-rss-reader'].on('render',function(){
		Ext.Ajax.request({
			url: GO.url('summary/rssFeed/store'),
			params: {},
			waitMsg: t("Loading..."),
			waitMsgTarget: 'portlet-rss-reader',
			scope:this,
			callback: function(options, success, response){
				if(!success)
				{
					Ext.MessageBox.alert(t("Error"), t("Could not connect to the server. Please check your internet connection."));
				}else
				{
					var rssTabPanels = Ext.decode(response.responseText);
					if(rssTabPanels.results.length == 0)
					{
						rssTabPanel.add(new Ext.Panel({
							title: '<br />',
							html: '<br />'+t("No RSS feeds have been added.<br />Click the settings button in the top right corner of this window to add feeds.", "summary"),
							cls: 'go-form-panel'
						}));
						rssTabPanel.setActiveTab(0);
					}
					else
					{
						for(var i=0;i<rssTabPanels.results.length;i++){
							rssTabPanel.add(new GO.portlets.rssFeedPortlet({
								autoHeight: true,
								style:'max-height: 600px;overflow-y:auto;overflow-x: hidden',
								feed_id: rssTabPanels.results[i].id,
								feed: rssTabPanels.results[i].url,
								title: rssTabPanels.results[i].title,
								showPreview:rssTabPanels.results[i].summary,
								closable:false
							}));
							rssTabPanel.setActiveTab(0);
						};
					}
				}
				this.doLayout();
			}
		});
	});
	
	/* start note portlet */
	
	
	var noteInput = new Ext.form.TextArea({
		hideLabel: true,
		name: 'text',
		anchor: '100% 100%'
		
	});
	
	noteInput.on('change', function(){
		notePanel.form.submit({
			url: GO.url('summary/note/submit'),
			params: {},
			waitMsg: t("Saving...")			
		});
	});
	
	var notePanel = new Ext.form.FormPanel({
		items: {
			xtype: "fieldset",
			anchor: "100% 100%",
			items: noteInput
		},
		waitMsgTarget: true
	});
	
	notePanel.on('render', function(){
		notePanel.load({
			url: GO.url('summary/note/load'),
			params:{},
			waitMsg: t("Loading...")
		});				
	});
	
	GO.summary.portlets['portlet-note']=new GO.summary.Portlet({
		id: 'portlet-note',
		//iconCls: 'note-icon',
		title: t("Notes", "summary"),
		layout:'fit',
		tools: [{
			id:'close',
			handler: function(e, target, panel){
				panel.removePortlet();
			}
		}],
		items: notePanel,
		height:300
	});
		
	
	
	GO.summary.announcementsPanel = new GO.summary.AnnouncementsViewGrid();
	GO.summary.announcementsPanel.store.on('load', function(){
		if(GO.summary.announcementsPanel.store.getCount())
		{
			if(!GO.summary.portlets['portlet-announcements'].isVisible())
			{
				GO.summary.portlets['portlet-announcements'].show();
				GO.summary.portlets['portlet-announcements'].doLayout();
			}
		}else
		{
			GO.summary.portlets['portlet-announcements'].hide();
		}
			
	},this);
	
	GO.summary.portlets['portlet-announcements']=new GO.summary.Portlet({
		id: 'portlet-announcements',
		title: t("Announcements", "summary"),
		layout:'fit',
		items: GO.summary.announcementsPanel,
		autoHeight:true,
		hideMode:'offsets'
	});
	
	GO.summary.portlets['portlet-announcements'].hide();

});
