<?
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

getpreferences();

$fromloc = ((isset($_REQUEST['fromloc']))?$_REQUEST['fromloc']:'0:1:40:1:1');
$bookmarks = ((isset($_COOKIE['rev_bookmarks']))?$_COOKIE['rev_bookmarks']:'');

$myrevsid  = 'public';
$myrevid = 0;
checklogin();

$arbmks = explode(';', $bookmarks??'');
$arfrom = explode(':', $fromloc??'');
$page = $arfrom[0];
$test = $arfrom[1];
$book = $arfrom[2];
$chap = $arfrom[3];
$vers = $arfrom[4];


$ret='';
//$ret.= 'fromloc: '.$fromloc.'<br />';
if($page==0 || $page==4 || $page==5 || $page==10 || ($page==14 && $book>0) || $page==26 || $page==44){
  if($page==4) $vers=0;
  $curloc = getcurrentloc($page,$test,$book,$chap,$vers);
  if($vers<0) $vers=0;

  //$ret.= 'func: addbmk('.$page.','.$test.','.$book.','.$chap.','.$vers.')<br />';
  //$ret.= 'chkbm: '.checkbm($page.','.$test.','.$book.','.$chap.','.(($page==0&&$vers>0)?'nav':'').$vers).'<br />';
  //$ret.= 'bmks: '.$bookmarks.'<br />';

  if(checkbm($page.','.$test.','.$book.','.$chap.','.(($page==0&&$vers>0)?'nav':'').$vers)==1){
    $ret.= '<span style="color:'.$colors[7].';font-style:italic;font-size:90%;">'.$curloc.' is bookmarked.</span>';
  }else{
    $ret.= '<a class="amenu" style="margin-bottom:7px;" onclick="addbmk('.$page.','.$test.','.$book.','.$chap.','.$vers.');return false;">+Add '.$curloc.'</a><br />';
  }
}

$ret.= '<div id="mybmks" style="display:table;width:95%;margin:0 auto;table-layout:fixed;">';

$havebmk=0;
for($i=0;$i<sizeof($arbmks);$i++){
  if($arbmks[$i]!=''){
    $havebmk=1;
    $ret.= '<div class="mybmk" style="display:table-row;white-space:nowrap;" data-itm="'.$arbmks[$i].'">';
    $ret.= '<div style="display:table-cell;padding:0 1px;width:16px;"><img src="/i/mnu_menu'.$colors[0].'.png" class="bmksorthandle" style="margin:0 0 2px 0;width:1.1em;cursor:ns-resize;" alt="" /></div>';
    $ret.= '<div style="display:table-cell;padding:0 1px;width:22px;"><a onclick="return delbmk(\''.$arbmks[$i].'\');" title="delete bookmark"><img src="/i/REV_cancel'.$colors[0].'.png" alt="cancel" style="border:0;margin:0 0 2px 4px;width:1.1em;" /></a></div>';
    $ret.= '<div style="display:table-cell;padding:0 1px;">';
    $arbmk = explode(',',$arbmks[$i]);
    $tit = getbooktitle($arbmk[1],$arbmk[2],(($ismobile)?1:0));
    switch ($arbmk[0]) { // page
    case 0: // viewbible
      $href = str_replace(' ','-',$tit).'/'.(($arbmk[3]==-1)?'':$arbmk[3].((substr($arbmk[4], 0,3)=='nav')?'/'.$arbmk[4]:''));
      $htit = $tit.(($arbmk[3]>0)?' '.$arbmk[3].((substr($arbmk[4], 0,3)=='nav')?':'.preg_replace('/\D/', '', $arbmk[4]):''):'');
      $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$htit.'</a>';
      break;
    case 4: // viewcomm
      $href = 'comm/'.str_replace(' ','-',$tit).'/'.$arbmk[3].((substr($arbmk[4], 0,3)=='nav')?'/'.$arbmk[4]:'');
      $htit = $tit.' '.$arbmk[3].((substr($arbmk[4], 0,3)=='nav')?':'.preg_replace('/\D/', '', $arbmk[4]):'');
      $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$htit.(($ismobile==1)?' commentary':' commentary').'</a>';
      break;
    case 5: // viewverscomm
      $href = str_replace(' ','-',$tit).'/'.$arbmk[3].'/'.$arbmk[4];
      $htit = $tit.' '.$arbmk[3].':'.$arbmk[4];
      $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$htit.(($ismobile==1)?' (Comm)':' (Commentary)').'</a>';
      break;
    case 10: // viewbookcomm
      $href = 'book/'.str_replace(' ','-',$tit);
      $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$tit.(($ismobile==1)?' (Bk Comm)':' (Book Commentery)').'</a>';
      break;
    case 14: // viewappxinfo
      switch ($arbmk[1]) {
      case 2: // info
        $tit = getbooktitle($arbmk[1],$arbmk[2],0);
        $href = 'info/'.$arbmk[2];
        $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$tit.'</a>';
        break;
      case 3: // appx
        $row = rs('select ifnull(tagline, title) from book where testament = 3 and book = '.$arbmk[2].' ');
        $tit = $row[0];
        $tit = trim(substr($tit, strpos($tit,':')+((strpos($tit,':')>0)?1:0)));
        //$tit = substr($tit, 0, (($ismobile==1)?12:24)).'..';
        $tit = (($ismobile==1)?'Apx ':'Appx ').$arbmk[2].': '.$tit;
        $href = 'appx/'.$arbmk[2];
        $tit = str_replace('&ldquo;','',$tit);
        $tit = str_replace('&rdquo;','',$tit);
        $tit = str_replace('&rsquo;','',$tit);
        $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$tit.'</a>';
        break;
      case 4: // word study
        $tit = getbooktitle($arbmk[1],$arbmk[2],0);
        $href = 'wordstudy/'.str_replace(' ', '_', $tit);
        $tit = (($ismobile==1)?'WS: ':'Word study: ').$tit;
        $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$tit.'</a>';
        break;
      };
      break;
    case 26: // blog
      $row = rs('select blogtitle from revblog where blogid = '.$arbmk[1].' ');
      $tit = $row[0];
      $href = 'blog/'.$arbmk[1].'/'.str_replace(' ','-',$tit);
      $tit = 'Blog: '.$tit;
      $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$tit.'</a>';
      break;
    case 44: // bib
      $href = (($arbmk[3]==1)?'bibliography':'abbrev');
      //$tit = (($arbmk[3]==1)?(($ismobile==1)?'Bibliography':'Bibliography'):(($ismobile==1)?'Abbreviations':'Abbreviations'));
      $tit = (($arbmk[3]==1)?'Bibliography':'Abbreviations');
      $ret.= '<a id="id_'.$arbmks[$i].'" href="/'.$href.'" class="bmkmenu">'.$tit.'</a>';
      break;
    }
    $ret.= '</div></div>';
  }
}

