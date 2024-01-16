/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: MessagesGrid.js 22437 2018-03-01 07:55:17Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.MessagesGrid = function(config){

	config = config || {};
	config.layout='fit';
	config.autoScroll=true;
	config.paging=true;

	config.hideMode='offsets';
		
		config.cm =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true,
			groupable:false
		},
		columns:[
		{
			id:'icon',
			header:"&nbsp;",
			width:dp(40),
			dataIndex: 'icon',
			renderer: this.renderIcon,
			hideable:false,
			sortable:false
		},{
			id: 'labels',
			header: t("Labels", "email"),
			width:dp(60),
			xtype: 'templatecolumn',
			tpl: new Ext.XTemplate('<div class="em-messages-grid-labels-container"><tpl for="labels"><div ext:qtip="{name}" style="background-color: #{color}">&nbsp;</div></tpl></div>'),
			hidden:true,
			sortable:false
		},{
			header: t("From", "email"),
			dataIndex: 'from',
			renderer:{ 
				fn: this.renderMessage,
				scope: this
			},
			css: 'white-space:normal;',
			id:'message'

		},{
			id:'arrival',
			header: t("Date"),
			dataIndex:'internal_udate',
			hidden:true,
			groupable:true,
			align:'right',
			width: dp(100),
			renderer: function(value, metaData, record, rowIndex, colIndex, store){
				return !store.groupField ? go.util.Format.dateTime(value) : go.util.Format.time(value);
			},
			groupRenderer : function(value){
				return go.util.Format.shortDateTime(value,false,true, true);
			}
		},{
			id:'date',
			header: t("Date sent", "email"),
			dataIndex:'udate',
			groupable:true,
			align:'right',
			width: dp(100),
			renderer: function(value, metaData, record, rowIndex, colIndex, store){
				return !store.groupField ? go.util.Format.dateTime(value) : go.util.Format.time(value);
			},
			groupRenderer : function(value){
				return go.util.Format.shortDateTime(value,false,true, true);
			}
		},{
			id:'size',
			header: t("Size"),
			dataIndex: 'size',
			width:dp(80),
			align:'right',
			hidden:true,
			renderer:Ext.util.Format.fileSize
		}]
		});
		config.bbar = new Ext.PagingToolbar({
			cls: 'go-paging-tb',
			store: config.store,
			pageSize: parseInt(GO.settings['max_rows_list']),
			//displayInfo: true,
			//displayMsg: t("Total: {2}"),
			emptyMsg: t("No items to display")
		});
	config.bbar.refresh.setVisible(false);

	config.autoExpandColumn='message';

	config.view = new go.grid.GroupingView({
		groupTextTpl:'{group}',
		emptyText: t("No items to display"),
		totalDisplay: true,
		getRowClass:function(row, index) {
			return (row.data.seen == '0') ? 'ml-unseen-row' : 'ml-seen-row';
		}		
	});

	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	config.border=false;
	config.split= true;
	config.header=false;
	config.enableDragDrop= true;
	config.ddGroup = 'EmailDD';
	config.animCollapse=false;

	this.searchType = new Ext.form.Hidden({
		value:GO.email.search_type_default || 'any'
	});

	this.updateSearchTypeChecks = function() {
		this.searchTypeButton.menu.items.each(function(i) {
			if(i.value) {
				i.setChecked(this.searchType.getValue() == i.value);
				if (i.checked) {
					this.searchTypeButton.setIconClass('ic-' + i.icon);
					this.searchTypeButton.setTooltip(i.text);
				}
			}
		}, this)
	}

	this.searchTypeButton = new Ext.Button({
		iconCls: 'ic-star',
		menu: new Ext.menu.Menu({
			listeners: {
				beforeshow: function(menu) {
					this.updateSearchTypeChecks();
				},
				scope: this
			},
			defaults: {
				checked: false,
				listeners: {
					beforecheckchange: function(item, checked) {

						if (!item.value) {
							return false;
						}
					},
					checkchange: function(item, checked) {


						if(checked) {
							this.searchType.setValue(item.value);
							this.updateSearchTypeChecks();

							GO.email.search_type = item.value;

							if(localStorage){
								localStorage.email_search_type = GO.email.search_type;
							}

							if(this.searchField && this.searchField.getValue()) {
								// GO.email.messagesGrid.store.baseParams['search'] = this.searchField.getValue();
								// this.searchField.hasSearch = true;

								// GO.email.messagesGrid.store.reload();
								this.searchField.search();
							}
						}
					},
					scope: this
				},
				group:"radio"
			},
			items: [{
				value: 'any',
				text:  t("Any field", "email"),
				icon: 'select-all'
			}, {
				value: 'from',
				text:  t("From", "email"),
				icon: 'inbox'
			}, {
				value: 'subject',
				text:  t("Subject", "email"),
				icon: 'description'
			}, {
				value: 'to',
				text:  t("Recipient", "email"),
				icon: 'send'
			}, {
				value: 'cc',
				text:  t("Recipient (CC)", "email"),
				icon: 'send'
			}, "-", {
				group: "none",
				iconCls: 'ic-more',
				text: t("Advanced", "email"),
				handler: function(){
					// var first = !this.searchDialog.dialog;
					this.searchDialog.show();
					// if(first) {
					// 	this.searchDialog.dialog.on('hide', function() {
					// 		this.searchField.updateView();
					// 	}, this);
					// }
				},
				scope: this
			}]
		})
	});

	if(GO.settings.config.email_allow_body_search) {
		this.searchTypeButton.menu.insert(5,{
			value: 'fts',
			text:  t("Full message", "email"),
			icon: 'email'
		});
	}


	this.updateSearchTypeChecks();

	this.showUnreadButton = new Ext.Button({
		iconCls: 'ic-markunread',
		enableToggle:true,
		toggleHandler:this.toggleUnread,
		pressed:false,
		tooltip: t("Show unread", "email")
	});
	this.showFlaggedButton = new Ext.menu.CheckItem({
		//iconCls: 'ic-flag',
		enableToggle:true,
		listeners: {checkchange: this.toggleFlagged,scope:this},
		pressed:false,
		text: t("Show flagged", "email")
	});
	
	this.searchDialog = new GO.email.SearchDialog({
		store:config.store,
		grid: this
	});

	
	
	this.settingsMenu = new Ext.menu.Menu({
		items:[{
			iconCls: 'ic-account-box',
			text: t("Accounts", "email"),
			handler: function(){
				this.emailClient.showAccountsDialog();
			},
			scope: this
		}
//		,{
//			iconCls:'ic-view-compact',
//			text: t("Toggle message window position", "email"),
//			handler: function(){
//				this.emailClient.moveGrid();
//			},
//			scope: this
//		}
,
		this.showFlaggedButton
		]
	});


	if(!config.hideSearch)
		config.tbar = [];
	
	GO.email.MessagesGrid.superclass.constructor.call(this, config);

	var me = this;



	if(!config.hideSearch) {
		this.getTopToolbar().enableOverflow = true;

		this.getTopToolbar().add({
				cls: 'go-narrow',
				iconCls: "ic-menu",
				handler: function () {
					this.emailClient.treePanel.show();
				},
				scope: this
			},
			this.composerButton = new Ext.Button({
				iconCls: 'ic-edit',
				desktop: {
					text: t("Compose", "email"),
				},
				mobile: {
					tooltip: t("Compose", "email"),
				},
				cls: 'primary',
				handler: function () {
					GO.email.showComposer({account_id: this.account_id});
				},
				scope: this
			}), this.btnRefresh = new Ext.Button({
				iconCls: 'ic-autorenew',
				tooltip: t("Refresh"),
				handler: function () {
					this.emailClient.refresh(true);
				},
				scope: this
			}), this.deleteButton = new Ext.Button({
				hidden: GO.util.isMobileOrTablet(),
				iconCls: 'ic-delete',
				tooltip: t("Delete"),
				handler: function () {
					this.deleteSelected();
					this.expand();
				},
				scope: this
			}),
			'->',
			this.showUnreadButton,
			this.searchField = new go.toolbar.SearchButton({
				//store: config.store,
				paramName: 'search',
				hidden: config.hideSearch,
				tools: [
					this.moveAllButton = new Ext.Button({
						iconCls: 'ic-move-to-inbox',
						tooltip: t('Move all'),
						disabled: true,
						handler: function (b) {

							Ext.MessageBox.confirm(t("Confirm move"), t("Are you sure you want to move all the emails from the search result? (" + GO.email.messagesGrid.store.reader.jsonData.allUids.length + " emails)"), function (btn) {
								if (btn !== "yes") {
									return;
								}
								this.showMoveMailToDialog();
							}, this);

						},
						scope: this
					}),
					this.deleteAllButton = new Ext.Button({
						iconCls: 'ic-delete-sweep',
						tooltip: t('Delete all'),
						disabled: true,
						handler: function (b) {

							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete all the emails from the search result? (" + GO.email.messagesGrid.store.reader.jsonData.allUids.length + " emails)"), function (btn) {
								if (btn !== "yes") {
									return;
								}

								delete GO.email.messagesGrid.store.baseParams['query'];
								GO.email.messagesGrid.store.baseParams['delete_keys'] = Ext.encode(GO.email.messagesGrid.store.reader.jsonData.allUids);
								//GO.email.messagesGrid.store.load();
								this.searchField.reset();
								this.searchField.back();
								delete GO.email.messagesGrid.store.baseParams['delete_keys'];

							}, this);

						},
						scope: this
					}),
					this.searchType,
					this.searchTypeButton
				],
				listeners: {
					search: function (me, v) {
						config.store.baseParams['search'] = v;
						config.store.load();

						this.moveAllButton.setDisabled(false);
						this.deleteAllButton.setDisabled(false);
					},
					reset: function () {
						this.moveAllButton.setDisabled(true);
						this.deleteAllButton.setDisabled(true);

						this.searchDialog.hasSearch = false;
						delete this.store.baseParams.query;
						delete this.store.baseParams.search;
						delete this.store.baseParams.searchIn;
						this.resetSearch();
						this.store.load({params: {start: 0}});
					},
					scope: this
				},
				hasActiveSearch: function () {
					return me.store.baseParams.search || me.store.baseParams.query;
				}
			}), {
				iconCls: 'ic-more-vert',
				tooltip: t("Settings"),
				menu: this.settingsMenu
			}
		);
	}


	var origRefreshHandler = this.getBottomToolbar().refresh.handler;

	this.getBottomToolbar().refresh.handler=function(){
		this.store.baseParams.refresh=true;
		origRefreshHandler.call(this);
		delete this.store.baseParams.refresh;
	};

	//stop/start drag and drop when store loads when account is readOnly
	this.store.on('load', function(store, records, options) {
		if(this.getView().dragZone){
			if(store.reader.jsonData.permission_level <= GO.permissionLevels.read) {
				this.getView().dragZone.lock();
			} else {
				this.getView().dragZone.unlock();
			}
		}
	}, this);



}

