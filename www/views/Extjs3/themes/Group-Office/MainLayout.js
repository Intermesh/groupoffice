/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: MainLayout.js 3020 2009-08-06 09:04:01Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.MainLayout.override({
	beforeRender : function(){
		this.tabPanel.baseCls='go-moduletabs';
	}
});
 