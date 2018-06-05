/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SearchPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.SearchPanel = function(config)
	{
		if(!config){
			config = {};
		}
	
		config.layout='table';
		config.split=false;
		config.height=40;
		config.forceLayout=true;
		config.baseCls='x-plain';

		config.keys= [{
			key: Ext.EventObject.ENTER,
			fn: function(){
				this.selectedLetter='';
				this.alphabetView.clearSelections();
				this.fireQueryEvent();
			},
			scope:this
		}];

		this.alphabetArray = t("A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z", "addressbook").split(",");
  
		var alphabetStoreData = new Array();
		alphabetStoreData.push({
			value: '[0-9]'
		});
 	
		for(var i = 0;i<this.alphabetArray.length;i++)
		{
			alphabetStoreData.push({
				value: this.alphabetArray[i]
				});
		}
    
		var tpl = new Ext.XTemplate(
			'<tpl for=".">',
			'<span class="letter" onclick="">{value}</span>',
			'</tpl>'
			);
  
		this.selectedLetter = 0;
		this.alphabetView = new Ext.DataView({
			height:30,
			store: new Ext.data.JsonStore({
				fields: ['value'],
				data : alphabetStoreData
			}),
			tpl: tpl,
			singleSelect: true,
			cls: 'alphabet-view',
			overClass:'alphabet-view-over',
			selectedClass: 'alphabet-view-selected',
			itemSelector:'span.letter'
		});
 	
		this.alphabetView.on('selectionchange',
			function(dataview, arraySelections)
			{
				if(arraySelections[0])
				{
					this.selectedLetter = arraySelections[0].innerHTML;
					this.queryField.setValue("");
					this.fireQueryEvent();
				}
			},
			this);
 	
		this.alphabetView.on('containerclick',
			function(dataview, e)
			{
				return false;
			},
			this);
 	
 	
 	
		this.queryField = new Ext.form.TextField({
			name: 'query',
			width: 200,
			emptyText:t("Search")+ ' '+t("(Use '%' as a wildcard) ", "addressbook")
		});
 	
		config.defaults={
			border: false,
			cls:'ab-search-form-panel',
			baseCls:'x-plain',
			forceLayout:true
		};
		config.items=[{
			items: this.alphabetView
		},{
			items: this.queryField
		}
		,
		{
			items: new Ext.Button({
				handler: function()
				{
					this.selectedLetter='';
					this.alphabetView.clearSelections();
					this.fireQueryEvent();
				},
				text: t("Search"),
				scope: this
			})
		},{
			items: new Ext.Button({
				handler: function()
				{
					this.selectedLetter='';
					this.alphabetView.clearSelections();
					this.queryField.setValue("");
									
					this.fireQueryEvent();
				},
				text: t("Reset"),
				scope: this
			})
		}];
	
		GO.addressbook.SearchPanel.superclass.constructor.call(this, config);
	
		this.addEvents({
			queryChange : true
		});
	}

Ext.extend(GO.addressbook.SearchPanel, Ext.Panel, {
	selectedLetter : ''
	,
	fireQueryEvent : function(){
		var params = {
			clicked_letter : this.selectedLetter,
			query : this.queryField.getValue(),
			advancedQueryData : ''
		};
		
		this.fireEvent('queryChange', params);
	}
	
});
