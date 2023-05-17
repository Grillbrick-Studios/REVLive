<?php
if(empty($userid) || empty($superman) || $userid==0 || $superman==0) die('unauthorized access');

//if($userid==1)
error_reporting(E_ALL);
setlocale(LC_ALL, 'en_US.UTF8');
//ini_set('memory_limit','768M');     //
ini_set('memory_limit','256M');     //
//ini_set('max_execution_time', 480); //480 seconds = 8 minutes
set_time_limit(480); // 8 minutes
$docroot = $_SERVER['DOCUMENT_ROOT'];
//die(phpinfo());
require_once $docroot."/includes/exporttagging.php";
$msg='';
$processed = 0;
$whchprgm = ((isset($_POST['whchprgm']))?$_POST['whchprgm']:1); // which program: 1=eSword, 2=MySword
$whchfile = ((isset($_POST['whchfile']))?$_POST['whchfile']:1); // which file: 1=Bible, 2=Commentary, 3=appx
$incvers = ((isset($_POST['incvers']))?$_POST['incvers']:0);    // include verse at top of commentary
$inctags = ((isset($_POST['inctags']))?$_POST['inctags']:0);    // Process tagging
$zipped = ((isset($_POST['zipped']))?$_POST['zipped']:0);       // should the file be zipped
$datestamp = getexporttimestamp();

if(isset($_POST['oper']) && $_POST['oper'] == 'dodel'){
  for($ni=0;$ni<$_POST['delcount'];$ni++){
    if(isset($_POST['dodel'.$ni]) && $_POST['dodel'.$ni]==1)
      unlink($docroot.'/export/expdown/'.$_POST['delfile'.$ni]);
  }
}

