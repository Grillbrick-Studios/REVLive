<?
header('Content-Type: text/javascript; charset=UTF-8');
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

$ref = (isset($_REQUEST['ref']))?$_REQUEST['ref']:'40,1,1,1,1';
$ar  = explode(',', $ref);

$book = (int) ((isset($ar[0]))?$ar[0]:40);
if($book==0) $book=40;
$test= ($book<40)?0:1;

$chap = (int) ((isset($ar[1]))?$ar[1]:1);
if($chap==0) $chap=1;

$vers = (int) ((isset($ar[2]))?$ar[2]:-1);
$chp2 = (int) ((isset($ar[3]))?$ar[3]:-1);
$vrs2 = (int) ((isset($ar[4]))?$ar[4]:-1);

//$book= $ar[0];
//$chap= $ar[1];
//$vers= $ar[2];
//$chp2= $ar[3];
//$vrs2= $ar[4];
$dbg = 'b='.$book.': c='.$chap.': v='.$vers.': c2='.$chp2.': v2='.$vrs2;

if($chp2 < $chap) $chp2 = $chap;
if($vers < 1 && $vrs2 < 1){ $vers = 1; $vrs2 = 199;}
if($vers < 1) $vers = 1;
if($vrs2 < 1) $vrs2 = $vers;
$oldc= $chap;
$vcnt = 0;

$dbg.= '<br />b='.$book.': c='.$chap.': v='.$vers.': c2='.$chp2.': v2='.$vrs2;

$cbk  = (isset($_REQUEST['callback']))?$_REQUEST['callback']:'callback100000';

$sql = 'select chapter, verse, versetext from verse
        where testament = '.$test.'
        and book = '.$book.' ';
if($chap!=$chp2){
  $sql.= 'and ((chapter >= '.$chap.' and (case when chapter = '.$chap.' then verse >= '.$vers.' else verse > 0 end))
          and (chapter <= '.$chp2.' and (case when chapter = '.$chp2.' then verse <= '.$vrs2.' else verse < 199 end))) ';
}else{
  $sql.= 'and chapter = '.$chap.'
          and verse between '.$vers.' and '.$vrs2.' ';
}
$sql.='order by chapter, verse ';

$vrss = dbquery($sql);

$out=$cbk.'({"content":"';
while(($row = mysqli_fetch_array($vrss)) && $vcnt < 999){
  if($row[0] != $oldc){
    $out.='<span style=\"display:block;margin-top:7px;\"></span><strong>'.(($book==19)?'Psalm':'Chapter').' '.$row[0].'</strong><br />';
    $oldc = $row[0];
  }
  $vtxt = $row[2];
  $vtxt = str_replace('"',    '\\"',$vtxt); // escape double quotes for json
  $vtxt = str_replace('[fn]', '',   $vtxt); // remove footnote indicators
  $vtxt = str_replace('[mvh]',' ',  $vtxt); // remove mid-verse header indicators
  $vtxt = str_replace('[mvs]',' ',  $vtxt); // remove mid-verse superscript indicators
  $vtxt = str_replace('[pg]', ' ',  $vtxt); // remove paragraph indicators
  $vtxt = str_replace('[bq]', ' ',  $vtxt); // remove blockquote indicators
  $vtxt = str_replace('[/bq]',' ',  $vtxt); // remove end blockquote indicators
  if(left($vtxt, 4) == '[br]')                  // remove beginning [br] indicators
    $vtxt = substr($vtxt, 4);
  $vtxt = str_replace('[br]', ' ',  $vtxt);      // remove remaining <br /> indicators
  $vtxt = str_replace('[hpbegin]',' ',  $vtxt);  // remove hebrew poetry indicators
  $vtxt = str_replace('[hpend]',' ',  $vtxt);    // remove hebrew poetry indicators
  $vtxt = str_replace('[hp]',' ',  $vtxt); // remove hebrew poetry indicators
  $vtxt = str_replace('[listbegin]',' ',  $vtxt);  // remove list indicators
  $vtxt = str_replace('[listend]',' ',  $vtxt);    // remove list indicators
  $vtxt = str_replace('[lb]',' ',  $vtxt); // remove list indicators
  $needclosingtag = ((strpos($vtxt, '[[') !== false && strpos($vtxt, ']]') === false)?1:0);
  $vtxt = str_replace('[[',   '<span class=\"rNotInText\">[[', $vtxt);
  $vtxt = str_replace(']]',']]</span>', $vtxt);
  if($needclosingtag) $vtxt.='</span>';
  // no longer doing this, requires prefs..
  // not that big of a dead
  if(1==2 && $test==1 && $ucaseot==1 && strpos($vtxt, '<strong>')!==false){
    $vtxt = str_replace('&ldquo;', '[ldq]', $vtxt);
    $vtxt = str_replace('&rdquo;', '[rdq]', $vtxt);
    $vtxt = str_replace('&lsquo;', '[lsq]', $vtxt);
    $vtxt = str_replace('&rsquo;', '[rsq]', $vtxt);
    $vtxt = str_replace('&mdash;', '[mdh]', $vtxt);
    $vtxt = preg_replace_callback('#(.*?)<strong>(.*?)</strong>(.*?)#', function($m){return $m[1].'<strong>'.strtoupper($m[2]).'</strong>'.$m[3];}, $vtxt);
    $vtxt = str_ireplace('[ldq]', '&ldquo;', $vtxt);
    $vtxt = str_ireplace('[rdq]', '&rdquo;', $vtxt);
    $vtxt = str_ireplace('[lsq]', '&lsquo;', $vtxt);
    $vtxt = str_ireplace('[rsq]', '&rsquo;', $vtxt);
    $vtxt = str_ireplace('[mdh]', '&mdash;', $vtxt);
  }
  if(left($vtxt,1)=='~'){
    $vtxt = str_replace('~','<span class=\"rNotInText\">', $vtxt);
    $vtxt = str_replace('<em>','<em class=\"rNotInText\">', $vtxt);
    $vtxt.= '</span>';
  }

  $out.='<span style=\"display:block;margin-top:7px;\"></span><sup class=\"versenum\">'.$row[1].'</sup>'.$vtxt.'<br/>';
  $vcnt++;
}
$out.='"});';
print($out);

mysqli_close($db);
?>

