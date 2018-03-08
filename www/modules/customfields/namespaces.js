
if(!GO.customfields)
{
	Ext.namespace("GO.customfields");
	GO.customfields.types={};
}

/*
 * This object will have keys that are link types.
 * 
 * Eg. :
 * 
 * GO.customfields.types[2]={name: 'Contacts', panels : [new GO.customfields.CustomeFormPanel()]};
 * 
 * The array will be filled by scripts.inc.php files in each module that supports custom fields
 *
 */