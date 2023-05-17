<?php
const crlf = "\r\n";
$db = opendb();

$userid=0;
$colors=[];
$hilitecolors=[];
$inapp=0;
$myrevsid  = 'public';
$cookieexpiredays = 180;

$useragent = strtolower(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'unknown');
$ismobile  = (strpos($useragent, "iphone") || strpos($useragent, "android") || strpos($useragent, "mobile"))?1:0;
if(isset($_COOKIE['rev_rswismobile'])) $ismobile = $_COOKIE['rev_rswismobile'];

//
//
//
function opendb(){
  global $dbserv, $dbuser, $dbpass, $dbname;
  $dtb = mysqli_connect ($dbserv, $dbuser, $dbpass, $dbname) or die ("Could not connect to REV database");
  mysqli_query($dtb, 'SET NAMES utf8');
  mysqli_query($dtb, 'SET CHARACTER SET utf8');
  mysqli_query($dtb, 'SET sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'))');
  mysqli_set_charset($dtb, "utf8");
  return $dtb;
}

function dbquery($q) {
  global $db, $sqlerr, $page, $dbhits;
  // at least some injection protection
  $q = str_replace('<scr', '', $q);
  $q = str_replace('cast(', '', $q);
  $result = mysqli_query($db, $q);
  if ($result === false) {
    $sqlerr = mysqli_error($db);
    file_put_contents("logs/mysql.log", date("m/d/Y H:i", time())."\r\nScript name: ".$_SERVER['PHP_SELF'].": Page: ".$page."\r\nMySQL error: ".$sqlerr."\r\nQuery string: ".$q."\r\n\r\n" , FILE_APPEND);
  }
  $dbhits++;
  return $result;
}

function rs($s){
  return mysqli_fetch_array(dbquery($s));
}
function getbooktitle($t,$b, $abr){
  $sql = 'select title, abbr from book where testament = '.$t.' and book = '.$b.' ';
  $row = rs($sql);
  if ($row) return (($abr)?$row[1]:$row[0]);
  else return 'unknown';
}
function right($txt, $num){
  return substr($txt, -$num);
}
function left($txt, $num){
  return substr($txt, 0, $num);
}
function logview($p,$t,$b,$c,$v,$m=''){
  global $userid, $ismobile, $inapp;
  if(isset($_COOKIE['rev_inapp'])) $inapp = $_COOKIE['rev_inapp'];
  $ip = ((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'unknown');
  $m = trim(str_replace('\'', '\\\'',$m));
  $sql = 'insert into viewlogs(userid, remoteip, page, testament, book, chapter, verse, viewtime, mobile, misc) values ('.
         (($inapp==1)?-7:0).',\''.$ip.'\','.$p.','.$t.','.$b.','.$c.','.$v.',UTC_TIMESTAMP(),'.$ismobile.','.(($m=='')?'null':'\''.left($m, 100).'\'').') ';
  $insert = dbquery($sql);
}
function checklogin(){
  global $myrevid, $myrevsid, $userid;
  $myrevsid  = ((isset($_COOKIE["myrevsid"]))?$_COOKIE["myrevsid"]:'public');
  if($myrevsid=='public'){
    $userid = 0;
    $myrevid=0;
  }else{
    $sql = 'select myrevid, userid from myrevusers where cursession = \''.$myrevsid.'\' ';
    $row = rs($sql);
    if($row){
      $myrevid = $row['myrevid'];
      $userid  = $row['userid'];
    }else{
      $myrevsid = 'public';
      $myrevid = 0;
      $userid = 0;
    }
  }
}

function getpreferences(){
  // doing this mainly for $colors[]
  global $colors, $hilitecolors;
  $arprefs   = explode(';', (isset($_COOKIE['rev_preferences']))?$_COOKIE['rev_preferences']:'1;1;1;1.3;merriweather;0;0;1;1;0;0;0;1;1;0;0;0;0;2;1;0');
  $viewcols  = $arprefs[0];
  $versebreak= $arprefs[1];
  $fontsize  = $arprefs[2];
  $lineheight= $arprefs[3];
  $fontfamily= $arprefs[4];
  $swipenav  = $arprefs[5];
  $useoefirst= $arprefs[6];
  $parachoice= $arprefs[7];
  $navonchap = $arprefs[8];
  $commnewtab= $arprefs[9];
  $colortheme= $arprefs[10];
  $commlinkstyl = 0;
  $lexicon      = $arprefs[12];
  $scrollynav   = $arprefs[13];
  $showdevitems = $arprefs[14];
  $showcommlinks= 0;
  $viewversnav  = $arprefs[16];
  $showpdflinks = $arprefs[17];
  $versnavwhat  = $arprefs[18];
  $linkcommentary= $arprefs[19];
  $ucaseot   = $arprefs[20];

  // colors[0] = file extension
  // colors[1] = main font color
  // colors[2] = background
  // colors[3] = soft div border
  // colors[4] = menu text color
  // colors[5] = comlink hover color
  // colors[6] = highlight
  // colors[7] = subtle
  switch($colortheme){
  case 1: // black background
      $colors = array('_LOD','#ddd','#000','#666','#ddd','yellow', '#666','#aaa');
      $eventcolors = array('_LOD','#909090','#878787','#808080','#777777','#707070', '#676767','#977','none');
      $hilitecolors = array('transparent','#664','#464','#446', '#644','#444');
      break;
  case 2; // sepia background
      $colors = array('_SEP','#5f4b32','#fbf0d9','#bda78e','#5f4b32','blue','#dbd0b9','#bda78e');
      $eventcolors = array('_LOD','#fee','#efe','#eef','#ffe','#eff', '#fef','#fdd','none');
      $hilitecolors = array('transparent','#ded7c9','#efe','#eef','#ffe','#ddd');
      break;
  default: // white background
      $colors = array('','#000','#fff','#ccc','#525252','blue','#ddd','#aaa');
      $eventcolors = array('_LOD','#fee','#efe','#eef','#ffe','#eff', '#fef','#fdd','none');
      $hilitecolors = array('transparent','#ff9','#dfd','#def','#fdd','#ddd');
      break;
  }
}

function getsettingvalue($nam, $typ){
  global $timezone;
  switch($typ){
    case 'time':
        $fld = 'sometime';
        $ret = new DateTime(null ?? '', new DateTimeZone('UTC'));
        $ret = $ret->format('Y-m-d H:i:s');
        break;
    case 'num':    $fld = 'somenumber';$ret = '0'; break;
    case 'string':
    default:       $fld = 'somestring';$ret = 'text';
  }
  $row = rs('select '.$fld.' from settings where settingname = \''.$nam.'\' ');
  if($row){
    return $row[0];
  }else{
    $insert = dbquery('insert into settings(settingname, '.$fld.') values (\''.$nam.'\',\''.$ret.'\')');
    return $ret;
  }
}
