<?php

// Local, read-only preview of HTML rendered under the isolated test database.
// No application bootstrap, auth bypass, database connection or business actions.
if (PHP_SAPI !== 'cli-server') exit(2);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = realpath(__DIR__.'/../../public');
$site = realpath(__DIR__.'/../../docs/qa/responsive-100/site');
$candidate = str_ends_with($uri, '.html') ? realpath($site.'/'.$uri) : realpath($root.'/'.$uri);
if (!$candidate || (!str_starts_with($candidate, $root.DIRECTORY_SEPARATOR) && !str_starts_with($candidate, $site.DIRECTORY_SEPARATOR)) || !is_file($candidate)) { http_response_code(404); exit; }
$types = ['html'=>'text/html; charset=utf-8','css'=>'text/css','js'=>'application/javascript','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','webp'=>'image/webp','woff2'=>'font/woff2','mp4'=>'video/mp4'];
$extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
if (!isset($types[$extension])) { http_response_code(404); exit; }
header('Content-Type: '.$types[$extension]);
readfile($candidate);
