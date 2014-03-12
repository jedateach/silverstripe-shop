<?php
/**
 * CheckoutPage is a CMS page-type that shows the order
 * details to the customer for their current shopping
 * cart on the site.
 *
 * @see CheckoutPage_Controller->Order()
 *
 * @package shop
 */
class CheckoutPage extends Page {

	private static $db = array(
		'PurchaseComplete' => 'HTMLText'
	);

	private static $icon = 'shop/images/icons/money';

	/**
	 * Returns the link to the checkout page on this site
	 *
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	public static function find_link($urlSegment = false, $action = null, $id = null) {
		if(!$page = self::get()->first()) {
			return Controller::join_links(
				Director::baseURL(),
				CheckoutPage_Controller::config()->url_segment
			);
		}
		$id = ($id)? "/".$id : "";
		return ($urlSegment) ?
			$page->URLSegment :
			Controller::join_links($page->Link($action), $id);
	}

	/**
	 * Only allow one checkout page
	 */
	public function canCreate($member = null) {
		return !self::get()->exists();
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', array(
			HtmlEditorField::create('PurchaseComplete', 'Purchase Complete', 4)
				->setDescription(
					"This message is included in reciept email, after the customer submits the checkout"
				)
		), 'Metadata');
		return $fields;
	}

}

/**
 *  @package shop
 */
class CheckoutPage_Controller extends Page_Controller {

	private static $allowed_actions = array(
		'OrderForm',
		'payment',
		'PaymentForm'
	);

	public function Title() {
		if($this->Title) {
			return $this->Title;
		}

		return _t('CheckoutPage.TITLE', "Checkout");
	}

	public function OrderForm() {
		if(!(bool)$this->Cart()) {
			return false;
		}

		$form = new PaymentForm(
			$this,
			'OrderForm',
			Injector::inst()->create("CheckoutComponentConfig", ShoppingCart::curr())
		);

		$form->Cart = $this->Cart();

		return $form;
	}

	/**
	 * Action for making on-site payments
	 */
	public function payment() {
		if(!$this->Cart()) {
			return $this->redirect($this->Link());
		}

		return array(
			'Title' => 'Make Payment',
			'OrderForm' => $this->PaymentForm()
		);
	}

	public function PaymentForm() {
		if(!(bool) $this->Cart()) {
			return false;
		}

		$config = new CheckoutComponentConfig(ShoppingCart::curr(), false);
		$config->AddComponent(new OnsitePaymentCheckoutComponent());

		$form = PaymentForm::create($this, "PaymentForm", $config);

		$form->setActions(new FieldList(
			FormAction::create("submitpayment", "Submit Payment")
		));

		$form->setFailureLink($this->Link());

		return $form;
	}
}
