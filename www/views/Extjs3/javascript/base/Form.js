GO.base.form.getFormFieldByType = function(gotype, colName, config){
	var editor;
	switch(gotype){
		case 'date':
		case 'unixtimestamp':
		case 'unixdate':
			editor = new Ext.form.DateField(config);
			break;
					
		case 'number':
			editor = new GO.form.NumberField(config);
			break;
		
		case 'user':
			editor = new GO.form.SelectUser(config); 
			break;
		
		case 'boolean':
			editor = new Ext.form.Checkbox(config);
			break;
		case 'color':
			
			editor = new GO.form.ColorField(config);
			break;
			
			
//		case 'customfield':
//			//colName might be cf.col_4. Change it into col_4.
//			var dotIndex = colName.indexOf('.');
//			if(dotIndex){
//				dotIndex++;
//				colName = colName.substr(dotIndex,colName.length-dotIndex);
//			}
//			
//			editor = new GO.customfields.getFormField(GO.customfields.columnMap[colName], config);
//			if (editor.xtype=='xcheckbox')			
//				editor = GO.customfields.dataTypes['GO\\Customfields\\Customfieldtype\\BinaryCombobox'].getFormField(GO.customfields.columnMap[colName], config);
//			
//			break;

		default:
			editor = new Ext.form.TextField(config);
			break;				
	}
	
	return editor;
}
