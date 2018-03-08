// Creates namespaces to be used for scoping variables and classes so that they are not global.
Ext.namespace('GO.presidents');

/*
 * This is the constructor of our MainPanel
 */
GO.presidents.MainPanel = function(config){

	if(!config)
	{
		config = {};
	} 

	config.html='Hello World';
	
	/*
	 * Explicitly call the superclass constructor
	 */
 	GO.presidents.MainPanel.superclass.constructor.call(this, config);

}

/*
 * Extend the base class
 */
Ext.extend(GO.presidents.MainPanel, Ext.Panel,{

});

/*
 * This will add the module to the main tabpanel filled with all the modules
 */
GO.moduleManager.addModule('presidents', GO.presidents.MainPanel, {
	title : 'Presidents',
	iconCls : 'go-module-icon-presidents'
});