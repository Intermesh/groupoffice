/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: namespaces.js 22359 2018-02-11 19:11:59Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 

Ext.namespace("GO.form");
Ext.namespace("GO.util");
Ext.namespace('GO.plugins');
Ext.namespace("GO.grid");
Ext.namespace("GO.panel");
Ext.namespace("GO.state");
Ext.namespace("GO.data");
Ext.namespace("GO.dialog");
Ext.namespace("GO.users");
Ext.namespace('GO.layout');
Ext.namespace('GO.mailFunctions');
Ext.namespace('GO.menu');
Ext.namespace('GO.base.email');
Ext.namespace('GO.base.model');
Ext.namespace('GO.base.model.multiselect');
Ext.namespace("GO.base.form");
Ext.namespace("GO.base.upload");
Ext.namespace("GO.base.tree");
Ext.namespace("GO.base.util");
Ext.namespace("GO.portlets");
Ext.namespace("GO.panels");

//An object of functions that open a particular link.
//the index is the link type and the function gets the id as a parameter
GO.linkHandlers={};
//GO.linkPreviewPanels={};

//Will be filled by modules with menu items for the new menu in various panels.
GO.newMenuItems=[];


Ext.namespace("GO.customfields");
GO.customfields.types={};
GO.customfields.columns={};


//GO.settings = Ext.decode(GO.settings);
