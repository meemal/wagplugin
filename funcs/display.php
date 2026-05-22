<?php

function ftd_render_simple_card($heading = '', $body = '', $button = '', $subtext = '') {
    echo '<div class="card  has-text-align-center">';

    if (!empty($heading)) {
        echo '<h3 class="text-purple">' . esc_html($heading) . '</h3>';
    }

    if (!empty($body)) {
        echo '<p class="text-midgrey" style="margin-bottom: 1rem;">' . wp_kses_post($body) . '</p>';
    }

    if (!empty($button)) {
        echo $button;
    }

    if (!empty($subtext)) {
        echo '<p class="text-small text-midgrey" style="margin-top: 1rem;"><em>' . esc_html($subtext) . '</em></p>';
    }

    echo '</div>';
}


add_filter( 'avatar_defaults', 'ftd_custom_default_avatar' );
function ftd_custom_default_avatar( $avatars ) {
	$default_url = function_exists( 'ftd_get_default_profile_image_url' )
		? ftd_get_default_profile_image_url()
		: 'https://wearegeniuses.com/wp-content/uploads/2025/07/Default-Profile-Image.png';

    $avatars[ $default_url ] = 'We Are Geniuses Default Profile';
    return $avatars;
}

// add_filter( 'get_avatar', 'my_user_avatar_filter', 20, 5 );
// function my_user_avatar_filter( $avatar, $id_or_email, $size, $default, $alt ) {
//     // 1) Figure out the user ID
//     if ( is_numeric( $id_or_email ) ) {
//         $user_id = (int) $id_or_email;
//     } elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
//         $user_id = (int) $id_or_email->user_id;
//     } else {
//         // email lookup, etc.
//         $user    = get_user_by( 'email', $id_or_email );
//         $user_id = $user ? $user->ID : 0;
//     }

//     // 2) If they have a custom upload, use it
//     if ( $user_id ) {
//         $avatar_id = get_user_meta( $user_id, 'wp_user_avatar', true );
//         if ( ! empty( $avatar_id ) ) {
//             $src    = wp_get_attachment_image_src( $avatar_id, [ $size, $size ] );
//             if ( ! empty( $src[0] ) ) {
//                 return sprintf(
//                     "<img alt='%s' src='%s' class='avatar avatar-%d photo' height='%d' width='%d' />",
//                     esc_attr( $alt ),
//                     esc_url( $src[0] ),
//                     $size,
//                     $size,
//                     $size
//                 );
//             }
//         }
//     }

//     // 3) No custom? Fallback to whatever default you want
//     //    (you can pass in a custom URL, or let WP/Gravatar handle it)
//     return $avatar;
// }
