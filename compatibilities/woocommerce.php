<?php

class Brizy_Compatibilities_Woocommerce {

	public function __construct() {
		add_action( 'woocommerce_checkout_terms_and_conditions', [ $this, 'woocommerce_checkout_terms_and_conditions' ], 29 );
	}

	/*
	 * Dont allow woo to render post_content of the terms page if it is edited with the brizy,
	 * will lead to the display of the unformatted our html above the place order button on checkout page.
	 */
	public function woocommerce_checkout_terms_and_conditions() {
		$terms_page_id = wc_terms_and_conditions_page_id();

		if ( ! $terms_page_id || ! Brizy_Editor_Entity::isBrizyEnabled( $terms_page_id ) ) {
			return;
		}

		remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
	}
}