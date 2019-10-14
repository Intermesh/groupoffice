/* global GO */

GO.customfields.nonGridTypes = [
	'GO\\Customfields\\Customfieldtype\\Textarea',
	'GO\\Customfields\\Customfieldtype\\Html', 
	'GO\\Customfields\\Customfieldtype\\Header', 
	'GO\\Customfields\\Customfieldtype\\Infotext'
];

GO.customfields.addColumns = function(link_type, fields) {
	if(!GO.customfields.columns[link_type]) {
		return;
	}
	for(var i = 0; i < GO.customfields.columns[link_type].length; i++) {
		if(GO.customfields.nonGridTypes.indexOf(GO.customfields.columns[link_type][i].datatype) == -1 &&
				GO.customfields.columns[link_type][i].exclude_from_grid !== 'true') {

			fields.fields.push(GO.customfields.columns[link_type][i].dataIndex);
			fields.columns.push(GO.customfields.columns[link_type][i]);
		}
	}
};

GO.customfields.getMatchingFieldNamesMap = function(sourceLinkId, targetLinkId){
	var sourceFields={};
	for(var i = 0; i < GO.customfields.types[sourceLinkId].panels.length; i++) {
		var p = GO.customfields.types[sourceLinkId].panels[i];
		for(var n = 0; n < p.customfields.length; n++) {
			sourceFields[p.customfields[n]['name']] = p.customfields[n]['databaseName'];
		}
	}

	var map = {};
	for(var i = 0; i < GO.customfields.types[targetLinkId].panels.length; i++) {
		var p = GO.customfields.types[targetLinkId].panels[i];
		for(var n = 0; n < p.customfields.length; n++) {
			if(sourceFields[p.customfields[n]['name']]){
				map[sourceFields[p.customfields[n]['name']]]=p.customfields[n]['databaseName'];
			}
		}
	}
	return map;
};

GO.customfields.getFormField = function(customfield, config) {
	config = config || {};
	
	if(!GO.customfields.dataTypes[customfield.datatype]) {
		console.debug("Could not find custom field of type: "+customfield.datatype+". Is this module installed?");
		return false;
	}

	return GO.customfields.dataTypes[customfield.datatype].getFormField(customfield, config);

};


GO.customfields.MainPanel = function(config){

	config = config || {};

	this.typePanel = new GO.customfields.TypePanel({
		region:'center',
		border: true
	});

	var navData = [];

	for(var link_type in GO.customfields.types)
	{
		navData.push([link_type, GO.customfields.types[link_type].name]);
	}

	var navStore = new Ext.data.SimpleStore({
		fields: ['link_type', 'name'],
		data : navData
	});

	this.navMenu= new GO.grid.SimpleSelectList({
		store: navStore
	});


	this.navMenu.on('click', function(dataview, index){

		var link_type = dataview.store.data.items[index].data.link_type;
		this.typePanel.setLinkType(link_type);
		this.typePanel.store.load();

	}, this);

	this.navPanel = new Ext.Panel({
		region:'west',
		title:t("Menu"),
		autoScroll:true,
		width: dp(256),
		split:true,
		resizable:true,
		items:this.navMenu
	});



	config.items=[
		this.navPanel,
		this.typePanel
	];

	config.layout='border';
	GO.customfields.MainPanel.superclass.constructor.call(this, config);

};


Ext.extend(GO.customfields.MainPanel, Ext.Panel, {
	afterRender : function(){
		GO.customfields.MainPanel.superclass.afterRender.call(this);
	}
});


GO.customfields.categoriesStore = new GO.data.JsonStore({
	//url: GO.settings.modules.customfields.url+'json.php',
	url:GO.url('customfields/category/store'),
	totalProperty: "count",
	baseParams:{
		link_type:0
	},
	root: "results",
	id: "id",
	fields:[
	'id',
	'name'
	]
});


GO.customfields.displayPanelTemplate =
	'<tpl if="values.customfields && customfields.length">'+
	'<tpl for="customfields">'+
'{[this.collapsibleSectionHeader(values.name, "cf-"+parent.panelId+"-"+values.id, "cf-"+values.id)]}'+
'<table cellpadding="0" cellspacing="0" border="0" class="display-panel" id="cf-{parent.panelId}-{id}">'+

'<tpl for="fields">'+
'<tpl if="!value">'+
'<tr>'+
'<td class="table_header_links" colspan="2">{name}</td>'+
'</tr>'+
'</tpl>'+
'<tpl if="value && value.length">'+
	'<tr>'+
	'<td>{name}:</td>'+
	'{[GO.customfields.renderType(values)]}'+
	'</tr>'+
'</tpl>'+
'</tpl>'+
'</table>'+
'</tpl>'+
'</tpl>';

GO.customfields.renderType = function(data) {
	switch(data.datatype) {
		case 'GO\\Customfields\\Customfieldtype\\FunctionField':
		case 'GO\\Customfields\\Customfieldtype\\Number':
			return '<td style="text-align: right;">'+data.value+'</td>';
		case 'GO\\Files\\Customfieldtype\\File':
			return '<td>'+data.value+'</td>'; /* '<td>'+data.value+'</td>'+
				'<td style="white-space:nowrap;"><a onclick="" style="display:block;float: right;" class="go-icon btn-edit">&nbsp;</a></td>';*/
		default:
			return '<td>'+data.value+'</td>';
	}
};

GO.customfields.displayPanelBlocksTemplate =
'<tpl if="values.items_under_blocks && values.items_under_blocks.length">'+
	'<tpl for="items_under_blocks">'+
	'{[this.collapsibleSectionHeader(values.block_name, "block-"+parent.panelId+"-"+values.id,"block")]}'+
//	'{[this.collapsibleSectionHeader(values.block_name, "block-"+parent.panelId+"-"+values.id, "block-"+values.id)]}'+
		'<table cellpadding="0" cellspacing="0" border="0" class="display-panel" id="block-{parent.panelId}-{values.id}">'+
			'<tpl for="items">'+
				'<tr>'+
				'<td class="table_header_links" style="width:30px;">'+
					'<div class="display-panel-link-icon go-model-icon-{[this.replaceWithUnderscore(values.model_name)]}" ext:qtip="{values.type}">'+'</div>'+
				'</td>'+
				'<td class="table_header_links">'+
					'<a  onclick="GO.linkHandlers[\'{[values.model_name.replace(/\\\\/g, \'\\\\\\\\\')]}\'].call(this,{model_id});">{item_name}</a>'+
				'</td>'+
				'</tr>'+
			'</tpl>'+
		'</table>'+
	'</tpl>'+
'</tpl>';



