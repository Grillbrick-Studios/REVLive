<?
header('Content-Type: text/javascript; charset=UTF-8');
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

getpreferences();

$ref = (isset($_REQUEST['ref']))?$_REQUEST['ref']:'18|1|40|1|1|0';  // default to rob@swva
$ar  = explode('|', $ref);

$myrevid = (int) ((isset($ar[0]))?$ar[0]:0);
if($myrevid==0) die('nope');

$task = (isset($_REQUEST['task']))?$_REQUEST['task']:'nope';

$test = (int) ((isset($ar[1]))?$ar[1]:1);
$book = (int) ((isset($ar[2]))?$ar[2]:40);
$chap = (int) ((isset($ar[3]))?$ar[3]:1);
$vers = (int) ((isset($ar[4]))?$ar[4]:1);
$colr = (int) ((isset($ar[5]))?$ar[5]:0);

$sql = 'select testament, book, chapter, verse, highlight, ifnull(marginnote,\'\') marginnote, ifnull(myrevnotes, \'\') myrevnotes
        from myrevdata
        where myrevid = '.$myrevid.'
        and testament = '.$test.'
        and book = '.$book.'
        and chapter = '.$chap.'
        and verse = '.$vers.'';

$sopswhere = 'where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';

switch($task){
//
// MyREV
//
case 'sort':
  $refs = (isset($_REQUEST['refs']))?$_REQUEST['refs']:'0|0|0|0|0';
  $offset = (int) (isset($_REQUEST['offset']))?$_REQUEST['offset']:0;
  $items= explode('~', $refs);
  $ni=$offset;
  foreach($items as $item){
    $itm = explode('|', $item);
    $qry = dbquery('update myrevdata set sqn = '.$ni.'
                   where myrevid = '.(int) $itm[0].'
                   and testament = '.(int) $itm[1].'
                   and book = '.(int) $itm[2].'
                   and chapter = '.(int) $itm[3].'
                   and verse = '.(int) $itm[4].'
                   ');

    $ni++;
  }
  $colr=0;
  $href='';
  $scls='success!';
  $myrevnotes='';
  // for logging
  $ref.=':'.$refs;
  break;
case 'hlit':
  $row = rs($sql);
  if($row){
    if($row['myrevnotes']!='' || $colr>0 || $row['marginnote']!='')
      $qry = dbquery('update myrevdata set highlight = '.$colr.',
          lastupdate = UTC_TIMESTAMP()
          where myrevid = '.$myrevid.'
          and testament = '.$test.'
          and book = '.$book.'
          and chapter = '.$chap.'
          and verse = '.$vers.'');
    else
      $qry = dbquery('delete from myrevdata
          where myrevid = '.$myrevid.'
          and testament = '.$test.'
          and book = '.$book.'
          and chapter = '.$chap.'
          and verse = '.$vers.'');
  }else{
    if($colr>0)
      $qry = dbquery('insert into myrevdata (myrevid, testament, book, chapter, verse, highlight, lastupdate) values ('.
          $myrevid.','.$test.','.$book.','.$chap.','.$vers.','.$colr.', UTC_TIMESTAMP())');
  }
  $myrevnotes='';
  $babbr = getbooktitle($test,$book, 1);
  $bhabbr= str_replace(' ', '', $babbr);
  $href = '/'.$bhabbr.'/'.$chap.'/nav'.$vers;
  $scls = 'hl_'.substr($ref, 0, strrpos($ref, '|'));
  break;
case 'data':
  $row = rs($sql);
  if($row){
    $myrevnotes = str_replace('"', '\"', $row['myrevnotes']);
    $marginnote = str_replace('"', '\"', $row['marginnote']);
    $lcolor     = $row['highlight'];
  }else{
    $myrevnotes = '';
    $marginnote = '';
    $lcolor     = 0;
  }
  $out='{"myrevnotes":"'.$myrevnotes.'", "marginnote":"'.$marginnote.'", "color":"'.$lcolor.'"}';
  print($out);
  mysqli_close($db);
  exit();
  break;

//
// SOPS
//
case 'sopstask1': // extend timeout
case 'sopstask2': // extend timeout + 1 hour
  checklogin();
  $timelockeduntil = ((isset($_REQUEST['timelockeduntil']))?$_REQUEST['timelockeduntil']:(time()+15*60)*1000);  // default to now+15 min
  $newlockeduntil = new DateTime(gmdate("Y-m-d H:i:s", ($timelockeduntil/1000)));
  $strnewlockeduntil = $newlockeduntil->format('Y-m-d H:i:s');
  $qry = dbquery('update verse set lockeduntil = \''.$strnewlockeduntil.'\' '.$sopswhere);
  $out='{"sopstask":"extend","expires":"'.$strnewlockeduntil.'"}';
  print($out);
  mysqli_close($db);
  exit();
  break;
case 'sopstask4': // release
  checklogin();
  $row = rs('select edituserid from verse '.$sopswhere);
  if($userid==$row[0])
    $qry = dbquery('update verse set edituserid = 0, editsession = null, lockeduntil = null '.$sopswhere);
  $out='{"sopstask":"release"}';
  print($out);
  mysqli_close($db);
  exit();
  break;
case 'sopstask5': // request timeout
  $tzoffset = ((isset($_COOKIE['rev_timezone']))?$_COOKIE['rev_timezone']:0);
  $timezone = timezone_name_from_abbr("", $tzoffset*60);
  if($timezone=='') $timezone = 'America/New_York'; // yeouch!
  $row = rs('select ifnull(lockeduntil, UTC_TIMESTAMP) from verse '.$sopswhere);
  $out='{"sopstask":"inquire", "expires":"'.$row[0].'", "displayexpires":"'.convertTZ($row[0], $timezone).'"}';
  print($out);
  mysqli_close($db);
  exit();
  break;

//
// editor notes
//
case 'edata':
  $sql = 'select ifnull(editnote, \'\') editnote, ifnull(editdetails, \'\') editdetails,
          ifnull(ifnull(mr.revusername, mr.myrevname), \'unknown\') author,
          lastupdate, resolved
          from editnotes
          left join myrevusers mr on mr.myrevid = '.$myrevid.'
          where testament = '.$test.'
          and book = '.$book.'
          and chapter = '.$chap.'
          and verse = '.$vers.'';
  $row = rs($sql);
  if($row){
    $editnote = str_replace('"', '\"', $row['editnote']);
    $editdetl = str_replace('"', '\"', $row['editdetails']);
    $author   = $row['author'];
    $lastupdate = $row['lastupdate'];
    $resolved = $row['resolved'];
  }else{
    $editnote = '';
    $editdetl = '';
    $author   = '';
    $lastupdate = '';
    $resolved = 0;
  }
  $out='{"editnote":"'.$editnote.'", "editdetails":"'.$editdetl.'", "author":"'.$author.'", "lastupdate":"'.$lastupdate.'", "resolved":"'.$resolved.'"}';
  print($out);
  mysqli_close($db);
  exit();
  break;
//
// peernotes
//
case 'pdata':
  $sql = 'select ifnull(editnote, \'\') editnote, ifnull(editdetails, \'\') editdetails,
          ifnull(ifnull(mr.revusername, mr.myrevname), \'unknown\') author,
          lastupdate, resolved
          from peernotes
          left join myrevusers mr on mr.myrevid = '.$myrevid.'
          where testament = '.$test.'
          and book = '.$book.'
          and chapter = '.$chap.'
          and verse = '.$vers.'';
  $row = rs($sql);
  if($row){
    $editnote = str_replace('"', '\"', $row['editnote']);
    $editdetl = str_replace('"', '\"', $row['editdetails']);
    $author   = $row['author'];
    $lastupdate = $row['lastupdate'];
    $resolved = $row['resolved'];
  }else{
    $editnote = '';
    $editdetl = '';
    $author   = '';
    $lastupdate = '';
    $resolved = 0;
  }
  checklogin(); // grabs real myrevid
  $row = rs('select ifnull(peerworknotes, \'\') peerwork from myrevusers where myrevid = '.$myrevid.' ');
  if($row) $peerwork = (($row['peerwork']=='')?0:1);
  else $peerwork=0;
  $out='{"peernote":"'.$editnote.'", "peerdetails":"'.$editdetl.'", "author":"'.$author.'", "lastupdate":"'.$lastupdate.'", "resolved":"'.$resolved.'", "peerwork":"'.$peerwork.'"}';
  print($out);
  mysqli_close($db);
  exit();
  break;
case 'relednote':
  $sql = 'update editnotes
          set edituserid = 0,
          editlockeduntil = null ';
  $qry = dbquery($sql.$sopswhere);
  $out='{"sopstask":"ednoterelease"}';
  print($out);
  mysqli_close($db);
  exit();
  break;
default:
  $myrevnotes='I\'m lost';
  $href  = '';
  $scls  = '';
}
logview(400,$test,$book,$chap,$vers,$task.':'.$ref);

$out='{"color":"'.$hilitecolors[$colr].'", "href":"'.$href.'", "spanclass":"'.$scls.'", "myrevnotes":"'.str_replace('"', '\"', $myrevnotes).'"}';
print($out);
mysqli_close($db);

function convertTZ($date, $userTimeZone = 'America/New_York'){
  //$format = 'n/j/Y g:i:s A';
  $format = 'g:i:s A';
  $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime($date ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}
?>