if(isset($_POST['oper']) && $_POST['oper'] == 'go'){
  //die(phpinfo());
  $workdir = createworkdir();

  // determine the filename
  switch($whchfile){
    case 1: // bible
      $filnam = 'REV_Bible.'; break;
    case 2: // commentary
      $filnam = 'REV_Commentary'.(($incvers==1)?'_with_verses':'').'.'; break;
    case 3: // Appx, WS, Info
      $filnam = 'REV_Appx.';$dtitle='REV Appendices'.$datestamp;$dabbr='REV_Appx'; break;
    case 4: // WS
      $filnam = 'REV_WS.';$dtitle='REV Word Studies'.$datestamp;$dabbr='REV_WS'; break;
    case 5: // Info
      $filnam = 'REV_Info.';$dtitle='REV Information'.$datestamp;$dabbr='REV_Info'; break;
  };

  // determine the extension
  switch($whchprgm){
  case 1: // eSword
    $estimestamp = getexporttimestamp(3);
    $filnam = substr($filnam, 0, -1).$estimestamp.'.';
    switch($whchfile){
      case 1: // bible
        $filext = 'bbli'; break;
      case 2: // commentary
        $filext = 'cmti'; break;
      case 3: // Appx
      case 4: // WS
      case 5: // Info
        $filext = 'refi'; break;
    };
    getsettingvalue('estimestamp', 'string');
    savesettingvalue('estimestamp', 'string', '\''.$estimestamp.'\'');
    break;
  case 2: // MySword
    switch($whchfile){
      case 1: // bible
        $filext = 'bbl.mybible'; break;
      case 2: // commentary
        $filext = 'cmt.mybible'; break;
      default: // Appx, WS, Info            // TODO this might need tweaking
        $filext = 'bok.mybible'; break;
      };
    break;
  case 3: // BibleWorks
    $filnam = 'REV_Bibleworks.';
    $filext = 'zip';
    break;
  case 4: // theWord
    switch($whchfile){
      case 1: // bible
        $filnam = 'REV.';
        $filext = 'ont'; break;
      case 2: // commentary
        $filext = 'cmt.twm'; break;
      default: // Appx, WS, Info            // TODO this might need tweaking
        $filext = 'gbk.twm'; break;
      };
    break;
  case 5: // SqLite
    $filnam = 'rev_sqlite.';
    $filext = 'zip';
    break;
  case 6: // Swordsearcher
    $filnam = 'REV_Swordsearcher.';
    $filext = 'zip';
    break;
  case 7: // Accordance
    $filnam = 'REV_Accordance.';
    $filext = 'zip';
    break;
  case 8: // JSON
    $filnam = 'REV_JSON.';
    $filext = 'json';
    break;
  case 9: // Logos
    $filnam = 'REV_Logos.';
    $filext = 'zip';
    break;
  }
  $filname = $docroot.'/export/'.$workdir.$filnam.$filext;

  // if we're tagging the file, get arrays of appendix names and ws names
  if(($inctags==1 && ($whchprgm==1 || $whchprgm==2 || $whchprgm==4)) || $whchprgm==6){
    $sql = 'select ifnull(tagline,title) title
            from book
            where testament = 3
            and active = 1
            order by sqn ';
    $tmp = dbquery($sql);
    $arappx = array();
    $ni=1;
    while($row = mysqli_fetch_array($tmp)){
      $arappx[$ni] = $row['title'];
      $ni++;
    }

    $sql = 'select book, ifnull(tagline,title) title
            from book
            where testament = 4
            and active = 1
            order by sqn ';
    $tmp = dbquery($sql);
    $arws = array();
    $ni=1;
    while($row = mysqli_fetch_array($tmp)){
      $arws[$ni] = array($row['book'], replacediacritics($row['title']));
      $ni++;
    }
    //print_r($arws);
    //die();
  }

  // load booknames array
  $booknames = array();
  $booknames = loadbooknames($booknames);
  $bookaliases = array();
  $bookaliases = loadaliases($bookaliases);

  if($whchprgm==8){ // JSON
    //$workdir = createworkdir();
    $dir  = 'export/'.substr($workdir, 0, -1);
    $fil = json_export($dir, 'timestamp', 'JSON_REV_timestamp');
    rename($docroot.$fil, $docroot.'/export/expdown/JSON_REV_timestamp.json');
    $fil = json_export($dir, 'bible', 'JSON_REV_bible');
    rename($docroot.$fil, $docroot.'/export/expdown/JSON_REV_bible.json');
    $fil = json_export($dir, 'commentary', 'JSON_REV_commentary');
    rename($docroot.$fil, $docroot.'/export/expdown/JSON_REV_commentary.json');
    $fil = json_export($dir, 'appendices', 'JSON_REV_appendices');
    rename($docroot.$fil, $docroot.'/export/expdown/JSON_REV_appendices.json');
    $msg = '<b>DONE Exporting JSON files</b>';
    $zipped=0;

  }else if($whchprgm==3){ // Bibleworks
    $inccomm = 1;
    $filwork = '/export/'.$workdir.'REV.txt';
    file_put_contents($docroot.$filwork, '');

    $ni = 0;
    $sql = 'select b.bwabbr, v.chapter, v.verse, v.versetext, v.commentary
           from verse v
             inner join book b on (b.book = v.book and b.testament = v.testament)
           where v.testament in (0,1)
           order by v.testament, v.book, v.chapter, v.verse ';
    $export = dbquery($sql);
    $toolong = '... commentary truncated due to a limitation of Bibleworks.';
    while($row = mysqli_fetch_array($export)){
      $tmp = $row['bwabbr'].' '.$row['chapter'].':'.$row['verse'].' ';
      $tmpvs = processBWverseforexport($row['versetext']);
      $tmp = $tmp.$tmpvs;
      $comn = $row['commentary'];
      if($inccomm == 1 && strlen($comn??'') > 0){
        $tmp .= ' {'.processBWcommentaryforexport($comn, $row['chapter'], $row['verse']);
        if(strlen($tmp) > 32766){
          $tmp = left($tmp, (32767-strlen($toolong))).$toolong;
        }
        $tmp .= '}';
      }
      file_put_contents($docroot.$filwork, $tmp.crlf, FILE_APPEND);
      $ni++;
    }
    $zip = new ZipArchive();
    $zipfile = $docroot."/export/".$workdir.$filnam."zip";

    if ($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {
        exit("cannot open <$zipfile>\n");
    }

    $zip->addFile('.'.$filwork, 'REV.txt');
    $zip->addFile('./includes/resource/rev.ddf', 'rev.ddf');
    $zip->addFile('./includes/resource/instructions.txt', 'instructions.txt');
    $zip->close();
    unlink($docroot.$filwork);
    rename($docroot.'/export/'.$workdir.$filnam.$filext, $docroot.'/export/expdown/'.$filnam.$filext);
    $msg = '<b>DONE:</b> '.$ni.' items exported to '.$filnam.$filext.'.<br /><a href="/export/expdown/'.$filnam.$filext.'">Click here</a> to download.';
    $zipped=0;
  }else if($whchprgm==7){ // Accordance
    $inccomm = 1;
    $filwork = '/export/'.$workdir.'REV.txt';
    file_put_contents($docroot.$filwork, '');

    $ni = 0;
    // rsw need to do accordance!!
    $sql = 'select b.abbr, b.bwabbr, v.book, v.chapter, v.verse, v.paragraph, ifnull(v.heading, \'\') heading, v.versetext, v.commentary
           from verse v
             inner join book b on (b.book = v.book and b.testament = v.testament)
           where v.testament in (0,1)
           order by v.testament, v.book, v.chapter, v.verse ';
    $export = dbquery($sql);
    $curdir = 'xxx';
    $rtfprefix = '{\rtf1\ansi\ansicpg1252\deff0\nouicompat\deflang1033{\fonttbl{\f0\fswiss\fprq2\fcharset0 Arial;}{\f1\fnil\fcharset0 Arial;}}
{\colortbl ;\red0\green0\blue0;}
{\*\generator Riched20 10.0.19041}\viewkind4\uc1
\pard\cf1\f0\fs20 ';
    $paramark = '¶';
    while($row = mysqli_fetch_array($export)){
      $abbr = str_replace(' ', '', strtolower($row['abbr']));
      switch ($abbr) {
      case '1kng': $abbr='1kings';break;
      case '2kng': $abbr='2kings';break;
      case 'phlm':  $abbr='philemon';break;
      default: break;
      }
      $hed = $row['heading'];
      if($row['book']==19 && $row['verse']==1 && $hed!='' && ((substr($hed, 0, 4)!='BOOK' && substr($hed, 0, 4)!='ALEP') || strlen($hed??'')>16)){
        if(strpos($hed, 'BOOK')===0)
          $hed = substr($hed, strpos($hed,'[br]')+4);
        $hed = trim(str_replace('[br]', ' ', $hed)); // no crlf in accordance
        $tmp = $abbr.' '.$row['chapter'].':0 ';
        $tmp = $tmp.processACCverseforexport($hed);
        $tmp = iconv("UTF-8", "macintosh", $tmp);
        file_put_contents($docroot.$filwork, $tmp.crlf, FILE_APPEND);
      }
      $tmp = $abbr.' '.$row['chapter'].':'.$row['verse'].' ';
      if($row['paragraph']==1) $tmp.= $paramark.' ';
      $tmp = $tmp.processACCverseforexport($row['versetext']);

      // add Rev 12:18
      //if($row['book']==66 && $row['chapter']==12 && $row['verse']==17){
      //  $tmp.=crlf.$abbr.' '.$row['chapter'].':18 ';
      //}


      $tmp = iconv("UTF-8", "macintosh", $tmp);
      file_put_contents($docroot.$filwork, $tmp.crlf, FILE_APPEND);

      $comn = $row['commentary'];
      if($inccomm == 1 && strlen($comn??'') > 0){
        if($curdir != $row['bwabbr']){
          $curdir = $row['bwabbr'];
          $oldmask = umask(0);
          mkdir($docroot.'/export/'.$workdir.$curdir, 0777);
          umask($oldmask);
          $notesdir = $docroot.'/export/'.$workdir.$curdir.'/';
        }
        $comn = processACCcommentaryforexport($comn, $row['chapter'], $row['verse']);
        //  write notes file
        $notefile = $row['chapter'].'_'.$row['verse'].'.bww';
        file_put_contents($notesdir.$notefile, $rtfprefix.$comn.'\f1\par'.crlf.'}');
      }
      $ni++;
    }
    $path = $docroot.'/export/'.$workdir;
    $zip = new ZipArchive();
    $zipfile = $docroot."/export/".$workdir.$filnam."zip";
    if ($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {exit("cannot open <$zipfile>\n");}

    $zip->addFile('.'.$filwork, 'REV.txt');
    if($inccomm==1){
      $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($path),
          RecursiveIteratorIterator::LEAVES_ONLY
      );
      foreach($files as $name=>$file){
        // Skip directories (they would be added automatically)
        if(!$file->isDir() && $file->getFilename() != 'REV.txt'){
          // Get real and relative path for current file
          $filePath = $file->getRealPath();
          $relativePath = 'notes/'.str_replace('\\', '/', substr($filePath, strlen($path)));
          $zip->addFile($filePath, $relativePath);
          }
      }
    }
    $zip->close();

    rename($docroot.'/export/'.$workdir.$filnam.$filext, $docroot.'/export/expdown/'.$filnam.$filext);
    deltreee($docroot.'/export/'.$workdir, 1);
    $msg = '<b>DONE:</b> '.$ni.' items exported to '.$filnam.$filext.'.<br /><a href="/export/expdown/'.$filnam.$filext.'">Click here</a> to download.';
    $zipped=0;

  }else if($whchprgm==6){ // Swordsearcher
    $inccomm = 1;
    $filwork = '/export/'.$workdir.'REV.txt';
    $hdr = '; TITLE: Revised English Version'.crlf.'; ABBREVIATION: REV'.crlf.'; HAS ITALICS'.crlf.'; HAS FOOTNOTES'.crlf;
    file_put_contents($docroot.$filwork, $hdr);
    $filcom = '/export/'.$workdir.'REVCom.txt';
    $hdr = "\xEF\xBB\xBF".'; TITLE: REV Commentary'.crlf.'; ABBREVIATION: REVCom'.crlf;
    file_put_contents($docroot.$filcom, $hdr);

    $ni = 0;
    $sql = 'select b.bwabbr, v.testament, v.book, v.chapter, v.verse, v.versetext, v.commentary, v.footnotes, v.comfootnotes
           from verse v
             inner join book b on (b.book = v.book and b.testament = v.testament)
           where v.testament in (0,1)
           order by v.testament, v.book, v.chapter, v.verse ';
    $export = dbquery($sql);
    while($row = mysqli_fetch_array($export)){
      $tmp = '$$ '.$row['bwabbr'].' '.$row['chapter'].':'.$row['verse'].crlf;
      $tmp = $tmp.processSSverseforexport($row['versetext'], $row['footnotes']);
      file_put_contents($docroot.$filwork, $tmp.crlf, FILE_APPEND);

      $comn = $row['commentary'];
      if($inccomm == 1 && strlen($comn??'') > 0){
        $com = '$$ '.$row['bwabbr'].' '.$row['chapter'].':'.$row['verse'].crlf;
        $com.= processSScommentaryforexport($comn, $row['chapter'], $row['verse']);
        $comfootnotes = getfootnotes($row['testament'], $row['book'], $row['chapter'], $row['verse'], 'com');
        $com = processcommentaryfootnotes($com, $comfootnotes??'', 6);
        $com = parsenoparse($com);
        //$com = parsescripturerefs($com, 6){  TODO
        $com = parseSScommentaryrefs($com);
        $com = parsestrongs($com, $whchprgm);
        $com = parseappx($com, $whchprgm, $arappx);
        //$com = parsewords($com, $whchprgm);
        file_put_contents($docroot.$filcom, $com.crlf, FILE_APPEND);
      }
      $ni++;
    }

    // SS appendices
    $filapx = '/export/'.$workdir.'REVAppx.txt';
    $hdr = "\xEF\xBB\xBF".'; TITLE: REV Appendices'.crlf.'; ABBREVIATION: REVAppx'.crlf;
    file_put_contents($docroot.$filapx, $hdr);
    $sql = 'select ifnull(b.tagline,b.title) title, v.testament, v.book, v.chapter, v.verse, v.commentary, v.comfootnotes
            from book b
            inner join verse v on v.testament = b.testament and v.book = b.book
            where b.testament = 3
            and (active = 1)
            order by sqn ';
    $appx = dbquery($sql);
    $na=0;
    while($row = mysqli_fetch_array($appx)){
      $apx = '$$ '.processtitleforSSexport($row['title']).crlf;
      $apx.= processSScommentaryforexport($row['commentary'], 1, 1);
      $comfootnotes = getfootnotes($row['testament'], $row['book'], $row['chapter'], $row['verse'], 'com');
      $apx = processcommentaryfootnotes($apx, $comfootnotes??'', 6);
      $apx = parsenoparse($apx);
      $apx = parsestrongs($apx, $whchprgm);
      $apx = parseappx($apx, $whchprgm, $arappx);
      file_put_contents($docroot.$filapx, $apx.crlf, FILE_APPEND);
      $na++;
    }

    // SS word studies
    $filws = '/export/'.$workdir.'REV_ws.txt';
    $hdr = "\xEF\xBB\xBF".'; TITLE: REV Word Studies'.crlf.'; ABBREVIATION: REV_WS'.crlf;
    file_put_contents($docroot.$filws, $hdr);
    $sql = 'select ifnull(b.tagline,b.title) title, v.testament, v.book, v.chapter, v.verse, v.commentary, v.comfootnotes
            from book b
            inner join verse v on v.testament = b.testament and v.book = b.book
            where b.testament = 4
            and (active = 1)
            order by 1 ';
    $wrds = dbquery($sql);
    $na=0;
    while($row = mysqli_fetch_array($wrds)){
      $wrd = '$$ '.processtitleforSSexport($row['title']).crlf;
      $wrd.= processSScommentaryforexport($row['commentary'], 1, 1);
      $comfootnotes = getfootnotes($row['testament'], $row['book'], $row['chapter'], $row['verse'], 'com');
      $wrd = processcommentaryfootnotes($wrd, $comfootnotes??'', 6);
      $wrd = parsenoparse($wrd);
      $wrd = parsestrongs($wrd, $whchprgm);
      //$wrd = parseappx($wrd, $whchprgm, $arappx);
      file_put_contents($docroot.$filws, $wrd.crlf, FILE_APPEND);
      $na++;
    }

    $zip = new ZipArchive();
    $zipfile = $docroot."/export/".$workdir.$filnam."zip";

    if($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {exit("cannot open <$zipfile>\n");}

    $zip->addFile('.'.$filwork, 'REV.txt');
    $zip->addFile('.'.$filcom, 'REVCom.txt');
    $zip->addFile('.'.$filapx, 'REVAppx.txt');
    if($revws) $zip->addFile('.'.$filws, 'REVWS.txt');
    $zip->close();
    unlink($docroot.$filwork);
    rename($docroot.'/export/'.$workdir.$filnam.$filext, $docroot.'/export/expdown/'.$filnam.$filext);
    $msg = '<b>DONE:</b> '.$ni.' items exported to '.$filnam.$filext.'.<br /><a href="/export/expdown/'.$filnam.$filext.'">Click here</a> to download.';
    $zipped=0;
  }else if($whchprgm==9){ // Logos
    $inccomm = 1;
    $filwork = '/export/'.$workdir.'REV.txt';
    $filcom = '/export/'.$workdir.'REVCom.txt';

    $ni = 0;
    $sql = 'select b.title, b.bwabbr, v.testament, v.book, v.chapter, v.verse, v.versetext, v.commentary, v.footnotes, v.comfootnotes
           from verse v
             inner join book b on (b.book = v.book and b.testament = v.testament)
           where v.testament in (0,1)
           order by v.testament, v.book, v.chapter, v.verse ';
    $export = dbquery($sql);
    $lastbook = 0;
    $lastchap = 0;
    $babbr = '';
    while($row = mysqli_fetch_array($export)){
      $hdr = '';
      if($row['book'] != $lastbook){
        $book = $row['book'];
        $babbr = $row['bwabbr'];
        if($book==22) $babbr = 'Son';
        if($book==50) $babbr = 'Php';
        $hdr.= '<h1>'.$row['title'].'</h1>'.crlf;
        //$tmp.= $row['title'].crlf;
        $lastbook = $row['book'];
        $lastchap = 0;
      }
      if($row['chapter'] != $lastchap){
        $hdr.= '<h2>Chapter '.$row['chapter'].'</h2>'.crlf;
        //$tmp.= 'Chapter '.$row['chapter'].crlf;
        $lastchap = $row['chapter'];
      }
      $tmpabbr = $babbr.' '.$row['chapter'].':'.$row['verse'];

      //[[@Bible:Gen 1:13]][[1:13 >> Gen 1:13]] {{field-on:bible}}

      $tmp = $hdr.'[[@Bible:'.$tmpabbr.']][['.$row['chapter'].':'.$row['verse'].' >> '.$tmpabbr.']] {{field-on:bible}} ';
      $tmp.= processLogosverseforexport($row['versetext'], $row['footnotes']);
      $tmp.= '{{field-off:bible}}';
      file_put_contents($docroot.$filwork, $tmp.crlf, FILE_APPEND);

      $comn = $row['commentary'];
      if($inccomm == 1 && strlen($comn??'') > 0){
        $tmp = $hdr.'[[@Bible:'.$tmpabbr.']][['.$row['chapter'].':'.$row['verse'].' >> '.$tmpabbr.']] ';
        $comfootnotes = getfootnotes($row['testament'], $row['book'], $row['chapter'], $row['verse'], 'com');
        $tmp.= processLogoscommforexport($comn, $comfootnotes);
        file_put_contents($docroot.$filcom, $tmp.crlf, FILE_APPEND);
      } //else $tmp.= 'no commentary..';

      $ni++;
    }

    $zip = new ZipArchive();
    $zipfile = $docroot."/export/".$workdir.$filnam."zip";

    if($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {exit("cannot open <$zipfile>\n");}

    $zip->addFile('.'.$filwork, 'REV.txt');
    $zip->addFile('.'.$filcom, 'REVcom.txt');
    $zip->close();
    unlink($docroot.$filwork);
    unlink($docroot.$filcom);
    rename($docroot.'/export/'.$workdir.$filnam.$filext, $docroot.'/export/expdown/'.$filnam.$filext);
    $msg = '<b>DONE:</b> '.$ni.' items exported to '.$filnam.$filext.'.<br /><a href="/export/expdown/'.$filnam.$filext.'">Click here</a> to download.';
    $zipped=0;

  }else if($whchprgm==5){ // SqLite
    $workdir = createworkdir();
    $filenam = '/export/'.$workdir.'rev_sqlite.db';
    $sdbto = new SQLite3($docroot.$filenam);
    $sdbto->exec('pragma synchronous = off;');
    $sdbto->exec('pragma journal_mode=MEMORY;');
    //
    // handle table "book"
    //
    $sdbto->query('DROP TABLE IF EXISTS "Biblebook";');
    $sql = 'CREATE TABLE "Biblebook" (
            book smallint(6) NOT NULL,
            testament smallint(6) NOT NULL,
            title varchar(50) DEFAULT NULL,
            abbr varchar(50) DEFAULT NULL,
            chapters smallint(6) NOT NULL,
            tagline varchar(1000) DEFAULT NULL,
            sqn smallint(6) NOT NULL,
            active tinyint(4) NOT NULL,
            bwabbr varchar(7) DEFAULT NULL,
            aliases varchar(500) DEFAULT NULL,
            comfootnotes varchar(4000) DEFAULT NULL,
            commentary mediumtext,
            PRIMARY KEY (book,testament));';
    $sdbto->query($sql);

    // http://php.net/manual/en/sqlite3stmt.bindparam.php
    // https://code.tutsplus.com/tutorials/why-you-should-be-using-phps-pdo-for-database-access--net-12059
    $stmt = $sdbto->prepare("insert into Biblebook
                            (book, testament, title, abbr, chapters, tagline, sqn, active, bwabbr, aliases, comfootnotes, commentary)
                            values
                            (:book, :testament, :title, :abbr, :chapters, :tagline, :sqn, :active, :bwabbr, :aliases, :comfootnotes, :commentary) ");
    $stmt->bindParam(':book', $book, SQLITE3_INTEGER);
    $stmt->bindParam(':testament', $testament, SQLITE3_INTEGER);
    $stmt->bindParam(':title', $title, SQLITE3_TEXT);
    $stmt->bindParam(':abbr', $abbr, SQLITE3_TEXT);
    $stmt->bindParam(':chapters', $chapters, SQLITE3_INTEGER);
    $stmt->bindParam(':tagline', $tagline, SQLITE3_TEXT);
    $stmt->bindParam(':sqn', $sqn, SQLITE3_INTEGER);
    $stmt->bindParam(':active', $active, SQLITE3_INTEGER);
    $stmt->bindParam(':bwabbr', $bwabbr, SQLITE3_TEXT);
    $stmt->bindParam(':aliases', $aliases, SQLITE3_TEXT);
    $stmt->bindParam(':comfootnotes', $comfootnotes, SQLITE3_TEXT);
    $stmt->bindParam(':commentary', $commentary, SQLITE3_TEXT);

    $sqn = 1;

    $data = dbquery('SELECT book, testament, title, abbr, chapters, tagline, sqn, active, bwabbr, aliases, comfootnotes, commentary from book where testament in (0,1,2,3,4) and active=1; ');
    while($row = mysqli_fetch_array($data)){

      $book         = $row['book'];
      $testament    = $row['testament'];
      $title        = $row['title'];
      $abbr         = $row['abbr'];
      $chapters     = $row['chapters'];
      $tagline      = $row['tagline'];
      $sqn          = $row['sqn'];
      $active       = $row['active'];
      $bwabbr       = $row['bwabbr'];
      $aliases      = $row['aliases'];
      $comfootnotes = $row['comfootnotes'];
      $commentary   = $row['commentary'];

      $stmt->execute();
      $sqn++;
    }
    $sdbto->query('vacuum;');
    $msg = 'Exported '.$sqn. ' books.<br />';


    //
    // handle table "verse"
    //
    $sdbto->query('DROP TABLE IF EXISTS "Bibleverse";');
    $sql = 'CREATE TABLE "Bibleverse" (
            testament smallint(6) NOT NULL,
            book smallint(6) NOT NULL,
            chapter smallint(6) NOT NULL,
            verse smallint(6) NOT NULL,
            heading varchar(300) DEFAULT NULL,
            superscript varchar(300) DEFAULT NULL,
            paragraph tinyint(4) NOT NULL DEFAULT 0,
            style tinyint(1) NOT NULL DEFAULT 1,
            versetext varchar(2000) NOT NULL,
            footnotes varchar(2000) NOT NULL,
            comfootnotes varchar(4000) DEFAULT NULL,
            commentary mediumtext,
            PRIMARY KEY (testament,book,chapter,verse));';
    $sdbto->query($sql);

    $stmt = $sdbto->prepare("insert into Bibleverse
                            (testament, book, chapter, verse, heading, superscript, paragraph, style, versetext, footnotes, comfootnotes, commentary)
                            values
                            (:testament, :book, :chapter, :verse, :heading, :superscript, :paragraph, :style, :versetext, :footnotes, :comfootnotes, :commentary) ");
    $stmt->bindParam(':testament', $testament, SQLITE3_INTEGER);
    $stmt->bindParam(':book', $book, SQLITE3_INTEGER);
    $stmt->bindParam(':chapter', $chapter, SQLITE3_INTEGER);
    $stmt->bindParam(':verse', $verse, SQLITE3_INTEGER);
    $stmt->bindParam(':heading', $heading, SQLITE3_TEXT);
    $stmt->bindParam(':superscript', $superscript, SQLITE3_TEXT);
    $stmt->bindParam(':paragraph', $paragraph, SQLITE3_INTEGER);
    $stmt->bindParam(':style', $style, SQLITE3_INTEGER);
    $stmt->bindParam(':versetext', $versetext, SQLITE3_TEXT);
    $stmt->bindParam(':footnotes', $footnotes, SQLITE3_TEXT);
    $stmt->bindParam(':comfootnotes', $comfootnotes, SQLITE3_TEXT);
    $stmt->bindParam(':commentary', $commentary, SQLITE3_TEXT);

    $sqn=1;
    $data = dbquery('SELECT v.testament, v.book, v.chapter, v.verse,
                     (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
                     v.heading superscript, v.paragraph, v.style, v.versetext, v.footnotes, v.comfootnotes, v.commentary
                     from verse v
                     where v.testament in (0,1,2,3,4); ');
    while($row = mysqli_fetch_array($data)){
      $testament    = $row['testament'];
      $book         = $row['book'];
      $chapter      = $row['chapter'];
      $verse        = $row['verse'];
      $heading      = '';
      if($row['headcount'] > 0){
        $sql = 'select heading, level, reference from outline where testament = '.$testament.' and book = '.$book.' and chapter = '.$chapter.' and verse = '.$verse.' and link=1 order by level ';
        $heds = dbquery($sql);
        $hdcnt=0;$head='';
        while($rrow = mysqli_fetch_array($heds)){
          if($hdcnt>0) $head.= '[br]';
          $head.= $rrow[0];
          if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
          $hdcnt++;
        }
        $heading    = $head;
      }
      $superscript  = $row['superscript'];
      $paragraph    = $row['paragraph'];
      $style        = $row['style'];
      $versetext    = $row['versetext'];
      $footnotes    = $row['footnotes'];
      $comfootnotes = $row['comfootnotes'];
      $commentary   = $row['commentary'];

      $stmt->execute();
      $sqn++;
    }
    $sdbto->query('vacuum;');
    $msg.= 'Exported '.$sqn. ' verses.<br />';


    //
    // handle table "revblog"
    //
    $sdbto->query('DROP TABLE IF EXISTS "REVBlog";');
    $sql = 'CREATE TABLE REVBlog (
      blogid int(11) NOT NULL,
      blogdate datetime NOT NULL,
      blogtitle varchar(200) NOT NULL,
      active tinyint(4) NOT NULL,
      blogtext mediumtext NOT NULL,
      PRIMARY KEY (blogid));';
    $sdbto->query($sql);

    $stmt = $sdbto->prepare("insert into REVBlog
                            (blogid, blogdate, blogtitle, active, blogtext)
                            values
                            (:blogid, :blogdate, :blogtitle, :active, :blogtext)");

    $stmt->bindParam(':blogid', $blogid, SQLITE3_INTEGER);
    $stmt->bindParam(':blogdate', $blogdate, SQLITE3_TEXT);
    $stmt->bindParam(':blogtitle', $blogtitle, SQLITE3_TEXT);
    $stmt->bindParam(':active', $active, SQLITE3_INTEGER);
    $stmt->bindParam(':blogtext', $blogtext, SQLITE3_TEXT);

    $sqn=1;
    $data = dbquery('SELECT blogid, blogdate, blogtitle, active, blogtext from revblog where active=1; ');
    while($row = mysqli_fetch_array($data)){
      $blogid    = $row['blogid'];
      $blogdate  = $row['blogdate'];
      $blogtitle = $row['blogtitle'];
      $active    = 1; // $row['blogid'];
      $blogtext  = $row['blogtext'];
      $stmt->execute();
      $sqn++;
    }
    $sdbto->query('vacuum;');
    $msg.= 'Exported '.$sqn. ' blogs.<br />';


    //
    // handle table "editlogs"
    //
    $sdbto->query('DROP TABLE IF EXISTS "EditLogs";');
    $sql = 'CREATE TABLE EditLogs (
            logid int(11) NOT NULL,
            page tinyint(4) NOT NULL,
            testament tinyint(4) NOT NULL,
            book tinyint(4) NOT NULL,
            chapter smallint(4) NOT NULL,
            verse smallint(4) NOT NULL,
            editdate datetime NOT NULL,
            comment varchar(200) DEFAULT NULL,
            whatsnew tinyint(1) NOT NULL,
            PRIMARY KEY (logid));';
    $sdbto->query($sql);
    $sdbto->query('CREATE INDEX idx_edit on EditLogs(whatsnew, editdate);');

    $stmt = $sdbto->prepare("insert into EditLogs
                            (logid, page, testament, book, chapter, verse, editdate, comment, whatsnew)
                            values
                            (:logid, :page, :testament, :book, :chapter, :verse, :editdate, :comment, :whatsnew)");

    $stmt->bindParam(':logid', $logid, SQLITE3_INTEGER);
    $stmt->bindParam(':page', $page, SQLITE3_INTEGER);
    $stmt->bindParam(':testament', $testament, SQLITE3_INTEGER);
    $stmt->bindParam(':book', $book, SQLITE3_INTEGER);
    $stmt->bindParam(':chapter', $chapter, SQLITE3_INTEGER);
    $stmt->bindParam(':verse', $verse, SQLITE3_INTEGER);
    $stmt->bindParam(':editdate', $editdate, SQLITE3_TEXT);
    $stmt->bindParam(':comment', $comment, SQLITE3_TEXT);
    $stmt->bindParam(':whatsnew', $whatsnew, SQLITE3_INTEGER);

    $sqn=1;
    $data = dbquery('SELECT logid, page, testament, book, chapter, verse, editdate, comment, whatsnew from editlogs where whatsnew=1; ');
    while($row = mysqli_fetch_array($data)){
      $logid     = $row['logid'];
      $page      = $row['page'];
      $testament = $row['testament'];
      $book      = $row['book'];
      $chapter   = $row['chapter'];
      $verse     = $row['verse'];
      $editdate  = $row['editdate'];
      $comment   = $row['comment'];
      $whatsnew  = $row['whatsnew'];
      $stmt->execute();
      $sqn++;
    }
    $sdbto->query('vacuum;');
    $msg.= 'Exported '.$sqn. ' whatsnew editlogs.<br />';

    $sdbto->close();

    //
    // zip it
    //
    $zip = new ZipArchive();
    $zipfile = $docroot.'/export/'.$workdir.'rev_sqlite.zip';
    if ($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {exit("cannot open <$zipfile>\n");}

    $zip->addFile('.'.$filenam, 'rev_sqlite.db');
    $zip->close();
    rename($docroot.'/export/'.$workdir.'rev_sqlite.zip', $docroot.'/export/expdown/rev_sqlite.zip');
    unlink($docroot.$filenam);

    $msg.= '<br />Click <a href="/export/expdown/rev_sqlite.zip">here</a> to download';
    $zipped=0;

  }else if($whchprgm==4){ // theWord
    switch($whchfile){
      case 1: // Bible
        file_put_contents($filname, '');

        $ni = 0;
        $sql = 'select v.testament, v.book, v.chapter, v.verse, v.versetext, v.paragraph,
                (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
                v.heading superscript, style, footnotes
                from verse v
                where v.testament in (0,1)
                order by v.testament, v.book, v.chapter, v.verse ';
        $export = dbquery($sql);
        $lastvtxt='';
        while($row = mysqli_fetch_array($export)){
          $curvers = $row['versetext'];
          $styl = $row['style'];
          if($ni>0){
            // handle 3 John 15
            if($row['book']==64 && $row['chapter']==1 && $row['verse']==15){
              $lastvtxt.='<CM><PI>'.processTWverseforexport($curvers, $row['footnotes']);
              $row = mysqli_fetch_array($export);  // grab the next
              $curvers = $row['versetext'];
              $styl = $row['style'];
            }
            switch($styl){
              case 1: // prose
                if($row['paragraph']==1) $lastvtxt.='<CM>';
                if(strpos($curvers, '[bq]')===0){
                  $lastvtxt.='<CM>';
                  $curvers = substr($curvers, 4);
                }
                $inlist=0;
                break;
              case 2: // poetry
              case 3: // poetry_NB
                $lastvtxt.=(($row['paragraph']==1)?'<CM>':'<CI>');
                $curvers = ((strpos($curvers, '[hpbegin]')===FALSE)?'<PF>':'').$curvers;
                $inlist=0;
                break;
              case 4: // BR_poetry
              case 5: // BR_poetry_NB
                $lastvtxt.='<CM>';
                $curvers = ((strpos($curvers, '[hpbegin]')===FALSE)?'<PF>':'').$curvers;
                $inlist=0;
                break;
              case 6: // list
                if($inlist==0 && strpos($curvers, '[listbegin]')===FALSE) $lastvtxt.='<CI>';
                $inlist=1;
                break;
              case 7: // list_END
                $inlist=0;
                break;
              case 8: // BR_list
                $lastvtxt.=(($row['paragraph']==1)?'<CM><PF>':'<CI><PF>');
                $inlist=1;
                break;
              case 9: // BR_list_END
                $lastvtxt.=(($row['paragraph']==1)?'<CM><PF>':'<CI><PF>');
                $inlist=0;
                break;
            }
            file_put_contents($filname, $lastvtxt.crlf, FILE_APPEND);
          }
          $head='';
          if($row['headcount'] > 0){
            $sql = 'select heading, level, reference from outline where testament = '.$row['testament'].' and book = '.$row['book'].' and chapter = '.$row['chapter'].' and verse = '.$row['verse'].' and link=1 order by level ';
            $heds = dbquery($sql);
            $hdcnt=0;$head='';
            while($rrow = mysqli_fetch_array($heds)){
              if($hdcnt>0) $head.= '[br]';
              $head.= $rrow[0];
              if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
              $hdcnt++;
            }
          }
          $lastvtxt = processMSTWheadings($curvers, $head, $row['superscript'], $styl);
          $lastvtxt = processTWverseforexport($lastvtxt, $row['footnotes']);
          $ni++;
        }
        file_put_contents($filname, $lastvtxt.crlf.crlf, FILE_APPEND);
        $revinfo = 'id=REV
lang=eng
charset=0
description=The Revised English Version (REV) is an English translation of the Bible by Spirit & Truth
short.title=REV
version.major=1
version.minor=0
version.date='.substr($datestamp, 1).'
publish.date='.substr($datestamp, 1).'
creator=STF
about=This is the Revised English Version of the Bible being worked on and published by Spirit & Truth<br />\
This translation is from a Biblical Unitarian perspective.<br />\
This work is copyrighted, but it may be freely distributed.<br /><br />\
For more information:<br />\
<a href="https://www.stfonline.org/">https://www.stfonline.org/</a><br />\
<a href="https://www.truthortradition.com/">https://www.truthortradition.com/</a><br />\
<a href="https://www.biblicalunitarian.com/">https://www.biblicalunitarian.com/</a><br />\
<a href="https://www.revisedenglishversion.com/">https://www.revisedenglishversion.com/</a>';
        file_put_contents($filname, $revinfo.crlf, FILE_APPEND);
        break;
      case 2: // Commentary
        copy($docroot.'/includes/resource/REV_Commentary.cmt.twm', $filname);
        $sdb = new SQLite3($filname);
        $sdb->exec('pragma synchronous = off;');
        $sdb->exec('pragma journal_mode=MEMORY;');
        $sdb->query('update config set value = \''.$datestamp.'\' where name = \'publish.date\'');
        $sdb->query('update config set value = \''.$datestamp.'\' where name = \'version.date\'');
        $sdb->query('update config set value = \'rc7\' where name = \'id\'');
        $sdb->query('insert into config (name, value) values (\'content.type\', \'rtf\')');
        if($incvers==1){
          $sdb->query('update config set value = \'rcv7\' where name = \'id\'');
          $sdb->query('update config set value = \'REV Commentary with verses\' where name = \'title\'');
          $sdb->query('update config set value = \'REVvCom\' where name = \'abbrev\'');
        }
        $rtfprefix = '{\rtf1\ansi\ansicpg0\uc1\deff0\deflang0\deflangfe0'.
                     '{\fonttbl{\f0\fnil\fcharset0 Tahoma;}{\f1\fnil Arial;}{\f2\fnil\fcharset0 Times New Roman;}}'.
                     '{\colortbl;\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;\red0\green0\blue128;\red0\green128\blue128;\red0\green128\blue0;\red128\green0\blue128;\red128\green0\blue0;\red128\green128\blue0;\red128\green128\blue128;\red192\green192\blue192;\red243\green243\blue243;}'.
                     '\fs20 ';

        $ni = 1;
        if($incvers==1)
          $sql = 'select b.title, v.book, v.chapter, v.verse, v.versetext, v.commentary, v.style, v.footnotes
                  from verse v
                  inner join book b on (b.testament = v.testament and v.book = b.book)
                  where v.testament in (0,1) and v.commentary is not null
                  order by v.testament, v.book, v.chapter, v.verse ';
        else
          $sql = 'select book, chapter, verse, versetext, commentary, style, footnotes
                  from verse
                  where testament in (0,1) and commentary is not null
                  order by testament, book, chapter, verse ';
        $export = dbquery($sql);
        while($row = mysqli_fetch_array($export)){
          if($incvers==1){
            $comvers = '{\b1 '.$row['title'].' '.$row['chapter'].':'.$row['verse'].'\b0}) '.HTMLtoRTF(processTW2verseforexport($row['versetext'])).'\par \par ';
          }else{
            $comvers='';
          }
          $comn = $row['commentary'];
          if(strlen($comn??'') > 0){
            $comm = $comvers.processTWcommentaryforexport($comn, $row['chapter'], $row['verse']);
            if($inctags==1){
              $comm = parsescripturerefs($comm, $whchprgm);
              $comm = parsestrongs($comm, $whchprgm);
              $comm = parseappx($comm, $whchprgm, $arappx);
              $comm = parsewords($comm, $whchprgm);
            }
            $comm.='}';
          }else{
            $comm='';
          }
          $comm = str_replace('\'', '\'\'', $comm);
          if(strlen(trim($comm??'')) > 0){
            $sdb->query("INSERT INTO bible_refs (topic_id, bi, ci, fvi, tvi, content_type) VALUES (".$ni.", ".$row['book'].", ".$row['chapter'].", ".$row['verse'].", ".$row['verse'].", 'rtf')");
            $sql = 'INSERT INTO content (topic_id, data) VALUES ('.$ni.', \''.$rtfprefix.$comm.'\')';
            if(!$sdb->query($sql))
                print($sql.'<br /><br />');
            $ni++;
          };

        }
        //$sdb->query('CREATE INDEX idx_bible_refs_bi_ci on bible_refs(bi, ci);');
        $sdb->query('CREATE INDEX idx_bible_refs on bible_refs(bi, ci, fvi, tvi);');
        $sdb->close();

        break;
      //case 2: // Info
      case 3: // Appx
      case 4: // Word Studies
          copy($docroot.'/includes/resource/REV_Appx.gbk.twm', $filname);
          $sdb = new SQLite3($filname);
          $sdb->exec('pragma synchronous = off;');
          $sdb->exec('pragma journal_mode=MEMORY;');
          $sdb->query('update config set value = \''.$datestamp.'\' where name = \'version.date\'');
          $sdb->query('delete from topics');
          $sdb->query('delete from content');
          if($whchfile==4){ // Word Studies
            $sdb->query('update config set value = \'REV Word Studies\' where name = \'about\'');
            $sdb->query('update config set value = \'REV Word Studies\' where name = \'description\'');
            $sdb->query('update config set value = \'REV Word Studies\' where name = \'title\'');
            $sdb->query('update config set value = \'rw7\' where name = \'id\'');
            $sdb->query('update config set value = \'REV WS\' where name = \'abbrev\'');
          }

          $rtfprefix = '{\rtf1\ansi\ansicpg0\uc1\deff0\deflang0\deflangfe0'.
                       '{\fonttbl{\f0\fnil\fcharset0 Tahoma;}{\f1\fnil Arial;}{\f2\fnil\fcharset0 Times New Roman;}}'.
                       '{\colortbl;\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;\red0\green0\blue128;\red0\green128\blue128;\red0\green128\blue0;\red128\green0\blue128;\red128\green0\blue0;\red128\green128\blue0;\red128\green128\blue128;\red192\green192\blue192;\red243\green243\blue243;}'.
                       '\fs20 ';

          $ni = 0;
          $test =(($whchfile==3)?3:(($whchfile==4)?4:2));
          $sql = 'select ifnull(b.tagline,b.title) title, v.commentary, b.book
                  from book b
                  inner join verse v on v.testament = b.testament and v.book = b.book
                  where b.testament = '.$test.'
                  and (active = 1)
                  order by '.(($test==4)?'2':'sqn').' ';

          $export = dbquery($sql);
          while($row = mysqli_fetch_array($export)){
            $comn = $row['commentary'];
            $comm = processTWcommentaryforexport($comn, 0, 0);
            if($inctags==1){
              $comm = parsescripturerefs($comm, $whchprgm);
              $comm = parsestrongs($comm, $whchprgm);
              $comm = parseappx($comm, $whchprgm, $arappx);
              $comm = parsewords($comm, $whchprgm);
            }
            $comm.='}';
            $comm = str_replace('\'', '\'\'', $comm);
            if(strlen(trim($comm)) > 0){
              $idx = (($whchfile==4)?$row['book']:($ni+1));
              $sdb->query("INSERT INTO topics (id, subject, rel_order) VALUES (".$idx.", '".processtitleforexport($row['title'], $whchfile)."', ".($ni+1).")");
              $sql = 'INSERT INTO content (topic_id, data) VALUES ('.$idx.', \''.$rtfprefix.$comm.'\')';
              if(!$sdb->query($sql))
                  print($sql.'<br /><br />');
              $ni++;
            };

          }
          $sdb->close();
        break;
      default:
          die('unknown whchfile: '.$whchfile);
    }
    $zip = new ZipArchive();
    $zipfile = $docroot."/export/".$workdir.$filnam.$filext."twzip";

    if ($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {
        exit("cannot open <$zipfile>\n");
    }
    $zip->addFile($filname, $filnam.$filext);
    $zip->close();

    // this is for iBibleStudy
    copy($docroot.'/export/'.$workdir.$filnam.$filext, $docroot.'/export/expdown/'.$filnam.$filext);
    rename($docroot.'/export/'.$workdir.$filnam.$filext.'twzip', $docroot.'/export/expdown/'.$filnam.$filext.'.twzip');
    $msg = '<b>DONE:</b> '.$ni.' items exported to '.$filnam.$filext.'.<br /><a href="/export/expdown/'.$filnam.$filext.'.twzip">Click here</a> to download.';
    $zipped=0;

  }else{ // eSword or MySword
    $sdb = new SQLite3($filname);
    if (!$sdb) {
      die("Unable to create database!");
    }
    $sdb->exec('pragma synchronous = off;');
    $sdb->exec('pragma journal_mode=MEMORY;');
    switch($whchfile){
      case 1: // Bible
        // create Bible database
        // create Detail table for bible database. This works for both eSword and MySword
        // update 20220830 Need new details table for eSword
        if($whchprgm==1){ //eSword
          $sdb->query('CREATE TABLE "Details" (
                          "Title" NVARCHAR(100), "Abbreviation" NVARCHAR(50),
                          "Information" TEXT, "Version" INT,
                          "OldTestament" BOOL, "NewTestament" BOOL,
                          "Apocrypha" BOOL, "Strongs" BOOL, "RightToLeft" BOOL);');
          // insert Detail table row
          $sdb->query("INSERT INTO Details VALUES (
                          'RevisedEnglishVersion".$datestamp."', 'REV".getexporttimestamp(4)."', '<p>Revised English Version</p>', 4, 1, 1, 0, 0, 0)");
        }else{ //MySword
          $sdb->query('CREATE TABLE "Details" (
                          "Title" NVARCHAR(255), "Description" TEXT, "Abbreviation" NVARCHAR(50),
                          "Comments" TEXT, "Version" TEXT, "VersionDate" DATETIME, "PublishDate" DATETIME,
                          "Publisher" TEXT, "Author" TEXT, "Creator" TEXT, "Source" TEXT,
                          "EditorialComments" TEXT, "Language" NVARCHAR(3), "RightToLeft" BOOL,
                          "OT" BOOL, "NT" BOOL, "Strong" BOOL, "VerseRules" TEXT,
                          "Font" TEXT, "Apocrypha" BOOL);');

          // insert Detail table row
          $sdb->query("INSERT INTO Details VALUES (
                          'RevisedEnglishVersion".$datestamp."', 'RevisedEnglishVersion', 'REV', 'None', '4', null,
                          null, 'STF', 'STF', 'STF', 'STF', 'None', 'eng', 0, 1, 1, 0, null, 'DEFAULT', 0)");
        }

        // create Bible table for bible database
        $sdb->query("CREATE TABLE 'Bible' (Book INT, Chapter INT, Verse INT, Scripture TEXT);");
        //$sdb->query("CREATE INDEX BookChapterVerseIndex ON Bible (Book, Chapter, Verse);");

        $mainsql = 'select v.testament, v.book, v.chapter, v.verse, v.versetext, v.paragraph,
                   (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
                   v.heading superscript, v.style, v.footnotes
                   from verse v
                   where v.testament in (0,1)
                   order by v.testament, v.book, v.chapter, v.verse ';
        break;

      case 2: // Commentary
        // create Commentary database
        if($whchprgm==1){ // eSword

          $sdb->query('CREATE TABLE Details (Title NVARCHAR(100), Abbreviation NVARCHAR(50), Information TEXT, Version INT);');
          $sdb->query("INSERT INTO Details (Title, Abbreviation, Information, Version) VALUES (
                      'REV Commentary".(($incvers==1)?' with verses':'').$datestamp."', 'REV".(($incvers==1)?'_v':'').getexporttimestamp(4)."', 'No information', 4);");
          $sdb->query('CREATE TABLE BookCommentary (Book INT, Comments TEXT);');
          $sdb->query('CREATE TABLE ChapterCommentary (Book INT, Chapter INT, Comments TEXT);');
          $sdb->query('CREATE TABLE VerseCommentary (Book INT, ChapterBegin INT, VerseBegin INT, ChapterEnd INT, VerseEnd INT, Comments TEXT);');

        }else{ // MySword

          // create Detail table for commentary database
          $sdb->query('CREATE TABLE "details"(
                      "title" TEXT, "abbreviation" TEXT, "description" TEXT, "comments" TEXT,
                      "author" TEXT, "version" TEXT, "versiondate" DATETIME, "publishdate" TEXT,
                      "publisher" TEXT, "creator" TEXT, "source" TEXT, "language" NVARCHAR(3),
                      "editorialcomments" TEXT, "righttoleft" INT default 0, "customcss" TEXT)');
          // insert Detail table row
          $sdb->query("INSERT INTO Details VALUES (
                      'RevisedEnglishVersion".(($incvers==1)?'_with_verses':'').$datestamp."', 'REV".(($incvers==1)?'_v':'')."', 'No description', 'No comments',
                      'STF', '1', null, null, 'STF', 'STF', 'STF', 'eng', null, 0, null)");

          $sdb->query('CREATE TABLE commentary(
                      id INTEGER primary key autoincrement, book INTEGER, chapter INTEGER,
                      fromverse INTEGER, toverse INTEGER, data TEXT);');
        }
          $mainsql = 'select v.testament, v.book, v.chapter, v.verse, v.versetext, v.commentary,
                      (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
                      v.heading superscript, v.style, v.footnotes, v.comfootnotes
                      from verse v
                      where v.testament in (0,1) and v.commentary is not null
                      order by v.testament, v.book, v.chapter, v.verse ';
        break;
      case 3: // Appendices
      case 4: // Word Studies
      case 5: // Information
        if($whchprgm==1){ // eSword
          $sdb->query('CREATE TABLE Details (Title NVARCHAR(255), Abbreviation NVARCHAR(50), Information TEXT, Version INT, prefix NVARCHAR(50), prefix2 NVARCHAR2(50), prefixstartnum INT);');
          $sdb->query("INSERT INTO Details (Title, Abbreviation, Information, Version, prefix, prefix2, prefixstartnum) VALUES (
                          '".$dtitle."', '".$dabbr.getexporttimestamp(4)."', 'No information', 4, '#0', '. ', 1)");
          $sdb->query('CREATE TABLE Reference (Chapter NVARCHAR(100), Content TEXT);');
        }else{ // MySword
          $sdb->query('CREATE TABLE data(rowid INTEGER primary key autoincrement, id TEXT collate nocase, description TEXT collate nocase, date DATETIME, filename TEXT, content BLOB);');
          $sdb->query('CREATE TABLE details(name TEXT, title TEXT, abbreviation TEXT, author TEXT, description TEXT, comments TEXT, version TEXT, versiondate DATETIME, publishdate TEXT, readonly BOOL, righttoleft BOOL, customcss TEXT);');
          $sdb->query("INSERT INTO Details (name, title, abbreviation, author, description, comments, version, versiondate, publishdate, readonly, righttoleft, customcss) VALUES (
                          '".$dtitle."', '".$dtitle."', '".$dabbr."', 'STF', 'No description', 'No comments', '1', date('now'), date('now'), 0, 0, null)");
          $sdb->query('CREATE TABLE journal(rowid INTEGER primary key autoincrement, id TEXT collate nocase, title TEXT collate nocase, date DATETIME, tags TEXT, content TEXT, relativeorder INT default 0, hidden INT default 0);');
          $sdb->query('CREATE VIRTUAL TABLE journalFTS USING FTS3(title, content, tags, tokenize=porter);');
        }
        $test =(($whchfile==3)?3:(($whchfile==4)?4:2));

        $mainsql = 'select ifnull(b.tagline,b.title) title, v.testament, v.book, v.chapter, v.verse, v.commentary, v.comfootnotes
                    from book b
                    inner join verse v on v.testament = b.testament and v.book = b.book
                    where b.testament = '.$test.'
                    and (active = 1)
                    order by '.(($test==4)?'2':'sqn').' ';
        break;
      default: // ??
        die('unknown whchfile: '.$whchfile);
    }

    $ni = 0;
    $export = dbquery($mainsql);
    $lastvtxt='';
    $inlist=0;
    while($row = mysqli_fetch_array($export)){
      switch($whchfile){
      case 1: // Bible
        if($whchprgm==1){ // eSword
          $tmp = processESverseforexport($row['versetext'], $row['footnotes']);
          $sql = "INSERT INTO Bible VALUES (".$row['book'].", ".$row['chapter'].", ".$row['verse'].", '".$tmp."')";
          $ret = $sdb->query($sql);
          if($ret==FALSE) print($sql.'<br />');
        }else{ // MySword
          $curvers = $row['versetext'];
          $styl = $row['style'];
          if($ni>0){
            switch($styl){
              case 1: // prose
                if($row['paragraph']==1) $lastvtxt.='<CM>';
                if(strpos($curvers, '[bq]')===0){
                  $lastvtxt.='<CM>';
                  $curvers = substr($curvers, 4);
                }
                $inlist=0;
                break;

              case 2: // poetry
              case 3: // poetry_NB
                $lastvtxt.=(($row['paragraph']==1)?'<CM>':'<CI>');
                $curvers = ((strpos($curvers, '[hpbegin]')===FALSE)?'<PF>':'').$curvers;
                $inlist=0;
                break;
              case 4: // BR_poetry
              case 5: // BR_poetry_NB
                $lastvtxt.='<CM>';
                $curvers = ((strpos($curvers, '[hpbegin]')===FALSE)?'<PF>':'').$curvers;
                $inlist=0;
                break;
              case 6: // list
                if($inlist==0 && strpos($curvers, '[listbegin]')===FALSE) $lastvtxt.='<PI1>';
                $inlist=1;
                break;
              case 7: // list_END
                $inlist=0;
                break;
              case 8: // BR_list
                $lastvtxt.=(($row['paragraph']==1)?'<PI1>':'<PI1>');    // redundant...
                $inlist=1;
                break;
              case 9: // BR_list_END
                $lastvtxt.=(($row['paragraph']==1)?'<PI1>':'<PI1>');    // redundant...
                $inlist=0;
                break;
            }
            $sql = "INSERT INTO Bible VALUES (".$lastbook.", ".$lastchap.", ".$lastvers.", '".$lastvtxt."')";
            $ret = $sdb->query($sql);
            if($ret==FALSE) die($sql.'<br />');
          }
          $lastbook = $row['book'];
          $lastchap = $row['chapter'];
          $lastvers = $row['verse'];
          $laststyl = $row['style'];
          $head='';
          if($row['headcount'] > 0){
            $sql = 'select heading, level, reference from outline where testament = '.$row['testament'].' and book = '.$row['book'].' and chapter = '.$row['chapter'].' and verse = '.$row['verse'].' and link=1 order by level ';
            $heds = dbquery($sql);
            $hdcnt=0;$head='';
            while($rrow = mysqli_fetch_array($heds)){
              if($hdcnt>0) $head.= '[br]';
              $head.= $rrow[0];
              if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
              $hdcnt++;
            }
          }
          $lastvtxt = processMSTWheadings($curvers, $head, $row['superscript'], $styl);
          $lastvtxt = processMSverseforexport($lastvtxt, $row['footnotes']);
        }
        $ni++;
        break;
      case 2: // commentary
        if($incvers==1){
          if($whchprgm==1){ // eSword
            $comvers = processESverseforexport($row['versetext'], $row['footnotes']).'<hr>';
            //$comvers = '<b>'.$row['title'].' '.$row['chapter'].':'.$row['verse'].'</b>) '.processESverseforexport($row['versetext'], $row['footnotes']).'<hr>';
          }else{ // MSword
            $comvers = processMSHTMLverseforexport($row['versetext']).'<hr>';
            //$comvers = '<b>'.$row['title'].' '.$row['chapter'].':'.$row['verse'].'</b>) '.processMSHTMLverseforexport($row['versetext']).'<hr>';
          }
        }else{
          $comvers='';
        }
        $comn = $row['commentary'];
        if(strlen($comn??'') > 0){
          $comm = $comvers.processcommentaryforexport($comn, $row['chapter'], $row['verse']);
          $comfootnotes = getfootnotes($row['testament'], $row['book'], $row['chapter'], $row['verse'], 'com');
          $comm = processcommentaryfootnotes($comm, $comfootnotes??'', $whchprgm);
          $comm = parsenoparse($comm);
          //if($row['book']==27 && $row['chapter']==8 && $row['verse']==9) print($comm);
          if($inctags==1){
            $comm = parsescripturerefs($comm, $whchprgm);
            $comm = parsestrongs($comm, $whchprgm);
            // Unfortunately, these do not work in eSword, yet
            if($whchprgm==2){
              $comm = parseappx($comm, $whchprgm, $arappx);
              $comm = parsewords($comm, $whchprgm);
            }else{
              $comm = str_replace("<blockquote>", "", $comm); // remove blockquote for eSword
              $comm = str_replace("</blockquote>", "", $comm);
            }
          }
          $comm.= '<p>&nbsp;</p>';
        }else{
          $comm='';
          //$comm = $comvers.(($incvers==1)?'There is no REV commentary for this verse.':'');
        }
        if(strlen(trim($comm)) > 0){
          if($whchprgm==1){ // eSword
            $sdb->query("INSERT INTO VerseCommentary (Book, ChapterBegin, VerseBegin, ChapterEnd, VerseEnd, Comments) VALUES (".$row['book'].", ".$row['chapter'].", ".$row['verse'].", ".$row['chapter'].", ".$row['verse'].", '".$comm."')");
          }else{ // MySword
            $sdb->query("INSERT INTO Commentary (book, chapter, fromverse, toverse, data) VALUES (".$row['book'].", ".$row['chapter'].", ".$row['verse'].", ".$row['verse'].", '".$comm."')");
          }
          $ni++;
        };
        break;
      case 3; // Appx
      case 4; // WS
      case 5; // Info
        if($whchprgm==1){
          // eSword
          $comm = processcommentaryforexport($row['commentary'], 1, 1);
          $comfootnotes = getfootnotes($row['testament'], $row['book'], $row['chapter'], $row['verse'], 'com');
          $comm = processcommentaryfootnotes($comm, $comfootnotes??'', $whchprgm);
          $comm = parsenoparse($comm);
          $comm = str_replace("<blockquote>", "", $comm);
          $comm = str_replace("</blockquote>", "", $comm);
          if($inctags==1){
            $comm = parsescripturerefs($comm, $whchprgm);
            $comm = parsestrongs($comm, $whchprgm);
            // Unfortunately, these do not work for eSword, yet
            // $comm = parseappx($comm, $whchprgm, $arappx);
            // $comm = parsewords($comm, $whchprgm);
          }
          $sdb->query("INSERT INTO Reference (Chapter, Content) VALUES ('".processtitleforexport($row['title'], $whchfile)."', '".$comm."')");
        }else{
          // MySword  NOT WORKING for WS
          $stitle = processtitleforexport($row['title'], $whchfile);
          $comm = processcommentaryforexport($row['commentary'], 1, 1);
          $comfootnotes = getfootnotes($row['testament'], $row['book'], $row['chapter'], $row['verse'], 'com');
          $comm = processcommentaryfootnotes($comm, $comfootnotes??'', $whchprgm);
          $comm = parsenoparse($comm);
          if($inctags==1){
            $comm = parsescripturerefs($comm, $whchprgm);
            $comm = parsestrongs($comm, $whchprgm);
            $comm = parseappx($comm, $whchprgm, $arappx);
            $comm = parsewords($comm, $whchprgm);
          }
          $sdb->query("INSERT INTO Journal (id, title, date, tags, content, relativeorder, hidden) VALUES ('".$stitle."', '".$stitle."', date('now'), null, '".$comm."', ".$ni.", 0)");
        };
        $ni++;
        break;
      }
    }
    if($whchprgm==2 && $whchfile==1){
      $sql = "INSERT INTO Bible VALUES (".$lastbook.", ".$lastchap.", ".$lastvers.", '".$lastvtxt."')";
      $ret = $sdb->query($sql);
    }

    switch($whchfile){
      case 1: // Bible
        $sdb->query("CREATE INDEX BookChapterVerseIndex ON Bible (Book, Chapter, Verse);");
        break;
      case 2: // commentary
        if($whchprgm==1){ // eSword
          $sdb->query('CREATE INDEX BookIndex ON BookCommentary (Book);');
          $sdb->query('CREATE INDEX BookChapterIndex ON ChapterCommentary (Book, Chapter);');
          $sdb->query('CREATE INDEX BookChapterVerseIndex ON VerseCommentary (Book, ChapterBegin, VerseBegin);');
        }else{ // MySword
          $sdb->query('CREATE INDEX idx_commentary on commentary(book, chapter, fromverse);');
        };
        break;
    case 3: // Appx
    case 4: // WS
    case 5: // Info
      if($whchprgm==2){ // MySword
        $sdb->query('CREATE UNIQUE INDEX idx_data_id on data(id);');
        $sdb->query('CREATE UNIQUE INDEX idx_data_description on data(description);');
        $sdb->query('CREATE UNIQUE INDEX idx_journal_id on journal(id);');
        $sdb->query('CREATE UNIQUE INDEX idx_journal_title on journal(title);');
      };
      break;
    }

    $sdb->close();
    if($zipped==1){
      $zip = new ZipArchive();
      $zipfile = $docroot."/export/".$workdir.$filnam.$filext.".zip";
      if ($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {exit("cannot open <$zipfile>\n");}

      $zip->addFile($filname, $filnam.$filext);
      $zip->close();
      unlink($filname);
      $zipped = 0;
      rename($docroot.'/export/'.$workdir.$filnam.$filext.'.zip', $docroot.'/export/expdown/'.$filnam.$filext.'.zip');
      $msg = '<b>DONE:</b> '.$ni.' items exported to '.$filnam.$filext.'.<br /><a href="/export/expdown/'.$filnam.$filext.'.zip">Click here</a> to download.';
    }else{
      rename($docroot.'/export/'.$workdir.$filnam.$filext, $docroot.'/export/expdown/'.$filnam.$filext);
      $msg = '<b>DONE:</b> '.$ni.' items exported to '.$filnam.$filext.'.<br /><a href="/export/expdown/'.$filnam.$filext.'">Click here</a> to download.';
    };
  }


  $processed = 1;
}
//print('<h2>Generate Export files <span style="color:red">(Admin)</span></h2>');
print('<span class="pageheader">Generate Export files</span></span>');
print('<div style="margin:0 auto;text-align:center"><small>'.usermenu().'</small></div>');
print('<div style="margin:0 auto;text-align:center"><small>'.adminmenu().'</small></div>');
print('<br /><div style="margin:0 auto;width:480px"><table><tr><td>');
if($processed==1){
  print($msg.'<br /><br />');
}
?>
<form name="frm" method="post" action="/">
  Indicate the program.<br />
  <table>
    <tr><td><input type="radio" name="whchprgm" id="ft1" value="1" onclick="setpgm(1);"<?=fixrad($whchprgm==1)?> /></td><td><label for="ft1">e-Sword&reg; (PC, X, HD, LT)</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft2" value="2" onclick="setpgm(2);"<?=fixrad($whchprgm==2)?> /></td><td><label for="ft2">MySword&reg; (Android)</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft3" value="3" onclick="setpgm(3);"<?=fixrad($whchprgm==3)?> /></td><td><label for="ft3">BibleWorks&reg; (Windows PC)</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft4" value="4" onclick="setpgm(4);"<?=fixrad($whchprgm==4)?> /></td><td><label for="ft4">theWord&reg; (PC) or iBibleStudy</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft6" value="6" onclick="setpgm(6);"<?=fixrad($whchprgm==6)?> /></td><td><label for="ft6">Swordsearcher&reg; (PC)</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft7" value="7" onclick="setpgm(7);"<?=fixrad($whchprgm==7)?> /></td><td><label for="ft7">Accordance&reg; (PC or Mac)</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft5" value="5" onclick="setpgm(5);"<?=fixrad($whchprgm==5)?> /></td><td><label for="ft5">SqLite</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft8" value="8" onclick="setpgm(8);"<?=fixrad($whchprgm==8)?> /></td><td><label for="ft8">JSON (offline REV)</label></td></tr>
    <tr><td><input type="radio" name="whchprgm" id="ft9" value="9" onclick="setpgm(9);"<?=fixrad($whchprgm==9)?> /></td><td><label for="ft9">Logos&reg;</label></td></tr>
  </table>
  <br />
  Indicate the module.<br />
  <table>
    <tr><td><input type="radio" name="whchfile" id="fw1" value="1"<?=fixrad($whchfile==1)?> onclick="$('incvers').checked=false;$('inctags').checked=false;" /></td><td><label for="fw1">REV Bible</label></td></tr>
    <tr><td><input type="radio" name="whchfile" id="fw2" value="2"<?=fixrad($whchfile==2)?> /></td><td><label for="fw2">REV Commentary</label>
      <input type="checkbox" name="incvers" id="incvers" value="1"<?=fixchk($incvers);?> onclick="if(this.checked) $('fw2').checked=true;" /><label for="incvers"><small>w/verse</small></label>
      <input type="checkbox" name="inctags" id="inctags" value="1"<?=fixchk($inctags);?> onclick="if(this.checked && $('fw1').checked==true) $('fw2').checked=true;" /><label for="inctags"><small>tag</small></label>
      </td></tr>
    <tr><td><input type="radio" name="whchfile" id="fw3" value="3"<?=fixrad($whchfile==3)?> onclick="$('incvers').checked=false;" /></td><td><label for="fw3">REV Appendices</label></td></tr>
<?if($revws==1){?>
    <tr><td><input type="radio" name="whchfile" id="fw4" value="4"<?=fixrad($whchfile==4)?> onclick="$('incvers').checked=false;" /></td><td><label for="fw4">REV Word Studies</label><?=(($revws==0)?' <span style="color:red;">NOT LIVE!!</span>':'')?></td></tr>
<?}else{
     print('<input type="radio" style="display:none;" name="whchfile" id="fw4" value="4" />');
}?>
  </table>
  <br />
  <!--
  <table>
    <tr><td style="vertical-align:top;"><input type="checkbox" name="zipped" id="zipped" value="1"<?=fixchk($zipped);?>></td><td><label for="zipped">zipped</label> <small>(no, too hard for ppl to install)</small></td></tr>
  </table>
  <br />
  -->
  <input type="submit" name="btn" id="btn" value="Go!" onclick="doit();" /> <small>(this may take several seconds)</small>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="31" />
  <input type="hidden" name="qs" value="<?=$qs?>" />
  <input type="hidden" name="oper" value="" />
</form><br />
<form name="frmdel" method="post" action="/">
<table style="font-size:.7em;border-spacing:0;border-collapse:separate;">
  <tr><td>File</td><td style="padding-left:5px;">Date Generated</td></tr>
<?
  $expfiles = array();
  $path = $docroot.'/export/expdown/';
  if ($handle = opendir($path)) {
    $nowdate = new DateTime(null ?? '', new DateTimeZone($timezone));
    if(!($serverTimeZone = date_default_timezone_get())) $serverTimeZone = 'UTC';
    $ni = 0;
    while (false !== ($file = readdir($handle))) {
      if(($file!='.' && $file!='..' && $file!='!readme.txt')){
        $processfile=1;
        $thefile = preg_replace('#(.*?)_\d*(\..*?)#', '$1$2', $file);
        switch($thefile){
          // e-sword
          case 'REV_Bible.bbli':                 $idx=0;$fnam='e-Sword / '.$file;break;
          case 'REV_Commentary.cmti':            $idx=1;$fnam='e-Sword / '.$file;break;
          case 'REV_Commentary_with_verses.cmti':$idx=2;$fnam='e-Sword / '.$file;break;
          case 'REV_Appx.refi':                  $idx=3;$fnam='e-Sword / '.$file;break;
          case 'REV_WS.refi':                    $idx=4;$fnam='e-Sword / '.$file;break;

          // mysword
          case 'REV_Bible.bbl.mybible':          $idx=5;$fnam='MySword / REV Bible';break;
          case 'REV_Commentary.cmt.mybible':     $idx=6;$fnam='MySword / REV Commentary';break;
          case 'REV_Commentary_with_verses.cmt.mybible':$idx=7;$fnam='MySword / REV Comm w/verse';break;
          case 'REV_Appx.bok.mybible':           $idx=8;$fnam='MySword / REV Appendices';break;
          case 'REV_WS.bok.mybible':             $idx=9;$fnam='MySword / REV Word Studies';break;

          case 'REV_Bibleworks.zip':             $idx=10;$fnam='BibleWorks / Bible &amp; Commentary';break;
          case 'REV.ont.twzip':                  $idx=11;$fnam='theWord / REV Bible';break;
          case 'REV_Commentary.cmt.twm.twzip':   $idx=12;$fnam='theWord / REV Commentary';break;
          case 'REV_Commentary_with_verses.cmt.twm.twzip':$idx=13;$fnam='theWord / REV Comm w/verse';break;
          case 'REV_Appx.gbk.twm.twzip':         $idx=14;$fnam='theWord / REV Appendices';break;
          case 'REV_WS.gbk.twm.twzip':           $idx=15;$fnam='theWord / REV Word Studies';break;
          case 'REV.ont':                        $idx=16;$fnam='iBibleStudy / REV Bible';break;
          case 'REV_Commentary.cmt.twm':         $idx=17;$fnam='iBibleStudy / REV Commentary';break;
          case 'REV_Commentary_with_verses.cmt.twm':$idx=18;$fnam='iBibleStudy / REV Comm w/verse';break;
          case 'REV_Appx.gbk.twm':               $idx=19;$fnam='iBibleStudy / REV Appendices';break;
          case 'REV_WS.gbk.twm':                 $idx=20;$fnam='iBibleStudy / REV Word Studies';break;
          case 'rev_sqlite.zip':                 $idx=21;$fnam='REV / SQLite';break;
          case 'REV_Swordsearcher.zip':          $idx=22;$fnam='Swordsearcher / Bible/Comm';break;
          case 'REV_Accordance.zip':             $idx=23;$fnam='Accordance / Bible/Comm';break;
          case 'JSON_REV_timestamp.json':        $idx=24;$fnam='JSON / REV Timestamp';break;
          case 'JSON_REV_bible.json':            $idx=25;$fnam='JSON / REV Bible';break;
          case 'JSON_REV_commentary.json':       $idx=26;$fnam='JSON / REV Commentary';break;
          case 'JSON_REV_appendices.json':       $idx=27;$fnam='JSON / REV Appendices';break;
          //case 'REV_Bible.docx':                 $idx=28;$fnam='MSW / REV Bible';break;
          //case 'REV_Commentary.docx':            $idx=29;$fnam='MSW / REV Commentary';break;
          case 'REV_Appendices.docx':            $idx=30;$fnam='MSW / REV Appendices';break;
          case 'REV_Information.docx':           $idx=31;$fnam='MSW / REV Information';break;
          case 'REV_Wordstudies.docx':           $idx=32;$fnam='MSW / REV Word Studies';break;
          case 'REV_MSWord.zip':                 $idx=33;$fnam=$file;break;
          case 'REV_Logos.zip':                  $idx=34;$fnam='Logos / Bible';break;
          default: $processfile=0;
        }
        if($processfile==1){
          $timestamp = date('m/d/Y ga',filemtime($path.$file));
          $tsdate = new DateTime($timestamp ?? '', new DateTimeZone($serverTimeZone));
          $interval = $tsdate->diff($nowdate);
          $days = abs($interval->days);

          $timestamp = getuserdate($timestamp, $timezone);

          $expfiles[$ni] = array($idx, $fnam, $timestamp, $days, $file);
          $ni++;
        }
      }
    }
    closedir($handle);
  }

  sort($expfiles);
  for($ni=0;$ni<sizeof($expfiles);$ni++){
    print('<tr style="line-height:12px;'.(($expfiles[$ni][3]>=14)?'background-color:#f66;':'').'"><td style="white-space:nowrap;">'.$expfiles[$ni][1].'</td><td style="padding-left:5px;white-space:nowrap;">'.$expfiles[$ni][2].'<input type="hidden" name="delfile'.$ni.'" value="'.$expfiles[$ni][4].'" /></td><td><input type="checkbox" name="dodel'.$ni.'" value="1" /></td></tr>'.crlf);
  }
?>
</table>
<input type="hidden" name="page" value="31" />
<input type="hidden" name="oper" value="" />
<input type="hidden" name="delcount" value="<?=$ni?>" />
<input type="submit" name="smtdel" value="Delete Checked" onclick="document.frmdel.oper.value='dodel';" />
</form>
</td></tr></table></div>
  <script>
  function doit(){
      $('btn').value = 'Please wait..';
      setTimeout('$(\'btn\').disabled=true', 300);
      document.frm.oper.value='go';
  }

  function setpgm(ctl){
  switch(ctl){
    case 1: // esword
    case 2: // mysword
    case 4: // theWord
      $('fw1').disabled=false;
      $('fw2').disabled=false;
      $('fw3').disabled=false;
      $('fw4').disabled=false;
      $('incvers').disabled=false;
      $('inctags').disabled=false;
      if($('fw1').checked==false && $('fw2').checked==false && $('fw3').checked==false && $('fw4').checked==false)
        $('fw1').checked=true;
      break;
    case 3: // bibleworks
    case 5: // SqlLite
    case 6: // Swordsearcher
    case 7: // Accordance
    case 8: // JSON
    case 9: //Logos
      $('fw1').checked=true;
      $('fw1').disabled=true;
      $('fw2').disabled=true;
      $('fw3').disabled=true;
      $('fw4').disabled=true;
      $('incvers').checked=false;
      $('incvers').disabled=true;
      $('inctags').checked=false;
      $('inctags').disabled=true;
      break;
  }
}
  setTimeout("setpgm(<?=$whchprgm?>);", 300);

</script>

<?

//print('your tz: '.$timezone.'<br />');
//print('srvr tz: '.$serverTimeZone.'<br />');


function processtitleforexport($tit, $whchfil){
  $titl = $tit;
  if($whchfil==4){ // word study
    $titl = 'Word Study on &ldquo;'.$titl.'&rdquo;';
  }
  $titl = str_replace("&ldquo;", "“", $titl);
  $titl = str_replace("&rdquo;", "”", $titl);
  $titl = str_replace("&lsquo;", "‘", $titl);
  $titl = str_replace("&rsquo;", "’", $titl);
  return $titl;
}

function processtitleforSSexport($tit){
  // no quotes in title
  $titl = $tit;
  $titl = str_replace("&ldquo;", "", $titl);
  $titl = str_replace("&rdquo;", "", $titl);
  $titl = str_replace("&lsquo;", "", $titl);
  $titl = str_replace("&rsquo;", "", $titl);
  $titl = str_replace("“", "", $titl);
  $titl = str_replace("”", "", $titl);
  $titl = str_replace("‘", "", $titl);
  $titl = str_replace("’", "", $titl);
  return $titl;
}

function processcommentaryfootnotes($comm, $footnotes, $whch){
  if($footnotes=='' || strpos($comm, '[fn]')===false){
    $ret = str_replace('[fn]', '', $comm);
    return $ret;
  }
  $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
  $fword = "[fn]";
  $arfnotes = explode('~~', $footnotes);
  $nf = 0;
  $strfns = '';
  $havefootnote = ((strpos($comm, $fword)>-1)?strpos($comm, $fword):-1);
  while($havefootnote>-1 && isset($arfnotes[$nf])){
    if($arfnotes[$nf] != ''){
      $fidx=substr($footnoteindicator, ($nf%26), 1);
      $fpreidx = trim(substr(' '.$footnoteindicator, (intval($nf/26)%26), 1));
      $tmp = '<sup>'.$fpreidx.$fidx.'</sup>';
      $strfns.= $fpreidx.$fidx.') '.$arfnotes[$nf].'<br />';
      $nf++;
    }else{
      $tmp = '';
    }
    $comm = substr($comm, 0, ($havefootnote)).$tmp.substr($comm, ($havefootnote+4));
    $havefootnote = ((strpos($comm, $fword)>-1)?strpos($comm, $fword):-1);
  }
  if($nf>0){
    $comm = trim($comm).'<hr><small>'.$strfns.'</small>';
  }
  //print($comm);
  $ret = str_replace('[fn]', '', $comm);
  return $ret;
}


function processexptfootnotes($v, $f, $p){
  if($f=='~~~~~~~~') return $v;
  $arf = explode('~~', $f);
  foreach($arf as $fn){
    if($fn.'' !== ''){
      $pos = strpos($v, '[fn]');
      if($pos !== false) {
        if($p==1)
          $replace = '<sup style="color:green;font-size:75%;">['.$fn.']</sup>';
        else if($p==6){
          $fn = str_replace('<em>', '[', $fn);
          $fn = str_replace('</em>', ']', $fn);
          $replace = '{'.$fn.'}';
        }else if($p==9){ // logos
          $fn = str_replace("“", "&ldquo;", $fn);
          $fn = str_replace("”", "&rdquo;", $fn);
          $fn = str_replace("‘", "&lsquo;", $fn);
          $fn = str_replace("’", "&rsquo;", $fn);
          $fn = str_replace("&lsquo;", '\'', $fn);
          $fn = str_replace("&rsquo;", '\'', $fn);
          $fn = str_replace("&ldquo;", '"', $fn);
          $fn = str_replace("&rdquo;", '"', $fn);
          $replace = '[~'.$fn.'~]';
        }else{
          $replace = '<RF q=fn>'.$fn.'<Rf>';
          if($p==4){ // theWord
            $replace = processTWverseforexport($replace, '~~~~~~~~');
          }
        }
        $v = substr_replace($v, $replace, $pos, 4);
      }
    }
  }
  return $v;
}

function processESverseforexport($vrs, $fnts){
  $vrse = $vrs;

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("—", "&mdash;", $vrse);
  $vrse = str_replace("–", "&ndash;", $vrse);
  $vrse = str_replace("ï",  "&iuml;",  $vrse);

  // TODO this needs handled...
  //$notintext = (strpos($vrse, '~')===0);
  //$vrse = str_replace("~[[", "[[", $vrse);
  $vrse = str_replace("~", "", $vrse);
  //if($notintext && right($vrse, 1)==']') $vrse.=']';

  $vrse = processexptfootnotes($vrse, $fnts, 1);     // no good way to display footnotes in eSword
  $vrse = str_replace("[fn]", " ", $vrse);
  $vrse = str_replace("[pg]", " ", $vrse);
  $vrse = str_replace("[mvh]", " ", $vrse);
  $vrse = str_replace("[mvs]", " ", $vrse);
  $vrse = str_replace("[br]", " ", $vrse);
  $vrse = str_replace("[hpbegin]", " ", $vrse);
  $vrse = str_replace("[hpend]", " ", $vrse);
  $vrse = str_replace("[hp]", " ", $vrse);
  $vrse = str_replace("[listbegin]", " ", $vrse);
  $vrse = str_replace("[listend]", " ", $vrse);
  $vrse = str_replace("[lb]", " ", $vrse);
  $vrse = str_replace("[bq]", " ", $vrse);
  $vrse = str_replace("[/bq]", " ", $vrse);
  $vrse = str_replace("<o:p>", "", $vrse);  // weird
  $vrse = str_replace("</o:p>", "", $vrse);
  $vrse = str_replace("<br />", "", $vrse);  // dunno where these are coming from on the live site
  $vrse = str_replace("&#39;", "&rsquo;", $vrse);
  $vrse = preg_replace('#\s+#', ' ', $vrse);        // replace repeating spaces
  return $vrse;
}

// for eSword and MySword
function processcommentaryforexport($commentary, $ch, $vs){
  $comm = $commentary;
  $comm = str_replace("“", "&ldquo;", $comm);         // convert quotes
  $comm = str_replace("”", "&rdquo;", $comm);
  $comm = str_replace("‘", "&lsquo;", $comm);
  $comm = str_replace("’", "&rsquo;", $comm);
  $comm = str_replace("&#39;", "&rsquo;", $comm);
  $comm = str_replace("—", "&mdash;", $comm);
  $comm = str_replace("–", "&ndash;", $comm);
  $comm = str_replace("ï",  "&iuml;",  $comm);
  //$comm = str_replace("[fn]", "", $comm);
  $comm = str_replace('[longdash]','&mdash;&mdash;&mdash;', $comm);
  //$comm = str_replace('[noparse]', '', $comm);
  //$comm = str_replace('[/noparse]','', $comm);
  $comm = preg_replace('#<a id="toc(.*?)>(.*?)</a>#', '$2', $comm); // remove TOC links
  $comm = preg_replace('#<a nam(.*?)</a>#', '', $comm); // remove whatsnew markers
  $comm = preg_replace('#<a id=(.*?)</a>#', '', $comm);
  $comm = preg_replace('#<h2 (.*?)</h2>#', '', $comm); // remove H2 tags, intended for Appendices
  $comm = replacegreekhtml($comm);
  $comm = preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $comm);  // 20170406 remove anchor tags
  $comm = trim($comm);
  return $comm;
}

function processTWcommentaryforexport($commentary, $ch, $vs){
  $comm = $commentary;
  $comm = preg_replace('#<a id="toc(.*?)>(.*?)</a>#', '$2', $comm); // remove TOC links
  $comm = preg_replace('#<a nam(.*?)</a>#', '', $comm); // remove whatsnew markers
  $comm = preg_replace('#<a id=(.*?)</a>#', '', $comm);
  $comm = preg_replace('#<span dir=(.*?)</span>#', '', $comm); // remove hebrew words
  $comm = preg_replace('#<h2(.*?)>(.*?)</h2>#', '\pard\qc\fs28{\b1 $2\b0}\fs20\par\ql\par0', $comm); // handle H2 tags, intended for Appendices
  $comm = preg_replace('#<h3(.*?)>(.*?)</h3>#', '$2', $comm); // handle H3 tags, for TOCs
  $comm = preg_replace('#<p(.*?)>#', '<p>', $comm); // clean up <p> tags, intended for Appendices
  $comm = replacegreekhtml($comm);
  $comm = preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $comm);  // 20170406 remove anchor tags

  $comm = str_replace("<p>", "", $comm);
  $comm = str_replace("</p>", "\par \par ", $comm);

  $comm = str_replace("“", "&ldquo;", $comm);         // convert quotes
  $comm = str_replace("”", "&rdquo;", $comm);
  $comm = str_replace("‘", "&lsquo;", $comm);
  $comm = str_replace("’", "&rsquo;", $comm);
  $comm = str_replace("&#39;", "&rsquo;", $comm);
  $comm = str_replace("—", "&mdash;", $comm);
  $comm = str_replace("–", "&ndash;", $comm);
  $comm = str_replace("ï",  "&iuml;",  $comm);
  $comm = str_replace("[fn]", "", $comm);               // no footnotes for theWord. Why not?
  $comm = str_replace('[longdash]','&mdash;&mdash;&mdash;', $comm);
  $comm = str_replace('[noparse]', '', $comm);
  $comm = str_replace('[/noparse]','', $comm);

  $comm = HTMLtoRTF($comm);

  $comm = str_replace("<blockquote>", "", $comm);
  $comm = str_replace("</blockquote>", "", $comm);

  $comm = str_replace("<strong>", "{\b1 ", $comm);
  $comm = str_replace("</strong>", "\b0}", $comm);
  $comm = str_replace("<u>", "{\ul1 ", $comm);
  $comm = str_replace("</u>", "\ul0}", $comm);
  $comm = str_replace("<i>", "<em>", $comm);
  $comm = str_replace("</i>", "</em>", $comm);
  $comm = str_replace("<em>", "{\i1{", $comm);
  $comm = str_replace("</em>", "}\i0}", $comm);

  $comm = str_replace("<ul>", "", $comm);
  $comm = str_replace("</ul>", "\par0", $comm);
  $comm = str_replace('<ol type="a">', "\par0", $comm); // for Ps 110:1
  $comm = str_replace("<ol>", "", $comm);
  $comm = str_replace("</ol>", "\par0", $comm);
  $comm = str_replace("<li>", "{\li360{\'95 ", $comm);
  $comm = str_replace("</li>", "}\par0}", $comm);
  $comm = str_replace("<sup>", "", $comm);      // take 'em out
  $comm = str_replace("</sup>", "", $comm);
  $comm = str_replace("<br />", "\par0", $comm);  // see John 1:6

  $comm = replacediacritics($comm);
  $comm = handleunprintables($comm);
  //$comm = Utf8ToRtf($comm); // function below


  $comm = trim($comm);
  return $comm;
}

// trying to handle Gk and Hebrew.  No luck
function Utf8ToRtf($utf8_text) {
  $utf8_text = str_replace("n", "parn", str_replace("r", "n", str_replace("rn", "n", $utf8_text)));
  return preg_replace_callback("/([xC2-xF4][x80-xBF]+)/", 'FixUnicodeForRtf', $utf8_text);
}

function FixUnicodeForRtf($matches) {
  return 'u'.hexdec(bin2hex(iconv('UTF-8', 'UTF-16BE', $matches[1]))).'?';
}

function processACCcommentaryforexport($commentary, $ch, $vs){
  $comm = $commentary;
  $comm = preg_replace('#<a id="toc(.*?)>(.*?)</a>#', '$2', $comm); // remove TOC links
  $comm = preg_replace('#<a nam(.*?)</a>#', '', $comm); // remove whatsnew markers
  $comm = preg_replace('#<a id=(.*?)</a>#', '', $comm);
  $comm = preg_replace('#<span dir=(.*?)</span>#', '', $comm); // remove hebrew words
  $comm = preg_replace('#<h2(.*?)>(.*?)</h2>#', '\pard\qc\fs28{\b1 $2\b0}\fs20\par\ql\par0', $comm); // handle H2 tags, intended for Appendices
  $comm = preg_replace('#<h3(.*?)>(.*?)</h3>#', '$2', $comm); // handle H3 tags, for TOCs
  $comm = preg_replace('#<p(.*?)>#', '<p>', $comm); // clean up <p> tags, intended for Appendices
  //$comm = replacegreekhtml($comm);
  $comm = preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $comm);  // 20170406 remove anchor tags

  $comm = str_replace("<p>", "", $comm);
  $comm = str_replace("</p>", "\par \par ", $comm);

  $comm = str_replace("“", "&ldquo;", $comm);         // convert quotes
  $comm = str_replace("”", "&rdquo;", $comm);
  $comm = str_replace("‘", "&lsquo;", $comm);
  $comm = str_replace("’", "&rsquo;", $comm);
  $comm = str_replace("&#39;", "&rsquo;", $comm);
  $comm = str_replace("—", "&mdash;", $comm);
  $comm = str_replace("–", "&ndash;", $comm);
  $comm = str_replace("ï",  "&iuml;",  $comm);
  $comm = str_replace("[fn]", "", $comm);

  $comm = HTMLtoRTF($comm);

  //$comm = str_replace("<blockquote>", "{\ql\fi0\li720\ri0\sb0\sa288\sl0{\f0\fs22\cf13{", $comm);
  //$comm = str_replace("</blockquote>", "}}\par0}", $comm);
  //$comm = str_replace("<blockquote>", "\par ", $comm);
  //$comm = str_replace("</blockquote>", "\par ", $comm);
  $comm = str_replace("<blockquote>", "", $comm);
  $comm = str_replace("</blockquote>", "", $comm);

  $comm = str_replace("<strong>", "{\b1 ", $comm);
  $comm = str_replace("</strong>", "\b0}", $comm);
  $comm = str_replace("<u>", "{\ul1 ", $comm);
  $comm = str_replace("</u>", "\ul0}", $comm);
  $comm = str_replace("<i>", "<em>", $comm);
  $comm = str_replace("</i>", "</em>", $comm);
  $comm = str_replace("<em>", "{\i1{", $comm);
  $comm = str_replace("</em>", "}\i0}", $comm);

  $comm = str_replace("<ul>", "", $comm);
  $comm = str_replace("</ul>", "\par0", $comm);
  //$comm = str_replace('<ol class="lalpha">', "\par0", $comm); // for Ps 110:1
  $comm = str_replace('<ol type="a">', "\par0", $comm); // for Ps 110:1
  $comm = str_replace("<ol>", "", $comm);
  $comm = str_replace("</ol>", "\par0", $comm);
  $comm = str_replace("<li>", "{\li360{\'95 ", $comm);
  $comm = str_replace("</li>", "}\par0}", $comm);
  //$comm = str_replace("<sup>", "\up7", $comm);      // font size issue
  //$comm = str_replace("</sup>", "\up0 ", $comm);
  $comm = str_replace("<sup>", "", $comm);      // take 'em out
  $comm = str_replace("</sup>", "", $comm);
  $comm = str_replace("<br />", "\par0", $comm);  // see John 1:6

  $comm = replacegreekhtml($comm);
  $comm = replacediacritics($comm);
  $comm = handleunprintables($comm);


  $comm = trim($comm);
  return $comm;
}

function HTMLtoRTF($str){
  $table = array('&lsquo;' => "\'91", '&rsquo;' => "\'92", '&ldquo;' => "\'93", '&rdquo;' => "\'94", '&hellip;' => "\'85", '&ndash;' => "\'96", '&mdash;' => "\'97",
                 '&amp;' => "&", '&frac14;' => "\'bc", '&frac12;' => "\'bd", '&frac34;' => "\'be", '&iuml;' => "\'ef", '&scaron;' => "\'9a", '&ecirc;' => "\'ea",
                 '&nbsp;' => "\~", '&copy;' => "\'a9", '&eacute;' => "\'e9", '&divide;' => "\'f7", '&auml;' => "\'e4", '&para;' => "\'b6", '&prime;' => "\'b4",
                 '&ocirc;' => "\'f4", '&auml;' => "\'e4", '&Auml;' => "\'c4", '&aacute;' => "\'e1", '&rarr;' => "->", '&sect;' => "\'a7", '&uuml;' => "\'fc");
  return strtr($str, $table);

  //'&rarr;' => "\u8594"
}

function handleunprintables($com){
  $comm = $com;

  $comm = preg_replace( '/[^[:print:]]/', '',$comm);  // NOTE!  This does not work on the dev server, but it does on the live
  $comm = str_replace(" )", ')', $comm);        // cleanup after Greek removal
  $comm = str_replace("( ", '(', $comm);
  $comm = str_replace("()", '', $comm);
  $comm = preg_replace('#\s+,#', ',', $comm);       // remove spaces before commas
  $comm = preg_replace('#\s+;#', ';', $comm);       // remove spaces before semicolons
  $comm = preg_replace('#\s+#', ' ', $comm);        // replace repeating spaces
  $comm = preg_replace('#(.)\(#', '$1 (', $comm);
  $comm = str_replace("(; ", '(', $comm);
  $comm = str_replace(" )", ')', $comm);
  $comm = str_replace("  (", ' (', $comm);

  return $comm;
}

function processBWverseforexport($vrs){
  $vrse = $vrs;

  $vrse = str_replace("<strong>", "", $vrse);
  $vrse = str_replace("</strong>", "", $vrse);

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&lsquo;", '\'', $vrse);
  $vrse = str_replace("&rsquo;", '\'', $vrse);
  $vrse = str_replace("&ldquo;", '"', $vrse);
  $vrse = str_replace("&rdquo;", '"', $vrse);
  $vrse = str_replace("&mdash;", "--", $vrse);
  $vrse = str_replace("&ndash;", "-", $vrse);
  $vrse = str_replace("&hellip;", "...", $vrse);
  $vrse = str_replace("&iuml;", "i", $vrse);  // 20170428 for naive
  $vrse = str_replace("[separator]", "", $vrse);

  $vrse = str_replace("[fn]", "", $vrse);
  $vrse = str_replace("[pg]", " ", $vrse);
  $vrse = str_replace("[mvh]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[hpbegin]", " ", $vrse);
  $vrse = str_replace("[hpend]", " ", $vrse);
  $vrse = str_replace("[hp]", " ", $vrse);
  $vrse = str_replace("[listbegin]", " ", $vrse);
  $vrse = str_replace("[listend]", " ", $vrse);
  $vrse = str_replace("[lb]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[bq]", " ", $vrse);
  $vrse = str_replace("[/bq]", " ", $vrse);
  $vrse = str_replace("<br />", "", $vrse);  // dunno where these are coming from on the live site

  $notintext = (strpos($vrse, '~')===0);
  $vrse = str_replace("~", "", $vrse);
  $vrse = str_replace("[[", "-x-", $vrse);
  $vrse = str_replace("]]", "-y-", $vrse);
  $vrse = str_replace("[", "[[", $vrse);
  $vrse = str_replace("]", "]]", $vrse);
  $vrse = str_replace("-x-", "[[", $vrse);
  $vrse = str_replace("-y-", "]]", $vrse);
  if($notintext && strpos($vrse, '[[')===false)
    $vrse = '[['.$vrse;
  if($notintext && strpos($vrse, ']]')===false)
    $vrse = $vrse.']]';

  $vrse = str_replace("<em>", "[", $vrse);
  $vrse = str_replace("</em>", "]", $vrse);
  $vrse = str_replace("<i>", "[", $vrse);
  $vrse = str_replace("</i>", "]", $vrse);

  return $vrse;
}

function processACCverseforexport($vrs){
  global $paramark;
  $vrse = $vrs;

  $vrse = str_replace("<strong>", "<b>", $vrse);
  $vrse = str_replace("</strong>", "</b>", $vrse);

  $vrse = str_replace("<em>", "<i>", $vrse);
  $vrse = str_replace("</em>", "</i>", $vrse);

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&lsquo;", '\'', $vrse);
  $vrse = str_replace("&rsquo;", '\'', $vrse);
  $vrse = str_replace("&ldquo;", '"', $vrse);
  $vrse = str_replace("&rdquo;", '"', $vrse);
  $vrse = str_replace("&mdash;", "--", $vrse);
  $vrse = str_replace("&ndash;", "-", $vrse);
  $vrse = str_replace("&hellip;", "...", $vrse);
  $vrse = str_replace("&iuml;", "i", $vrse);  // 20170428 for naive
  $vrse = str_replace("[separator]", "", $vrse);

  $notintext = (strpos($vrse, '~')===0);
  $vrse = str_replace("~", "", $vrse);
  if($notintext && strpos($vrse, '[[')===false)
    $vrse = '[['.$vrse;
  if($notintext && strpos($vrse, ']]')===false)
    $vrse = $vrse.']]';
  if(strpos($vrse, '[[')>0 && strpos($vrse, ']]')===false)
    $vrse = $vrse.']]';

  $vrse = str_replace("[fn]", "", $vrse);
  $vrse = str_replace("[pg]", " ".$paramark." ", $vrse);
  $vrse = str_replace("[mvh]", " ", $vrse);
  $vrse = str_replace("[br]", "<br>", $vrse);
  $vrse = str_replace("[hpbegin]", "<br>\t", $vrse);
  $vrse = str_replace("[hpend]", "<br>", $vrse);
  $vrse = str_replace("[hp]", "<br>\t", $vrse);
  $vrse = str_replace("[listbegin]", "<br>\t", $vrse);
  $vrse = str_replace("[listend]", "<br>", $vrse);
  $vrse = str_replace("[lb]", "<br>\t", $vrse);
  $vrse = str_replace("[bq]", "<br>", $vrse);
  $vrse = str_replace("[/bq]", "<br>", $vrse);
  $vrse = str_replace("<br />", "<br>", $vrse);  // dunno where these are coming from on the live site
  // 20210403 Still not fixed
  // 20210419 supposedly fixed
  //if(substr($vrse, 0, 1)=='<') $vrse = '.'.$vrse; // bug in Accordance Bible import
  return $vrse;
}

function processSSverseforexport($vrs, $fnts){
  $vrse = $vrs;

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&lsquo;", '\'', $vrse);
  $vrse = str_replace("&rsquo;", '\'', $vrse);
  $vrse = str_replace("&ldquo;", '"', $vrse);
  $vrse = str_replace("&rdquo;", '"', $vrse);
  // convert OT quotes to uppercase
  $vrse = preg_replace_callback('#(.*?)<strong>(.*?)</strong>(.*?)#', function($m){return $m[1].strtoupper($m[2]).$m[3];}, $vrse);
  // for two places, Matt 3:3 and Luke 3:4.  dunno
  $vrse = str_replace("​", "", $vrse);

  $vrse = str_replace("&mdash;", "--", $vrse);
  $vrse = str_replace("&ndash;", "-", $vrse);
  $vrse = str_replace("&hellip;", "...", $vrse);
  $vrse = str_replace("&iuml;", "i", $vrse);  // 20170428 for naive

  $vrse = str_replace("[[", "-x-", $vrse);
  $vrse = str_replace("]]", "-y-", $vrse);
  $vrse = str_replace("[", "[[", $vrse);
  $vrse = str_replace("]", "]]", $vrse);
  $notintext = (strpos($vrse, '~')===0);
  $vrse = str_replace("~", "", $vrse);
  if($notintext && strpos($vrse, '[[')===false)
    $vrse = '[['.$vrse;
  if($notintext && strpos($vrse, ']]')===false)
    $vrse = $vrse.']]';
  $vrse = str_replace("-x-", "[[", $vrse);
  $vrse = str_replace("-y-", "]]", $vrse);
  $vrse = str_replace("[[[[", "[[", $vrse);
  $vrse = str_replace("]]]]", "]]", $vrse);

  $vrse = str_ireplace("<em>", "[", $vrse);
  $vrse = str_ireplace("</em>", "]", $vrse);
  $vrse = str_ireplace("<i>", "[", $vrse);
  $vrse = str_ireplace("</i>", "]", $vrse);

  $vrse = processexptfootnotes($vrse, $fnts, 6);
  $vrse = str_replace("[pg]", " ", $vrse);
  $vrse = str_replace("[mvh]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[hpbegin]", " ", $vrse);
  $vrse = str_replace("[hpend]", " ", $vrse);
  $vrse = str_replace("[hp]", " ", $vrse);
  $vrse = str_replace("[listbegin]", " ", $vrse);
  $vrse = str_replace("[listend]", " ", $vrse);
  $vrse = str_replace("[lb]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[bq]", " ", $vrse);
  $vrse = str_replace("[/bq]", " ", $vrse);
  $vrse = str_replace("[separator]", "", $vrse);
  $vrse = str_replace("<br />", "", $vrse);  // dunno where these are coming from on the live site

  return $vrse;
}

function processLogosverseforexport($vrs, $fnts){
  $vrse = $vrs;

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&lsquo;", '\'', $vrse);
  $vrse = str_replace("&rsquo;", '\'', $vrse);
  $vrse = str_replace("&ldquo;", '"', $vrse);
  $vrse = str_replace("&rdquo;", '"', $vrse);
  /*
  // convert OT quotes to uppercase
  $vrse = preg_replace_callback('#(.*?)<strong>(.*?)</strong>(.*?)#', function($m){return $m[1].strtoupper($m[2]).$m[3];}, $vrse);
  // for two places, Matt 3:3 and Luke 3:4.  dunno
  $vrse = str_replace("​", "", $vrse);
  */
  $vrse = str_replace("&mdash;", "--", $vrse);
  $vrse = str_replace("&ndash;", "-", $vrse);
  $vrse = str_replace("&hellip;", "...", $vrse);

  $vrse = str_replace("&iuml;", "i", $vrse);  // 20170428 for naive

  $vrse = str_replace("[pg]", " ", $vrse);
  $vrse = str_replace("[mvh]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[hpbegin]", " ", $vrse);
  $vrse = str_replace("[hpend]", " ", $vrse);
  $vrse = str_replace("[hp]", " ", $vrse);
  $vrse = str_replace("[listbegin]", " ", $vrse);
  $vrse = str_replace("[listend]", " ", $vrse);
  $vrse = str_replace("[lb]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[bq]", " ", $vrse);
  $vrse = str_replace("[/bq]", " ", $vrse);
  $vrse = str_replace("[separator]", "", $vrse);
  $vrse = str_replace("[fn]", "^!^", $vrse);
  $vrse = str_replace("<br />", "", $vrse);  // dunno where these are coming from on the live site

  $vrse = str_replace("[[", "-x-", $vrse);
  $vrse = str_replace("]]", "-y-", $vrse);
  $vrse = str_replace("[", "[[", $vrse);
  $vrse = str_replace("]", "]]", $vrse);
  $notintext = (strpos($vrse, '~')===0);
  $vrse = str_replace("~", "", $vrse);
  if($notintext && strpos($vrse, '[[')===false)
    $vrse = '[['.$vrse;
  if($notintext && strpos($vrse, ']]')===false)
    $vrse = $vrse.']]';
  $vrse = str_replace("-x-", "[[", $vrse);
  $vrse = str_replace("-y-", "]]", $vrse);
  $vrse = str_replace("[[[[", "[[", $vrse);
  $vrse = str_replace("]]]]", "]]", $vrse);

  $vrse = str_replace("^!^", "[fn]", $vrse);
  $vrse = processexptfootnotes($vrse, $fnts, 9);

  return $vrse;
}

function processLogoscommforexport($vrs, $fnts){
  $vrse = $vrs;

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&lsquo;", '\'', $vrse);
  $vrse = str_replace("&rsquo;", '\'', $vrse);
  $vrse = str_replace("&ldquo;", '"', $vrse);
  $vrse = str_replace("&rdquo;", '"', $vrse);
  $vrse = str_replace("&mdash;", "--", $vrse);
  $vrse = str_replace("&ndash;", "-", $vrse);
  $vrse = str_replace("&hellip;", "...", $vrse);
  $vrse = str_replace("&iuml;", "i", $vrse);  // 20170428 for naive

  $vrse = str_replace("<ul>", "", $vrse);
  $vrse = str_replace("</ul>", "", $vrse);
  $vrse = preg_replace('#<ol(.*?)>#i', '', $vrse); // remove all ol

  //$vrse = str_replace("<ol>", "", $vrse);
  $vrse = str_replace("</ol>", "", $vrse);
  $vrse = str_replace("</li>", "", $vrse);
  $vrse = str_replace("</p>", "", $vrse);
  $vrse = str_replace("<li>", "<p class=li>", $vrse);

  // table, mainly for Rev 22:1
  $vrse = preg_replace('#<table(.*?)>(.*?)</table>#i', '$2', $vrse);
  $vrse = preg_replace('#<tbody>(.*?)</tbody>#i', '$1', $vrse);
  $vrse = preg_replace('#<tr(.*?)>(.*?)</tr>#i', '$2', $vrse);
  $vrse = preg_replace('#<td(.*?)>(.*?)</td>#i', '<br />$2', $vrse);

  $vrse = str_replace("</blockquote>", "", $vrse);
  $vrse = str_replace("<blockquote>", "<p>", $vrse);
  $vrse = str_replace("<p align=\"left\">", "<p>", $vrse);
  $vrse = str_replace("<br /> ", "<br />", $vrse);
  $vrse = str_replace("<br />&nbsp;", "", $vrse);
  $vrse = preg_replace('#<span(.*?)>(.*?)</span>#i', '$2', $vrse);
  $vrse = preg_replace('#<div(.*?)>(.*?)</div>#i', '$2', $vrse);
  $vrse = preg_replace('#<a(.*?)>(.*?)</a>#i', '$2', $vrse); // remove all anchors
  //$vrse = preg_replace('#<a nam(.*?)</a>#', '', $vrse); // remove whatsnew markers
  //$vrse = preg_replace('#<a id=(.*?)</a>#', '', $vrse);

  $vrse = processexptfootnotes($vrse, $fnts, 9);
  $vrse = str_replace("<noparse>", "", $vrse);
  $vrse = str_replace("</noparse>", "", $vrse);
  $vrse = str_replace("[noparse]", "", $vrse);
  $vrse = str_replace("[/noparse]", "", $vrse);

  return $vrse;
}

function processTWverseforexport($vrs, $fnts){
  $vrse = $vrs;

  $vrse = str_replace("<strong>", "<FO>", $vrse);
  $vrse = str_replace("</strong>", "<Fo>", $vrse);

  $vrse = str_replace("<em>", "<FI>", $vrse);
  $vrse = str_replace("</em>", "<Fi>", $vrse);
  $vrse = str_replace("<i>", "<FI>", $vrse);
  $vrse = str_replace("</i>", "<Fi>", $vrse);

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&#39;", "&rsquo;", $vrse);

  //$notintext = (strpos($vrse, '~')===0);
  //$vrse = str_replace("~[", "[[", $vrse);
  $vrse = str_replace("~", "", $vrse);
  //if($notintext && right($vrse, 1)==']') $vrse.=']';

  $vrse = processexptfootnotes($vrse, $fnts, 4);
  $vrse = str_replace("[fn]", "", $vrse);
  $vrse = str_replace("[pg]", "<CM><PF>", $vrse);

  // no HTML
  $vrse = str_replace("&lsquo;", '\'', $vrse);
  $vrse = str_replace("&rsquo;", '\'', $vrse);
  $vrse = str_replace("&ldquo;", '"', $vrse);
  $vrse = str_replace("&rdquo;", '"', $vrse);
  $vrse = str_replace("&mdash;", "--", $vrse);
  $vrse = str_replace("&ndash;", "-", $vrse);
  $vrse = str_replace("–", "-", $vrse);
  $vrse = str_replace("—", "-", $vrse);
  $vrse = str_replace("ï", "i", $vrse);  //for naive

  $vrse = str_replace("&iuml;", "i", $vrse);  // 20170428 for naive
  $vrse = str_replace("&hellip;", "...", $vrse);
  $vrse = str_replace("…", "...", $vrse);

  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[hpbegin]", "<CM><PF>", $vrse);
  $vrse = str_replace("[hpend]", "<CM>", $vrse);
  $vrse = str_replace("[hp]", "<CI><PF>", $vrse);
  $vrse = str_replace("[listbegin]", "<CM>", $vrse);
  $vrse = str_replace("[listend]", "<CM>", $vrse);
  $vrse = str_replace("[lb]", "<CI><PF>", $vrse);

  $vrse = str_replace("[separator]", "", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("<br />", "", $vrse);
  $vrse = str_replace("[bq]", "<CM>", $vrse);
  $vrse = str_replace("[/bq]", "<CM>", $vrse);
  $vrse = preg_replace('#\s+#', ' ', $vrse);        // replace repeating spaces

  return $vrse;
}

function processTW2verseforexport($vrs){
  $vrse = $vrs;

  // why isn't this showing as RTF?
  $vrse = str_replace("<strong>", "", $vrse);
  $vrse = str_replace("</strong>", "", $vrse);

  $vrse = str_replace("<em>", "[", $vrse);
  $vrse = str_replace("</em>", "]", $vrse);
  $vrse = str_replace("<i>", "[", $vrse);
  $vrse = str_replace("</i>", "]", $vrse);

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&lsquo;", '\'', $vrse);
  $vrse = str_replace("&rsquo;", '\'', $vrse);
  $vrse = str_replace("&ldquo;", '"', $vrse);
  $vrse = str_replace("&rdquo;", '"', $vrse);
  $vrse = str_replace("&mdash;", "--", $vrse);
  $vrse = str_replace("&ndash;", "-", $vrse);
  $vrse = str_replace("&hellip;", "...", $vrse);
  $vrse = str_replace("&iuml;", "i", $vrse);  // 20170428 for naive

  $vrse = str_replace("[separator]", "", $vrse);
  $vrse = str_replace("[fn]", "", $vrse);
  $vrse = str_replace("[pg]", " ", $vrse);
  $vrse = str_replace("[mvh]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[hpbegin]", " ", $vrse);
  $vrse = str_replace("[hpend]", " ", $vrse);
  $vrse = str_replace("[hp]", " ", $vrse);
  $vrse = str_replace("[listbegin]", " ", $vrse);
  $vrse = str_replace("[listend]", " ", $vrse);
  $vrse = str_replace("[lb]", " ", $vrse);
  $vrse = str_replace("[br]", "", $vrse);
  $vrse = str_replace("[bq]", " ", $vrse);
  $vrse = str_replace("[/bq]", " ", $vrse);
  $vrse = str_replace("<br />", "", $vrse);  // dunno where these are coming from on the live site

  $notintext = (strpos($vrse, '~')===0);
  $vrse = str_replace("~", "", $vrse);
  if($notintext && strpos($vrse, '[[')===false)
    $vrse = '[['.$vrse;
  if($notintext && strpos($vrse, ']]')===false)
    $vrse = $vrse.']]';

  return $vrse;
}

function processMSTWheadings($vrs, $head, $scrp, $styl){
  $vrse = $vrs;
  $head = str_replace('[separator]', '', nvl($head,'')); // not handled in exports
  $scrp = str_replace('[separator]', '', nvl($scrp,'')); // not handled in exports
  if($scrp!==''){ // do superscript first
    $scrp = str_replace('[br]', '<br>', $scrp);

    $mvscnt = substr_count($vrse, '[mvs]');
    $arhead = explode('~~', $scrp);
    $idx = 0;
    if($mvscnt < sizeof($arhead)){ // heading before verse
      $vrse = '<TS3>'.$arhead[0].'<Ts>'.$vrse; // no need to add <PF>, it's handled in the calling routine
      $idx = 1;
    }
    while($idx < sizeof($arhead)){ // heading(s) within verse
      $pos = strpos($vrse, '[mvs]');
      if($pos){
        $replace = '<TS3>'.$arhead[$idx].'<Ts>'.(($styl>1)?'<PF>':'');
        $vrse = substr_replace($vrse, $replace, $pos, 5);
      }
      $idx++;
    }
  }
  if($head!==''){
    $head = str_replace('[br]', '<br>', $head);

    $mvhcnt = substr_count($vrse, '[mvh]');
    $arhead = explode('~~', $head);
    $idx = 0;
    if($mvhcnt < sizeof($arhead)){ // heading before verse
      $vrse = '<TS>'.$arhead[0].'<Ts>'.$vrse; // no need to add <PF>, it's handled in the calling routine
      $idx = 1;
    }
    while($idx < sizeof($arhead)){ // heading(s) within verse
      $pos = strpos($vrse, '[mvh]');
      if($pos){
        $replace = '<TS>'.$arhead[$idx].'<Ts>'.(($styl>1)?'<PF>':'');
        $vrse = substr_replace($vrse, $replace, $pos, 5);
      }
      $idx++;
    }
  }
  $vrse = str_replace("[mvh]", " ", $vrse);     // get rid of possible extras
  $vrse = str_replace("[mvs]", " ", $vrse);     // get rid of possible extras
  return $vrse;
}

function processMSverseforexport($vrs, $fnts){
  $vrse = $vrs;

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&#39;", "&rsquo;", $vrse);
  $vrse = str_replace("'", "&rsquo;", $vrse);

  // the bold is not working
  // not sure if it's me or MS
  $vrse = str_replace("<strong>", "<FO>", $vrse);
  $vrse = str_replace("</strong>","<Fo>", $vrse);
  $vrse = str_replace("<em>",     "<FI>", $vrse);
  $vrse = str_replace("</em>",    "<Fi>", $vrse);
  $vrse = str_replace("<i>",      "<FI>", $vrse);
  $vrse = str_replace("</i>",     "<Fi>", $vrse);
  $vrse = str_replace("<br />",   "",     $vrse);        // where are these coming from?

  $vrse = str_replace("~", "", $vrse);

  $vrse = processexptfootnotes($vrse, $fnts, 2);
  $vrse = str_replace("[fn]", "", $vrse); // these can probably be converted to MS notes.
  $vrse = str_replace("[pg]", "<CM> ", $vrse);

  $vrse = str_replace("[hpbegin]", "<CM> <CI><PF>", $vrse); // works, the space is needed
  $vrse = str_replace("[hpend]", "<CM>", $vrse);
  $vrse = str_replace("[hp]", "<CI><PF>", $vrse);
  $vrse = str_replace("[separator]", "", $vrse);

  $vrse = str_replace("[listbegin]", "<PI1>", $vrse);
  $vrse = str_replace("[listend]", "<CM>", $vrse);
  $vrse = str_replace("[lb]", "<br>", $vrse);

  $vrse = str_replace("[bq]", "<CM>", $vrse);   // this is acceptable
  $vrse = str_replace("[/bq]", "<CM>", $vrse);

  $vrse = str_replace("[br]", "", $vrse);
  $vrse = preg_replace('#\s+#', ' ', $vrse);        // replace repeating spaces

  return $vrse;
}

//
// Used for MySword commentary exports when verses are included
// process as HTML
//
function processMSHTMLverseforexport($vrs){
  $vrse = $vrs;

  $notintext = (strpos($vrse, '~')===0);
  $vrse = str_replace("~", "", $vrse);
  if($notintext && strpos($vrse, '[[')===false)
    $vrse = '[['.$vrse;
  if($notintext && strpos($vrse, ']]')===false)
    $vrse = $vrse.']]';

  $vrse = str_replace("[fn]", "", $vrse);
  $vrse = str_replace("[pg]", "<p>", $vrse);

  $vrse = str_replace("[mvh]", "<p>", $vrse);

  $vrse = str_replace("“", "&ldquo;", $vrse);
  $vrse = str_replace("”", "&rdquo;", $vrse);
  $vrse = str_replace("‘", "&lsquo;", $vrse);
  $vrse = str_replace("’", "&rsquo;", $vrse);
  $vrse = str_replace("&#39;", "&rsquo;", $vrse);
  $vrse = str_replace("'", "&rsquo;", $vrse);

  $vrse = str_replace("[br]", "<br />", $vrse);
  $vrse = str_replace("[hpbegin]", "<br />", $vrse);
  $vrse = str_replace("[hpend]", "<br />", $vrse);
  $vrse = str_replace("[hp]", "<br />", $vrse);
  $vrse = str_replace("[listbegin]", " ", $vrse);
  $vrse = str_replace("[listend]", " ", $vrse);
  $vrse = str_replace("[lb]", " ", $vrse);
  $vrse = str_replace("[br]", "<br />", $vrse);
  $vrse = str_replace("[bq]", " ", $vrse);
  $vrse = str_replace("[/bq]", " ", $vrse);
  $vrse = str_replace("[separator]", "", $vrse);
  $vrse = trim(preg_replace('#\s+#', ' ', $vrse));        // replace repeating spaces

  return $vrse;
}

function processBWcommentaryforexport($commentary, $ch, $vs){
  $comm = $commentary;
  $comm = str_replace("{", "(", $comm);                 // remove curly braces
  $comm = str_replace("}", ")", $comm);
  $comm = str_replace("<strong>", "<b>", $comm);        // convert <strong> to <b>
  $comm = str_replace("</strong>", "</b>", $comm);
  $comm = str_replace("<em>", "<i>", $comm);            // convert <em> to <i>
  $comm = str_replace("</em>", "</i>", $comm);

  $comm = str_replace("“", "&ldquo;", $comm);         // convert quotes
  $comm = str_replace("”", "&rdquo;", $comm);
  $comm = str_replace("‘", "&lsquo;", $comm);
  $comm = str_replace("’", "&rsquo;", $comm);
  $comm = str_replace("&lsquo;", '\'', $comm);
  $comm = str_replace("&rsquo;", '\'', $comm);
  $comm = str_replace("&ldquo;", '"', $comm);
  $comm = str_replace("&rdquo;", '"', $comm);

  $comm = str_replace("&#39;", "'", $comm);
  $comm = str_replace("&quot;", '"', $comm);

  $comm = str_replace("&prime;", '`', $comm);           // convert html entities to ascii
  $comm = str_replace("&mdash;", "-", $comm);
  $comm = str_replace("&ndash;", "-", $comm);
  $comm = str_replace("&hellip;", "...", $comm);
  $comm = str_replace("&frac14;", "1/4", $comm);
  $comm = str_replace("&frac12;", "1/2", $comm);
  $comm = str_replace("&frac34;", "3/4", $comm);
  $comm = str_replace("&copy;", "", $comm);
  $comm = str_replace("&para;", "paragraph", $comm);
  $comm = str_replace("&auml;", "a", $comm);
  $comm = str_replace("&Auml;", "A", $comm);
  $comm = str_replace("&iuml;", "i", $comm);
  $comm = str_replace("&uuml;", "u", $comm);
  $comm = str_replace("&eacute;", "e", $comm);
  $comm = str_replace("&aacute;", "a", $comm);
  $comm = str_replace("&rarr;", "->", $comm);
  $comm = str_replace("&sect;", "section ", $comm);
  $comm = str_replace("&ecirc;", "e", $comm);
  $comm = str_replace("&ocirc;", "o", $comm);
  $comm = str_replace("&divide;", "/", $comm);
  $comm = str_replace("&thinsp;", "", $comm);
  $comm = str_replace("&amp;", "&", $comm);
  $comm = str_replace("&iuml;", "i", $comm);  // 20170428 for naive

  $comm = str_replace("</p>", "<p>", $comm);
  $comm = str_replace("[fn]", "", $comm);               // remove footnotes
  $comm = str_replace("[", "[[", $comm);
  $comm = str_replace("]", "]]", $comm);
  $comm = str_replace("<blockquote>", "", $comm);       // remove blockquotes, no support
  $comm = str_replace("</blockquote>", "", $comm);
  $comm = str_replace("<ul>", "<p>", $comm);            // convert ordered and unordered lists
  $comm = str_replace("</ul>", "", $comm);
  $comm = str_replace("<ol>", "<p>", $comm);
  $comm = str_replace("</ol>", "", $comm);
  $comm = str_replace("<li>", " * ", $comm);
  $comm = str_replace("</li>", "<p>", $comm);
  $comm = str_replace("<u>", "", $comm);                // remove underline tags, no support
  $comm = str_replace("</u>", "", $comm);
  $comm = str_ireplace('<span dir="rtl">', '', $comm);  // remove hebrew spans
  $comm = str_replace('</span>', '', $comm);
  $comm = preg_replace('#<a nam(.*?)</a>#', '', $comm); // remove whatsnew markers
  $comm = preg_replace('#<a id=(.*?)</a>#', '', $comm);

  // remove <i> tags nested in <b> tags, no support
  $comm = preg_replace_callback('#<b>((?:(?!<\/b>).)*?(?=<i>))(.*?)<\/b>#',
                                function($matches){return preg_replace('#<i>(.*?)</i>#', '</b><i>$1</i><b>', $matches[0]);},
                                $comm);

  $comm = str_replace('<br />','<p>', $comm);
  $comm = str_replace(PHP_EOL, ' ', $comm);
  $comm = str_replace(crlf, ' ', $comm);
  $comm = str_replace("\n", ' ', $comm);

  // Bibleworks notes are ascii only
  // remove greek and hebrew
  $comm = replacegreekhtml($comm);
  $comm = replacediacritics($comm);
  $comm = preg_replace( '/[^[:print:]]/', '',$comm);  // NOTE!  This does not work on the dev server, but it does on the live
  $comm = str_replace(" )", ')', $comm);        // cleanup after Greek removal
  $comm = str_replace("( ", '(', $comm);
  $comm = str_replace("()", '', $comm);
  $comm = preg_replace('#\s+,#', ',', $comm);       // remove spaces before commas
  $comm = preg_replace('#\s+;#', ';', $comm);       // remove spaces before semicolons
  $comm = preg_replace('#\s+#', ' ', $comm);        // replace repeating spaces
  $comm = str_replace("(; ", '(', $comm);
  $comm = str_replace(";)", ')', $comm);
  $comm = str_replace("<p> ", '<p>', $comm);
  $comm = preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $comm);  // 20170406 remove anchor tags

  return $comm;
}

function processSScommentaryforexport($commentary, $ch, $vs){
  $comm = $commentary;
  $comm = preg_replace('#<a name="m(.*?)</a>#', '', $comm); // remove whatsnew markers
  $comm = preg_replace('#<a id="m(.*?)</a>#', '', $comm);
  $comm = preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $comm);  // 20170406 remove anchor tags
  $comm = str_ireplace('<span dir="rtl">', '', $comm);  // remove hebrew spans
  $comm = str_replace('</span>', '', $comm);
  $comm = replacegreekhtml($comm);
  // removes all nonprintables (Greek, Hebrew, etc)
  //$comm = replacediacritics($comm);
  //$comm = preg_replace( '/[^[:print:]]/', '',$comm);  // NOTE!  This does not work on the dev server, but it does on the live
  //
  // 20210227: I fixed it!  the BOM must be prepended to the output file in double quotes (")
  //
  //$comm = str_replace(" )", ')', $comm);        // cleanup after Greek removal
  //$comm = str_replace("( ", '(', $comm);
  //$comm = str_replace("()", '', $comm);
  //$comm = preg_replace('#\s+,#', ',', $comm);       // remove spaces before commas
  //$comm = preg_replace('#\s+;#', ';', $comm);       // remove spaces before semicolons
  //$comm = preg_replace('#\s+#', ' ', $comm);        // replace repeating spaces
  //$comm = str_replace("(; ", '(', $comm);
  //$comm = str_replace(" ;)", ')', $comm);
  $comm = str_replace('[longdash]','&mdash;&mdash;&mdash;', $comm);
  //$comm = str_replace('[noparse]', '', $comm);
  //$comm = str_replace('[/noparse]','', $comm);
  //$comm = str_replace('[fn]','', $comm);
  $comm = str_replace("<p> ", '<p>', $comm);
  return $comm;
}

function replacegreekhtml($cm){

  $cm = str_ireplace("&Alpha;"  , "α", $cm);
  $cm = str_ireplace("&Beta;"   , "β", $cm);
  $cm = str_ireplace("&Gamma;"  , "γ", $cm);
  $cm = str_ireplace("&Delta;"  , "δ", $cm);
  $cm = str_ireplace("&Epsilon;", "ε", $cm);
  $cm = str_ireplace("&Zeta;"   , "ζ", $cm);
  $cm = str_ireplace("&Eta;"    , "η", $cm);
  $cm = str_ireplace("&Theta;"  , "θ", $cm);
  $cm = str_ireplace("&Iota;"   , "ι", $cm);
  $cm = str_ireplace("&Kappa;"  , "κ", $cm);
  $cm = str_ireplace("&Lambda;" , "λ", $cm);
  $cm = str_ireplace("&Mu;"     , "μ", $cm);
  $cm = str_ireplace("&Nu;"     , "ν", $cm);
  $cm = str_ireplace("&Xi;"     , "ξ", $cm);
  $cm = str_ireplace("&Omicron;", "ο", $cm);
  $cm = str_ireplace("&Pi;"     , "π", $cm);
  $cm = str_ireplace("&Rho;"    , "ρ", $cm);
  $cm = str_ireplace("&Sigma;"  , "σ", $cm);
  $cm = str_ireplace("&Tau;"    , "τ", $cm);
  $cm = str_ireplace("&Upsilon;", "υ", $cm);
  $cm = str_ireplace("&Phi;"    , "φ", $cm);
  $cm = str_ireplace("&Chi;"    , "χ", $cm);
  $cm = str_ireplace("&Psi;"    , "ψ", $cm);
  $cm = str_ireplace("&Omega;"  , "ω", $cm);

  $cm = str_ireplace("&thetasym;","ϑ", $cm);
  $cm = str_ireplace("&upsih;"  , "ϒ", $cm);
  $cm = str_ireplace("&piv;"    , "ϖ", $cm);
  $cm = str_ireplace("&sigmaf;" , "ς", $cm);

  return $cm;
}

function getexporttimestamp($format = 0){
  switch($format){
  case 1:
    $format = '_Ymd_His'; break;
  case 2:
    $format = '_Ymd_Hi'; break;
  case 3:
    $format = '_Ymd'; break;
  case 4:
    $format = '_ymd'; break;
  default:
    $format = ' m/d/Y'; break;
  }
  try {
    $dateTime = new DateTime(null ?? '', new DateTimeZone('America/Denver'));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return 'oops';
  }
}
function getuserdate($date, $userTimeZone = 'America/New_York'){
  $format = 'n/j/Y ga';
  if(!($serverTimeZone = date_default_timezone_get())) $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime($date ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}
function deltreee($dir, $deldir) {
  $files = array_diff(scandir($dir), array('.','..'));
  foreach ($files as $file) {
    (is_dir("$dir/$file") && !is_link($dir)) ? deltreee("$dir/$file", $deldir) : unlink("$dir/$file");
  }
  return (($deldir==1)?rmdir($dir):'');
}

function json_export($dir, $what, $name){
  global $docroot;
  $fullname = $dir.'/'.$name.'.json';
  $workfile = $docroot.'/'.$fullname;
  $ni=0;
  switch(strtolower($what)){
  case 'timestamp':
      file_put_contents($workfile, '{"REV_Timestamp":[', FILE_APPEND);
      $t=time();
      $timstamp = gmdate("Y-m-d H:00",$t);
      file_put_contents($workfile, '{"timestamp":"'.$timstamp.'"}', FILE_APPEND);
      break;
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
        //{"book":"Genesis", "chapter":"1", "verse":"1", "versetext":"In the beginning God created the heavens and the earth."},
        $tmp = (($ni>0)?',':'').'{';
        $tmp.= '"book":"'.$bookname.'", ';
        $tmp.= '"chapter":'.$chap.', ';
        $tmp.= '"verse":'.$vers.', ';
        $head='';
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
        }
        $tmp.= '"heading":"'.$head.'", ';
        $tmp.= '"superscript":"'.$row['superscript'].'", ';
        $tmp.= '"paragraph":'.$row['paragraph'].', ';
        $tmp.= '"style":'.$row['style'].', ';
        $tmp.= '"footnotes":"'.$row['footnotes'].'", ';
        $tmp.= '"versetext":"'.preparejsonverse($vtxt).'"';
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
        $tmp.= '"commentary":"'.preparejsoncomm($vtxt).'"';
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
        $vtxt = stripjsonTOC($vtxt);
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

  return '/'.$fullname;
}

function preparejsonverse($vrs){
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
function preparejsoncomm($com){
    $com = preg_replace('#<a nam(.*?)</a>#', '', $com); // remove whatsnew markers
    $com = preg_replace('#<a id=(.*?)</a>#', '', $com);
    $com = preg_replace('#<span dir=(.*?)>(.*?)</span>#', '$2', $com); // remove hebrew RTL span tags
    $com = str_replace('"', '\"', $com);
    return $com;
}
function stripjsonTOC($com){
  if(strpos($com, 'a id="toc_') > 0){
    $com= preg_replace('#<a id="toc_(\d+)_(\d+)_(\d+)_(\d+)_(\d+)(.*?)>#', '[toc$5]', $com);
    $com= preg_replace('#\[toc(\d+)\](.*?)</a>#', '$2', $com);
    $com= preg_replace('#<a id="tocdest_(\d+)_(\d+)_(\d+)_(\d+)_(\d+)(.*?)>#', '[tocdest$5]', $com);
    $com= preg_replace('#\[tocdest(\d+)\](.*?)</a>#', '$2', $com);
  }
  return $com;
}




?>
