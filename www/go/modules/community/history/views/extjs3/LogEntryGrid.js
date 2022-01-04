Ext.define('go.modules.community.history.LogEntryGrid',{
	extend: go.grid.GridPanel,

	layout:'fit',
	autoExpandColumn: 'user',
	stateId: 'logentrygrid-detail',

	multiSelectToolbarEnabled: false,

	deleteSelected: function() {

	},

	// input json object output html
	renderJson: function(json, name) {

		var html = [];
		if(!json) {
			return html;
		}

		if(Ext.isDate(json)) {
			// skip for now
			html.push('datum');
		} else if(Ext.isArray(json)) {
			html.push('<b>' + name + '</b> ');
			for(var i = 0 ; i < json.length; i++) {
				html.push.apply(html, this.renderJson(json[i], ' - '));
			}
		} else if (json === null) {
			html.push('<b>' + name + '</b> null');
		} else if(typeof json === 'object') {
			//html.push('<b>' + key + '</b> ');
			for(var key in json) {
				html.push.apply(html, this.renderJson(json[key], key));
				//html.push(' - <b>' + key + '</b> ' + json[key]);
			}
		} else { // string number bool
			html.push('<b>' + name + '</b> ' + json);
		}

		return html;
	},

	renderJsonValue: function(data) {
		var html = [];
		if(data === null) {
			html.push('<i>null</i>');
		} else if(Ext.isArray(data)) {
			for(var i = 0 ; i < data.length; i++) {
				if(i !== 0) {
					html.push(''); // extra enter
				}
				html.push.apply(html, this.renderJsonValue(data[i]));
			}
		} else if(typeof data === 'object') {
			//html.push.apply(html, this.renderJson(data));
			for(var key in data) {
				html.push('<b>' + key + '</b> ' + data[key]);
			}
		} else {
			html.push(data);
		}
		return html;
	},

	renderOldNew: function(json) {
		if(!json) {
			return [];
		}
		html = ['<table class="display-panel" style="table-layout: fixed;word-wrap:break-word;"><tr class="line"><th>'+t('Name')+'</th><th>'+t('Old')+'</th><th>'+t('New')+'</th></tr>'];
		for(var key in json) {
			html.push('<tr><td>'+key+':</td><td>'+this.renderJsonValue(json[key][1]).join('<br>')+
				'</td><td>'+this.renderJsonValue(json[key][0]).join('<br>')+'</td></tr>');
		}
		html.push('</tr></table>');
		return html;
	},

	forDetailView: true,



	onCellClick : function(grid, rowIndex, colIndex, e) {

		if(e.target.tagName != "BUTTON") {
			return;
		}

		var rec = this.store.getAt(rowIndex), json = JSON.parse(rec.data.changes);

		if(!json) {
			return;
		}

		var target = this.view.getCell(rowIndex, colIndex),
			html = "<p>" + t("Date") + ": " + go.util.Format.dateTime(rec.data.createdAt) + "</p>";

		html += "<h4>" + t("Changes") + "</h4>";

		switch(rec.data.action) {
			case 'update': html += this.renderOldNew(json).join('');
				break;
			case 'create':
			case 'delete': html += this.renderJson(json).join('<br>');
				break;
			case 'login': html += rec.data.createdAt;
				break;
		}

		const win = new go.Window({
			cls: "go-text-dialog",
			closable: true,
			minimizable: true,
			title: rec.data.description,
			html: html,
			autoScroll: true,
			width: dp(500),
			height: dp(500)

		});
		win.show();

		// var tt = new Ext.menu.Menu({
		// 	//target: target,
		// 	//title: rec.data.description,
		// 	width:500,
		// 	html: '<div style="padding:7px;max-height:400px;overflow-y:scroll;"><h5>'+rec.data.description+'</h5>'+html+'</div>' ,
		// 	autoHide: false
		// 	//closable: true
		// });
		// tt.show(target);
	},

	initComponent: function() {


		var cols = [{
			header: t('ID'),
			width: dp(80),
			dataIndex: 'id',
			hidden:true,
			align: "right"
		},{
			id: 'user',
			header: t('User'),
			dataIndex: 'creator',
			width:300,
			renderer: function (v) {
				return v ? v.displayName : "-";
			}
		},{
			header: t('Changes'),
			dataIndex: "changes",
			width: dp(80),
			renderer: function(v, meta, record) {
				if(v) {
					return '<button class="icon">note</button>';
				}
			}
		},{
			header: t('Action'),
			dataIndex: 'action',
			renderer: function(v, meta, r) {
				return t(v.charAt(0).toUpperCase() + v.slice(1));
				//return go.Modules.registered.community.history.actionTypes[v] || 'Unknown';
			}
		},{
			xtype: "datecolumn",
			header: t('Date'),
			dataIndex: 'createdAt'
		}];

		if(!this.forDetailView) {

			cols.push({
				id: "IP",
				header: "IP",
				dataIndex: "remoteIp"
			});

			cols.splice(1,0, {
				header: t('Name'),
				dataIndex: 'description',
				id: 'name'
			},{
				header: t('Entity'),
				dataIndex: 'entity',
				id: 'entity'
			});
			this.autoExpandColumn = 'name';
			this.stateId = 'logentrygrid-main';
		}

		Ext.applyIf(this,{
			store: new go.data.Store({
				fields: [
					{name:'createdAt',type:'date'},
					'id',
					'entity',
					'action',
					'changes',
					'createdBy',
					'description',
					{name: 'creator', type: "relation"},
					'remoteIp'
				],
				baseParams: {sort: [{property: "id", isAscending:false}]},
				entityStore: "LogEntry"
			}),
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("Item was never modified",'community','history') + '</p>',
				totalDisplay: false //heavy impact on performance
			},
			columns: cols
		});

		this.callParent();

		this.on('cellclick', this.onCellClick, this);
	}
});