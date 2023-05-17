<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";
if($userid!=1) die('unauthorized access');

ini_set('memory_limit','768M');     //
ini_set('max_execution_time', 1200); //1200 seconds = 20 minutes

$test = isset($_GET["test"])?$_GET["test"]:-1;
$book = isset($_GET["book"])?$_GET["book"]:-1;
$chap = isset($_GET["chap"])?$_GET["chap"]:-1;
$vers = isset($_GET["vers"])?$_GET["vers"]:-1;
$fpag = isset($_GET["fpag"])?$_GET["fpag"]:0;
switch($fpag){
case 10: // viewbookcomm
    $task = 'book';
    $fil = 'Book_'.str_replace(' ', '_', getbooktitle($test, $book, 0));
    $basesql = 'update book set abbr = [abbr], active = [actv], aliases = [alss], bwabbr = [bwab], comfootnotes = [cfot], commentary = [comm], tagline = [tlin], title = [titl] where testament = '.$test.' and book = [book]; ';
    $sql = 'select abbr, active, aliases, bwabbr, comfootnotes, commentary, tagline, title from book where testament = '.$test.' and book = '.$book.' ';
    $basefootsql = 'insert into footnotes (testament, book, chapter, verse, fntype, fnidx, footnote) values ([test], [book], [chap], [vers], \'[ftyp]\', [fidx], [foot]); ';
    break;
case 24: // outline
    $task = 'outl';
    $fil = 'Outline_'.str_replace(' ', '_', getbooktitle($test, $book, 0));
    $basesql = 'insert into outline (testament, book, chapter, verse, level, link, inoutline, heading, reference, comment) values ([test], [book], [chap], [vers], [levl], [link], [inol], [head], [refr], [cmnt]); ';
    $sql = 'select testament, book, chapter, verse, level, link, inoutline, heading, reference, comment from outline where testament = '.$test.' and book = '.$book.' order by chapter, verse ';
    break;
default:
    $task = 'deft';
    if($book>0)
      $fil = str_replace(' ', '_', getbooktitle($test, $book, 0));
    else{
      if($test==0) $fil = 'Old_Testament';
      if($test==1) $fil = 'New_Testament';
      if($test==2) $fil = 'Information';
      if($test==3) $fil = 'Appendices';
      if($test==4) $fil = 'Word_Studies';
    }
    $fil.= (($test<2)?(($chap>0)?'_chapter_'.$chap:'_full'):'');
    $fil.= (($test<2)?(($vers>0)?'_vers_'.$vers:''):'');
    $basesql = 'update verse set heading = [head], paragraph = [para], style = [styl], footnotes = [foot], comfootnotes = [cfot], versetext = [vers], commentary = [comm] where testament = '.$test.' and book = [book] and chapter = [chap] and verse = [vnum]; ';
    $sql = 'select book, chapter, verse, versetext, heading, paragraph, style, footnotes, comfootnotes, commentary from verse where testament = '.$test.(($book>0)?' and book = '.$book:'').(($chap>0)?' and chapter = '.$chap:'').(($vers>0)?' and verse = '.$vers:'').' order by book, chapter, verse ';
    $basefootsql = 'insert into footnotes (testament, book, chapter, verse, fntype, fnidx, footnote) values ([test], [book], [chap], [vers], \'[ftyp]\', [fidx], [foot]); ';
    break;
}
$fil.= '.sql';
if(file_exists('export/'.$fil)) unlink('export/'.$fil);

file_put_contents('export/'.$fil, '--'.PHP_EOL , FILE_APPEND);
file_put_contents('export/'.$fil, '-- name: '.$fil.PHP_EOL , FILE_APPEND);
file_put_contents('export/'.$fil, '-- from: '.$site.PHP_EOL , FILE_APPEND);
file_put_contents('export/'.$fil, '-- date: '.converttouserdate(gmdate('n/j/Y g:i A'), $timezone).PHP_EOL , FILE_APPEND);
file_put_contents('export/'.$fil, '--'.PHP_EOL , FILE_APPEND);

