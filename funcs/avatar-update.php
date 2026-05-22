<?php
/**
 * Add avatar field to profile page
 * Description: Displays predefined PMPro fields on the frontend user profile using [pmpro_member_profile].
 * Version: 1.0
 * Author: Naomi Spirit
 */

/**
 * Create Custom User Avatars with the Register Helper Add On
 *
 * Allow members to upload their avatar using a Register Helper field during checkout or on the Member Profile Edit page.
 *  
 * title: Create Custom User Avatars with the Register Helper Add On
 * layout: snippet
 * collection: add-ons, register-helper
 * category: user-avatars
 * 

 */

// Filter the saved or updated User Avatar meta field value and add the image to the Media Library.
function my_updated_user_avatar_user_meta( $meta_id, $user_id, $meta_key, $meta_value ) {
    // Change user_avatar to your Register Helper file upload name.
    if ( 'user_avatar' === $meta_key ) {
        $user_info     = get_userdata( $user_id );
        $filename      = $meta_value['fullpath'];
        $filetype      = wp_check_filetype( basename( $filename ), null );
        $wp_upload_dir = wp_upload_dir();
        $attachment    = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_status'    => 'inherit',
        );
        $attach_id     = wp_insert_attachment( $attachment, $filename );
        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        update_user_meta( $user_id, 'wp_user_avatar', $attach_id );
    }
}

add_action( 'added_user_meta', 'my_updated_user_avatar_user_meta', 10, 4 );
add_action( 'updated_user_meta', 'my_updated_user_avatar_user_meta', 10, 4 );
add_filter( 'get_avatar', 'my_user_avatar_filter', 20, 5 );

function my_user_avatar_filter( $avatar, $id_or_email, $size, $default, $alt ) {
    // 1) Figure out the user ID
    if ( is_numeric( $id_or_email ) ) {
        $user_id = (int) $id_or_email;
    } elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
        $user_id = (int) $id_or_email->user_id;
    } else {
        // email lookup, etc.
        $user    = get_user_by( 'email', $id_or_email );
        $user_id = $user ? $user->ID : 0;
    }

    // 2) If they have a custom upload, use it
    if ( $user_id ) {
        $avatar_id = get_user_meta( $user_id, 'wp_user_avatar', true );
        if ( ! empty( $avatar_id ) ) {
            $src    = wp_get_attachment_image_src( $avatar_id, [ $size, $size ] );
            if ( ! empty( $src[0] ) ) {
                return sprintf(
                    "<img alt='%s' src='%s' class='avatar avatar-%d photo' height='%d' width='%d' />",
                    esc_attr( $alt ),
                    esc_url( $src[0] ),
                    $size,
                    $size,
                    $size
                );
            }
        }
    }

    // 3) No custom upload — use the site default profile image.
    if ( function_exists( 'ftd_get_default_profile_image_url' ) ) {
        $default_url = ftd_get_default_profile_image_url();

        if ( $default_url ) {
            return sprintf(
                "<img alt='%s' src='%s' class='avatar avatar-%d photo' height='%d' width='%d' />",
                esc_attr( $alt ),
                esc_url( $default_url ),
                $size,
                $size,
                $size
            );
        }
    }

    return $avatar;
}


// Add the User Avatar field at checkout and on the profile edit forms.
function my_pmprorh_init_user_avatar() {
    //don't break if Register Helper is not loaded
    if ( ! function_exists( 'pmprorh_add_registration_field' ) ) {
        return false;
    }
    //define the fields
    $fields   = array();
    $fields[] = new PMProRH_Field(
        'user_avatar',              // input name, will also be used as meta key
        'file',                 // type of field
        array(
            'label'     => 'Profile Picture',
            'hint'      => 'Recommended size 600px X 600px',
            'profile'   => true,    // show in user profile
            'preview'   => true,    // show a preview-sized version of the image
            'addmember' => true,
            'allow_delete' => true,
    
        )
    );

    //add the fields into a new checkout_boxes are of the checkout page
    foreach ( $fields as $field ) {
        pmprorh_add_registration_field(
            'checkout_boxes', // location on checkout page
            $field            // PMProRH_Field object
        );
    }
}
add_action( 'init', 'my_pmprorh_init_user_avatar' );
