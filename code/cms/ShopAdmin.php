<?php

/**
 * ModelAdmin interface for managing the Shop objects
 *
 * @package shop
 */
class ShopAdmin extends ModelAdmin {
	
	private static $url_segment = 'shop';

	private static $menu_title = 'Shop';

	private static $menu_icon = 'shop/images/icons/local-admin.png';

	private static $managed_models = array(
		'Zone',
		'Product',
		'ProductCategory',
		'ProductAttributeType',
		'Order' => array(
			'title' => 'Orders'
		)
	);

	private static $model_importers = array(
		"Product" => "ProductBulkLoader"
	);

	public function getList() {
		$context = $this->getSearchContext();
		$params = $this->request->requestVar('q');

		$list = $context->getResults($params);
		$this->extend('updateList', $list);

		return $list;
	}
}