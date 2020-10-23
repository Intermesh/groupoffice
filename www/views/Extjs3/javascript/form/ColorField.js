/**
 * Based on code found at http://extjs.com/forum/showthread.php?t=5106
 *
 * Modified by Merijn Schering <mschering@intermesh.nl>
 *
 * Changes:
 *  -Handles value better. Uses value config property as start value.
 *  -Removed changed trigger image because it didn't handle state.
 * 	-Added colors config property so you can overide the default color palette *
 *
 * @class GO.form.ColorField
 * @extends Ext.form.TriggerField
 * Provides a very simple color form field with a ColorMenu dropdown.
 * Values are stored as a six-character hex value without the '#'.
 * I.e. 'ffffff'
 * @constructor
 * Create a new ColorField
 * <br />Example:
 * <pre><code>
var cf = new Ext.form.ColorField({
	fieldLabel: 'Color',
	hiddenName:'pref_sales',
	showHexValue:true
});
</code></pre>
 * @param {Object} config
 */


GO.form.ColorField =  Ext.extend(function(config){

	config = config || {};

	if(!config.colors) {
		if(config.dark) {
			config.colors = [
				'B71C1C','C62828','D32F2F','E53935','F44336', // Red
				'880E4F','AD1457','C2185B','D81B60','E91E63', // Pink
				'4A148C','6A1B9A','7B1FA2','8E24AA','9C27B0', // Purple
				'311B92','4527A0','512DA8','5E35B1','673AB7', // Deep purple
				'1A237E','283593','303F9F','3949AB','3F51B5', // Indigo
				'0D47A1','1565C0','1976D2','1E88E5','2196F3', // Blue
				'01579B','0277BD','0288D1','039BE5','03A9F4', // Light blue
				'006064','00838F','0097A7','00ACC1','00BCD4', // Cyan
				'004D40','00695C','00796B','00897B','009688', // Teal
				'1B5E20','2E7D32','388E3C','43A047','4CAF50', // Green
				'33691E','558B2F','689F38','7CB342','8BC34A', // Light Green
				'827717','9E9D24','AFB42B','C0CA33','CDDC39', // Lime
				'F57F17','F9A825','FBC02D','FDD835','FFEB3B', // Yellow
				'FF6F00','FF8F00','FFA000','FFB300','FFC107', // Amber
				'E65100','EF6C00','F57C00','FB8C00','FF9800', // Orange
				'212121','424242','616161','757575','BDBDBD', // Grey

				'009BC9', //Group-Office blue
				'243A80', //Intermesh blue
				'689F38', //default secondary
				'FF9100'  //Default accent
			];
		} else if(config.light) {
			config.colors = [
				'EF5350','E57373','EF9A9A','FFCDD2', // Red
				'EC407A','F06292','F48FB1','F8BBD0', // Pink
				'AB47BC','BA68C8','CE93D8','E1BEE7', // Purple
				'7E57C2','9575CD','B39DDB','D1C4E9', // Deep purple
				'5C6BC0','7986CB','9FA8DA','C5CAE9', // Indigo
				'42A5F5','64B5F6','90CAF9','BBDEFB', // Blue
				'29B6F6','4FC3F7','81D4FA','B3E5FC', // Light blue
				'26C6DA','4DD0E1','80DEEA','B2EBF2', // Cyan
				'26A69A','4DB6AC','80CBC4','B2DFDB', // Teal
				'66BB6A','81C784','A5D6A7','C8E6C9', // Green
				'9CCC65','AED581','C5E1A5','DCEDC8', // Light Green
				'D4E157','DCE775','E6EE9C','F0F4C3', // Lime
				'FFEE58','FFF176','FFF59D','FFF9C4', // Yellow
				'FFCA28','FFD54F','FFE082','FFECB3', // Amber
				'FFA726','FFB74D','FFCC80','FFE0B2', // Orange
				'E0E0E0','EEEEEE','F5F5F5','FFFFFF' // Grey
			];
		} else
		{
			config.colors = [
				'B71C1C','C62828','D32F2F','E53935','F44336','EF5350','E57373','EF9A9A','FFCDD2', // Red
				'880E4F','AD1457','C2185B','D81B60','E91E63','EC407A','F06292','F48FB1','F8BBD0', // Pink
				'4A148C','6A1B9A','7B1FA2','8E24AA','9C27B0','AB47BC','BA68C8','CE93D8','E1BEE7', // Purple
				'311B92','4527A0','512DA8','5E35B1','673AB7','7E57C2','9575CD','B39DDB','D1C4E9', // Deep purple
				'1A237E','283593','303F9F','3949AB','3F51B5','5C6BC0','7986CB','9FA8DA','C5CAE9', // Indigo
				'0D47A1','1565C0','1976D2','1E88E5','2196F3','42A5F5','64B5F6','90CAF9','BBDEFB', // Blue
				'01579B','0277BD','0288D1','039BE5','03A9F4','29B6F6','4FC3F7','81D4FA','B3E5FC', // Light blue
				'006064','00838F','0097A7','00ACC1','00BCD4','26C6DA','4DD0E1','80DEEA','B2EBF2', // Cyan
				'004D40','00695C','00796B','00897B','009688','26A69A','4DB6AC','80CBC4','B2DFDB', // Teal
				'1B5E20','2E7D32','388E3C','43A047','4CAF50','66BB6A','81C784','A5D6A7','C8E6C9', // Green
				'33691E','558B2F','689F38','7CB342','8BC34A','9CCC65','AED581','C5E1A5','DCEDC8', // Light Green
				'827717','9E9D24','AFB42B','C0CA33','CDDC39','D4E157','DCE775','E6EE9C','F0F4C3', // Lime
				'F57F17','F9A825','FBC02D','FDD835','FFEB3B','FFEE58','FFF176','FFF59D','FFF9C4', // Yellow
				'FF6F00','FF8F00','FFA000','FFB300','FFC107','FFCA28','FFD54F','FFE082','FFECB3', // Amber
				'E65100','EF6C00','F57C00','FB8C00','FF9800','FFA726','FFB74D','FFCC80','FFE0B2', // Orange
				'212121','424242','616161','757575','BDBDBD','E0E0E0','EEEEEE','F5F5F5','FFFFFF', // Grey

				'009BC9', //Group-Office blue
				'243A80', //Intermesh blue
				'689F38', //default secondary
				'FF9100'  //Default accent
			];
		}


	}

	GO.form.ColorField.superclass.constructor.call(this, config);

},Ext.form.TriggerField,  {

	/**
	 * @cfg {Boolean} showHexValue
	 * True to display the HTML Hexidecimal Color Value in the field
	 * so it is manually editable.
	 */
	showHexValue : false,

	/**
	   * @cfg {String} triggerClass
	   * An additional CSS class used to style the trigger button.  The trigger will always get the
	   * class 'x-form-trigger' and triggerClass will be <b>appended</b> if specified (defaults to 'x-form-color-trigger'
	   * which displays a calendar icon).

	triggerClass : 'go-form-color-trigger',
	* */

	/**
	 * @cfg {String/Object} autoCreate
	 * A DomHelper element spec, or true for a default element spec (defaults to
	 * {tag: "input", type: "text", size: "10", autocomplete: "off"})
	 */
	// private
	defaultAutoCreate : {
		tag: "input",
		type: "text",
		size: "1",
		autocomplete: "off",
		maxlength:"6"
	},

	/**
	 * @cfg {String} lengthText
	 * A string to be displayed when the length of the input field is
	 * not 3 or 6, i.e. 'fff' or 'ffccff'.
	 */
	lengthText: "Color hex values must be either 3 or 6 characters.",
	invalidText : "{0} is not a valid color - it must be in a the hex format {1}",
	//text to use if blank and allowBlank is false
	blankText: "Must have a hexidecimal value in the format ABCDEF.",

	/**
	 * @cfg {String} color
	 * A string hex value to be used as the default color.  Defaults
	 * to 'FFFFFF' (white).
	 */
	//defaultColor: 'FFFFFF',

	maskRe: /[a-f0-9]/i,
	// These regexes limit input and validation to hex values
	regex: /[a-f0-9]/i,

	//private
	curColor: 'FFFFFF',

	// private
	validateValue : function(value){
		if(!this.showHexValue) {
			return true;
		}
		if(value.length<1) {
			this.el.setStyle({
				'background-color':'#FFFFFF'
			});
			if(!this.allowBlank) {
				this.markInvalid(String.format(this.blankText, value));
				return false;
			}
			return true;
		}
		if(!this.checkHex(value)) {
			this.markInvalid(String.format(this.lengthText, value));
			return false;
		}
		this.setColor(value);
		return true;
	},

	// private
	validateBlur : function(){
		return !this.menu || !this.menu.isVisible();
	},

	getValue : function(){
		return this.curColor;
    },
	/**
   * Sets the value of the color field.  Format as hex value 'FFFFFF'
   * without the '#'.
	 *
   * @param {String} hex The color value
   */
	setValue : function(hex){	
		GO.form.ColorField.superclass.setValue.call(this, hex);
		this.setColor(hex);
	},

	// private
	initValue : function(){
		GO.form.ColorField.superclass.initValue.call(this);
		this.setColor(this.value);
	},

	/**
	 * Sets the current color and changes the background.
	 * Does *not* change the value of the field.
	 *
	 * @param {String} hex The color value.
	 */
	setColor : function(hex) {
		if(this.rendered)
		{
			
			if(!this.checkHex(hex)){
				hex = null;
			}
			
			this.curColor = hex;

			this.el.setStyle( {
				'background-color': hex ? '#' + hex : "transparent",
				'background-image': 'none'
			});
			if(!this.showHexValue) {
				this.el.setStyle({'text-indent': '-100px'});
			}

			if(this.menu && !GO.util.empty(this.curColor) && Ext.isDefined(this.colors) && this.colors.indexOf(this.curColor)>-1)
				this.menu.palette.select(this.curColor);
		}
	},
	
	checkHex : function(hex) {
		
		if(!hex){
			return true;
		}
		
		return hex.match(/^([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/);
	},

	//private
	handleSelect : function(palette, selColor) {
		var old = this.getValue();
		this.setValue(selColor);
		this.fireEvent('change', this, selColor, old);
	},

	// private
	// Implements the default empty TriggerField.onTriggerClick function to display the ColorPicker
	onTriggerClick : function(){
		if(this.disabled){
			return;
		}

		if(!this.menu){
			this.menu = new Ext.menu.ColorMenu();
			
			this.menu.insert(0,{
				iconCls:'ic-invert-colors-off',
				text:'Auto',
				handler: function() {
					this.handleSelect(this.menu.palette, null);
					this.menu.hide();
				},
				scope:this
			});
			
			this.menu.palette.on('select', this.handleSelect, this );
			this.menu.palette.value=this.curColor;
//			this.menu.on(Ext.apply({}, this.menuListeners, {scope:this} ));

			if(this.colors)
			{
				this.menu.palette.colors=this.colors;
			}
		}

		this.menu.show(this.el, "tl-bl");
	}
});

Ext.reg('colorfield', GO.form.ColorField);
