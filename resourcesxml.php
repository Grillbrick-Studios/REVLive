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


header('Content-Type:text/html; charset=utf-8');
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

error_reporting(E_ALL);

// increase script timeout value
ini_set('max_execution_time', 5000);

if(!isset($_POST['go']) && $autorun==0){
?>
<form name="frm" method="post" action="/resourcesxml.php/?autorun=1<?=$getstr?>">
  When you click "Go", the resources table will be exported in XML format.  It takes a couple of seconds.<p>
  <input type="submit" name="btnx" value="Go" onclick="setTimeout('this.disabled=true',300);this.value='please wait.'"><p>
  <input type="hidden" name="go" value="1">
  If you don't want to do the export, close this page.

</form>
<?
}else{

  $manualrun = isset($_POST['go']);

  $workdir = createworkdir();
  $unique = getuserfiletimestamp($timezone, 2);
  $archive = 'export/'.$workdir.'REV_XML'.$unique.'.zip';
  $zip = new ZipArchive();
  if ($zip->open($archive, ZipArchive::CREATE) !== TRUE) {die ("Could not open archive");}

  $fullname = '';
  $fil = 'Resources_xmldump'.$unique;

  $dir  = 'export/'.substr($workdir, 0, -1);
  $name = $fil;

  if($manualrun) print("<b>exporting..</b><br />");

  $dbresult = export_topicsresources( $dir, $name, $dbserv, $dbuser, $dbpass, $dbname);

  if($dbresult != 'success'){
    die("<span style=\"color:red\">problem exporting database</span><br />");
  }

  if($manualrun){
    print( "<b>Export created successfully.</b><br />" );
    print('Click <a href="/'.$fullname.'">here</a> to download the file.<br /><br />');
    print('After the download is complete, you may close this page.<br /><br />');
    print('The file will be removed from the server after about 3 minutes');
  }else{
    header('Location: /'.$fullname);
  }
  logview(39,0,0,0,0);
} // end of checking for POST



