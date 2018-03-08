GO.ExportQueryDialog = Ext.extend(Ext.Window, {

	/*
	 * Pass extra type radio buttons with this config option
	 */
	customTypes : [],

	initComponent : function() {



		this.formPanelItems[0].items = [{
			boxLabel : 'CSV',
			name : 'type',
			inputValue : 'csv_export_query',
			checked : true
		}, {
			boxLabel : 'PDF',
			name : 'type',
			inputValue : 'pdf_export_query',
			supportsOrientation:true
		}, {
			boxLabel : GO.lang.toScreen,
			name : 'type',
			inputValue : 'html_export_query'
		},
		this.hiddenParamsField = new Ext.form.Hidden({
			name:'params'
		})];

		if(this.query && GO.customexports[this.query]){
			for(var i=0;i<GO.customexports[this.query].length;i++){
				this.formPanelItems[0].items.push({
					boxLabel : GO.customexports[this.query][i].name,
					name : 'type',
					inputValue : GO.customexports[this.query][i].cls
				});
			}
		}

		for(var i=0,max=this.customTypes.length;i<max;i++)
			this.formPanelItems[0].items.push(this.customTypes[i]);


		if(!this.title)
			this.title = GO.lang.cmdExport;
		
		Ext.apply(this, {
			
			items : this.formPanel = new Ext.FormPanel({
				url:BaseHref+'export_query.php',
				items : this.formPanelItems,
				bodyStyle : 'padding:5px'
			}),
			autoHeight : true,
			closeAction : 'hide',
			closeable : true,
			height : 400,
			width : 400,
			buttons : [{
				text : GO.lang.strEmail,
				handler : function() {
					this.hide();

					this.beforeRequest();
					GO.email.showComposer({
						loadUrl : BaseHref + 'json.php',
						loadParams : this.loadParams
					});
				},
				scope : this
			}, {
				text : GO.lang.download,
				handler : function() {

					this.beforeRequest();

					this.formPanel.form.standardSubmit=true;
					this.formPanel.form.getEl().dom.target='_blank';

					this.hiddenParamsField.setValue(Ext.encode(this.loadParams));

					this.formPanel.form.el.dom.target='_blank';

					this.formPanel.form.submit({
						url:BaseHref+'export_query.php',
						params:this.loadParams
					});

//					var downloadUrl = '';
//					for (var name in this.loadParams) {
//
//						if (downloadUrl == '') {
//							downloadUrl = BaseHref
//							+ 'export_query.php?';
//						} else {
//							downloadUrl += '&';
//						}
//
//						downloadUrl += name
//						+ '='
//						+ encodeURIComponent(this.loadParams[name]);
//					}
//					alert(downloadUrl.length);
//					alert(downloadUrl);
//					window.open(downloadUrl);
					this.hide();
				},
				scope : this
			}, {
				text : GO.lang['cmdClose'],
				handler : function() {
					this.hide();
				},
				scope : this
			}]
		});

		GO.ExportQueryDialog.superclass.initComponent.call(this);
	},

	loadParams : {},
	downloadUrl : '',
	showAllFields:false,

	formPanelItems : [{
		autoHeight : true,
		xtype : 'radiogroup',
		fieldLabel : GO.lang.strType,
		listeners:{
			scope:this,
			change:function(group, checkedRadio){
				this.orientationCombo.setDisabled(!checkedRadio.supportsOrientation);
			}
		},
		columns:2,
		items:[]
	},{
		xtype:'checkbox',
		name:'export_hidden',
		hideLabel:true,
		boxLabel:GO.lang.exportHiddenColumns
	},this.orientationCombo = new GO.form.ComboBox({
		xtype:'combo',
		disabled:true,
		fieldLabel : GO.lang.orientation,
		hiddenName : 'orientation',
		store : new Ext.data.SimpleStore({
			fields : ['value', 'text'],
			data : [['L', GO.lang.landscape],
			['P', GO.lang.portrait]]

		}),
		value : 'landscape',
		valueField : 'value',
		displayField : 'text',
		mode : 'local',
		triggerAction : 'all',
		editable : false,
		selectOnFocus : true,
		forceSelection : true
	})],

	show : function(config) {

		GO.ExportQueryDialog.superclass.show.call(this);

		var config = config || {};

		Ext.apply(this, config);

	},

	beforeRequest : function() {
		var columns = [];

		var exportHidden = (this.showAllFields) ? true : this.formPanel.form.findField('export_hidden').getValue();

		if (this.colModel) {
			for (var i = 0; i < this.colModel.getColumnCount(); i++) {
				var c = this.colModel.config[i];
				if ((exportHidden || !c.hidden) && !c.hideInExport)
					columns.push(c.dataIndex + ':' + c.header);
			}
		}

		if (GO.util.empty(this.title))
			this.title = this.query

		Ext.apply(this.loadParams, {
			task : 'email_export_query',
			query : this.query,
			columns : columns.join(','),
			title : this.title
		});

		if (this.subtitle) {
			this.loadParams.subtitle = this.subtitle;
		}

		if (this.text) {
			this.loadParams.text = this.text;
		}
		
		if (this.html) {
			this.loadParams.html = this.html;
		}

		var values = this.formPanel.form.getValues();
		Ext.apply(this.loadParams, values);
	}
});