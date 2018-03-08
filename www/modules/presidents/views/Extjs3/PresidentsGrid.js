/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PresidentsGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.presidents.PresidentsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = t("Presidents", "presidents"); //Title of this panel
	config.layout='fit'; //How to lay out the panel
	config.autoScroll=true;
	config.split=true;
	config.autoLoadStore=true; //Load the datastore when grid render for the first time
	config.paging=true; //Use pagination for the grid
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true; //Mask the grid when it is loading data
	
	//This dialog will be opened when double clicking a row or calling showEditDialog()
	config.editDialogClass = GO.presidents.PresidentDialog;
	
	//Configuring the column model
	config.cm =  new Ext.grid.ColumnModel({
		defaults:{
			sortable: true
		},
		columns : [{
			header: '#',
			readOnly: true,
			dataIndex: 'id',
			renderer: function(value, cell){ 
				cell.css = "readonlycell";
				return value;
			},
			width: 50
		},{
			header: t("First name", "presidents"),
			dataIndex: 'firstname'
		},{
			header: t("Last name", "presidents"),
			dataIndex: 'lastname'
		},{
			header: t("Party", "presidents"),
			dataIndex: 'party_id'
		},{
			header: t("Entering Office", "presidents"),
			dataIndex: 'tookoffice'
		},{
			header: t("Leaving Office", "presidents"),
			dataIndex: 'leftoffice'
		},{
			header: t("Income", "presidents"),
			dataIndex: 'income',
			width: 120,
			renderer: function(value, metaData, record){
				if(record.data.income_val > 1000000){
					metaData.attr = 'style="color:#336600;"';
				} else if (record.data.income_val > 100000){
					metaData.attr = 'style="color:#FF9900;"';
				} else {
					metaData.attr = 'style="color:#CC0000;"';
				}
				return "$ "+value;
			},
			align: "right"
		}]
	});
	
	//Defining the data store for the grid
	config.store = new GO.data.JsonStore({
		url: GO.url('presidents/president/store'),
		fields: ['id','firstname','lastname','party_id','tookoffice','leftoffice','income', 'income_val'],
		remoteSort: true,
		model: 'GO\\Presidents\\Model\\President'
	});
	
	//Adding the gridview to the grid panel
	config.view=new Ext.grid.GridView({
		emptyText: t("strNoItemse")
	});
	
	//Setup a toolbar for the grid panel
	config.tbar = new Ext.Toolbar({
			items: [
				t("Search") + ':',
				new GO.form.SearchField({
					store: config.store,
					width:320
				})
			]
	});

	//Construct the Gridpanel with the above configuration
	GO.presidents.PresidentsGrid.superclass.constructor.call(this, config);

};

//Extend the PresidentsGrid from GridPanel
Ext.extend(GO.presidents.PresidentsGrid, GO.grid.GridPanel,{
	
});