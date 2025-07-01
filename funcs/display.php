<?php 

add_filter('pmpro_no_access_message_html', function($html, $level_ids) {
  if (!is_user_logged_in() || !pmpro_hasMembershipLevel(2)) {
    return '<div style="position: relative; background-image: url(\'/wp-content/uploads/2025/06/genius-map.png\'); background-size: cover; background-position: center; padding: 64px 128px; max-width: 450px; margin: 0 auto 2rem; box-shadow: 0 0 20px rgba(0,0,0,0.3); color: white; text-align: center;">
    <div style="background-color: rgba(255, 255, 255, 0.85); padding: 32px; border-radius: 12px;">
        <h2 style="font-size: 32px; font-weight: bold; margin-bottom: 16px;color:#673f69;">We\'d Love to See You on the Map!</h2>
        <p style="font-size: 16px; margin-bottom: 24px;color:#333;">
            View where Joe Dispenza students are across the world—and put yourself on the map.
        </p>
        <a href="/join-we-are-geniuses/" class="btn">Unlock The Map</a>
        <p style="color:#666;">Already a level 2 member or higher?  <a href="/login/" >Login</a></p>
    </div>
</div>';

  }

  // Default fallback (return original message if user has access)
  return $html;
}, 10, 2);



