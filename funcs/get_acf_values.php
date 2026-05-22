<?php

/**
 * Default profile image URL (site option, local upload, or production fallback).
 *
 * @return string
 */
function ftd_get_default_profile_image_url() {
	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	if ( function_exists( 'get_field' ) ) {
		$default = get_field( 'default_profile_image', 'option' );

		if ( is_array( $default ) && ! empty( $default['url'] ) ) {
			$url = (string) $default['url'];
			return $url;
		}

		if ( is_numeric( $default ) ) {
			$attachment_url = wp_get_attachment_image_url( (int) $default, 'medium' );

			if ( $attachment_url ) {
				$url = $attachment_url;
				return $url;
			}
		}
	}

	$local = WP_CONTENT_DIR . '/uploads/2025/07/Default-Profile-Image.png';

	if ( file_exists( $local ) ) {
		$url = content_url( 'uploads/2025/07/Default-Profile-Image.png' );
		return $url;
	}

	$url = 'https://wearegeniuses.com/wp-content/uploads/2025/07/Default-Profile-Image.png';

	return $url;
}

/**
 * Whether a profile image URL is usable (not empty / Gravatar placeholder).
 *
 * @param string $url Image URL.
 * @return bool
 */
function ftd_is_usable_profile_image_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return false;
	}

	if ( false !== strpos( $url, 'gravatar.com/avatar/?' ) ) {
		return false;
	}

	if ( preg_match( '#gravatar\.com/avatar/[a-f0-9]{32}\?.*(?:[;&]d=(?:mm|mystery|blank|404|identicon|wavatar|retro|robohash)|$)#i', $url ) ) {
		return false;
	}

	if ( false !== strpos( $url, 'blank.gif' ) ) {
		return false;
	}

	return true;
}

/**
 * Profile picture URL for a user, falling back to the site default.
 *
 * @param int $user_id User ID.
 * @param int $size    Image size in pixels.
 * @return string
 */
function ftd_get_user_profile_image_url( $user_id, $size = 96 ) {
	$user_id = (int) $user_id;
	$default = ftd_get_default_profile_image_url();

	if ( $user_id <= 0 ) {
		return $default;
	}

	if ( function_exists( 'get_field' ) ) {
		$profile = get_field( 'profile_picture', 'user_' . $user_id );

		if ( is_array( $profile ) && ! empty( $profile['url'] ) && ftd_is_usable_profile_image_url( $profile['url'] ) ) {
			return (string) $profile['url'];
		}
	}

	$avatar_id = (int) get_user_meta( $user_id, 'wp_user_avatar', true );

	if ( $avatar_id > 0 ) {
		$attachment_url = wp_get_attachment_image_url( $avatar_id, array( $size, $size ) );

		if ( $attachment_url && ftd_is_usable_profile_image_url( $attachment_url ) ) {
			return $attachment_url;
		}
	}

	$gravatar = get_avatar_url(
		$user_id,
		array(
			'size' => $size,
		)
	);

	if ( ftd_is_usable_profile_image_url( $gravatar ) ) {
		return $gravatar;
	}

	return $default;
}

/**
 * Whether the user has uploaded a custom profile photo (not the site default).
 *
 * @param int $user_id User ID.
 * @return bool
 */
function ftd_user_has_custom_profile_image( $user_id ) {
	$user_id = (int) $user_id;

	if ( $user_id <= 0 ) {
		return false;
	}

	if ( function_exists( 'get_field' ) ) {
		$profile = get_field( 'profile_picture', 'user_' . $user_id );

		if ( is_array( $profile ) && ! empty( $profile['url'] ) && ftd_is_usable_profile_image_url( $profile['url'] ) ) {
			return true;
		}
	}

	$avatar_id = (int) get_user_meta( $user_id, 'wp_user_avatar', true );

	if ( $avatar_id > 0 ) {
		$attachment_url = wp_get_attachment_image_url( $avatar_id, 'thumbnail' );

		if ( $attachment_url && ftd_is_usable_profile_image_url( $attachment_url ) ) {
			return true;
		}
	}

	return false;
}

/**
 * @param string|null $id ACF user field key prefix, e.g. user_123, or post ID.
 * @return array<string, mixed>
 */
function get_user_profile_pic( $id = null ) {
	if ( $id && is_string( $id ) && preg_match( '/^user_(\d+)$/', $id, $matches ) && function_exists( 'ftd_get_user_profile_image_url' ) ) {
		$user_id = (int) $matches[1];
		$user    = get_userdata( $user_id );

		return array(
			'url' => ftd_get_user_profile_image_url( $user_id ),
			'alt' => $user instanceof WP_User ? $user->display_name : __( 'Profile picture', 'ftd-directory-listings' ),
		);
	}

	if ( $id ) {
		$profile = get_field( 'profile_picture', $id );
	} else {
		$profile = get_field( 'profile_picture' );
	}

	if ( empty( $profile ) || ! is_array( $profile ) || empty( $profile['url'] ) || ! ftd_is_usable_profile_image_url( $profile['url'] ) ) {
		$profile = get_field( 'default_profile_image', 'option' );
	}

	if ( empty( $profile ) || ! is_array( $profile ) || empty( $profile['url'] ) ) {
		$profile = array(
			'url' => ftd_get_default_profile_image_url(),
			'alt' => __( 'Default profile', 'ftd-directory-listings' ),
		);
	}

	return $profile;
}
