<?php

class TaxonomyTerm extends DataObject implements PermissionProvider {
	public static $db = array(
		'Name' => 'Varchar(255)'
	);

	public static $has_many = array(
		'Children' => 'TaxonomyTerm'
	);

	public static $has_one = array(
		'Parent' => 'TaxonomyTerm'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$controller = Controller::curr();

		// Do not show parent selection when adding new items - populated automatically.
		if ($controller && $controller->request->param('ID')==='new') {
			$fields->removeByName('ParentID');
		}

		return $fields;
	}

	public function onBeforeDelete() {
		parent::onBeforeDelete();

		foreach($this->Children() as $term) {
			$term->delete();
		}
	}

	public function canView($record = null) {
		return true;
	}

	public function canEdit($record = null) {
		return Permission::check('TAXONOMYTERM_EDIT');
 	}

	public function canDelete($record = null) {
		return Permission::check('TAXONOMYTERM_DELETE');
	}

	public function canCreate($record = null) {
		return Permission::check('TAXONOMYTERM_CREATE');
	}

	public function providePermissions() {
		return array(
			'TAXONOMYTERM_EDIT' => 'Edit a taxonomy term',
			'TAXONOMYTERM_DELETE' => 'Delete a taxonomy term',
			'TAXONOMYTERM_CREATE' => 'Create a taxonomy term',
		);
	}

}
