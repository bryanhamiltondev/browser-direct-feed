<?php
/* browser-direct-feed.php - slim CORS JSON feed. Production-verbatim extraction (thedjcalendar.com/top10.php).
   Reads a local JSON source, ranks, emits items + djs with CORS open and a 30-min cache hint. */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=1800');
$src = @file_get_contents(__DIR__ . '/djs.json');
if ($src === false) { http_response_code(503); echo '{"items":[],"djs":[]}'; exit; }
$all = json_decode($src, true);
$items = array(); $djs = array(); $nshows = 0;
if (is_array($all)) {
  $ranked = array();
  foreach ($all as $a) {
    if (!is_array($a)) continue;
    if (!empty($a['tour_dates']) && is_array($a['tour_dates'])) { $nshows += count($a['tour_dates']); }
    if (empty($a['rank']) || !empty($a['hide'])) continue;
    $r = (int)$a['rank'];
    if ($r >= 1 && $r <= 100) $ranked[$r] = $a;
  }
  ksort($ranked);
  $i = 0;
  foreach ($ranked as $r => $a) {
    if (++$i > 10) break;
    $nm = $a['name'];
    $items[] = '#' . $r . ' ' . (function_exists('mb_strtoupper') ? mb_strtoupper($nm) : strtoupper($nm));
    $slug = isset($a['id']) && $a['id'] !== '' ? $a['id'] : preg_replace('/[^a-z0-9]/', '', strtolower((function_exists('iconv') ? (string)@iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nm) : $nm)));
    $u = 'https://thedjcalendar.com/' . $slug;
    $dt = '';
    if (!empty($a['tour_dates']) && is_array($a['tour_dates'])) {
      foreach ($a['tour_dates'] as $td) {
        if (!empty($td['date']) && (empty($td['sort_ts']) || (int)$td['sort_ts'] >= time() - 86400)) { $dt = $td['date']; break; }
      }
      if ($dt === '' && !empty($a['tour_dates'][0]['date'])) $dt = $a['tour_dates'][0]['date'];
    }
    $djs[$i] = array('name' => $nm, 'url' => $u, 'img' => isset($a['image']) ? $a['image'] : '', 'date' => $dt);
  }
}
array_unshift($items, "The DJ Calendar top 10 DJ's worldwide");
echo json_encode(array('items' => $items, 'djs' => $djs, 'artists' => is_array($all) ? count($all) : 0, 'shows' => $nshows, 'ts' => time()));
