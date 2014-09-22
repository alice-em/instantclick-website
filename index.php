<?
$style = file_get_contents('style.css');
$style = preg_replace('#\s*/\*(.+)\*/\s*#', '', $style);
$style = str_replace(array("\r", "\n", "\t"), '', $style);
$style = str_replace(': ', ':', $style);
$style = str_replace(' {', '{', $style);
$style = str_replace(';}', '}', $style);
$style = str_replace(' + ', '+', $style);

if (!function_exists('find_page_in_dir')) {
  function find_page_in_dir($page, $dir) {
    $handle = opendir($dir);
    while ($file = readdir($handle)) {
      if ($file[0] != '.' && is_dir($dir . '/' . $file)) {
        $recursive_search = find_page_in_dir($page, $dir . '/' . $file);
        if ($recursive_search) {
          return $recursive_search;
        }
      }
      elseif ($file == $page . '.html') {
        return $dir . '/' . $page . '.html';
      }
    }
    return false;
  }
}

$page = 'index';

if (isset($_GET['page']) && strlen($_GET['page']) > 1) {
  $page = substr($_GET['page'], 1); // Starting at offset 1 because 0 is a slash.
}

/* When included by generate_static_files.php */
if (isset($static_filename)) {
  $page = substr($static_filename, 0, -strlen('.html'));
}

$page_path = find_page_in_dir($page, 'pages');

if (!$page_path) {
  $page = '404';
  $page_path = 'pages/404.html';
  header('HTTP/1.1 404 Not Found');
}

$page_source = $page_content = file_get_contents($page_path);

if (preg_match('#^---(.+)---#s', $page_source, $matches)) {
  $params = explode("\n", $matches[1]);
  foreach ($params as $param) {
    $colon_pos = strpos($param, ':');
    if (!$colon_pos) {
      continue;
    }

    $name = substr($param, 0, $colon_pos);
    $value = trim(substr($param, $colon_pos + 1));

    if ($name == 'title') {
      $page_title = $value;
    }
    if ($name == 'description') {
      $page_description = $value;
    }
  }
  $page_content = trim(substr($page_source, strlen($matches[0])));
}
?>
<!doctype html>
<meta charset="utf-8">
<? if (isset($page_title)): ?>
<title><?= $page_title ?></title>
<? endif ?>
<meta name="viewport" content="width=768">
<style><?= $style ?></style>
<? if (isset($page_description)): ?>
<meta name="description" content="<?= $page_description ?>">
<? endif ?>
<? if ($page != '404'): ?>
<link rel="canonical" href="http://instantclick.io/<? if ($page != 'index') { echo $page; } ?>">
<? endif ?>

<header id="header">
  <div class="logo"><a href=".">InstantClick</a></div>
  <ul>
    <li><a href="/download">Download</a>
    <li><a href="/click-test">Click test</a>
    <li><a href="/blog">Blog</a>
  </ul>
  <div class="border"></div>
</header>
<article class="container">
<?= $page_content ?>
</article>
<div id="footer">InstantClick is released under the <a href="license">MIT License</a>, © 2014 Alexandre Dieulot</div>
<script src="script-6.js" data-no-instant></script>