/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: MainPanel.js 22356 2018-02-09 16:33:58Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

 GO.summary.MainPanel = function(config)
 {
 	
 	if(!config){
 		config={};
 	}
 	
 	
 	var state  = Ext.state.Manager.get('summary-active-portlets');
 	
 	if(state) {
 		state=Ext.decode(state);
 		if(!state[0] || state[0].col=='undefined') {
	 		state=false;
	 	}
 	}
 	
 	if(!state) {
 		this.activePortlets=['portlet-announcements', 'portlet-tasks', 'portlet-calendar','portlet-note'];
 		state=[{id:'portlet-announcements', col:0},{id:'portlet-tasks', col:0},{id:'portlet-calendar', col:1},{id:'portlet-note', col:1}];
 	}
 	this.activePortlets = [];
 	for(var i=0,l=state.length;i<l;i++) {
 		this.activePortlets.push(state[i].id);
 	}
 	
 	
 	this.columns = [/*{
				columnWidth:.33,
	      style:'padding:10px 0 10px 10px',
	      border:false
	  	},*/
	  	{
			columnWidth:.5,
			mobile: {
				columnWidth: 1,
			},
			style:'padding:10px 0 10px 10px',
			border:false
	  	},
	  	{
			columnWidth:.5,
			mobile: {
				columnWidth: 1,
			},
			style:'padding:10px 10px 10px 10px',
			border:false
	  	}];

	//var portletsPerCol = Math.ceil(this.activePortlets.length/this.columns.length);

 // var portletsInCol=0;
 // var colIndex=0;
  
		for(var p=0;p<state.length;p++) {
	  	    if(GO.summary.portlets[state[p].id] || GO.summary.portlets[state[p].portletType]) {
			
	  	//var index = Math.ceil((p+1)/portletsPerCol)-1;
	  	
	  	/*if(portletsInCol==portletsPerCol)
	  	{
	  		portletsInCol=0;
  			colIndex++;
	  	}*/
	  	  	
	  	        var column = this.columns[state[p].col];
			
				if(state[p].multiple){
					var portlet = new GO.summary.Portlet(GO.summary.portlets[state[p].portletType]);
					portlet.id = state[p].id;
				} else {
					var portlet = GO.summary.portlets[state[p].id];
				}

				portlet.mainPanel = this;
			
				if(state[p].settings){
					portlet.settings = state[p].settings;
				}
			
			
				portlet.on('remove_portlet', function(portlet){
		            portlet.ownerCt.remove(portlet, false);
		            portlet.hide();
		            this.saveActivePortlets();
	  	    }, this, {single: true});
			
			
	  	    if(!column.items) {
	  		    column.items=[portlet];
	  	    } else {
	  		    column.items.push(portlet);
	  	    }
	  	    //portletsInCol++;
		}
    }
  
	this.availablePortletsStore = new Ext.data.JsonStore({
		id: 'id',
	    root: 'portlets',
	    fields: ['id', 'title', 'iconCls', 'portletType', 'multiple']
	});
	
	for(var p in GO.summary.portlets) {
  	    if(typeof(GO.summary.portlets[p])=='object') {
		  	var indexOf = this.activePortlets.indexOf(p);
		  	if(indexOf==-1 || GO.summary.portlets[p].multiple) {
	  		    this.availablePortlets.push(GO.summary.portlets[p]);
	  	    }
  	    }
    }
    config.items = this.columns;
  
    if(!config.items)  {
    	config.html = t("You don't have any items on your start page.", "summary");
    }

    var tbarItems = [{
	 	xtype:'htmlcomponent',
		html:t("Start page", "summary"),
		cls:'go-module-title-tbar'
	},{
		id: 'add',
		text: t("Add"),
		iconCls:'ic-add',
		handler: this.showAvailablePortlets,
		scope: this
    }];

	if(GO.settings.modules.summary.write_permission) {
		tbarItems.push({
	  	    text: t("Manage announcements", "summary"),
			iconCls:'btn-settings',
			handler: function(){
	  		    if( !this.manageAnnouncementsWindow) {
	  			    this.manageAnnouncementsWindow = new go.Window({
		                layout:'fit',
		                items:this.announcementsGrid =  new GO.summary.AnnouncementsGrid(),
		                width:700,
		                height:400,
		                title:t("Announcements", "summary"),
		                closeAction:'hide',
		                buttons:[{
								text: t("Close"),
								handler: function(){this.manageAnnouncementsWindow.hide();},
								scope:this
						}],
						listeners:{
							show: function(){
								if(!this.announcementsGrid.store.loaded) {
									this.announcementsGrid.store.load();
								}							
							},
							scope:this
						}
	  			    });
	  			
	  			    this.announcementsGrid.store.on('load',function(){
	  				    this.announcementsGrid.store.on('load',function(){
	  					    GO.summary.announcementsPanel.store.load();
	  				    }, this);
	  			    }, this);
	            }
	  		    this.manageAnnouncementsWindow.show();
	  	    },
	  	    scope: this
	    });
	} 

	this.tbar=new Ext.Toolbar({
		cls:'go-head-tb',
		items:tbarItems
	});
  
	GO.summary.MainPanel.superclass.constructor.call(this,config);
	
	this.on('drop', this.saveActivePortlets, this);

};	

