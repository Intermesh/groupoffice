/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 


/*
 * This will add the module to the main tabpanel filled with all the modules
 */

 
GO.moduleManager.addModule('postfixadmin', GO.postfixadmin.DomainsGrid, {
	title : t("E-mail domains", "postfixadmin"),
	iconCls : 'go-tab-icon-postfixadmin'
});