function export_topicsresources( $directory, $outname , $dbhost, $dbusr, $dbpss ,$dbnam ) {
  global $fullname, $manualrun;
  $mysqli = new mysqli($dbhost, $dbusr, $dbpss, $dbnam);
  $mysqli->set_charset('utf8');

  $dir = $directory;
  $name = $outname;
  $fullname = $dir.'/'.$name.'.gz';
  if(file_exists($fullname)) {unlink($fullname);}

  if($manualrun) print("exporting topics and resources...<br />");

  $topic    = 0;
  $topicassoc= 1;
  $resource = 2;

  $artopicids = array();
  $arresourceids = array();
  $arcommentaryids = array();
  $topicassocfields = array('topicid','resourceid','sqn');
  $topicfields = array('topicid','topic');
  $resourcefields = array('title','description','publishedon','source','duration','identifier','externalurl','thumbnail');

  $table[]  = 'topic.xml';
  $table[]  = 'topic_assoc.xml';
  $table[]  = 'resource.xml';
  for($ni=0;$ni<3;$ni++){
    $tblnams[]  = $dir.'/'.$table[$ni];
    if(file_exists($tblnams[$ni])) {unlink($tblnams[$ni]);}
    $tbldat[$ni] = '<DATA>'.crlf;
  }

  $sql = 'select ta.topicid, ta.resourceid, ta.sqn,
              t.topic,
              r.title, r.description, r.publishedon, r.source, r.duration, r.identifier, r.externalurl, r.thumbnail, r.resourcetype,
              v.testament, v.book, v.chapter, v.verse, v.commentary
            from topic_assoc ta
              join topic t on (t.topicid = ta.topicid)
              left join resource r on (r.resourceid = ta.resourceid)
              left join verse v on (v.testament = ta.testament and v.book = ta.book and v.chapter = ta.chapter and v.verse = ta.verse)
            where ta.resourceid > -1
            order by topic, sqn';

  $result = dbquery($sql);

  $nlines = 0;
  $comresid = 80000;
  while ($row = mysqli_fetch_array($result)) {
    // topic
    if(!in_array($row['topicid'], $artopicids)){
      $tbldat[$topic] .= '<ROW>'.crlf;
      $tbldat[$topic] .= '<topicid>'.$row['topicid'].'</topicid>'.crlf;
      $tbldat[$topic] .= '<topic>'.$row['topic'].'</topic>'.crlf;
      $tbldat[$topic] .= '</ROW>'.crlf;
      $artopicids[] = $row['topicid'];
    }

    // topic_assoc
    $tbldat[$topicassoc] .= '<ROW>'.crlf;
    if($row['resourceid']==0){
      $commentaryid = $row['testament'].'~'.$row['book'].'~'.$row['chapter'].'~'.$row['verse'];
      $theresid = array_search($commentaryid, $arcommentaryids);
      if($theresid!==false)
        $resourceid = $theresid;
      else{
        $resourceid = $comresid++;
        $arcommentaryids[$resourceid] = $commentaryid;
      }
    }else
      $resourceid = $row['resourceid'];
    $tbldat[$topicassoc] .= '<topicid>'.$row['topicid'].'</topicid>'.crlf;
    $tbldat[$topicassoc] .= '<resourceid>'.$resourceid.'</resourceid>'.crlf;
    $tbldat[$topicassoc] .= '<sqn>'.$row['sqn'].'</sqn>'.crlf;
    $tbldat[$topicassoc] .= '</ROW>'.crlf;

    // resource
    if(!in_array($resourceid, $arresourceids)){
      $tbldat[$resource].= '<ROW>'.crlf;
      $tbldat[$resource] .= '<resourceid>'.$resourceid.'</resourceid>'.crlf;
      if($resourceid >= 80000)
          $tbldat[$resource] .= '<resourcetype>commentary</resourcetype>'.crlf;
      else{
        switch($row['resourcetype']){
        case 1: // video
          $tbldat[$resource] .= '<resourcetype>video</resourcetype>'.crlf;break;
        case 3: // audio
        case 4: // audio
          $tbldat[$resource] .= '<resourcetype>audio</resourcetype>'.crlf;break;
        case 5: // article
          $tbldat[$resource] .= '<resourcetype>article</resourcetype>'.crlf;break;
        case 7: // library
          $tbldat[$resource] .= '<resourcetype>library</resourcetype>'.crlf;break;
        default: // unknown
          $tbldat[$resource] .= '<resourcetype>unknown</resourcetype>'.crlf;break;
        }
      }
      $tbldat[$resource] .= '<resimport>import</resimport>'.crlf;

      if($resourceid < 80000){
        // a real resource
        foreach($resourcefields as $field){
          if($field=='thumbnail' || $field=='externalurl'){
            if(isset($row[$field])){
              if(substr($row[$field],0,4) != 'http'){
                $fieldvalue = 'https://www.revisedenglishversion.com'.$row[$field];
              }else{
                $fieldvalue = $row[$field];
              }
              $fieldvalue = fixentities($fieldvalue);
            }else{
              if($row['resourcetype']==3){ // audio
                //$fieldvalue = ((strtolower($row['source'])=='podbean')?'https://www.revisedenglishversion.com/i/stf_audio.png':'https://www.revisedenglishversion.com/i/wow_logo.png');
                $fieldvalue = ((!isset($row['thumbnail']))?'https://www.revisedenglishversion.com/i/sandt_audio.png':'https://www.revisedenglishversion.com'.$row['thumbnail']);
              }else if($row['resourcetype']==4){ // seminar
                $fieldvalue = 'https://www.revisedenglishversion.com/i/stf_audio.png';
              }else if($row['resourcetype']==5){ // article
                $fieldvalue = 'https://www.revisedenglishversion.com/i/thumbnails/resourcearticle.jpg';
              }else if($row['resourcetype']==7){ // library
                $fieldvalue = 'https://www.revisedenglishversion.com/i/thumbnails/resourcelibrary.jpg';
              }else{
                $fieldvalue = 'NULL';
              }
            }
          }else{
            $fieldvalue = ((isset($row[$field]))?$row[$field]:'NULL');
            if($field == 'description'){
              $fieldvalue = '<![CDATA['.$fieldvalue.']]>';
            }else{
              $fieldvalue = str_replace('&rel=0', '&amp;rel=0', $fieldvalue);
              $fieldvalue = fixentities($fieldvalue);
            }
          }
          $tbldat[$resource] .= '<'.$field.'>'.$fieldvalue.'</'.$field.'>'.crlf;
        }
      }else{
        // a commentary entry/appendix
        $comurl = 'https://www.revisedenglishversion.com'.getsummary($row, 1);
        $tbldat[$resource] .= '<title>'.getsummary($row, 0).'</title>'.crlf;
        $description = $row['commentary'];
        if($description==null || $description=='') $description = 'No Commentary ... yet. ';
        $description = str_replace('[fn]', '', $description);
        $description = preg_replace('#<a id="toc(.*?)">(.*?)</a>#', '$2', $description); // remove toc markers
        $description = processcommfordisplay($description, 1);

        if (strlen($description) >= 500) {
           $readmore = '<a href="'.$comurl.'" target="_blank">Read more..</a>';
          $description = truncateHtml($description, 500,'...', false, true, $readmore);
        }
        $tbldat[$resource] .= '<description><![CDATA['.$description.']]></description>'.crlf;
        $tbldat[$resource] .= '<publishedon>NULL</publishedon>'.crlf;
        $tbldat[$resource] .= '<source>REV</source>'.crlf;
        $tbldat[$resource] .= '<duration>NULL</duration>'.crlf;
        $tbldat[$resource] .= '<identifier>NULL</identifier>'.crlf;
        $tbldat[$resource] .= '<externalurl>'.$comurl.'</externalurl>'.crlf;
        $tbldat[$resource] .= '<thumbnail>https://www.revisedenglishversion.com/i/biblecommentary.jpg</thumbnail>'.crlf;
      }
      $tbldat[$resource].= '</ROW>'.crlf;
      $arresourceids[] = $resourceid;
    }

    $nlines++;
    if(($nlines % 300) == 0){
      for($ni=0;$ni<3;$ni++){
        if(!file_put_contents($tblnams[$ni], $tbldat[$ni], FILE_APPEND)) die("error writing to file ".$tblnams[$ni]);
        $tbldat[$ni] = '';
      }
    }

  }
  for($ni=0;$ni<3;$ni++){
    $tbldat[$ni].= '</DATA>'.crlf;
    if(!file_put_contents($tblnams[$ni], $tbldat[$ni], FILE_APPEND)) die("error writing to file ".$tblnams[$ni]);
  }

  //print_r($arcommentaryids);

  $sqlzip = new ZipArchive();
  if ($sqlzip->open($fullname, ZipArchive::CREATE) !== TRUE) {
    $result = 'failure';
  }else{
    $result = 'success';
    for($ni=0;$ni<3;$ni++){
      $sqlzip->addFile($tblnams[$ni], $table[$ni]) or die ("ERROR: Could not add file: $tblnams[$ni]");
    }
    $sqlzip->close();
  }

  if( $mysqli && !$mysqli->error ) {
    $mysqli->close();
  }
  return $result;
}

