<?php


// === Helpers (compact) ======================================================
if (!function_exists('ftd_is_numeric_identifier')) {
  function ftd_is_numeric_identifier($label) {
    if ($label === null) return false;
    $s = trim((string)$label);
    // Strip common prefixes
    $s = preg_replace('/^(ID:|Id:|id:)\s*/', '', $s); // "ID: 12345"
    $s = ltrim($s, '@+');                              // "@12345" or "+12345"
    // If after stripping we have only digits, treat as numeric identifier
    return ($s !== '') && ctype_digit($s);
  }
}
/**
 * Strip scheme like https://, http:// (and optional leading www.) from a URL.
 * Usage: echo ftd_strip_url_prefix($website);
 */
function ftd_strip_url_prefix($url, $strip_www = true, $strip_trailing_slash = false) {
  $s = trim((string)$url);
  // Remove any scheme (http, https, ftp, etc.)
  $s = preg_replace('#^[a-z][a-z0-9+\-.]*://#i', '', $s);
  // Optionally remove protocol-relative prefix //
  $s = preg_replace('#^//#', '', $s);
  if ($strip_www) {
    $s = preg_replace('#^www\.#i', '', $s);
  }
  if ($strip_trailing_slash) {
    $s = rtrim($s, '/');
  }
  return $s;
}
if (!function_exists('ftd_is_url')) {
  function ftd_is_url($s){ return (bool)preg_match('#^https?://#i', trim((string)$s)); }
}
if (!function_exists('ftd_path_segs')) {
  function ftd_path_segs($url){
    $p = wp_parse_url($url);
    $path = trim($p['path'] ?? '', '/');
    return $path === '' ? [] : explode('/', $path);
  }
}

// Return [url,label] for each network.
if (!function_exists('ftd_label_facebook')) {
  function ftd_label_facebook($in){
    if (!$in) return ['',''];
    $raw = trim($in);
    if (!ftd_is_url($raw)) return ["https://facebook.com/".ltrim($raw,'@/'), '@'.ltrim($raw,'@/')];
    $segs = ftd_path_segs($raw); $label='Facebook';
    parse_str(wp_parse_url($raw, PHP_URL_QUERY) ?? '', $q);
    if ($segs) {
      if ($segs[0]==='groups' && !empty($segs[1])) $label = 'Group: '.$segs[1];
      elseif ($segs[0]==='profile.php' && !empty($q['id'])) $label = 'ID: '.$q['id'];
      else $label = '@'.$segs[0];
    }
    return [$raw,$label];
  }
}
if (!function_exists('ftd_label_linkedin')) {
  function ftd_label_linkedin($in){
    if (!$in) return ['',''];
    $raw = trim($in);
    if (!ftd_is_url($raw)) return ["https://www.linkedin.com/in/".ltrim($raw,'@/'), 'in/'.ltrim($raw,'@/')];
    $s = ftd_path_segs($raw); $label='LinkedIn';
    if ($s) {
      $a=$s[0]; $b=$s[1]??'';
      if ($a==='in' && $b) $label="in/$b";
      elseif ($a==='company'&&$b) $label="company/$b";
      elseif ($a==='school'&&$b) $label="school/$b";
      elseif ($a==='groups'&&$b) $label="groups/$b";
      else $label=$a;
    }
    return [$raw,$label];
  }
}
if (!function_exists('ftd_label_youtube')) {
  function ftd_label_youtube($in){
    if (!$in) return ['',''];
    $raw=trim($in);
    if (!ftd_is_url($raw)) return ["https://www.youtube.com/@".ltrim($raw,'@/'), '@'.ltrim($raw,'@/')];
    $host = wp_parse_url($raw, PHP_URL_HOST);
    $s = ftd_path_segs($raw); $label='YouTube';
    if (stripos($host,'youtu.be')!==false) $label='Video';
    elseif ($s) {
      if ($s[0][0]==='@') $label=$s[0];
      elseif ($s[0]==='c' && !empty($s[1])) $label='c/'.$s[1];
      elseif ($s[0]==='channel' && !empty($s[1])) $label=$s[1];
      elseif ($s[0]==='user' && !empty($s[1])) $label='user/'.$s[1];
    }
    return [$raw,$label];
  }
}
if (!function_exists('ftd_label_instagram')) {
  function ftd_label_instagram($in){
    if (!$in) return ['',''];
    $raw=trim($in);
    if (!ftd_is_url($raw)) return ["https://instagram.com/".ltrim($raw,'@/'), '@'.ltrim($raw,'@/')];
    $s = ftd_path_segs($raw); $label='Instagram';
    $avoid=['p','reel','reels','tv','stories','explore'];
    if ($s && !in_array(strtolower($s[0]),$avoid,true)) $label='@'.$s[0];
    return [$raw,$label];
  }
}
if (!function_exists('ftd_label_telegram')) {
  function ftd_label_telegram($in){
    if (!$in) return ['',''];
    $raw=trim($in);
    if (!ftd_is_url($raw)) return ["https://t.me/".ltrim($raw,'@'), '@'.ltrim($raw,'@')];
    $s = ftd_path_segs($raw); $label='Telegram';
    if ($s){
      if ($s[0]==='joinchat' || $s[0][0]==='+') $label='Telegram Group';
      else $label='@'.$s[0];
    }
    return [$raw,$label];
  }
}
if (!function_exists('ftd_label_whatsapp')) {
  function ftd_label_whatsapp($in){
    if (!$in) return ['',''];
    $w=trim($in);
    if (ftd_is_url($w)) {
      $host=wp_parse_url($w, PHP_URL_HOST);
      $path=wp_parse_url($w, PHP_URL_PATH) ?: '';
      if (stripos($host,'chat.whatsapp.com')!==false || strpos($path,'/invite/')===0) return [$w,'WhatsApp Group'];
      if (stripos($host,'wa.me')!==false) { $digits=trim(basename($path),'/'); return [$w, '+'.$digits]; }
      parse_str(wp_parse_url($w, PHP_URL_QUERY) ?? '', $q);
      if (!empty($q['phone'])) return [$w, '+'.preg_replace('/\D+/','',$q['phone'])];
      return [$w,'WhatsApp'];
    }
    // invite code?
    if (preg_match('#^[A-Za-z0-9_-]{16,}$#',$w)) return ['https://chat.whatsapp.com/'.$w,'WhatsApp Group'];
    // phone
    $digits=preg_replace('/\D+/','',$w);
    if ($digits!=='') return ['https://wa.me/'.$digits, (strpos($w,'+')===0?'+':'').$digits];
    return ['',''];
  }
}




