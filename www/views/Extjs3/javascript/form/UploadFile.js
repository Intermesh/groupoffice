/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * Based on the File Upload Widget of Ing. Jozef Sakalos
 * 
 * @version $Id: UploadFile.js 22345 2018-02-08 15:24:09Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */ 
 


/**
 * @class GO.form.UploadFile
 *
 * Allows multiple files to be uploaded. They have to be selected one by one
 * though.
 *
 * @constructor
 * Creates a new SelectUser
 * @param {Object} config Configuration options
 */
GO.form.UploadFile = function(config) {
	
	if(!config.addText)
	{
		config.addText = t("Browse");
	}
	
	this.inputs = new Ext.util.MixedCollection();

	if(!config.createNoRows)
	{
		config.createNoRows  = false
	}
	
	GO.form.UploadFile.superclass.constructor.call(this, config);
	
	this.addEvents({
		fileAdded:true
	});
};


Ext.extend(GO.form.UploadFile, Ext.BoxComponent, {
	/**
	 * @cfg {String} The text on the button to add a file
	 */
	addText : '',
	/**
	 * @cfg {String} The class name of the base element
	 */
	cls : '',
	defaultAutoCreate : {
		tag: "div"
	},
	fileCls: 'filetype',
	
	inputName: 'attachments',

	/**
	 * @cfg {Number} The maximum number of files that can be selected
	 */
	max:0,
	
	onRender : function(ct, position){
		
		this.el = ct.createChild({
			tag: 'div',
			id: this.getId(),
			cls: this.cls
			});
	},

	afterRender : function(){
		GO.form.UploadFile.superclass.afterRender.call(this);

		this.createButtons();
		this.createUploadInput();
	},
	
	createUploadInput: function() {

		if(!this.inputName)
		{
			this.inputName = Ext.id();
		}
		
		var inp = this.inputWrap.createChild({
			tag:'input'
			,
			type:'file'
			,
			cls:'x-uf-input'
			,
			size:0
			,
			name:this.inputName+'[]'
		});
		inp.on('change', this.onFileAdded, this);
		this.inputs.add(inp);
		return inp;
	},
	
	createButtons: function() {

		// create containers sturcture
		//id's were needed since extjs 3.1.1 for IE 8
		this.buttonsWrap = this.el.createChild({
			tag:'div',
			cls:'x-uf-buttons-ct',
			children:[
			{
				tag:'div',
				cls:'x-uf-input-ct',
				children: [
				{
					tag:'div',
					cls:'x-uf-bbtn-ct'
				}
				, {
					tag:'div',
					cls:'x-uf-input-wrap'
				}
				]
			}

			]
		});

		// save containers for future use
		this.inputWrap = this.buttonsWrap.select('div.x-uf-input-wrap').item(0);
		this.addBtnCt = this.buttonsWrap.select('div.x-uf-input-ct').item(0);

		// add button
		var bbtnCt = this.buttonsWrap.select('div.x-uf-bbtn-ct').item(0);


		this.browseBtn = new Ext.Button({
			renderTo: bbtnCt,
			text:this.addText
		//, iconCls: 'btn-add'
		});
	},
	
	/**
		* File added event handler
		* @param {Event} e
		* @param {Element} inp Added input
		*/
	 
	onFileAdded: function(e, inp) {

		// hide all previous inputs
		this.inputs.each(function(i) {
			i.setDisplayed(false);
		});

		// create table to hold the file queue list
		if(!this.table && !this.createNoRows) {
			this.table =this.el.createChild({
				tag:'table',
				cls:'x-uf-table'
				,
				children: [ {
					tag:'tbody'
				} ]
			});
			this.tbody = this.table.select('tbody').item(0);

			this.table.on({
				click:{
					scope:this,
					fn:this.onDeleteFile,
					delegate:'a'
				}
			});
		}

		// add input to internal collection
		var inp = this.inputs.itemAt(this.inputs.getCount() - 1);

		// uninstall event handler
		inp.un('change', this.onFileAdded, this);
		
		if(!this.createNoRows)
		{
			// append input to display queue table
			this.appendRow(inp);

			// create new input for future use
			this.createUploadInput();

			if(this.max>0 && this.max<=this.inputs.getCount())
			{
				this.setDisabled(true);
			}
		}else
		{
			this.createUploadInput();
		}
				
		this.fireEvent('filesChanged',this, this.inputs);
		this.fireEvent('fileAdded',this, inp);
		
	},
	/**
		* Appends row to the queue table to display the file
		* Override if you need another file display
		* @param {Element} inp Input with file to display
		*/
	appendRow: function(inp) {
		var filename = inp.getValue();
		var o = {
			id:inp.id
			,
			fileCls: this.getFileCls(filename)
			,
			fileName: Ext.util.Format.ellipsis(filename.split(/[\/\\]/).pop(), this.maxNameLength)
			,
			fileQtip: filename
		}

		var t = new Ext.Template([
			'<tr id="r-{id}">'
			, '<td class="x-unselectable">'
			, '<span class="filetype-link {fileCls}" unselectable="on" qtip="{fileQtip}">{fileName}</span>'
			, '</td>'
			, '<td id="m-{id}" class="x-uf-filedelete"><a id="d-{id}" ><i class="icon">delete</i></a>'
			, '</td>'
			, '</tr>'
			]);

		// save row reference for future
		inp.row = t.append(this.tbody, o, true);
	},
	
	onDeleteFile: function(e, target) {
		this.removeFile(target.id.substr(2));
	}, 
	
	/**
		* Removes file from the queue
		* private
		*
		* @param {String} id Id of the file to remove (id is auto generated)
		* @param {Boolean} suppresEvent Set to true not to fire event
		*/
	removeFile: function(id) {		
		if(this.uploading) {
			return;
		}
		var inp = this.inputs.get(id);
		if(inp && inp.row) {
			inp.row.remove();
		}
		if(inp) {
			inp.remove();
		}
		this.inputs.removeKey(id);
		
		this.setDisabled(false);
		
		this.fireEvent('filesChanged',this, this.inputs);
	},
	
	getFileCls: function(name) {
		var atmp = name.split('.');
		if(1 === atmp.length) {
			return this.fileCls;
		}
		else {
			return this.fileCls + '-' + atmp.pop();
		}
	}, 
	clearQueue: function() {
		this.inputs.each(function(inp, index, length) {
			if(index < length - 1) {
				this.removeFile(inp.id, true);
			}
		}, this);
	},
	
	reset : function(){
		this.clearQueue();
	},

	/**
	* Disables/Enables the whole form by masking/unmasking it
	*
	* @param {Boolean} disable true to disable, false to enable
	* @param {Boolean} alsoUpload true to disable also upload button
	*/
	setDisabled: function(disable) {

		if(disable) {
			this.addBtnCt.mask();
		}
		else {
			this.addBtnCt.unmask();
		}
	}
	
});
