<?php

class TaxonomyTerm extends DataObject implements PermissionProvider {
	private static $db = array(
		'Name' => 'Varchar(255)',
		'SortOrder' => 'Int'
	);

	private static $has_many = array(
		'Children' => 'TaxonomyTerm'
	);

	private static $has_one = array(
		'Parent' => 'TaxonomyTerm'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// For now moving taxonomy terms is not supported.
		$fields->removeByName('ParentID');
		$fields->removeByName('SortOrder');

		$childrenGrid = $fields->dataFieldByName('Children');
		if($childrenGrid) {
			$deleteAction = $childrenGrid->getConfig()->getComponentByType('GridFieldDeleteAction');
			$addExistingAutocompleter = $childrenGrid->getConfig()->getComponentByType('GridFieldAddExistingAutocompleter');

			$childrenGrid->getConfig()->removeComponent($addExistingAutocompleter);
			$childrenGrid->getConfig()->removeComponent($deleteAction);
			$childrenGrid->getConfig()->addComponent(new GridFieldDeleteAction(false));
		}
		$config = GridFieldConfig::create()
			->addComponent(new GridFieldButtonRow('before'))
			->addComponent(new GridFieldToolbarHeader())
			->addComponent(new GridFieldTitleHeader())
			->addComponent(new GridFieldEditableColumns())
			->addComponent(new GridFieldDeleteAction())
			->addComponent(new GridFieldAddNewInlineButton())
			->addComponent(new GridFieldOrderableRows('SortOrder'));
		//$childrenGrid->setConfig($config);
		$fields->removeByName('Children');
		$fields->addFieldToTab("Root.Main", new GridField("Children", "Terms", $this->Children(), $config));
		return $fields;
	}

	/**
	 * Get the top-level ancestor which doubles as the taxonomy.
	 */
	public function getTaxonomy() {
		$object = $this;
		
		while($object->Parent() && $object->Parent()->exists()) {
			$object = $object->Parent();
		}
		
		return $object;
	}

	public function getTaxonomyName() {
		return $this->getTaxonomy()->Name;
	}
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this->Parent()->ID > 0 && $this->SortOrder == 0) {
			$this->SortOrder = ($this->Parent()->Children()->Count() + 1);
		}
	}

	public function onBeforeDelete() {
		parent::onBeforeDelete();

		foreach($this->Children() as $term) {
			$term->delete();
		}
	}

	public function canView($member = null) {
		return true;
	}

	public function canEdit($member = null) {
		return Permission::check('TAXONOMYTERM_EDIT');
	}

	public function canDelete($member = null) {
		return Permission::check('TAXONOMYTERM_DELETE');
	}

	public function canCreate($member = null) {
		return Permission::check('TAXONOMYTERM_CREATE');
	}

	public function providePermissions() {
		return array(
			'TAXONOMYTERM_EDIT' => array(
				'name' => 'Edit a taxonomy term',
				'category' => 'Taxonomy terms',
			),
			'TAXONOMYTERM_DELETE' => array(
				'name' => 'Delete a taxonomy term and all nested terms',
				'category' => 'Taxonomy terms',
			),
			'TAXONOMYTERM_CREATE' => array(
				'name' => 'Create a taxonomy term',
				'category' => 'Taxonomy terms'
			)
		);
	}

	public function toMap() {
		$map["ID"] = $this->ID;
		$map["Title"] = $this->Name;

		$map["Children"] = array();
		if ($this->ParentID == 0) {
			$map["Children"] = $this->Children()->sort("SortOrder")->toNestedArray();
		}
		return $map;
	}
}
