/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: DisplayPanel.js 19345 2015-08-25 10:11:22Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.DetailView = Ext.extend(Ext.Panel,{

	cls : 'go-detail-view',
	autoScroll:true,
	
	hasLinks : true,
	store: null,
	data: {},
	currentId: null,
	basePanels: [],
	
	isDisplayPanel : true, //for automatic refresh after save
	entityStore : null,
	
	initComponent : function() {
		go.panels.DetailView.superclass.initComponent.call(this,arguments);

		this.on('render', function() {
			this.reset();
		}, this);

	},


	reset : function(){

		this.data={};
		this.currentId = null;
		
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(true);
		}

		this.items.each(function (item, index, length) {
			item.hide();
		}, this);
		
		this.fireEvent('reset', this);
	},

	onLoad : function() {
		this.items.each(function(item, index, length){
			item.show();
			if(item.tpl) {
				item.update(this.data);
			}
			if (item.onLoad) {
				item.onLoad.call(item, this);
			}
		},this);
		this.doLayout();
		this.body.scrollTo('top', 0);
	},
	
	reload: function() {
		if(this.currentId) {
			this.load(this.currentId);
		}
	},
	// old way
	load : function(id, reload)
	{
		this.fireEvent('beforeload', this, id, reload);
		this.currentId = id;

			GO.request({
				maskEl:this.body,
				method:'GET',
				url: this.loadUrl,
				headers: {"Content-type": "application/json"},
				params:{id: id},
				success: function(options, response, result) {				
					this.data = result.data;
					
					if(this.getTopToolbar()) {
						this.getTopToolbar().setDisabled(false);
					}
					
					this.onLoad();

					this.fireEvent('load',this, this.model_id);
				},
				scope: this			
			});
		
	}
});

Ext.reg("tmpdetailview", GO.DetailView);