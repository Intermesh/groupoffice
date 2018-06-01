/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: QuickAddPanel.js 21644 2017-11-07 13:08:07Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits<wsmits@intermesh.nl>
 */
 
GO.panels.QuickAddPanel = Ext.extend(Ext.Container, {

	cmpWidth:10,
	cmpHeight:26,
	hideMode:'offsets',

	initComponent : function(){
		
		Ext.apply(this, {
			border:false,
			hidden:false,
			layout:'toolbar',
			cls: 'go-white-bg', 
			items:[]
		});

		GO.panels.QuickAddPanel.superclass.initComponent.call(this);
	},
	
	addButton : function(mnuCmp,position){
		this.insert(position,mnuCmp);
	},
	calcWidth : function(){
		this.cmpWidth=10;
		this.items.each(function(c){
			this.cmpWidth += c.getWidth();
		}, this);
		
		return this.cmpWidth;
	}
});

GO.quickAddPanel = new GO.panels.QuickAddPanel();

GO.mainLayout.on('render', function(){
	
	if(Ext.get("quick-add-menu")){

		// Render the quickAddPanel to the div so the DOM elements are created
		GO.quickAddPanel.render("quick-add-menu",0);

		// Get the dom elements 
		var menu = Ext.get('quick-add-menu');
		var toggleButton = Ext.get('quick-add-menu-collapse');

		// Set the width and the height of the component that contains the buttons
		menu.setWidth(GO.quickAddPanel.calcWidth());
		menu.setHeight(GO.quickAddPanel.cmpHeight);

		// Declare variable to keep the click state
		toggleButton.enableClick = true;

		GO.quickAddPanel.setVisible(true);
		menu.setVisible(true);
		// align the menu to the button
		menu.alignTo('quick-add-menu-collapse','r-l');

		// Set the click handler on the toggle button
		toggleButton.on('click',function(){

			// Check if the button is not clicked before the animation from the previous click is done.
			if(toggleButton.enableClick === true){

				// Animation is pending so disable clicking
				toggleButton.enableClick = false;

				// Check if the panel is currently hidden or visible
				var hidden = GO.quickAddPanel.hidden;

				if(hidden){
					// Replace the button class so the minus sign is displayed
					toggleButton.replaceClass('plus-sign','minus-sign');

					// Set the panel to visible
					GO.quickAddPanel.setVisible(true);
					menu.setVisible(true);

					// align the menu to the button
					menu.alignTo('quick-add-menu-collapse','r-l');

					// Animate the menu so it will fade in
					menu.fadeIn({
						endOpacity: 1,
						callback:function(el){

							// Enable the togglebutton click event again.
							toggleButton.enableClick = true;
						},
						scope:this
					});
				}else{
					// Replace the button class so the plus sign is displayed
					toggleButton.replaceClass('minus-sign','plus-sign');

					// Animate the menu so it will fade out
					menu.fadeOut({
						endOpacity: 0,
						callback:function(el){
							// Hide the items when the animation is complete
							GO.quickAddPanel.setVisible(false);
							menu.setVisible(false);

							// Enable the togglebutton click event again.
							toggleButton.enableClick = true;
						},
						scope:this
					});
				}
			}
		},this);   
	} 
},this);
