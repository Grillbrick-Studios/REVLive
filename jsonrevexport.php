<?php

$getstr = '';
if(isset($_GET['permission'])){
  if($_GET['permission']!='yUp')
    die('unauthorized access');
  else
    $getstr = '&permission=yUp';
}else{
  die('unauthorized access');
}
$autorun=0;
if(isset($_GET['autorun'])){
  if($_GET['autorun']==1)
    $autorun=1;
}
$what='';
if(isset($_REQUEST['what'])){
  $what = strtolower($_REQUEST['what']);
  if($what!='bible' && $what!='commentary' && $what!='appendices'){
    $what = 'bible';
    $autorun=0;
  }
}else{
  $what = 'bible';
  $autorun=0;
}


header('Content-Type:text/html; charset=utf-8');
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

error_reporting(E_ALL);

// increase script timeout value
ini_set('max_execution_time', 5000);

if(!isset($_POST['go']) && $autorun==0){
?>
<form name="frm" method="post" action="/jsonrevexport.php/?autorun=1<?=$getstr?>">
  When you click "Go", the REV
  <select name="what">
    <option value="Bible"<?=fixsel($what, 'bible')?>>Bible</option>
    <option value="Commentary"<?=fixsel($what, 'commentary')?>>Commentary</option>
    <option value="Appendices"<?=fixsel($what, 'appendices')?>>Appendices</option>
  </select>
  will be exported in JSON format.
  <p>It takes a few seconds to generate the Bible or Commentary, about 1 second for the Appendices.</p>
  <p><input type="submit" id="btnx" name="btnx" value="Go" onclick="setTimeout('document.getElementById(\'btnx\').disabled=true',300);this.value='please wait.'">
  <input type="hidden" name="go" value="1"></p>
  <p>To automatically generate and download the file, use<br /><span style="color:blue">https://<?=$site?>/jsonrevexport.php?permission=yUp&autorun=1&what=</span><span style="color:red">[bible || commentary || appendices].</span></p>
  <p>If you don't want to do the export, close this page.</p>

</form>
<?
}else{

  $manualrun = isset($_POST['go']);

  $workdir = createworkdir();
  $unique = getuserfiletimestamp($timezone, 2);

  $fullname = '';
  //$fil = 'REV_JSON_'.$what.'_'.$unique;
  $fil = 'REV_JSON_'.$what; //.'_'.$unique;

  $dir  = 'export/'.substr($workdir, 0, -1);
  $name = $fil;

  $dbresult = json_export($dir, $what, $name);

  if($dbresult != 'success'){
    die("<span style=\"color:red\">problem exporting database</span><br />");
  }

  if($manualrun){
    print( "<b>Export created successfully.</b><br />" );
    print('Click <a href="/'.$fullname.'">here</a> to download the file.<br /><br />');
    print('After the download is complete, you may close this page.<br /><br />');
    print('The file will be removed from the server after about 3 minutes');
  }else{
    //header('Location: /'.$fullname);
    header("Access-Control-Allow-Origin: *");
    header("content-type: application/json");
    echo file_get_contents($fullname);
  }
  logview(320,0,0,0,0);
} // end of checking for POST