Ext.extend(GO.summary.MainPanel, GO.summary.Portal, {
	
	activePortlets : Array(),
	availablePortlets : Array(),
	
	saveActivePortlets : function() {
		this.activePortlets = [];
		var state = [];
		for(var c=0;c<this.items.items.length;c++) {
			var col = this.items.items[c];
			for(var p=0;p<col.items.items.length;p++) {
				var id = col.items.items[p].id;
				this.activePortlets.push(id);
				state.push({id: id, col: c, portletType: col.items.items[p].portletType, multiple: col.items.items[p].multiple, settings: col.items.items[p].settings});
			}
		}
		
		this.availablePortlets=[];
		for(var p in GO.summary.portlets) {
	  	    if(typeof(GO.summary.portlets[p])=='object' && (this.activePortlets.indexOf(p) == -1 || GO.summary.portlets[p].multiple)) {
	  		    this.availablePortlets.push(GO.summary.portlets[p]);
	  	    }
	    }
	  
		this.availablePortletsStore.loadData({portlets: this.availablePortlets});
		Ext.state.Manager.set('summary-active-portlets', Ext.encode(state));
	},
	
	
	showAvailablePortlets : function(){

		if(!this.portletsWindow)
		{			
			var tpl ='<tpl for=".">'+
				'<div class="go-item-wrap">{title}</div>'+
				'</tpl>';
			
			var list = new GO.grid.SimpleSelectList({store: this.availablePortletsStore, tpl: tpl,  emptyText: t("No items to display")});
			
			list.on('click', function(dataview, index){
				
				var id = dataview.store.data.items[index].data.id;
				if(dataview.store.data.items[index].data.multiple) {
					id = dataview.store.data.items[index].data.portletType;
				}
				
				this.addPortlet(id);
				
				this.saveActivePortlets(true);			
				
				list.clearSelections();
				this.portletsWindow.hide();			
								
			}, this);
			
			this.portletsWindow = new go.Window({
				title: t("Select portlet", "summary"),
				layout:'fit',
				modal:false,
				height:400,
				width:600,
				closable:true,
				closeAction:'hide',	
				items: new Ext.Panel({
					items:list,
					cls: 'go-form-panel'
				})
			});
		}
		this.portletsWindow.show();
		this.availablePortletsStore.loadData({portlets: this.availablePortlets});
		
	},
	addPortlet : function(id) {
		
		var portlet;
		
		if(GO.summary.portlets[id].multiple) {
			portlet = new GO.summary.Portlet(GO.summary.portlets[id]);
			portlet.id = 'portlet-'+Ext.id();
		}else
		{
		  portlet = GO.summary.portlets[id];
		}
		
		portlet.mainPanel = this;
		
		portlet.on('remove_portlet', function(portlet){
	  		portlet.ownerCt.remove(portlet, false);
	  		portlet.hide(); 		
	  		this.saveActivePortlets();
	  	}, this, {single: true});
		
		this.items.items[0].add(portlet);
		if(this.rendered) {
			portlet.show();
			this.items.items[0].doLayout();
		}
	}
});

GO.moduleManager.addModule('summary', GO.summary.MainPanel, {
	title : t("Start page", "summary"),
	iconCls : 'go-tab-icon-summary'
});



GO.mainLayout.onReady(function(){
	
	if(go.Modules.isAvailable("legacy", "summary")) {
		GO.request({
			url: 'summary/announcement/checkLatestRead',
			success: function(response,options,result) {
				if (result.has_unread) {
					GO.mainLayout.openModule('summary');
				}
			}
		});
	};
	
});
