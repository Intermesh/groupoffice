<?php

use GO\Base\Util\StringHelper;

$GO_SCRIPTS_JS .= '
GO.customfields.types={};
GO.customfields.columns={};
GO.customfields.columnMap={};
';


$extendableModels = GO\Customfields\CustomfieldsModule::getExtendableModels();

foreach ($extendableModels as $extendableModel) {

	$GO_SCRIPTS_JS .= '

				GO.customfields.columns["' . StringHelper::escape_javascript($extendableModel) . '"]=[];


				GO.customfields.types["' . StringHelper::escape_javascript($extendableModel) . '"]={
					name: "' . StringHelper::escape_javascript($extendableModel::getClientName()) . '",
					panels: []
				};' . "\n";

	$stmt = \GO\Customfields\Model\Category::model()->findByModel($extendableModel);

	while ($category = $stmt->fetch()) {

		$fields = array();
		$fstmt = $category->fields();
		while ($field = $fstmt->fetch()) {
			$fields[] = $field->toJsonArray();
		}


		// Makes global, client-side, editable form panels for every customfield category
		if ($category->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION))
			$GO_SCRIPTS_JS .= "\n\n" . 'GO.customfields.types["' . StringHelper::escape_javascript($extendableModel) . '"].panels.push({xtype : "customformpanel", itemId:"cf-panel-' . $category->id . '", fieldSetId: ' . $category->id . ', title : "' . htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8') . '", customfields : ' . json_encode($fields) . '});' . "\n";

		/**
		 * Registers customfield column information in a global, client-side object, ordered by model.
		 * Also, this loop ensures that every customfield data being used has such information
		 * described in a global, client-side object, ordered by customfield id.
		 */
		foreach ($fields as $field) {
			$align = $field['datatype'] == 'GO\Customfields\Customfieldtype\Number' || $field['datatype'] == 'GO\Customfields\Customfieldtype\Date' || $field['datatype'] == 'GO\Customfields\Customfieldtype\Datetime' ? 'right' : 'left';
			$exclude_from_grid = $field['exclude_from_grid'] || $field['datatype'] == 'GO\Customfields\Customfieldtype\Heading' ? 'true' : 'false';

			$GO_SCRIPTS_JS .= 'GO.customfields.columns["' . StringHelper::escape_javascript($extendableModel) . '"].push({' .
							'header: "' . \GO\Base\Util\StringHelper::escape_javascript($field['name']) . '",' .
							'dataIndex: "' . $field['dataname'] . '" ,' .
							'datatype:"' . \GO\Base\Util\StringHelper::escape_javascript($field['datatype']) . '", ' .
							'align:"' . $align . '", ' .
							'sortable:true,' .
							'id: "' . $field['dataname'] . '",' .
							'customfield_id: "' . $field['id'] . '",' .
							'options: ' . \json_encode($field['options']).','.
							'nesting_level: "' . (isset($field['options']['nestingLevel']) ? $field['options']['nestingLevel'] : 0) . '",' .
							'multiselect: "' . (isset($field['options']['multiselect']) ? $field['options']['multiselect'] : 0) . '",' .
							'exclude_from_grid: "' . $exclude_from_grid . '",' .
							'isCustomField: true,' .
							'hidden:true});' . "\n" .
							'GO.customfields.columnMap["' . $field['dataname'] . '"]=GO.customfields.columns["' . StringHelper::escape_javascript($extendableModel) . '"][GO.customfields.columns["' . StringHelper::escape_javascript($extendableModel) . '"].length-1];' . "\n";
		}
	}
}