function json_export($dir, $what, $name){
  global $docroot, $fullname, $manualrun;
  $fullname = $dir.'/'.$name.'.json';
  $workfile = $docroot.'/'.$fullname;
  $ni=0;
  switch(strtolower($what)){
  case 'bible':
      file_put_contents($workfile, '{"REV_Bible":[', FILE_APPEND);
      $lastbook=0;
      $bookname='';
      $qry = dbquery('select v.testament, v.book, v.chapter, v.verse,
                      (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
                      v.heading superscript, v.paragraph, v.style, v.footnotes, v.versetext
                      from verse v
                      where v.testament in (0,1)
                      order by 1, 2, 3, 4 ');
      while($row = mysqli_fetch_array($qry)){
        $test = $row['testament'];
        $book = $row['book'];
        $chap = $row['chapter'];
        $vers = $row['verse'];
        $vtxt = $row['versetext'];
        if($book != $lastbook){
          $bookname = getbooktitle($test, $book, 0);
          $lastbook = $book;
        }
        $tmp = (($ni>0)?',':'').'{';
        $tmp.= '"book":"'.$bookname.'", ';
        $tmp.= '"chapter":'.$chap.', ';
        $tmp.= '"verse":'.$vers.', ';
        if($row['headcount'] > 0){
          $sql = 'select heading, level, reference from outline where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' and link=1 order by level ';
          $heds = dbquery($sql);
          $hdcnt=0;$head='';
          while($rrow = mysqli_fetch_array($heds)){
            if($hdcnt>0) $head.= '[br]';
            $head.= $rrow[0];
            if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
            $hdcnt++;
          }
          $tmp.= '"heading":"'.$head.'", ';
        }else
          $tmp.= '"heading":"", ';
        $tmp.= '"superscript":"'.$row['superscript'].'", ';
        $tmp.= '"paragraph":'.$row['paragraph'].', ';
        $tmp.= '"style":'.$row['style'].', ';
        $tmp.= '"footnotes":"'.$row['footnotes'].'", ';
        $tmp.= '"versetext":"'.prepareverse($vtxt).'"';
        $tmp.= '}';
        file_put_contents($workfile, $tmp, FILE_APPEND);
        $ni++;
      }
      break;
  case 'commentary':
      file_put_contents($workfile, '{"REV_Commentary":[', FILE_APPEND);
      $lastbook=0;
      $bookname='';
      $qry = dbquery('select testament, book, chapter, verse, commentary from verse where testament in (0,1) and commentary is not null order by 1, 2, 3, 4 ');
      while($row = mysqli_fetch_array($qry)){
        $test = $row['testament'];
        $book = $row['book'];
        $chap = $row['chapter'];
        $vers = $row['verse'];
        $vtxt = $row['commentary'];
        if($book != $lastbook){
          $bookname = getbooktitle($test, $book, 0);
          $lastbook = $book;
        }
        //{"book":"Genesis", "chapter":"1", "verse":"1", "versetext":"In the beginning God created the heavens and the earth."},
        $tmp = (($ni>0)?',':'').'{';
        $tmp.= '"book":"'.$bookname.'", ';
        $tmp.= '"chapter":"'.$chap.'", ';
        $tmp.= '"verse":"'.$vers.'", ';
        $tmp.= '"commentary":"'.preparecomm($vtxt).'"';
        $tmp.= '}';
        file_put_contents($workfile, $tmp, FILE_APPEND);
        $ni++;
      }
      break;
  case 'appendices':
      file_put_contents($workfile, '{"REV_Appendices":[', FILE_APPEND);
      $qry = dbquery('select testament, book, chapter, verse, commentary from verse where testament = 3 order by 1, 2, 3, 4 ');
      while($row = mysqli_fetch_array($qry)){
        $test = $row['testament'];
        $book = $row['book'];
        $chap = $row['chapter'];
        $vers = $row['verse'];
        $vtxt = $row['commentary'];
        $bookname = getbooktitle($test, $book, 0);
        $vtxt = stripTOC($vtxt);
        $vtxt = str_replace('"', '\"', $vtxt);
        //{"book":"Genesis", "chapter":"1", "verse":"1", "versetext":"In the beginning God created the heavens and the earth."},
        $tmp = (($ni>0)?',':'').'{';
        $tmp.= '"title":"'.$bookname.'", ';
        $tmp.= '"appendix":"'.$vtxt.'"';
        $tmp.= '}';
        file_put_contents($workfile, $tmp, FILE_APPEND);
        $ni++;
      }
      break;
  }
  file_put_contents($workfile, ']}', FILE_APPEND);

  return 'success';
}

function prepareverse($vrs){
    /*
    $vrs = str_replace('[pg]', ' ', $vrs);
    $vrs = str_replace('[hp]', ' ', $vrs);
    $vrs = str_replace('[hpbegin]', ' ', $vrs);
    $vrs = str_replace('[hpend]', ' ', $vrs);
    $vrs = str_replace('[lb]', ' ', $vrs);
    $vrs = str_replace('[listbegin]', ' ', $vrs);
    $vrs = str_replace('[listend]', ' ', $vrs);
    $vrs = str_replace('[bq]', ' ', $vrs);
    $vrs = str_replace('[/bq]', ' ', $vrs);
    $vrs = str_replace('[br]', ' ', $vrs);
    */
    $vrs = str_replace('"', '\"', $vrs);
    return $vrs;
}
function preparecomm($com){
    $com = preg_replace('#<a nam(.*?)</a>#', '', $com); // remove whatsnew markers
    $com = preg_replace('#<a id=(.*?)</a>#', '', $com);
    $com = preg_replace('#<span dir=(.*?)>(.*?)</span>#', '$2', $com); // remove hebrew RTL span tags
    $com = str_replace('"', '\"', $com);
    return $com;
}
function stripTOC($com){
  if(strpos($com, 'a id="toc_') > 0){
    $com= preg_replace('#<a id="toc_(\d+)_(\d+)_(\d+)_(\d+)_(\d+)(.*?)>#', '[toc$5]', $com);
    $com= preg_replace('#\[toc(\d+)\](.*?)</a>#', '$2', $com);
    $com= preg_replace('#<a id="tocdest_(\d+)_(\d+)_(\d+)_(\d+)_(\d+)(.*?)>#', '[tocdest$5]', $com);
    $com= preg_replace('#\[tocdest(\d+)\](.*?)</a>#', '$2', $com);
  }
  return $com;
}


?>
