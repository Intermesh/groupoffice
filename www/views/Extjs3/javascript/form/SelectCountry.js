/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SelectCountry.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */




 /**
 * @class GO.form.SelectCountry
 * @extends GO.form.ComboBox
 *
 * Selects a country. It will use the country's ISO code as value field
 * @constructor
 * Creates a new SelectCountry
 * @param {Object} config Configuration options
 */
  
GO.form.SelectCountry = function(config){

	if(!GO.countriesStore)
	{
		var countries = [];

		for(var c in GO.lang.countries)
		{
			countries.push([c, GO.lang.countries[c]]);
		}

		GO.countriesStore = new Ext.data.SimpleStore({
					fields: ['iso', 'name'],
					data : countries,
					sortInfo: {
							field: 'name',
							direction: 'ASC' // or 'DESC' (case sensitive for local sorting)
					}
			});
		//GO.countriesStore.sort('name');
	}
		
	Ext.apply(this, config);

	

	GO.form.SelectCountry.superclass.constructor.call(this,{
   store: GO.countriesStore,
		valueField: 'iso',
		displayField: 'name',
		triggerAction: 'all',
		editable: true,
		mode:'local',
		selectOnFocus:true,
		forceSelection: true,
		emptyText: GO.lang.strNoCountrySelected
	});

}
 
Ext.extend(GO.form.SelectCountry, Ext.form.ComboBox);