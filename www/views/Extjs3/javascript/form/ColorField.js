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

	config.colors = config.color || [
		'EBF1E2','95C5D3','FFFF99','A68340',
		'82BA80','F0AE67','66FF99','CC0099',
		'CC99FF','996600','999900','FF0000',
		'FF6600','FFFF00','FF9966','FF9900',
		/* Line 1 */
		'FB0467','D52A6F','CC3370','C43B72',
		'BB4474','B34D75','AA5577','A25E79',
		/* Line 2 */
		'FF00CC','D52AB3','CC33AD','C43BA8',
		'BB44A3','B34D9E','AA5599','A25E94',
		/* Line 3 */
		'CC00FF','B32AD5','AD33CC','A83BC4',
		'A344BB','9E4DB3','9955AA','945EA2',
		/* Line 4 */
		'6704FB','6E26D9','7033CC','723BC4',
		'7444BB','754DB3','7755AA','795EA2',
		/* Line 5 */
		'0404FB','2626D9','3333CC','3B3BC4',
		'4444BB','4D4DB3','5555AA','5E5EA2',
		/* Line 6 */
		'0066FF','2A6ED5','3370CC','3B72C4',
		'4474BB','4D75B3','5577AA','5E79A2',
		/* Line 7 */
		'00CCFF','2AB2D5','33ADCC','3BA8C4',
		'44A3BB','4D9EB3','5599AA','5E94A2',
		/* Line 8 */
		'00FFCC','2AD5B2','33CCAD','3BC4A8',
		'44BBA3','4DB39E','55AA99','5EA294',
		/* Line 9 */
		'00FF66','2AD56F','33CC70','3BC472',
		'44BB74','4DB375','55AA77','5EA279',
		/* Line 10 */
		'00FF00','2AD52A','33CC33','3BC43B',
		'44BB44','4DB34D','55AA55','5EA25E',
		/* Line 11 */
		'66FF00','6ED52A','70CC33','72C43B',
		'74BB44','75B34D','77AA55','79A25E',
		/* Line 12 */
		'CCFF00','B2D52A','ADCC33','A8C43B',
		'A3BB44','9EB34D','99AA55','94A25E',
		/* Line 13 */
		'FFCC00','D5B32A','CCAD33','C4A83B',
		'BBA344','B39E4D','AA9955','A2945E',
		/* Line 14 */
		'FF6600','D56F2A','CC7033','C4723B',
		'BB7444','B3754D','AA7755','A2795E',
		/* Line 15 */
		'FB0404','D52A2A','CC3333','C43B3B',
		'BB4444','B34D4D','AA5555','A25E5E',
		/* Line 16 */
		'FFFFFF','949494','808080','6B6B6B',
		'545454','404040','292929','000000'
	];

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

	// Manually apply the invalid line image since the background
	// was previously cleared so the color would show through.
	markInvalid : function( msg ) {
		GO.form.ColorField.superclass.markInvalid.call(this, msg);
		this.el.setStyle({
			'background-image': 'url(../lib/resources/images/default/grid/invalid_line.gif)'
		});
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
				hex = 'FFFFFF';
			}
			
			this.curColor = hex;

			this.el.setStyle( {
				'background-color': '#' + hex,
				'background-image': 'none'
			});
			if(!this.showHexValue) {
				this.el.setStyle({
					'text-indent': '-100px'
				});

				if(Ext.isIE7) {	// Check this because in IE7 this fix is not needed anymore
					this.el.setStyle({
						'margin-left': '100px'
					});
				}
			}

			if(this.menu && !GO.util.empty(this.curColor) && Ext.isDefined(this.colors) && this.colors.indexOf(this.curColor)>-1)
				this.menu.palette.select(this.curColor);
		}
	},
	
	checkHex : function(hex) {
		
		if(!hex){
			return false;
		}
		
		return hex.match(/^([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/);
	},

	// private
	menuListeners : {
		select: function(m, d){
			this.setValue(d);
		},
		show : function(){ // retain focus styling
			this.onFocus();
		},
		hide : function(){
			this.focus();
			var ml = this.menuListeners;
			this.menu.un("select", ml.select,  this);
			this.menu.un("show", ml.show,  this);
			this.menu.un("hide", ml.hide,  this);
		}
	},

	//private
	handleSelect : function(palette, selColor) {
		this.setValue(selColor);
	},

	// private
	// Implements the default empty TriggerField.onTriggerClick function to display the ColorPicker
	onTriggerClick : function(){
		if(this.disabled){
			return;
		}

		if(!this.menu){
			this.menu = new Ext.menu.ColorMenu();
			this.menu.palette.on('select', this.handleSelect, this );
			this.menu.palette.value=this.curColor;

			this.menu.on(Ext.apply({}, this.menuListeners, {
				scope:this
			}));

			if(this.colors)
			{
				this.menu.palette.colors=this.colors;
			}
		}

		this.menu.show(this.el, "tl-bl");
	}
});

Ext.reg('colorfield', GO.form.ColorField);
