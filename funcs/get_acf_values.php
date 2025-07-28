<?php

function get_user_profile_pic($id = null) {
  if ($id) {
   $profile = get_field('profile_picture', $id);
  }else{
    $profile = get_field('profile_picture');
  }
  
  if (!$profile) {
    $profile = get_field('default_profile_image', 'option');
  }
  return $profile;  
}