Ext.extend(GO.email.MessagesGrid, go.grid.GridPanel,{

	deleteSelected: GO.grid.GridPanel.prototype.deleteSelected,

	show : function()
	{
		if(GO.email.messagesGrid.store.baseParams['unread'] === 1 || GO.email.messagesGrid.store.baseParams['unread'] === true){
			this.showUnreadButton.pressed=true;
		} else {
			this.showUnreadButton.pressed=false;
		}

		if(!GO.email.search_type)
		{
			GO.email.search_type = GO.email.search_type_default;
		}
		if(!this.hideSearch) {
			this.setSearchFields(GO.email.search_type, GO.email.search_query);
		}

		GO.email.MessagesGrid.superclass.show.call(this);
	},
	resetSearch : function()
	{
		GO.email.search_type = GO.email.search_type_default;
		GO.email.search_query = '';

		this.setSearchFields(GO.email.search_type, GO.email.search_query);
	},
	setSearchFields : function(type, query)
	{
		this.searchType.setValue(type);
		this.searchField.setValue(query);
	},
	toggleUnread : function(item, pressed)
	{
		this.setIconClass(pressed ? 'ic-email' : 'ic-mark-as-unread');
		this.setTooltip(pressed ? t("Show all", "email") : t("Show unread", "email"));
		GO.email.messagesGrid.store.baseParams['unread']=pressed ? 1 : 0;

		GO.email.messagesGrid.store.load();
	},
	toggleFlagged : function(item, pressed)
	{
		GO.email.messagesGrid.store.baseParams['flagged']=pressed ? 1 : 0;
		
		GO.email.messagesGrid.store.load();
	},

	/* @deprecated
	renderNorthMessageRow : function(value, metaData, record){

		if( this.isSpoofed(record)) {
			metaData.css = 'danger';

			value += " &lt;" + record.data.sender + "&gt;";
		}

		if(record.data['seen']=='0')
			return String.format('<div id="sbj_'+record.data['uid']+'" '+this.createQtipTemplate(record)+' class="ml-unseen-mail">{0}</div>', value);
		else
			return String.format('<div id="sbj_'+record.data['uid']+'" '+this.createQtipTemplate(record)+' class="ml-seen-mail">{0}</div>', value);
	},
	*/
	/* @deprecated
	renderMessageSmallRes : function(value, metaData, record){

		if( this.isSpoofed(record)) {
			metaData.css = 'danger';
			value += " &lt;" + record.data.sender + "&gt;";
		}

		if(record.data['seen']=='0')
		{
			return String.format('<div id="sbj_'+record.data['uid']+'" '+this.createQtipTemplate(record)+' class="ml-unseen-from">{0}</div><div class="ml-unseen-subject">{1}</div>', value, record.data['subject']);
		}else
		{
			return String.format('<div id="sbj_'+record.data['uid']+'" '+this.createQtipTemplate(record)+' class="ml-seen-from">{0}</div><div class="ml-seen-subject">{1}</div>', value, record.data['subject']);
		}
	},*/

	createQtipTemplate: function(record){
		var qtipTemplate = '';
		
		if(this.getStore().baseParams.query){
			qtipTemplate = 'ext:qtitle="'+t('folder','email')+'" ext:qtip="' + record.data['mailbox'] + '"';
		}
		
		return qtipTemplate;
	},

	isSpoofed: function(record) {
		if(record.store.reader.jsonData.sent || record.store.reader.jsonData.drafts) {
			return false;
		}

		return Ext.form.VTypes.email(record.data.from) && record.data.from.toLowerCase() != record.data.sender.toLowerCase();
	},

	renderMessage : function(value, metaData, record){

		var deletedCls = record.data.deleted ? 'ml-deleted' : '';

		if( this.isSpoofed(record)) {
			metaData.css = 'danger';
			value += " &lt;" + record.data.sender + "&gt;";
		}

		if(record.data['seen']=='0'){
			return String.format('<div id="sbj_'+record.data['uid']+'" '+this.createQtipTemplate(record)+' class="ml-unseen-from '+deletedCls+'">{0}</div><div class="ml-unseen-subject '+deletedCls+'">{1}</div>', record.data['from'], record.data['subject']);
		}else
		{
			return String.format('<div id="sbj_'+record.data['uid']+'" '+this.createQtipTemplate(record)+' class="ml-seen-from '+deletedCls+'">{0}</div><div class="ml-seen-subject '+deletedCls+'">{1}</div>', record.data['from'], record.data['subject']);
		}
	},

	renderIcon : function(src, p, record){
		var icons = [];
		var unseen = '';
		if(record.data.answered) {
			icons.push('reply');
		}
		if(record.data.forwarded!=0){
			icons.push('forward');
		}
		if(!record.data.seen) {
			var unseen = '<div class="ml-unseen-dot"></div>';
		}
		if(record.data['has_attachments']=='1') {
			icons.push('attachment');
		}
		var priority = record.data['x_priority'];
		if(priority && priority < 3) {
			icons.push('priority_high');
		}
		if(priority && priority > 3) {
			icons.push('low_priority');
		}
		if(record.data['flagged'] == 1){
			icons.push('flag');
		}

		return unseen + icons.map(function(i) {
			return '<i class="icon '+(i!=='flag'?'c-secondary':'red')+'">' + i + '</i>';
		}).join("");
		
	},

	showMoveMailToDialog : function() {
		if (!this._copyMailToDialog) {
			this._copyMailToDialog = new GO.email.CopyMailToDialog({
				move: true
			});
			this._copyMailToDialog.on('copy_email',function(){
				this.searchField.reset();
				this.searchField.back();
			},this);
		}
		this._copyMailToDialog.move = true;

		var allUids = GO.email.messagesGrid.store.reader.jsonData.allUids;
		var selectedEmailMessages = [];
		for (var i=0; i<allUids.length;i++) {
			selectedEmailMessages.push({
				data : {
					account_id : GO.email.messagesGrid.store.baseParams.account_id,
					mailbox : GO.email.messagesGrid.store.baseParams.mailbox,
					uid : allUids[i],
					seen: null,
				}
			});
		}

		this._copyMailToDialog.show(selectedEmailMessages);
	}

});

