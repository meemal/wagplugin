<?php

function get_user_profile_pic($id){
  $profile = get_field('profile_picture', $id);
  if (!$profile) {
    $profile = get_field('default_profile_image', 'option');
  }
  return $profile;  
}



