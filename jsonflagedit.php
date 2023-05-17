<?
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

checklogin();

if(empty($userid) || $userid==0) exit('invalid user');
$editid = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['id']))?$_REQUEST['id']:-1));
$doocmt = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['doocmt']))?$_REQUEST['doocmt']:-1));
$donone = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['donone']))?$_REQUEST['donone']:0));
$edtcmt = ((isset($_REQUEST['cmt']))?$_REQUEST['cmt']:'');
$edtcmt = processsqltext($edtcmt, 400, 1, '');
$flagged=0;
if($editid > 0){
  if($donone==1){
    $row = rs('select 1 from editlogsviewed where userid = '.$userid.' and logid = '.$editid.' ');
    if(!$row){
      $row = rs('select userid from editlogs where logid = '.$editid.' ');
      $expuid = $row[0];
      $task = dbquery('insert into editlogsviewed (userid, loguserid, logid, flagged) values ('.$userid.', '.$expuid.', '.$editid.', 0)');
    }
  }else{
    $row = rs('select userid from editlogs where logid = '.$editid.' ');
    $expuid = $row[0];
    $row = rs('select flagged, ifnull(flagcomment, \'\') flagcomment from editlogsviewed where userid = '.$userid.' and logid = '.$editid.' ');
    if($row){
      $orig=$row[0];
      $havcmt = (($row[1]!='')?1:0);
      $flagged = 1-$row[0];
      if($doocmt==1){
        if($edtcmt!='null') $flagged=1;
        else $flagged = $orig;
        $sqlc = ', flagcomment = '.$edtcmt.' ';
      }else{
        $sqlc = '';
        if($havcmt==1) $flagged=1;
      }
      $task = dbquery('update editlogsviewed set flagged = '.$flagged.$sqlc.' where userid = '.$userid.' and logid = '.$editid.' ');
    }else{
      if($doocmt==1){
        $flagged = (($edtcmt!='null')?1:0);
        $sqla = ', flagcomment';
        $sqlb = ', '.edtcmt;
      }else {$sqla='';$sqlb='';$flagged=1;}
      $task = dbquery('insert into editlogsviewed (userid, loguserid, logid, flagged) values ('.$userid.', '.$expuid.', '.$editid.', '.$flagged.')');
    }
  }
}
print('{"result":"success", "flagged":"'.$flagged.'", "donone":"'.$donone.'"}');
mysqli_close($db);

//
//
//
function processsqltext($txt, $siz, $allownull, $default){
  $ret = trim($txt);
  if($ret){
    //$ret = preg_replace('#"+#', '', $ret);          // remove double quotes
    $ret = strip_tags($ret);                        // remove all html tags
    if(strlen($ret)==0){                            // everything has been removed
      if($allownull) return 'null';
      else $ret = $default;
    }
    $ret = substr($ret, 0, $siz);                   // check length
    $ret = preg_replace('#\'#', '\\\'', $ret);      // escape single quotes
    return '\''.$ret.'\'';
  }else{
    if($allownull) return 'null';
    else return '\''.$default.'\'';
  }
}

?>