function fixentities($dat){
  $ret = $dat;
  //$ret = str_replace("“", "&ldquo;", $ret);
  //$ret = str_replace("”", "&rdquo;", $ret);
  //$ret = str_replace("‘", "&lsquo;", $ret);
  //$ret = str_replace("’", "&rsquo;", $ret);
  $ret = str_replace("“", "\"", $ret);
  $ret = str_replace("”", "\"", $ret);
  $ret = str_replace("‘", "'", $ret);
  $ret = str_replace("’", "'", $ret);
  $ret = str_replace("&", "&amp;", $ret);
  return $ret;
}

function getsummary($r, $url=0){
  $title = getbooktitle($r['testament'], $r['book'], 0);
  if($r['testament'] < 2){
    if($r['chapter']==0 && $r['verse']==0){ // book commentary
      $htitle = $title;
      $href='/book/'.str_replace(' ','',$title);
      $pref = 'REV Commentary on the book of ';
    }else{
      $htitle = $title.' '.$r['chapter'].':'.$r['verse'];
      $href='/'.str_replace(' ','',$title).'/'.$r['chapter'].'/'.$r['verse'];
      $pref = 'REV Commentary on ';
    }
  }else{
    $htitle = $title;
    switch($r['testament']){
    case 2:
      $href='/intro/'.$r['book'];
      $pref = 'REV Introduction: ';
      break;
    case 3:
      $href='/appendix/'.$r['book'];
      $pref = 'REV Appendix: ';
      break;
    case 4:
      $href='/wordstudy/'.str_replace(' ', '_', $title);
      $pref = 'REV Word Study: ';
      break;
    }
  }
  //$href.='/1'; // adding $gedit for "Close Window" button.
  if($url==1)
    //$ret = '<a href="'.$href.'" target="_blank">'.$pref.$htitle.'</a>';
    $ret = $href;
  else
    $ret = $pref.$htitle;
  return $ret;

}

?>