// --- Utilities ---------------------------------------------------------------
function ftd_url_path_segments($url) {
  $path = parse_url($url, PHP_URL_PATH) ?? '';
  $path = trim($path, '/');
  return $path === '' ? [] : explode('/', $path);
}
function ftd_is_url($str) { return (bool)preg_match('#^https?://#i', trim($str)); }

// // --- Facebook ---------------------------------------------------------------
// function ftd_normalize_facebook($input) {
//   if (empty($input)) return ['url'=>'','label'=>''];
//   $raw = trim($input);
//   if (!ftd_is_url($raw)) {
//     // assume they passed a page/username/slug
//     $slug = ltrim($raw, '@/');
//     return ['url'=>"https://facebook.com/{$slug}", 'label'=>"@{$slug}"];
//   }
//   $segs = ftd_url_path_segments($raw);
//   $label = 'Facebook';
//   $host  = parse_url($raw, PHP_URL_HOST);
//   $query = parse_url($raw, PHP_URL_QUERY) ?? '';
//   parse_str($query, $qp);

//   if (!empty($segs)) {
//     if ($segs[0] === 'groups' && !empty($segs[1])) {
//       $label = "Group: {$segs[1]}";
//     } elseif ($segs[0] === 'profile.php' && !empty($qp['id'])) {
//       $label = "ID: " . $qp['id'];
//     } else {
//       // page/username
//       $label = '@' . $segs[0];
//     }
//   }
//   return ['url'=>$raw, 'label'=>$label];
// }

// // --- LinkedIn ---------------------------------------------------------------
// function ftd_normalize_linkedin($input) {
//   if (empty($input)) return ['url'=>'','label'=>''];
//   $raw = trim($input);
//   if (!ftd_is_url($raw)) {
//     $slug = ltrim($raw, '@/');
//     return ['url'=>"https://www.linkedin.com/in/{$slug}/", 'label'=>"in/{$slug}"];
//   }
//   $segs = ftd_url_path_segments($raw);
//   $label = 'LinkedIn';
//   if (!empty($segs)) {
//     $first = $segs[0];
//     $second = $segs[1] ?? '';
//     if ($first === 'in' && $second)        $label = "in/{$second}";
//     elseif ($first === 'company' && $second) $label = "company/{$second}";
//     elseif ($first === 'school' && $second)  $label = "school/{$second}";
//     elseif ($first === 'groups' && $second)  $label = "groups/{$second}";
//     elseif ($first === 'pub' && $second)     $label = "pub/{$second}";
//     else                                     $label = $first;
//   }
//   return ['url'=>$raw, 'label'=>$label];
// }

