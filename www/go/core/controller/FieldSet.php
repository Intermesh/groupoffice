<?php

namespace go\core\controller;

use go\core\customfield\Base;
use go\core\fs\Blob;
use go\core\jmap\EntityController;
use go\core\model;
use go\core\util\StringUtil;

class FieldSet extends EntityController {

	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\FieldSet::class;
	}
	
	private function checkEnabledModule(\go\core\orm\Query $query) {
		return $query	->join('core_module', 'm', 'm.id = e.moduleId')
						->where(['m.enabled' => true]);
	}

	protected function getQueryQuery($params) {
		return $this->checkEnabledModule(parent::getQueryQuery($params)->orderBy(['sortOrder' => 'ASC', 'id' => 'ASC']));
	}
	protected function getGetQuery($params) {
		return $this->checkEnabledModule(parent::getGetQuery($params)->orderBy(['sortOrder' => 'ASC', 'id' => 'ASC']));
	}
	
	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}

    /**
     * Export all customfields for the chosen Fieldsets to a json-file with the ability to import in on other instance of GroupOffice
     * The JSON file is saved in a blob which will be downloaded in the browser.
     *
     * @param $params
     * @return array
     * @throws \ReflectionException
     */
	public function exportToJson($params) {
	    $jsonArray = ['entity' => $params['entity'], 'goVersion' => go()->getVersion(), 'fieldSets' => []];
	    $fieldSets = model\FieldSet::findByIds($params['fieldSetIds']);
	    $fieldSetArray = [];
	    foreach ($fieldSets as $fieldSet) {
	        $fields = model\Field::find()->where('fieldSetId', '=', $fieldSet->id);
	        $fieldArray = [];
	        foreach ($fields as $field) {
	            $fieldArray[] = [
	                'name' => $field->name,
                    'databaseName' => $field->databaseName,
                    'type' => $field->type,
                    'sortOrder' => $field->sortOrder,
                    'required' => $field->required,
                    'hint' => $field->hint,
                    'prefix' => $field->prefix,
                    'options' => base64_encode(json_encode($field->getOptions())),
                    'extraOptions' => method_exists($field->getDataType(), 'getOptions') ? $this->stripIds($field->getDataType()->getOptions()) : false,
                    'hiddenInGrid' => $field->hiddenInGrid
                ];
            }
            $fieldSetArray[] = [
                'name' => $fieldSet->name,
                'description' => $fieldSet->description,
                'sortOrder' => $fieldSet->sortOrder,
                'isTab' => $fieldSet->isTab,
                'fields' => $fieldArray
            ];
        }
	    $jsonArray['fieldSets'] = $fieldSetArray;
        $blob = Blob::fromString(json_encode($jsonArray));
        $blob->name = $params['entity'] . '.json';
        $blob->type = 'json';
        $blob->save();
	    return ['success' => true, 'blobId' => $blob->id];
    }

    /**
     * Strip the ids from the Select and Multiselect options
     *
     * @param $options
     * @return mixed
     */
    private function stripIds($options) {
	    foreach ($options as $key => $o) {
	        unset($o['id']);
	        $children = $o['children'] ?? [];
	        $o['children'] = $this->stripIds($children);
	        $options[$key] = $o;
        }
	    return $options;
    }

    /**
     * Import a json-file with customFields from an other instance.
     * The next checks are done:
     * A: Per file:
     *  - correct GO version
     *  - correct entity
     * B: Per fieldset
     *  - If the fieldset exists with this name at this entity
     * C: Per field:
     *  - Does the CustomFieldType exists in this installation
     *  - Does the field already exist
     *
     * For fields which you the multiselect options they are also imported with new ID's
     *
     * @param $params
     * @return array
     * @throws \go\core\exception\ConfigurationException
     */
    public function importFromJson($params) {
	    $blob = Blob::findById($params['blobId']);
	    $json = $blob->getFile()->getContents();
	    $jsonArray = json_decode($json, true);
	    if ($jsonArray['goVersion'] !== go()->getVersion()) {
	        $success = false;
	        $errorMsg = 'Only possible in same version. File version = ' . $jsonArray['goVersion'] . ', current version = ' . go()->getVersion();
        } else if($jsonArray['entity'] !== $params['entity']) {
	        $success = false;
	        $errorMsg = 'Only possible in same entities. File entity = ' . $jsonArray['entity'] . ', current entity = ' . $params['entity'];
        } else {
	        $errorMsg = '';
	        $success = true;
	        foreach ($jsonArray['fieldSets'] as $fieldSetArray) {
	            $fieldSet = (object) $fieldSetArray;
	            // check if fieldSet->name exists
                $e = \go\core\orm\EntityType::findByName($params['entity']);
                $newFieldSet = model\FieldSet::find()
                    ->where('entityId', '=', $e->getId())
                    ->where('name', '=', $fieldSet->name)
                    ->single();
                if (!$newFieldSet) {
                    $newFieldSet = new model\FieldSet();
                    $newFieldSet->setEntity($params['entity']);
                    $newFieldSet->name = $fieldSet->name;
                    $newFieldSet->description = $fieldSet->description;
                    $newFieldSet->isTab = $fieldSet->isTab;
                    if (!$newFieldSet->save()) {
                        error_log(print_r($newFieldSet->getValidationErrors(), true));
                    }
                } else {
                    $errorMsg .= 'Fieldset ' . $fieldSet->name . ' already exists, merged fields in that Fieldset <br/>';
                }
                foreach ($fieldSet->fields as $fieldArray) {
                    $field = (object) $fieldArray;
                    // check if field->databaseName || $field->name exists
                    if (!strpos(Base::findByName($field->type), $field->type)) {
                        $errorMsg .= 'CustomfieldType ' . $field->type . ' not available in this installation <br/>';
                        continue;
                    }
                    $newField = model\Field::find()
                        ->where('fieldSetId', '=', $newFieldSet->id)
                        ->where('databaseName', '=', $field->databaseName)
                        ->where('name', '=', $field->name)
                        ->where('type', '=', $field->type)
                        ->single();
                    if (!$newField) {
                        $newField = new model\Field();
                        $field->options = json_decode(base64_decode($field->options), true);
                        $extraOptions = $field->extraOptions;
                        unset($field->extraOptions);
                        $newField->setValues((array) $field);
                        if (method_exists($newField->getDataType(), 'setOptions') && $extraOptions)
                            $newField->getDataType()->setOptions((array) $extraOptions);

                        $newField->fieldSetId = $newFieldSet->id;
                        $newField->id = null;
                        if (!$newField->save()) {
                            error_log(print_r($newField->getValidationErrors(), true));
                        }
                        $errorMsg .= 'Field: ' . $field->name . ' with databaseName (' . $field->databaseName . ') appended <br/>';
                    } else {
                        $errorMsg .= 'Field: ' . $field->name . ' with databaseName (' . $field->databaseName . ') exists <br/>';
                    }

                }
            }
        }
	    return ['success' => $success, 'feedback' => $errorMsg];
    }
}
