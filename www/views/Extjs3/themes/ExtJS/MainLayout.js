/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: MainLayout.js 14816 2013-05-21 08:31:20Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/*
 * Remove module icons
 */
GO.mainLayout.onReady(function(){
	for(var module in GO.moduleManager.panelConfigs){
		delete GO.moduleManager.panelConfigs[module].iconCls;
	}
});