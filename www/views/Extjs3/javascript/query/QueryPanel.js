//QueryPanel2
Ext.ns('GO.query');

GO.query.QueryPanel = Ext.extend(Ext.Panel , {
	autoScroll: true,
	layout: 'anchor',
	style: {overflow: 'auto'},

//	initComponent : function(){
//		GO.query.QueryPanel.superclass.initComponent.call(this);
//
//		this.addEvents(
//			'createNew'
//		);
//	},
//	
	constructor: function (config) {
		config = config || {};
		
		config.fieldStore = new GO.data.JsonStore({
			//url: GO.url("core/modelAttributes"),
			url:config.modelAttributesUrl,
			id:'name',
			baseParams:{
				modelName: config.modelName,
				exclude: config.modelExcludeAttributes
			},
			fields: ['name','label','gotype'],
			remoteSort: true,
			autoLoad: true
		});
//		var idRecord = new Ext.data.Record({'name':'t.id','label':'ID','gotype':'numberfield'},'id');
//		config.fieldStore.on('load',function(){
//			config.fieldStore.insert(0,[idRecord]);
//		},this);
		
		config.criteriaRecord =  Ext.data.Record.create([
			{name: 'andor',type: 'string'},
			{name: 'gotype',type: 'string'},
			{name: 'field',type: 'string'},
			{name: 'comparator',type: 'string'},
			{name: 'value'},
			{name: 'start_group',type:'string'}
		]);
		
		
		//add furst criteri item
		config.title = t("New");
		config.tools= [{
				id:'add',
				handler:this.onCreateNew,
				qtip:t('Create new query'),
				scope:this
		}];
		config.tbar = [
			new Ext.Button({
				iconCls: 'ic-add',
				text: t("Add query argument"),
				handler: function(){
					this.newCriteria();
				},
				scope: this
			}),
			'->',
			new Ext.Button({
				iconCls: 'ic-delete',
				text: t("Reset"),
				handler: function(){
					this.reset();
				},
				scope: this
			})
		];
		
		
		config.criteriaStore = new GO.data.JsonStore({
			fields: ['andor','field','comparator', 'value','start_group','gotype','rawValue','rawFieldLabel'],
//				remoteSort: true,
			listeners: {
				scope: this,
				add: function( store, records, options ) {

					Ext.each(records, function(record) {
						this.addCriteriaPanel(record);
					}, this);

				}
			}
		});

		GO.query.QueryPanel.superclass.constructor.call(this, config);
		
	},
	
	onCreateNew : function(){
		this.fireEvent('createNew', this);
		this.reset();
		this.newCriteria();
	},
	
	afterRender: function () {
		
		GO.query.QueryPanel.superclass.afterRender.call(this);
	},
	
	addCriteriaPanel: function(record) {
		
		var criteriaPanel = new GO.query.CriteriaFormPanel({
			fieldStore: this.fieldStore,
			trackResetOnLoad: true,
			items: [
				new Ext.Button({
					iconCls: 'ic-close',
					tooltip: t("Delete"),
					handler: function() {
						// remove the criteria form panel
						this.criteriaStore.remove(record);
						this.remove(criteriaPanel);
						this.doLayout();
					},
					scope: this
				})
			],
			monitorValid: true,
			listeners: {
				scope: this,
				clientvalidation: function(form, valid) {
					
					var formVal = form.getValues();

					record.set('andor', formVal.andor);
					record.set('field', formVal.field);
					record.set('comparator', formVal.comparator);
					record.set('value', formVal.value);
					record.set('start_group', formVal.start_group);
					record.set('gotype', formVal.gotype);
					record.set('rawValue', formVal.rawValue);
					record.set('rawFieldLabel', formVal.rawFieldLabel);
				}
			}
		});
		
		
		this.add(criteriaPanel);
		
		
		var values = {
			andor: record.get('andor'),
			field: record.get('field'),
			comparator: record.get('comparator'),
			value: record.get('value'),
			start_group: record.get('start_group'),
			gotype: record.get('gotype'),
			rawValue: record.get('rawValue'),
			rawFieldLabel: record.get('rawFieldLabel')
		};
		
		criteriaPanel.setValues(values);
		
		
		this.doLayout();
	},
		
	
	newCriteria: function() {
		
		//insertRow
		var rec = new this.criteriaRecord({
			andor:'AND',
			comparator:'LIKE',
			start_group:false
		});
		var count = this.criteriaStore.getCount();
		this.criteriaStore.insert(count, rec);
		
	},
	
	
	getData : function(dirtyOnly){
		
		var data = [];
		this.criteriaStore.each(function(rec) {
			data.push(rec.data);
		});
		
		return data;
	},
	
	clear: function() {
		this.criteriaStore.removeAll();
		this.removeAll();
		this.doLayout();
	},
	
	reset: function() {
		this.clear();
		this.setQueryTitel(t("New"))
	},
	
	setQueryTitel: function (value) {
		this.setTitle(value);
	}
	
});