if($havebmk==0) $ret.= 'You don&rsquo;t have any bookmarks';
$ret.= '</div>';
/*
$jscript='var mybmks = new Sortable(document.getElementById(\'mybmks\'), {
    animation: 150,
    handle: \'.bmksorthandle\',
    direction: \'vertical\',
    touchStartThreshold: 5,
    onEnd: function (evt) {
      var myitems = document.getElementById("mybmks");
      var refs = \'\';
      var items = myitems.getElementsByClassName("mybmk");
      for (var ni=0;ni<items.length; ni++) {
        if(ni>0) refs+= \';\';
        refs+= items[ni].getAttribute(\'data-itm\');
      }
      setCookie(\'rev_bookmarks\', refs, cookieexpiredays);
      reloadBookmarks(page,1);
    },
  });';
*/
$jscript='var mybmks = new Sortable(document.getElementById(\'mybmks\'), {animation: 150,handle: \'.bmksorthandle\',direction: \'vertical\',touchStartThreshold: 5,onEnd: function (evt) {var myitems = document.getElementById("mybmks");var refs = \'\';var items = myitems.getElementsByClassName("mybmk");for (var ni=0;ni<items.length; ni++) {if(ni>0) refs+= \';\';refs+= items[ni].getAttribute(\'data-itm\');}setCookie(\'rev_bookmarks\', refs, cookieexpiredays);reloadBookmarks(page,1);},});';

if($havebmk>0) $ret.= '<a class="bmkmenu" style="margin-top:7px;" onclick="delbmks();return false;">Delete All</a><br />';
if($myrevid>0) $qry = dbquery('update myrevusers set bookmarks = '.(($bookmarks!=='')?'\''.$bookmarks.'\'':'null').' where myrevid = '.$myrevid.' ');

$out='{"html":"'.str_replace('"', '\"', $ret).'", "jscript":"'.str_replace(crlf, '', str_replace('"', '\"', $jscript)).'"}';
print($out);

mysqli_close($db);

function checkbm($bmk){
  $bookmarks = ((isset($_COOKIE['rev_bookmarks']))?$_COOKIE['rev_bookmarks']:'');
  return ((strpos($bookmarks.';', $bmk.';')!==false)?1:0);
}

function getcurrentloc($p, $t, $b, $c, $v){
  global $ismobile;
  $ret='oops';
  $tit = getbooktitle($t,$b,(($ismobile)?1:0));
  switch ($p) { // page
    case 0: // viewbible
      $ret = $tit.(($c>0)?' '.$c.(($v>0)?':'.$v:''):'');
      break;
    case 4: // viewcomm
      $tit.= ' '.$c.((substr($v, 0,3)=='nav')?':'.preg_replace('/\D/', '', $v):'');
      $ret = $tit.(($ismobile==1)?' commentary':' commentary');
      break;
    case 5: // viewverscomm
      $ret = $tit.' '.$c.':'.$v.(($ismobile==1)?' (Comm)':' (Commentary)');
      break;
    case 10: // viewbookcomm
      $ret = $tit.(($ismobile==1)?' (Bk Comm)':' (Book Commentary)');
      break;
    case 14: // viewappxinfo
      switch ($t) {
        case 2: // info
          $ret = getbooktitle($t,$b,0);
          break;
        case 3: // appx
          $row = rs('select ifnull(tagline, title) from book where testament = 3 and book = '.$b.' ');
          $tit = $row[0];
          $tit = trim(substr($tit, strpos($tit,':')+((strpos($tit,':')>0)?1:0)));
          //$tit = substr($tit, 0, (($ismobile==1)?12:24)).'..';
          $tit = (($ismobile==1)?'Apx ':'Appx ').$b.': '.$tit;
          $ret = $tit;
          break;
        case 4: // word study
          $ret = (($ismobile==1)?'WS: ':'Word study: ').getbooktitle($t,$b,0);
          break;
      };
      break;
    case 26: // blog
      $row = rs('select blogtitle from revblog where blogid = '.$t.' ');
      $ret = 'Blog: '.$row[0];;
      break;
    case 44: // bib
      $ret = (($c==1)?'Bibliography':'Abbreviations');
      break;
  }
  return $ret;
}



?>