// // --- YouTube ---------------------------------------------------------------
// function ftd_normalize_youtube($input) {
//   if (empty($input)) return ['url'=>'','label'=>''];
//   $raw = trim($input);
//   if (!ftd_is_url($raw)) {
//     // Prefer the modern @handle if they gave a bare identifier
//     $handle = ltrim($raw, '@/');
//     return ['url'=>"https://www.youtube.com/@{$handle}", 'label'=>"@{$handle}"];
//   }
//   $host = parse_url($raw, PHP_URL_HOST);
//   $segs = ftd_url_path_segments($raw);
//   $label = 'YouTube';

//   if (stripos($host, 'youtu.be') !== false) {
//     $label = 'Video';
//   } else {
//     if (!empty($segs)) {
//       if ($segs[0][0] === '@')                $label = $segs[0];                // /@handle
//       elseif ($segs[0] === 'c' && !empty($segs[1]))      $label = 'c/' . $segs[1];
//       elseif ($segs[0] === 'channel' && !empty($segs[1])) $label = $segs[1];     // UC...
//       elseif ($segs[0] === 'user' && !empty($segs[1]))    $label = 'user/' . $segs[1];
//     }
//   }
//   return ['url'=>$raw, 'label'=>$label];
// }

// // --- Instagram --------------------------------------------------------------
// function ftd_normalize_instagram($input) {
//   if (empty($input)) return ['url'=>'','label'=>''];
//   $raw = trim($input);
//   if (!ftd_is_url($raw)) {
//     $h = ltrim($raw, '@/');
//     return ['url'=>"https://instagram.com/{$h}", 'label'=>"@{$h}"];
//   }
//   $segs = ftd_url_path_segments($raw);
//   $label = 'Instagram';
//   // Avoid content routes like /p/, /reel/, /tv/, /stories/
//   $avoid = ['p','reel','reels','tv','stories','explore'];
//   if (!empty($segs) && !in_array(strtolower($segs[0]), $avoid, true)) {
//     $label = '@' . $segs[0];
//   }
//   return ['url'=>$raw, 'label'=>$label];
// }

// // --- Telegram ---------------------------------------------------------------
// function ftd_normalize_telegram($input) {
//   if (empty($input)) return ['url'=>'','label'=>''];
//   $raw = trim($input);
//   if (!ftd_is_url($raw)) {
//     $h = ltrim($raw, '@');
//     return ['url'=>"https://t.me/{$h}", 'label'=>"@{$h}"];
//   }
//   $segs = ftd_url_path_segments($raw);
//   $label = 'Telegram';
//   if (!empty($segs)) {
//     if ($segs[0] === 'joinchat' || $segs[0][0] === '+') {
//       $label = 'Telegram Group';
//     } else {
//       $label = '@' . $segs[0];
//     }
//   }
//   return ['url'=>$raw, 'label'=>$label];
// }

// // --- WhatsApp (handles phone numbers or group invites) ----------------------
// function ftd_normalize_whatsapp($input) {
//   if (empty($input)) return ['url'=>'','label'=>''];
//   $w = trim($input);

//   if (ftd_is_url($w)) {
//     $host = parse_url($w, PHP_URL_HOST);
//     $path = parse_url($w, PHP_URL_PATH) ?: '';
//     $label = 'WhatsApp';
//     if (stripos($host, 'chat.whatsapp.com') !== false || strpos($path, '/invite/') === 0) {
//       $label = 'WhatsApp Group';
//     } elseif (stripos($host, 'wa.me') !== false) {
//       $digits = trim(basename($path), '/');
//       $label = '+' . $digits;
//     }
//     return ['url'=>$w, 'label'=>$label];
//   }

//   // Invite code only?
//   if (preg_match('#^[A-Za-z0-9_-]{16,}$#', $w)) {
//     return ['url'=>'https://chat.whatsapp.com/' . $w, 'label'=>'WhatsApp Group'];
//   }

//   // Phone number → wa.me
//   $digits = preg_replace('/\D+/', '', $w);
//   if ($digits !== '') {
//     // Preserve leading "+" in label if provided
//     $label = (strpos($w, '+') === 0 ? '+' : '') . $digits;
//     return ['url'=>'https://wa.me/' . $digits, 'label'=>$label];
//   }

//   // Fallback
//   return ['url'=>'', 'label'=>''];
// }
// ?>
