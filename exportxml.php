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
<form name="frm" method="post" action="/exportxml.php/?autorun=1<?=$getstr?>">
  When you click "Go", all the site content will be exported in XML format.  It takes 10 to 30 seconds.<p>
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
  if ($zip->open($archive, ZipArchive::CREATE) !== TRUE) {
      die ("Could not open archive");
  }

  $fullname = '';
  $fil = 'REV_xmldump'.$unique;

  $dir  = 'export/'.substr($workdir, 0, -1);
  $name = $fil;

  if($manualrun) show("<b>exporting..</b>");

  $dbresult = export_database( $dir, $name, $dbserv, $dbuser, $dbpass, $dbname);

  if($dbresult != 'success'){
    die("<span style=\"color:red\">problem exporting database</span><br />");
  }

  if($manualrun){
    show( "<b>Export created successfully.</b><br />" );
    print('Click <a href="/'.$fullname.'">here</a> to download the file.<br /><br />');
    print('After the download is complete, you may close this page.<br /><br />');
    print('The files will be removed from the server after about 3 minutes');
  }else{
    header('Location: /'.$fullname);
  }
  logview(39,0,0,0,0);
} // end of checking for POST


//
//
//
function show($str){
   echo $str . "<br/>\n";
   flush();
   //ob_flush();
   //sleep(1);   // I wish I could get the live site to flush these....
}

function export_database( $directory, $outname , $dbhost, $dbusr, $dbpss ,$dbnam ) {
  global $fullname, $manualrun;
  $mysqli = new mysqli($dbhost, $dbusr, $dbpss, $dbnam);
  $mysqli->set_charset('utf8');

  $dir = $directory;
  $res = true;

  $name = $outname;
  $fullname = $dir.'/'.$name.'.gz';
  if(file_exists($fullname)) {unlink($fullname);}

  $tables[] = 'book';
  $tables[] = 'verse';

  $ntable = 1;
  foreach($tables as $table){
    if($manualrun) show("exporting table `$table`");
    $sqlbase[]  = $table.'.xml';
    $sqlname    = $dir.'/'.$table.'.xml';
    $sqlnames[] = $sqlname;
    if(file_exists($sqlname)) {unlink($sqlname);}
    if($table=='book')
      $sql = 'SELECT testament,book,title,comfootnotes,commentary FROM book where active = 1 order by testament, book';
    else
      $sql = 'SELECT v.testament,v.book,v.chapter,v.verse,
             (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) heading,
              v.heading superscript,v.paragraph,v.style,v.versetext,v.footnotes,v.comfootnotes,v.commentary
              FROM verse v
              join book b on (b.testament = v.testament and v.book = b.book and b.active = 1)
              order by v.testament, v.book, v.chapter, v.verse';
    $return = '<DATA>'.crlf.crlf;

    $nlines = 0;
    $result = dbquery($sql);
    while ($row = mysqli_fetch_array($result)) {
        if($nlines==0){
          $fields = array();
          while($field = $result->fetch_field()){
            $fields[] = $field->name;
          }
        }
        $return .= '<ROW>'.crlf;
        foreach($fields as $field){
          if($field == 'heading'){
            if($row[$field] > 0){
              $ltest = $row['testament'];
              $lbook = $row['book'];
              $lchap = $row['chapter'];
              $lvers = $row['verse'];
              $sql = 'select heading, level, reference from outline where testament = '.$ltest.' and book = '.$lbook.' and chapter = '.$lchap.' and verse = '.$lvers.' and link=1 order by level ';
              $heds = dbquery($sql);
              $hdcnt=0;$head='';
              while($rrow = mysqli_fetch_array($heds)){
                if($hdcnt>0) $head.= '[br]';
                $head.= $rrow[0];
                if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
                $hdcnt++;
              }
              $return .= '<'.$field.'>'.$head.'</'.$field.'>'.crlf;
            }else{
              $return .= '<'.$field.'>NULL</'.$field.'>'.crlf;
            }
          }else{
            $return .= '<'.$field.'>'.((isset($row[$field]))?$row[$field]:'NULL').'</'.$field.'>'.crlf;
          }
        }
        $return .= '</ROW>'.crlf.crlf;

        $nlines++;
        if(($nlines % 700) == 0){
          if(!file_put_contents($sqlname, $return, FILE_APPEND)) die("error writing to file ".$sqlname);
          $return = '';
        }
    }
    $return .= '</DATA>'.crlf;

    if(!file_put_contents($sqlname, $return, FILE_APPEND)) die("error writing to file ".$sqlname);
    $ntable++;
  }

  $sqlzip = new ZipArchive();
  if ($sqlzip->open($fullname, ZipArchive::CREATE) !== TRUE) {
    $result = 'failure';
  }else{
    $result = 'success';
    for($i=0;$i<($ntable-1);$i++){
      $sqlzip->addFile($sqlnames[$i], $sqlbase[$i]) or die ("ERROR: Could not add file: $sqlbase[$i]");
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
  $ret = str_replace("“", "&ldquo;", $ret);
  $ret = str_replace("”", "&rdquo;", $ret);
  $ret = str_replace("‘", "&lsquo;", $ret);
  $ret = str_replace("’", "&rsquo;", $ret);
  return $ret;
}

?>
