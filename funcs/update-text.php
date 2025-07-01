<?php

// ------------------------------------------ //
function fdt_change_translate_text_multiple( $translated ) {
	$text = array(
		'You have selected'=>'You selected',
		' membership level.'=>'.',
    'You selected the'=>'You selected',
		'Your current membership level of '=>'',
	);
	$translated = str_ireplace(  array_keys($text),  $text,  $translated );
	return $translated;
}
add_filter( 'gettext', 'fdt_change_translate_text_multiple', 20 );