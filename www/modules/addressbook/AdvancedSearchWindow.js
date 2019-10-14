/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.addressbook.AdvancedSearchWindow = Ext.extend(GO.Window, {
	
	title: t("Advanced search", "addressbook"),
	collapsible: true,
	stateId: 'ab-adv-search-window',
	layout: 'card',
	layoutConfig: {
		deferredRender: false,
		layoutOnCardChange: true
	},
	resizable: true,
	width: 1100,
	height: 700,
	closeAction: 'hide',
	queryId : 0,
	initComponent: function() {
		
		this.items = [{
			layout:'border',
			items:[this._contactsQueryPanel = new GO.query.QueryPanel({
					region:'center',
					modelName:'GO\\Addressbook\\Model\\Contact',
					modelAttributesUrl:GO.url('addressbook/contact/attributes')
				}), this._contactsQueriesGrid = new GO.query.SavedQueryGrid({
					region: 'west',
					queryPanel: this._contactsQueryPanel,
					width:120,
					modelName:'GO\\Addressbook\\Model\\Contact'
				})]
		},{
			layout:'border',
			items:[this._companiesQueryPanel = new GO.query.QueryPanel({			
					region:'center',
					modelName:'GO\\Addressbook\\Model\\Company',
					modelAttributesUrl:GO.url('addressbook/company/attributes')
				}), this._companiesQueriesGrid = new GO.query.SavedQueryGrid({
					region: 'west',
					width:120,
					queryPanel: this._companiesQueryPanel,
					modelName:'GO\\Addressbook\\Model\\Company'
				})
			]
		}];

		this.buttons= [{
			text: t("Save"),
			handler: function(){
				if(this._getModelName()=='GO\\Addressbook\\Model\\Company')
					this._companiesQueriesGrid.showSavedQueryDialog();
				else
					this._contactsQueriesGrid.showSavedQueryDialog();
			},
			scope: this
		},{
			text: t("Execute query"),
			handler: function(){
				this.search();
			},
			scope: this
		}];
		
		GO.addressbook.AdvancedSearchWindow.superclass.initComponent.call(this);
	},
	
	/*
	 * Sets whether, during the time of use of this window, the data type is
	 * 'contact' or 'company', and apply the ensuing changes to this window.
	 * Made to be called from this.show(), but external calls also possible.
	 */
	updateDataType : function(type,masterPanel) {
		if (type!='companies' && type!='contacts')
			Ext.MessageBox.alert(t("strWarning"),"AdvancedSearchWindow.updateDataType() parameter must be either 'contacts' or 'companies'.");
		
		if (type=='contacts')
			this.getLayout().setActiveItem(0);
		else
			this.getLayout().setActiveItem(1);

		this._datatype = type;
	
		if (this._datatype=='contacts') {
			this.externalTargetGrid = masterPanel.contactsGrid;
		} else {
			this.externalTargetGrid = masterPanel.companiesGrid;
		}
	},

	getDatatype : function() {
		if (typeof(this._datatype)=='undefined')
			return false;		
		return this._datatype;
	},
	
	_getModelName : function() {
		switch (this.getDatatype()) {
			case 'contacts':
				return 'GO\\Addressbook\\Model\\Contact';
				break;
			case 'companies':
				return 'GO\\Addressbook\\Model\\Company';
				break;
			default:
				return false;
				break;
		}
	},
	
	show : function(config) {
		GO.addressbook.AdvancedSearchWindow.superclass.show.call(this,config);
		this.updateDataType(config.dataType,config.masterPanel);
	},
	
	search : function(){
		//checkbox values are only returned when ticked
		delete this.externalTargetGrid.store.baseParams.search_current_folder;
		
		if (this.getDatatype()=='contacts')
			this.externalTargetGrid.store.baseParams['advancedQueryData'] = Ext.encode(this._contactsQueryPanel.getData());
		else
			this.externalTargetGrid.store.baseParams['advancedQueryData'] = Ext.encode(this._companiesQueryPanel.getData());
		
		this.externalTargetGrid.store.load();
		this.externalTargetGrid.setDisabled(false);
		this.fireEvent('ok', this);
	},
	
	reset : function(){
		this.externalTargetGrid.store.removeAll();
		this.externalTargetGrid.setDisabled(true);
//		this.setTitle(t("Search files", "filesearch"));
		this.externalTargetGrid.exportTitle=t("Search");
	}
	
	
});
