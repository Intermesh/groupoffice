/**
 * Don't copy the next lines into a translation
 */
if(!GO.customfields)
{
	Ext.namespace("GO.customfields");
	GO.customfields.types={};
	GO.customfields.columns={};
}

GO.customfields.lang={};
/**
 * Copy everything below for translations
 */


GO.customfields.lang.customfields = 'Custom fields';
GO.customfields.lang.category = 'Category';
GO.customfields.lang.categories = 'Custom field categories';
GO.customfields.lang.manageCategories = 'Manage categories';
GO.customfields.lang.numberField = '<br />You can use any number field. Wrap field names in {} and put a space between every word (eg. {Number1} + {Number2} and not Number1+Number2).<br />';
GO.customfields.lang.selectOptions = 'Select options';
GO.customfields.lang.noOptions = 'There are no options defined yet';
GO.customfields.lang.enterText = 'Please enter the text of the option:';
GO.customfields.lang.functionProperties = 'Function properties';
GO.customfields.lang.restart = 'Changes you make here will take affect after you restart Group-Office.';
GO.customfields.lang.noFields = 'No custom fields to display';
GO.customfields.lang.createCategoryFirst='You must create a category first';
GO.customfields.lang.required='Required field';
GO.customfields.lang.validationRegexp='Validation regexp.';
GO.customfields.lang.helpText='Help text';

GO.customfields.lang.importText='Upload a CSV file with a single column for the value or just with the value on each line.';
GO.customfields.lang.multiselect='Multiselect';
GO.customfields.lang.maxOptions='Maximum number of options';
GO.customfields.lang.zeroMeansUnlimited='0 means unlimited';
GO.customfields.lang.multiselectForLastSlaveOnly='Only the last treeselect slave may be a multiselect combo';
GO.customfields.lang.clickApplyFirst='Please click apply first before you import';
GO.customfields.lang.treeImportText='You can import a CSV file where each column represents a tree level. eg.<br />"option 1","option 1.1", "option 1.1.1"<br />"option 1","option 1.2", "option 1.2.1". Please replace the comma with your personal setting of Group-Office.';
GO.customfields.lang.usableOperators='You can use the following operators: / , * , + and - :<br /><br />';
GO.customfields.lang.excludeFromGrid='Exclude from grid';
GO.customfields.lang.height='Height';

GO.customfields.lang.bulkEdit = 'Edit selection';
GO.customfields.lang.applyToSelectionInstructions = 'Use the checkboxes on the right to apply the fields\' value to all selected files.';
GO.customfields.lang.applyCategoryChanges = 'Apply the above changes to selection';
GO.customfields.lang.success = 'Success';
GO.customfields.lang.appliedToSelection = 'The changes have been applied to the customfields of the selected files.';
GO.customfields.lang.noFileSelected = 'No files have been selected. First select a number of files.';