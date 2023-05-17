<?
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

$fil = (isset($_REQUEST['fil']))?$_REQUEST['fil']:80;
if(strlen($fil)>4) $fil=80;
$fil = (INT) $fil;
if($fil<46 || $fil>9999) $fil=80;

$estimestamp = getsettingvalue('estimestamp', 'string');
$filmissing = '/expt';
switch($fil){
  // REV exports
  // eSword
  case 46:$filname='REV_Bible'.$estimestamp.'.bbli';break;
  case 47:$filname='REV_Commentary'.$estimestamp.'.cmti';break;
  case 48:$filname='REV_Commentary_with_verses'.$estimestamp.'.cmti';break;
  case 49:$filname='REV_Appx'.$estimestamp.'.refi';break;

  case 50:$filname='REV_Bible.bbl.mybible';break;
  case 51:$filname='REV_Commentary.cmt.mybible';break;
  case 52:$filname='REV_Commentary_with_verses.cmt.mybible';break;
  case 53:$filname='REV_Appx.bok.mybible';break;
  case 54:$filname='REV_Bibleworks.zip';break;
  case 55:$filname='REV.ont.twzip';break;
  case 56:$filname='REV_Commentary.cmt.twm.twzip';break;
  case 57:$filname='REV_Commentary_with_verses.cmt.twm.twzip';break;
  case 58:$filname='REV_Appx.gbk.twm.twzip';break;
  case 60:$filname='REV.ont';break;
  case 61:$filname='REV_Commentary.cmt.twm';break;
  case 62:$filname='REV_Commentary_with_verses.cmt.twm';break;
  case 63:$filname='REV_Appx.gbk.twm';break;
  case 64:$filname='REV_Swordsearcher.zip';break;
  case 66:$filname='REV_Accordance.zip';break;
  case 69:$filname='REV_Logos.zip';break;

  // MS Word
  case 80:$filname='REV_Bible.docx';break;
  case 81:$filname='REV_Commentary.docx';break;
  case 82:$filname='REV_Appendices.docx';break;
  case 83:$filname='REV_Wordstudies.docx';break;
  case 84:$filname='REV_Information.docx';break;
  case 89:$filname='REV_MSWord'.$estimestamp.'.zip';break;

  // wordstudies
  case 85:$filname='REV_WS'.$estimestamp.'.refi';break;  // eSword
  case 86:$filname='REV_WS.bok.mybible';break;
  case 87:$filname='REV_WS.gbk.twm.twzip';break;
  case 88:$filname='REV_WS.gbk.twm.twzip';break;

  // for Jim Hessin's offline REV
  case 200:$filname='JSON_REV_timestamp.json';break;
  case 201:$filname='JSON_REV_bible.json';break;
  case 202:$filname='JSON_REV_commentary.json';break;
  case 203:$filname='JSON_REV_appendices.json';break;

  //default: die('unknown file');
}

checklogin();
if($fil < 200){
  logview($fil,0,0,0,0,'');
  mysqli_close($db);
  if(file_exists($docroot.'/export/expdown/'.$filname))
    header('Location: /export/expdown/'.$filname);
  else  
    die('We\'re sorry, but the file you tried to download is missing. Click <a href="'.$filmissing.'">here</a> to go back.');
}else if($fil < 205){
  logview($fil,0,0,0,0,$filname);
  mysqli_close($db);
  if(file_exists($docroot.'/export/expdown/'.$filname)){
    header("Access-Control-Allow-Origin: *");
    header("content-type: application/json");
    echo file_get_contents($docroot.'/export/expdown/'.$filname);
  }else
    die('We\'re sorry, but the file you tried to download is missing. Click <a href="'.$filmissing.'">here</a> to go back.');
}else{
  // library items
  $filmissing='/resources';
  $row = rs('select externalurl from resource where resourcetype = 7 and resourceid = '.$fil.' ');
  if($row){
    $filname = substr($row[0], strrpos($row[0], '/')+1);
    //die($filname);
    logview($fil,0,0,-1,-1,$filname);
    mysqli_close($db);
    if(file_exists($docroot.'/export/library/'.$filname))
      header('Location: /export/library/'.$filname);
    else
      die('We\'re sorry, but the file you tried to download is missing. Click <a href="'.$filmissing.'">here</a> to go back.');
  
  }else{
    die('We\'re sorry, but the file you tried to download is missing. Click <a href="'.$filmissing.'">here</a> to go back.');
  }
}



?>

