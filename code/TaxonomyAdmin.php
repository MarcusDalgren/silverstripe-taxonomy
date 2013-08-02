<?php
/**
* Management interface for Taxonomies and TaxonomyTerms
*
* @package taxonomy
*/

class TaxonomyAdmin extends ModelAdmin {

	private static $url_segment = 'taxonomy';

	private static $managed_models = array('AssetType','TaxonomyTerm');

	private static $menu_title = 'Taxonomies';
	
	private static $menu_icon = "taxonomy/images/tag.png";

	public function getList() {
		$list = parent::getList();
		return ($this->modelClass == "TaxonomyTerm") ? $list->filter('ParentID', '0') : $list;
	}

}