if($task=='outl'){
  file_put_contents('export/'.$fil, 'delete from outline where testament = '.$test.' and book = '.$book.'; '.PHP_EOL , FILE_APPEND);
}
$vrs = dbquery($sql);
while($row = mysqli_fetch_array($vrs)){

  switch($task){
  case 'book':
      $book = $book;
      $vnum = 0;
      $chap = 0;
      $abbr = processsqltext($row['abbr'], 50, 1, '');
      $actv = processsqlnumb($row['active'], 1, 0, 0);
      $alss = processsqltext($row['aliases'], 500, 1, '');
      $bwab = processsqltext($row['bwabbr'], 7, 1, '');
      //$cfot = processsqltext($row['comfootnotes'], 4000, 1, '');
      $cfot = 'null';
      $comm = processsqlcomm(undoTOC($row['commentary']), 1, '');
      $tlin = processsqltext($row['tagline'], 1000, 1, '');
      $titl = processsqltext($row['title'], 50, 1, '');

      $sql  = str_replace('[book]', $book, $basesql);
      $sql  = str_replace('[abbr]', $abbr, $sql);
      $sql  = str_replace('[actv]', $actv, $sql);
      $sql  = str_replace('[alss]', $alss, $sql);
      $sql  = str_replace('[bwab]', $bwab, $sql);
      $sql  = str_replace('[cfot]', $cfot, $sql);
      $sql  = str_replace('[comm]', $comm, $sql);
      $sql  = str_replace('[tlin]', $tlin, $sql);
      $sql  = str_replace('[titl]', $titl, $sql);
      break;
  case 'outl':
      $test = $test;
      $book = $book;
      $chap = $row['chapter'];
      $vnum = $row['verse'];
      $levl = processsqlnumb($row['level'], 1, 0, 0);
      $link = processsqlnumb($row['link'], 1, 0, 0);
      $inol = processsqlnumb($row['inoutline'], 1, 0, 0);
      $head = processsqltext($row['heading'], 300, 0, 'missing');
      $refr = processsqltext($row['reference'], 20, 0, $row['chapter'].':'.$row['verse']);
      $cmnt = processsqlcomm($row['comment'], 1, '');

      $sql  = str_replace('[test]', $test, $basesql);
      $sql  = str_replace('[book]', $book, $sql);
      $sql  = str_replace('[chap]', $chap, $sql);
      $sql  = str_replace('[vers]', $vnum, $sql);
      $sql  = str_replace('[levl]', $levl, $sql);
      $sql  = str_replace('[link]', $link, $sql);
      $sql  = str_replace('[inol]', $inol, $sql);
      $sql  = str_replace('[head]', $head, $sql);
      $sql  = str_replace('[refr]', $refr, $sql);
      $sql  = str_replace('[cmnt]', $cmnt, $sql);
      break;
  case 'deft':
      $book = $row['book'];
      $chap = $row['chapter'];
      $vnum = $row['verse'];
      $head = processsqltext($row['heading'], 300, 1, '');
      $para = processsqlnumb($row['paragraph'], 1, 0, 0);
      $styl = processsqlnumb($row['style'], 9, 0, 1);
      $foot = processsqltext($row['footnotes'], 2000, 0, '~~~~~~~~');
      //$cfot = processsqltext($row['comfootnotes'], 4000, 1, '');
      $cfot = 'null';
      $vers = processsqlvers($row['versetext'], 2000, 0, 'missing!');
      $comm = processsqlcomm(undoTOC($row['commentary']), 1, '');

      $sql  = str_replace('[book]', $book, $basesql);
      $sql  = str_replace('[chap]', $chap, $sql);
      $sql  = str_replace('[vnum]', $vnum, $sql);
      $sql  = str_replace('[head]', $head, $sql);
      $sql  = str_replace('[para]', $para, $sql);
      $sql  = str_replace('[styl]', $styl, $sql);
      $sql  = str_replace('[foot]', $foot, $sql);
      $sql  = str_replace('[cfot]', $cfot, $sql);
      $sql  = str_replace('[vers]', $vers, $sql);
      $sql  = str_replace('[comm]', $comm, $sql);
      break;
  }
  file_put_contents('export/'.$fil, $sql.PHP_EOL , FILE_APPEND);

  // comm footnotes
  if($task=='book' || $task=='deft'){
    file_put_contents('export/'.$fil, 'delete from footnotes where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vnum.' and fntype = \'com\'; '.PHP_EOL , FILE_APPEND);

    $footsql = 'select testament, book, chapter, verse, fntype, fnidx, footnote from footnotes where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vnum.' and fntype = \'com\' order by testament, book, chapter, verse, fnidx ';
    $fnt = dbquery($footsql);
    $fnstr = '';
    $ni = 0;
    while($row = mysqli_fetch_array($fnt)){
      $test = $test;
      $book = $row['book'];
      $chap = $row['chapter'];
      $vnum = $row['verse'];
      $ftyp = 'com';
      $fidx = processsqlnumb($row['fnidx'], 999, 0, 0);
      $foot = processsqlcomm($row['footnote'], 0, 'missing'); // need to allow html, processsqltext removes it

      $sql  = str_replace('[test]', $test, $basefootsql);
      $sql  = str_replace('[book]', $book, $sql);
      $sql  = str_replace('[chap]', $chap, $sql);
      $sql  = str_replace('[vers]', $vnum, $sql);
      $sql  = str_replace('[ftyp]', $ftyp, $sql);
      $sql  = str_replace('[fidx]', $fidx, $sql);
      $sql  = str_replace('[foot]', $foot, $sql);

      file_put_contents('export/'.$fil, $sql.PHP_EOL , FILE_APPEND);
      $ni++;

      // handle comfootnote search str in verse table
      $fnstr.= $row['footnote'].'~~';
    }
    if($ni>0){
      $fnstr = substr($fnstr, 0, -2);
      $fnsql = 'update verse set comfootnotes = '.processsqlcomm($fnstr, 0, 'missing').' where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vnum.'; ';
      file_put_contents('export/'.$fil, $fnsql.PHP_EOL , FILE_APPEND);
      //file_put_contents('export/'.$fil, PHP_EOL , FILE_APPEND);
    }
  }

}


mysqli_close($db);

header("Content-type: text/csv");
header("Content-description: File Transfer");
header("Content-disposition: attachment; filename=\"".$fil."\"");
header("Pragma: public");
header("Cache-control: max-age=0");
header("Expires: 0");
readfile('export/'.$fil);

if(file_exists('export/'.$fil)) unlink('export/'.$fil);

die();
?>

