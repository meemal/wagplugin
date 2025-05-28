<?php
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'We Are Geniuses Settings',
        'menu_title' => 'We Are Geniuses Settings',
        'menu_slug'  => 'We Are Geniuses Settings',
        'capability' => 'manage_options',
        'redirect'   => false
    ));
}


if( function_exists('acf_add_local_field_group') ):

  acf_add_local_field_group(array(
      'key' => 'group_directory_listing',
      'title' => 'Directory Listing Fields',
      'fields' => array(
          array(
              'key' => 'field_headline',
              'label' => 'Headline',
              'name' => 'headline',
              'type' => 'text',
          ),
          array(
              'key' => 'field_business_name',
              'label' => 'Business Name',
              'name' => 'business_name',
              'type' => 'text',
          ),
          array(
              'key' => 'field_looking_for',
              'label' => 'Looking For',
              'name' => 'looking_for',
              'type' => 'textarea',
          ),
          array(
              'key' => 'field_website',
              'label' => 'Website',
              'name' => 'website',
              'type' => 'url',
          ),
          array(
              'key' => 'field_phone',
              'label' => 'Phone Number',
              'name' => 'phone',
              'type' => 'text',
          ),
          array(
              'key' => 'field_email',
              'label' => 'Email',
              'name' => 'email',
              'type' => 'email',
          ),
          array(
              'key' => 'field_facebook',
              'label' => 'Facebook',
              'name' => 'facebook',
              'type' => 'url',
          ),
          array(
              'key' => 'field_linkedin',
              'label' => 'LinkedIn',
              'name' => 'linkedin',
              'type' => 'url',
          ),
          array(
              'key' => 'field_youtube',
              'label' => 'YouTube',
              'name' => 'youtube',
              'type' => 'url',
          ),
          array(
              'key' => 'field_cover_picture',
              'label' => 'Cover Picture',
              'name' => 'cover_picture',
              'type' => 'image',
              'return_format' => 'array',
              'preview_size' => 'medium',
          ),
          array(
              'key' => 'field_profile_picture',
              'label' => 'Profile Picture',
              'name' => 'profile_picture',
              'type' => 'image',
              'return_format' => 'array',
              'preview_size' => 'thumbnail',
          ),
      ),
      'location' => array(
          array(
              array(
                  'param' => 'post_type',
                  'operator' => '==',
                  'value' => 'directory_listing',
              ),
          ),
      ),
  ));
  
  endif;