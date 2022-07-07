<?php
/**
 * Double Opt-In Helper-related functions
 *
 * @link https://contactform7.com/doi-helper/
 */


add_action(
	'doihelper_init',
	'wpcf7_constant_contact_doi_register_agent',
	10, 0
);

/**
 * Registers wpcf7_constant_contact as an agent.
 */
function wpcf7_constant_contact_doi_register_agent() {
	if ( ! function_exists( 'doihelper_register_agent' ) ) {
		return;
	}

	doihelper_register_agent( 'wpcf7_constant_contact', array(
		'optin_callback' => apply_filters(
			'wpcf7_constant_contact_doi_optin_callback',
			'wpcf7_constant_contact_doi_default_optin_callback'
		),
		'email_callback' => apply_filters(
			'wpcf7_constant_contact_doi_email_callback',
			'wpcf7_constant_contact_doi_default_email_callback'
		),
	) );
}


/**
 * Default optin_callback function.
 */
function wpcf7_constant_contact_doi_default_optin_callback( $properties ) {
	$service = WPCF7_ConstantContact::get_instance();

	if ( $service->is_active() ) {
		$service->create_contact( $properties );
	}
}


/**
 * Default email_callback function.
 */
function wpcf7_constant_contact_doi_default_email_callback( $args ) {
	if ( ! isset( $args['token'] ) or ! isset( $args['email_to'] ) ) {
		return;
	}

	$site_title = wp_specialchars_decode(
		get_bloginfo( 'name' ),
		ENT_QUOTES
	);

	$link = add_query_arg(
		array( 'doitoken' => $args['token'] ),
		home_url()
	);

	$to = $args['email_to'];

	$subject = sprintf(
		/* translators: %s: blog name */
		__( 'Opt-in confirmation from %s', 'lc_studio_contact_form' ),
		$site_title
	);

	$message = sprintf(
		/* translators: 1: blog name, 2: confirmation link */
		__( 'Hello,

This is a confirmation email sent from %1$s.

We have received your submission to our web form, according to which you have allowed us to add you to our contact list. But, the process has not yet been completed. To complete it, please click the following link.

%2$s

If it was not your intention, or if you have no idea why you received this message, please do not click on the link, and ignore this message. We will never collect or use your personal data without your clear consent.

Sincerely,
%1$s', 'lc_studio_contact_form' ),
		$site_title,
		$link
	);

	wp_mail( $to, $subject, $message );
}
