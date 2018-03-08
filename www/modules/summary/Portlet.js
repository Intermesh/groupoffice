/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Portlet.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.summary.Portlet = Ext.extend(Ext.Panel, {
    anchor: '100%',
    frame:true,
    collapsible:true,
    draggable:true,
    cls:'x-portlet',
    stateful:false,
    initComponent : function(){
    	this.addEvents({'remove_portlet' : true});
    	GO.summary.Portlet.superclass.initComponent.call(this);
    },
    saveState : function(){},    
    removePortlet : function(){    	
    	this.fireEvent('remove_portlet', this);
    }
});
Ext.reg('portlet', GO.summary.Portlet);