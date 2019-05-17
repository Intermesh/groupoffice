go.import.CsvMappingDialog = Ext.extend(go.Window, {
	modal: true,
	entity: null,
	blobId: null,
	values: null,
	
	
	width: dp(500),
	height: dp(500),
	title: t("Import Comma Separated values"),
	autoScroll: true,
	
	
	initComponent : function() {		

		
		
		this.formPanel = new Ext.form.FormPanel({
			
			items: [
				this.fieldSet = new Ext.form.FieldSet({
					labelWidth: dp(200),
					items: [{
							xtype: 'box',
							autoEl: 'p',
							html: t("Please match the CSV columns with the correct Group-Office columns and press 'Import' to continue.")
					}]
				})
			]
		});
		
		this.items = [this.formPanel];
		
		this.buttons = [
			'->',
			this.importButton = new Ext.Button({
				cls: 'raised',
				text: t("Import"),
				handler: this.doImport,
				scope: this
			})
		];
		
		go.import.CsvMappingDialog.superclass.initComponent.call(this);
		
		
		go.Jmap.request({
			method: this.entity + '/importCSVMapping',
			params: {
				blobId: this.blobId
			},
			callback: function(options, success, response) {
				
				if(!success) {
					Ext.MessageBox.alert(t("Error"), response.message);
					return;
				}
				var store = this.createGOHeaderStore(response.goHeaders);
				
				var index = 0;
				response.csvHeaders.forEach(function(h) {
					
					var storeIndex = store.find('name', h), v = null;
					if(storeIndex == -1) {
						storeIndex = store.find('label', h);
					};
					
					if(storeIndex > -1){
						console.log(store.getAt(storeIndex));
					  v = store.getAt(storeIndex).data.name;
					}
				
					
					this.fieldSet.add(this.createCombo({
						store: store,
						hiddenName:index++,
						fieldLabel: h,
						value: v
					}));
				}, this);
				
				this.doLayout();
			},
			scope: this
		});
	},
	
	createGOHeaderStore : function(headers) {
			var me = this, store = new Ext.data.ArrayStore({
			fields: ['name', 'label'],
			data: headers.map(function(h) {
				var label = me.labels[h.name] || h.label || h.name;
				return [h.name, label];
			})
		});
		
		return store;
	},
	
	createCombo : function(config) {
		return new go.form.ComboBox(Ext.apply(config,{
				displayField:'label',
				valueField:	'name',
				mode: 'local',
				triggerAction: 'all',
				editable:false
			}));
	},
	getMapping : function() {
		var mapping = {};
		this.fieldSet.items.each(function(i) {
			if(!Ext.isDefined(i.hiddenName)) {
				return true;
			}
			var path = i.getValue();
			if(path) {
				mapping[i.hiddenName] = path;
			}
		});
		
		return mapping;
	},
	doImport: function() {
		this.getEl().mask(t("Importing..."));
		go.Jmap.request({
			method: this.entity + "/import",
			params: {
				blobId: this.blobId,
				values: this.values,
				mapping: this.getMapping()
			},
			callback: function (options, success, response) {
				this.getEl().unmask();
				if (!success) {
					Ext.MessageBox.alert(t("Error"), response.errors.join("<br />"));
				} else
				{
					Ext.MessageBox.alert(t("Success"), t("Imported {count} items").replace('{count}', response.count));
					this.close();
				}

				// if (this.callback) {
				// 	this.callback.call(this.scope || this, response);
				// }
				
				this.close();
			},
			scope: this
		});
	}
});
