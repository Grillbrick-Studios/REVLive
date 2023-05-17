<?php
//
//
//
function getbibcontent($bibtype){
  $ret='<h2>'.(($bibtype==0)?'Abbreviations':'Bibliography').'</h2><ul>';
  $sql = 'select bibentry from bibliography where bibtype = '.$bibtype.' order by bibauthor ';
  $bib = dbquery($sql);
  while($row = mysqli_fetch_array($bib)){
    $ret.= '<li>'.str_replace('[longdash]','&mdash;&mdash;&mdash;', $row[0]).'</li>';
  }
  $ret.= '</ul>';
  return $ret;
}

function getmyrevnote($dat){
  $ret='';
  $dat = ((isset($dat))?$dat:'-1|0|0|0|-1');
  $ardat = explode('|', $dat);
  $redr = ((isset($ardat[0]))?$ardat[0]:-1);
  $test = ((isset($ardat[1]))?$ardat[1]:-1);
  $book = ((isset($ardat[2]))?$ardat[2]:-1);
  $chap = ((isset($ardat[3]))?$ardat[3]:-1);
  $vers = ((isset($ardat[4]))?$ardat[4]:-1);
  if($redr==-1 || $test==-1 || $book==-1 || $chap==-1 || $vers==-1) die('bad data');
  $row = rs('select myrevname from myrevusers where myrevid = '.$redr.' ');
  $redrnam = $row[0];
  if($chap==0 && $vers==0){
    $stitle = $redrnam.'&rsquo;s General Notes';
    $sql = 'select notes from myrevusers where myrevid = '.$redr.' ';
    $row = rs($sql);
    $mynotes = $row[0];
    $hlite = 0;
    $verse = '';
  }else{
    $btitle = getbooktitle($test,$book, (($test<2)?1:0));
    $stitle = $redrnam.'&rsquo;s Notes on '.$btitle.(($test<2)?' '.$chap.':'.$vers:'');
    $sql = 'select ifnull(rd.myrevnotes, \'\'), ifnull(rd.highlight, 0) highlight, ifnull(rd.marginnote, \'\') marginnote, if(v.versetext=\'-\', v.commentary, v.versetext) versetext
            from verse v
            left join myrevdata rd on rd.myrevid = '.$redr.' and v.testament = rd.testament and v.book = rd.book and v.chapter = rd.chapter and v.verse = rd.verse
            where v.testament = '.$test.'
            and v.book = '.$book.'
            and v.chapter = '.$chap.'
            and v.verse = '.$vers.' ';
    $row = rs($sql);
    $mynotes = $row[0];
    $hlite = $row[1];
    $mnote = $row[2];
    $verse = $row[3];
    $verse = str_replace('[pg]', ' ', $verse);
    $verse = str_replace('[hp]', ' ', $verse);
    $verse = str_replace('[hpbegin]', ' ', $verse);
    $verse = str_replace('[hpend]', ' ', $verse);
    $verse = str_replace('[lb]', ' ', $verse);
    $verse = str_replace('[listbegin]', ' ', $verse);
    $verse = str_replace('[listend]', ' ', $verse);
    $verse = str_replace('[bq]', ' ', $verse);
    $verse = str_replace('[/bq]', ' ', $verse);
    $verse = str_replace('[br]', ' ', $verse);
    $verse = str_replace('[fn]', ' ', $verse);
    $verse = str_replace('[mvh]', ' ', $verse);
    $verse = str_replace('[mvs]', ' ', $verse);
    if($test>1 && strlen($verse) > 500) $verse = truncateHtml($verse, 500); // truncate the appendices
  }
  //$ret.= '<h2>'.$stitle.'</h2>';
  if($verse!=''){
    //$ret.= '<hr size="1"><p>'.$btitle.' '.$chap.':'.$vers.') '.$verse.'</p>';
    $ret.= '<p><strong>'.$btitle.(($test<2)?' '.$chap.':'.$vers.')':'').'</strong> '.$verse.'</p>';
  }
  if($mnote!=''){
    $ret.= '<p style="margin-left:80px;"><strong><em>Margin note:</em> </strong>'.$mnote.'</p>';
  }
  if($mynotes!=''){
    //$ret.= 'My notes: '.$mynotes;
    $ret.= '<hr><blockquote>'.$mynotes.'</blockquote><hr>';
    //$ret.= '<div style="border:1px solid black">'.$mynotes.'</div>';
  }

  return $ret;
}
function getbookcomm(){
  global $test, $book, $vsfncnt, $parachoice;
  $btitle = getbooktitle($test, $book, 0);
  $sql = 'select ifnull(tagline, title), comfootnotes, commentary from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $ret = '<h2>Introduction to '.$row[0].'</h2>';
  if(!$row){
    $ret.='NO DATA';
  }elseif($row[2]==null){
    $ret.='<p>Sorry, there is no content for '.$row[0].'.</p>';
  }else{
    $vsfncnt = 0;
    $ret = $row['commentary'];
    $ret = nvl($ret, "-");
    $ret = fixcommforWord($ret);
    $comfootnotes = $row['comfootnotes'];
    // handle new commentary footnotes
    $comfootnotes = getfootnotes($test, $book, 0, 0, 'com');

    $ret = preg_replace('#\[fn-\d+\]#', '[fn]', $ret);
    //
    $ret = processfootnotes_docx($ret, $comfootnotes);
    $ret ='<h2>Introduction to '.$row[0].'</h2>'.$ret;  // <-- this is a jerryism
    //die($ret);
  }
  return $ret;
}


function getcommentary($pdf=0){
  global $test, $book, $chap, $parachoice, $vsfncnt;
  $btitle = getbooktitle($test,$book,0);
  $babbr = getshortbookabbr($test,$book);
  $ret ='<h3>'.$btitle.' Chapter '.$chap.'</h3>';
  $sql = 'select verse, comfootnotes, commentary from verse where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' order by chapter, verse ';
  $com = dbquery($sql);
  while($row = mysqli_fetch_array($com)){
    $commentary = $row['commentary'];
    $commentary = (($commentary)?$commentary:'-');
    if($commentary!='-'){
      if($pdf==1){
        $secthead = '<p style="font-weight:bold;text-indent:.01%;margin-bottom:0;">'.$chap.':'.$row['verse'].'</p>';
        if(left($commentary, 11)=='<p><strong>')
          $commentary = '<p style="text-indent:.01%;margin-top:0">'.substr($commentary, 3);
      }else{
        $commentary = '<p class="rVersHead" style="justify:left;text-indent:.01%;">'.$babbr.' '.$chap.':'.$row['verse'].'</p>'.$commentary;
        $secthead = '';
      }
      $commentary = fixcommforWord($commentary);
      $vsfncnt=0;
      // handle new commentary footnotes
      $comfootnotes = getfootnotes($test, $book, $chap, $row['verse'], 'com');
      //
      $commentary = processfootnotes_docx($commentary, $comfootnotes);
      $ret.=$secthead.$commentary;
    }
  }
  //die($ret);
  return $ret;
}

function getappxintro(){
  global $test, $book, $vsfncnt;
  $sql = 'select comfootnotes, commentary from verse where testament = '.$test.' and book = '.$book.' and chapter = 1 and verse = 1 ';
  $row = rs($sql);
  $ret = $row['commentary'];
  $ret = (($ret)?$ret:'No Content!');
  $ret = fixcommforWord($ret);
  $ret = str_replace('[longdash]','&mdash;&mdash;&mdash;', $ret);
  $vsfncnt = 0;
  // handle new commentary footnotes
  $comfootnotes = getfootnotes($test, $book, 1, 1, 'com');
  //

  $ret = processfootnotes_docx($ret, $comfootnotes);
  //die($ret);
  return $ret;
}

function getoutline(){
  global $test, $book;
  $ret = '';
  $sql = 'select ifnull(tagline,concat(\'The Book of \', title)) tagline from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $btitle = $row[0];
  $ret.= '<h3 style="text-align:center;">Outline for '.$btitle.'</h3>';
  $sql = 'select chapter, verse, level, heading, reference, link from outline
          where testament = '.$test.' and book = '.$book.' and inoutline=1
          order by chapter, verse, level ';
  $lastlvl=0;
  $ni=0;
  $qry = dbquery($sql);
  $ret.= '<ol>';
  while($row = mysqli_fetch_array($qry)){
    $lvl = $row['level'];
    if($lastlvl==1 && $lvl==0) $ret.= '</ol></li>';
    if($lastlvl==0 && $lvl==1) $ret.= '<ol type="A">';
    $heading = str_replace('~','',$row['heading']);
    $heading = str_replace('[br]',' ',$heading);
    $ret.= '<li>'.$heading.' <small>('.$row['reference'].')</small>';
    $lastlvl = $lvl;
    $ni++;
  }
  $ret.= '</li></ol>';
  if($ni==0) $ret = '<h3 style="text-align:center;">Outline for '.$btitle.'</h3><p>Sorry, there is no outline data for this book yet.</p>';
  //die($ret);
  return $ret;
}

function getverscomm(){
  global $test, $book, $chap, $vers, $parachoice, $vsfncnt;
  $btitle = getbooktitle($test,$book, 0);
  $ret = '<h2>REV Commentary for: '.$btitle.' '.$chap.':'.$vers.'</h2>';

  $sql = 'select versetext, heading, footnotes, comfootnotes, commentary from verse where book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  $theverse    = $row['versetext'];
  $footnotes   = $row['footnotes'];
  $commentary  = $row['commentary'];
  $comfootnotes= $row['comfootnotes'];
  $vsfncnt     = substr_count($row['heading'].'', '[fn]');
  $arfn = array();
  $theverse = processfootnotes_htm($theverse, $footnotes, $vers);
  $theverse = str_replace('[mvh]','[pg]', $theverse);
  $theverse = str_replace('[mvs]','[pg]', $theverse);
  $theverse = str_replace('[pg]','<br /><br />', $theverse);
  $theverse = str_replace('[bq]','<br /><br />', $theverse);
  $theverse = str_replace('[/bq]',' ', $theverse);
  $theverse = str_replace('[br]','', $theverse);
  $theverse = str_replace('[hp]','<br />', $theverse);
  $theverse = str_replace('[hpbegin]','<br />', $theverse);
  $theverse = str_replace('[hpend]','<br />', $theverse);
  $theverse = str_replace('[lb]','<br />', $theverse);
  $theverse = str_replace('[listbegin]','<br />', $theverse);
  $theverse = str_replace('[listend]','<br />', $theverse);


  if(substr($theverse, 0, 1)=='~'){
      //$theverse = '~[['.substr($theverse,1).']]';
      $theverse = '[['.substr($theverse,1).']]';
      $theverse = str_replace(']]]]', ']]', $theverse);
      $theverse = str_replace('[[[[', '[[', $theverse);
  }
  $theverse = fixverse($theverse);
  $theverse = fixverseforWord($theverse);

  $commentary = nvl($commentary, 'No Commentary...yet');

  // handle new commentary footnotes
  $comfootnotes = getfootnotes($test, $book, $chap, $vers, 'com');
  //

  $commentary = fixcommforWord($commentary);
  $vsfncnt = 0;
  $commentary = processfootnotes_docx($commentary, $comfootnotes);

  $ret.='<p style="text-align:left;text-indent:.01%;">'.$theverse.'</p>';
  $ret.=printfootnotes_htm(); // this prints the verse footnotes

  $ret.='<hr />';
  $ret.=$commentary;
  //die($ret);
  return $ret;
}

function getbible($printbkhead){
  global $test, $book, $chap, $vers;
  global $useoefirst, $parachoice, $vsfncnt;
  global $versebreak, $arfn, $viewcols;

  // not handling reading mode for MSW exports
  if($versebreak>1) $versebreak=0;

  $ret = '';
  $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
  $numchaps = $row[0];
  $oldchap = -1;
  $btitle = getbooktitle($test,$book, 0);
  $sql = 'select v.chapter, v.verse, v.versetext,
          (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
          ifnull(v.heading,\'noscript\') superscript, v.paragraph, v.style, v.footnotes
          from verse v
          where v.testament = '.$test.'
          and v.book = '.$book.' ';
  if($chap>0) $sql .= 'and v.chapter = '.$chap.' ';
  $sql .= 'order by v.chapter, v.verse ';
  $verses = dbquery($sql);
  $inhp = 0;
  $inlist = 0;
  $prevstyle = 0;
  $td1width = (($viewcols>1)?6:4);
  $td2width = 100-$td1width;

  while($row = mysqli_fetch_array($verses)){
    $chapter = $row['chapter'];
    $versnum = $row['verse'];
    $havepara = (($row['paragraph']==1)?1:0);
    $footnotes = $row['footnotes'];
    if($chapter != $oldchap){
      if($chapter==1 || $printbkhead==1){
        $sql = 'select ifnull(tagline, concat(\'The Book of \', title)) tagline from book where testament = '.$test.' and book = '.$book.' ';
        $roww = rs($sql);
        $ret.='<h2>'.$roww['tagline'].'</h2>';
      }
      $ret.='<h3>'.(($book==19)?'Psalm '.$chapter:$btitle.(($numchaps>1)?' Chapter '.$chapter:'')).'</h3>';
      $oldchap = $chapter;
    }

    // initialize everything
    $pvhead='';$pvpara='';$pvvnum='';$pvvimg='';$pvvers='';$pvpost='';
    $versbr='';$beginstub='';$endstub='';$havevnum=0;$needsep=0;$sep=0;$vsfncnt=0;

    $verse = trim($row['versetext']);
    $style = $row['style'];   // 1=prose, 2>=poetry, 5>=list

    if($versebreak==1){
      $verse = str_replace('[pg]', ' ', $verse);
      $verse = str_replace('[hp]', ' ', $verse);
      $verse = str_replace('[hpbegin]', ' ', $verse);
      $verse = str_replace('[hpend]', ' ', $verse);
      $verse = str_replace('[lb]', ' ', $verse);
      $verse = str_replace('[listbegin]', ' ', $verse);
      $verse = str_replace('[listend]', ' ', $verse);
      $verse = str_replace('[bq]', ' ', $verse);
      $verse = str_replace('[/bq]', ' ', $verse);
      $style=1;
    }
    $verse = str_replace('[separator]', '', $verse);

    // handle heading
    if($row['headcount'] > 0){
      $havess = (($row['superscript'] != 'noscript')?1:0);
      $nextp='<p style="margin-top:0;margin-bottom:0;'.(($versebreak==1)?'text-indent:.01%;':'').'">';
      $sql = 'select heading, level, reference from outline where testament = '.$test.' and book = '.$book.' and chapter = '.$chapter.' and verse = '.$versnum.' and link=1 order by level ';
      $heds = dbquery($sql);
      $hdcnt=0;$head='';
      while($rrow = mysqli_fetch_array($heds)){
        //if($hdcnt>0) $head.= '[br]&nbsp;&nbsp;&nbsp;';
        if($hdcnt>0) $head.= '[br]';
        $head.= $rrow[0];
        if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
        $hdcnt++;
      }
      $head = str_replace('[br]', '<br />', $head);
      if($head == '[separator]') $sep=1;
      $head = str_replace('[separator]', '<img src="/i/pgdivider.png" style="width:300px;" />', $head);

      // try to handle multiple headings, mainly for Song of Songs 5
      $mvhcnt = substr_count($verse, '[mvh]');
      $arhead = explode('~~', $head);
      $idx = 0;
      $headclass = 'rVersHead';
      if($mvhcnt < sizeof($arhead)){
        if($inhp==1 || $inlist==1){
          $pvhead.= '</table>';
          if($sep==1){
            $pvhead.=$head.(($style==1)?'':'<table>');
          }else{
            $pvhead.='<p class="'.$headclass.'">'.fixverse($arhead[0]).'</p>'.(($style==1)?$nextp:'<table>');
          }
        }else{
          $pvhead.= (($versnum==1 || $versebreak==1)?'':'</p>').'<p class="'.$headclass.'">'.fixverse($arhead[0]).'</p>'.(($havess==0 && ($style==1 || strpos($verse, '[hpbegin]')>0 || strpos($verse, '[listbegin]')>0))?$nextp:'');
        }
        $pvhead = processfootnotes_docx($pvhead, $footnotes, $versnum);
        $havepara = 0;
        $idx = 1;
      }
      while($idx < sizeof($arhead)){
        $pos = strpos($verse, '[mvh]');
        if($pos){
          if($inhp==1 || $style>1){
            $replace = '</td></tr></table><p class="'.$headclass.'">'.fixverse($arhead[$idx]).'</p><table><tr><td style="width:'.$td1width.'%;"></td><td style="width:'.$td2width.'%"><p class="rHP">';
          }else{
            $replace = '</p><p class="'.$headclass.'">'.fixverse($arhead[$idx]).'</p>'.$nextp;
          }
          $verse = substr_replace($verse, $replace, $pos, 5);
        }
        $idx++;
      }
    }

    // handle superscript
    if($row['superscript'] != 'noscript'){
      $nextp='<p style="margin-top:0;margin-bottom:0;'.(($versebreak==1)?'text-indent:.01%;':'').'">';
      $sscript = str_replace('[br]', '<br />', $row['superscript']);

      // try to handle multiple superscripts, mainly for Song of Songs 5
      $mvscnt = substr_count($verse, '[mvs]');
      $arhead = explode('~~', $sscript);
      $idx = 0;
      $headclass = 'rVersHeadSm';
      if($mvscnt < sizeof($arhead)){
        if($inhp==1 || $inlist==1){
          $pvhead.= '</table>';
          $pvhead.='<p class="'.$headclass.'">'.fixverse($arhead[0]).'</p>'.(($style==1)?$nextp:'<table>');
        }else{
          $pvhead.= (($versnum==1 || $versebreak==1)?'':'</p>').'<p class="'.$headclass.'">'.fixverse($arhead[0]).'</p>'.(($style==1 || strpos($verse, '[hpbegin]')>0 || strpos($verse, '[listbegin]')>0)?$nextp:'');
        }
        $pvhead = processfootnotes_docx($pvhead, $footnotes, $versnum);
        $havepara = 0;
        $idx = 1;
      }
      while($idx < sizeof($arhead)){
        $pos = strpos($verse, '[mvs]');
        if($pos){
          if($inhp==1 || $style>1){
            $replace = '</td></tr></table><p class="'.$headclass.'">'.fixverse($arhead[$idx]).'</p><table><tr><td style="width:'.$td1width.'%;"></td><td style="width:'.$td2width.'%"><p class="rHP">';
          }else{
            $replace = '</p><p class="'.$headclass.'">'.fixverse($arhead[$idx]).'</p>'.$nextp;
          }
          $verse = substr_replace($verse, $replace, $pos, 5);
        }
        $idx++;
      }
    }

    // all one...  no OE image, but it woould be nice
    //$pvvnum = '<sup class="rSupStyle">'.$versnum.'</sup>';

    // get and format versnum (images are disabled, see 1==2
    if(1==2 && $versnum==1 && $versebreak==0 && $useoefirst==1 && $style==1){
      $notintext = 0;
      if(substr($verse,0,1) == '~'){
        $verse = substr($verse,2);
        $notintext = 1;
      }
      if(substr($verse,0,2) == '"\'')     $verse = substr($verse,2); // for OT verses
      if(substr($verse,0,7) == '&ldquo;') $verse = substr($verse,7);
      if(substr($verse,0,2) == '[[')      $verse = substr($verse,2,1).'[['.substr($verse,3);
      if(substr($verse,0,1) == '"')       $verse = substr($verse,1);
      if(substr($verse,0,4) == '<em>')    $verse = substr($verse,4,1).'<em>'.substr($verse,5);
      $tmp = strtolower(substr($verse,0,1));
      $verse = substr($verse,1);
      $verse = (($notintext==1)?'~':'').$verse;
      //$pvvimg = '<img src="/i/'.$tmp.'.png" border="0" style="float:left;margin-top:-3px;margin-left:4px;height:2.1em" />';
      $pvvimg = '<img src="/i/'.$tmp.'.png" border="0" style="float:left;margin-left:4px;height:2.1em;" />';
    }else{
      $pvvnum = '<sup class="rSupStyle">'.$versnum.'</sup>';
    }

    if($versebreak==1) $pstyles = ' style="margin-bottom:0;margin-top:'.(($havepara==1 && $pvhead=='')?'16px':'5px').';text-indent:.01%;"';
    else $pstyles = ' style="margin-bottom:0;margin-top:'.(($havepara==1 && $pvhead=='')?'16px':'0').';"';

    $paraboth= '</p><p'.$pstyles.'>';

    switch($style){
    case 1: // prose
      $pvpara.= (($versebreak==1 && $pvhead=='')?'<p'.$pstyles.'>':(($havepara==1)?(($inlist==1 || $inhp==1)?'<p'.$pstyles.'>':$paraboth):''));
      if($inhp==1 || $inlist==1){
        $inhp = 0;
        $inlist = 0;
        if($pvhead==''){$pvhead = '</table>';}
      }
      if(left($verse, 4) == '[br]'){
        if(!$versebreak) $versbr = '<br />';
        $verse = substr($verse, 4);
      }
      if(left($verse, 4) == '[bq]'){
        $versbr = '<blockquote><p>';
        $verse = substr($verse, 4);
      }
      if(left($verse, 5) == '[/bq]'){
        $versbr = '</blockquote><p'.$pstyles.'>';
        $verse = substr($verse, 5);
      }
      if(right($verse, 4) == '[br]') $verse = substr($verse, 0,-4);
      $verse = str_replace('[pg]', '</p><p>', $verse);
      $verse = str_replace('[bq]', '<blockquote><p>', $verse);
      $verse = str_replace('[/bq]', '</p></blockquote>', $verse);
      $verse = str_replace('[br]','<br />', $verse);
      $pvvnum = $versbr.$pvvnum;
      $pvpost= (($versebreak==1 && right($verse, 13) != '</blockquote>')?'</p>':' ');
      break;
    case 2:  // poetry
    case 3:  // poetry_NB
    case 4:  // BR_poetry
    case 5:  // BR_poetry_NB
      if((($style==4 || $style==5) && $inhp==1)) $needsep=1;
      $ids = strpos($verse, '[hpbegin]');
      if($ids !== false){
        if($inhp==1) $beginstub = '</table><p style="margin-bottom:0;">';
        if($versnum==1) $beginstub = '<p style="margin-bottom:0;">';
        $beginstub.= $pvvnum.left($verse, $ids);
        // the str_replace([pg]) is for Obadiah 1:1
        $beginstub = str_replace('[pg]', '</p><p style="margin-bottom:0;">', $beginstub).'</p><table>';
        $havevnum = 1;
        $verse = substr($verse, $ids+9);
        $pvpara = (($havepara==1)?(($inhp==1)?'':$paraboth):'');
        $inhp = 1;
        $needsep=1;
      }
      $ids = strpos($verse, '[hpend]');
      if($ids !== false){
        if($inhp==0 && $beginstub=='') $beginstub = '<table>';
        $endstub = '</table><p style="margin-top:16px;margin-bottom:0;">'.substr($verse, $ids+7);
        $verse = left($verse, $ids);
        $inhp = 1;
      }
      $ar = explode('[hp]', $verse);
      if($inhp==0){
        $inhp=1;
        //$needsep=(($versnum>1)?1:0);
        $needsep=(($havepara)?1:0);
        $verse = (($versnum>1)?'</p>':'').'<table><tr>';
      }else $verse = '<tr>';
      $margintop = (($needsep==1)?'margin-top:16px;':'');
      $marginbot = 'margin-bottom:'.(($style==3 || $style==5)?'0;':'5px;');
      //$verse.='<td style="width:'.$td1width.'%;text-align:right;'.$margintop.$marginbot.'">'.(($havevnum==0)?$pvvnum:'').'</td><td style="width:'.$td2width.'%;'.$margintop.$marginbot.'">';
      $verse.='<td style="width:'.$td1width.'%;text-align:right;'.$margintop.$marginbot.'"><p class="rpvnum">'.(($havevnum==0)?$pvvnum:'').'</p></td><td style="width:'.$td2width.'%;'.$margintop.$marginbot.'">';
      for($ni=0;$ni<sizeof($ar);$ni++){
        if(trim($ar[$ni]) != ''){
          $verse.= '<p class="rHP">'.str_replace('[br]', '</p><p class="rHP">', trim($ar[$ni])).'</p>';
        }
      }
      $verse = str_replace('[pg]', '<br />&nbsp;<br />', $verse); // this is primarily for Matt 1:6
      $verse.='</td></tr>';
      $verse = $beginstub.$verse.$endstub;
      if($endstub != '') $inhp = 0;
      $pvvnum = '';
      break;
    case 6: // list
    case 7: // list_END
    case 8: // BR_list
    case 9: // BR_list_END
      $idx = strpos($verse, '[listbegin]');
      if($idx !== false){
        //$beginstub = (($prevstyle>1)?'</table><br />':'').(($prevstyle<2)?(($versebreak==1)?'<p style="margin-top:5px;margin-bottom:0;">':'<p>'):'');
        //$beginstub.= (($havepara==1)?$paraboth:'').$pvvnum;
        $beginstub = (($prevstyle>1)?'</table><p style="margin-bottom:0;">':'');
        $margintop = (($havepara==1)?'16':'5');
        $beginstub.= (($versnum==1)?'<p style="margin-top:'.$margintop.'px;margin-bottom:0;">':'');
        $beginstub.= (($versnum>1 && $versebreak==1)?'<p style="margin-top:'.$margintop.'px;margin-bottom:0;">':'');
        //$beginstub.= (($havepara==1)?$paraboth:'').$pvvnum;
        $beginstub.= $pvvnum;
        $beginstub.= trim(left($verse, $idx));
        $beginstub.= '</p><table><tr><td style="width:'.$td1width.'%;text-align:right;margin-top:16px;margin-bottom:0;"></td><td style="width:'.$td2width.'%;margin-top:16px;margin-bottom:0">';
        $havevnum = 1;
        $verse = trim(substr($verse, $idx+11, 2000));
        $inlist = 1;
      }
      $idx = strpos($verse, '[listend]');
      if($idx !== false){
        $endstub = '</td></tr></table><p style="margin-top:16px;margin-bottom:0;">';
        $endstub .= trim(substr($verse, $idx+9, 2000));
        $verse = trim(left($verse, $idx, 2000));
        $inlist = 1;
      }
      $tmpv = '';
      if($inlist==0){
        $inlist=1;
        $tmpv = (($prevstyle==1)?'</p>':'').(($inhp==0)?'<table>':'');
      }
      if($style==8 || $style==9 || ($havevnum==0 && $versnum==1)){ // br
        $margintop = (($havepara==1)?'margin-top:16px;':'');
        $tmpv.= '<tr><td style="width:'.$td1width.'%;text-align:right;'.$margintop.'">'.$pvvnum.'</td><td style="width:'.$td2width.'%;'.$margintop.'">';
        $havevnum = 1;
      }
      if($havevnum==0) $tmpv.= $pvvnum;
      $tmpv.= $verse;
      $tmpv = str_replace('[lb]', '<br />', $tmpv);
      $verse = $tmpv;
      $verse = $beginstub.$verse.((($style==7 || $style == 9))?'</td></tr>':'').$endstub;
      if($endstub != '') $inlist = 0;
      $pvvnum = '';
      break;
    }
    $verse = processfootnotes_docx($verse, $footnotes);
    $pvvers = fixverse($verse);
    $verse = $pvhead.$pvvimg.$pvpara.$pvvnum.$pvvers.$pvpost;
    $prevstyle = $style;

    $verse = fixverseforWord($verse);
    $ret.=$verse;
  }
  if($inhp==1 || $inlist==1) $ret.='</table>';
  else $ret.='</p>';
  $ret.='<br />';
  $ret = str_replace('</blockquote> </p>', '</blockquote>', $ret);
  $ret = preg_replace('#<p style="margin-bottom:0;margin-top:(.*?);"><blockquote>#', '<blockquote>', $ret);
  /*
  die($ret);
  //*/
  return $ret;
}

function printfootnotes_htm(){
  global $arfn;
  $ret='';
  if(is_array($arfn)) $fncnt = sizeof($arfn);
  else $fncnt = 0;
  if($fncnt > 0){
    $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
    $ret='<p class="footnote">';
    for($nf=0;$nf<($fncnt);$nf++){
      $artmp = explode('~~', $arfn[$nf]);
      $v = right('&nbsp;'.substr($footnoteindicator, ($nf%26), 1), (($artmp[0]<10)?7:1)).'['.$artmp[0].']&nbsp;';
      $ret.=$v;
      $ret.=$artmp[1].'<br />';
    }
    $ret.='</p>';
    $arfn = array();
  }
  return $ret;
}

function processfootnotes_docx($vers, $ftnotes){
  global $vsfncnt;
  $fword = "[fn]";
  $arfnotes = explode('~~', $ftnotes ?? '');
  $havefootnote = ((strpos($vers, $fword)!==false)?strpos($vers, $fword):-1);
  $nf = $vsfncnt;
  while($havefootnote>-1){
    if(isset($arfnotes[$nf]) && $arfnotes[$nf] != ''){
      $tmp = $arfnotes[$nf];
      $tmp = str_replace('<em>', '[em]', $tmp);
      $tmp = str_replace('</em>', '[/em]', $tmp);
      $tmp = str_replace('[noparse]', '', $tmp);
      $tmp = str_replace('[/noparse]', '', $tmp);
      $tmp = '<footnote>&nbsp;'.strip_tags($tmp).'</footnote>';
      $vsfncnt++;
    }
    else $tmp = '';
    // re Matt 10:29, handle dollar signs.  Whodathunkit?
    $tmp = str_replace('$', '\$', $tmp);
    // re John 1:1, handle ampersands in footnotes
    $tmp = str_replace(' & ', ' &amp;amp; ', $tmp);
    $vers = preg_replace('#\[fn\]#', $tmp, $vers, 1);
    $nf++;
    $havefootnote = ((strpos($vers, $fword)!==false)?strpos($vers, $fword):-1);
  }
  return $vers;
}

function processfootnotes_htm($vers, $ftnotes, $v){
  global $arfn, $vsfncnt;
  $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
  $fword = "[fn]";
  $arfnotes = explode('~~', $ftnotes);
  $havefootnote = ((strpos($vers, $fword)!==false)?strpos($vers, $fword):-1);
  $nf = $vsfncnt;
  if(is_array($arfn)) $fncnt = sizeof($arfn);
  else $fncnt = 0;
  //$fncnt = sizeof($arfn);
  while($havefootnote>-1){
    if($arfnotes[$nf] != ''){
      $arfn[$fncnt] = $v.'~~'.$arfnotes[$nf];
      $tmp = (($havefootnote==0)?'&nbsp;':'').'<sup>'.substr($footnoteindicator, ($fncnt%26), 1).'</sup>&nbsp;';
      $fncnt++;
      $vsfncnt++;
    }else{
      $tmp = ' '; // adding space here, cause removing it below
    }
    $vers = substr($vers, 0, ($havefootnote)).$tmp.substr($vers, ($havefootnote+5));
    $nf++;
    $havefootnote = ((strpos($vers, $fword)!==false)?strpos($vers, $fword):-1);
  }
  return $vers;
}

function fixverseforWord($vers){
    $vers = str_replace('<strong>','<span class="rOTQuote">', $vers);
    $vers = str_replace('</strong>','</span>', $vers);
    // 20200406 this might cause issues...
    if(strpos($vers, 'rNotInText')!==0 && strpos($vers, '</span>')===false) $vers.='</span>';
    return $vers;
}

function tidify($htm){
  global $versebreak, $parachoice, $jsonurl;
  $htm = str_replace('</li> <li>','</li><li>', $htm);
  $htm = str_replace('</li> </ol>','</li></ol>', $htm);
  $htm = str_replace('</li> </ul>','</li></ul>', $htm);
  $htm = str_replace('</p> <ol>','</p><ol>', $htm);
  $htm = str_replace('</p> <ul>','</p><ul>', $htm);
  $htm = str_replace('</tr> <tr>', '</tr><tr>', $htm);
  $htm = str_replace('</li> </ul> <p>', '</li></ul><p>', $htm);
  $htm = str_replace('</footnote> ','</footnote>&nbsp;', $htm);
  $htm = undoTOCforMSW($htm);
  $htm = preg_replace('#<a nam(.*?)</a>#', '', $htm); // remove whatsnew markers
  $htm = preg_replace('#<a id=(.*?)</a>#', '', $htm); // remove whatsnew markers
  $htm = str_replace('</blockquote> <br />&nbsp;<br />','</blockquote><br />', $htm); // for when a chapter ends with a blockquote.....nasty
  $htm = str_replace('<br /> <br />&nbsp;<br />','<br />&nbsp;', $htm); // .....nasty
  $htm = str_replace('~~docroot~~',$jsonurl, $htm); // .....nasty

  /* Output
  die($htm);
  //*/

  //$bqparaindent = (($parachoice==3 || $parachoice==4)?1:.01);
  // handle blockquotes
  $posbq= strpos($htm, '<blockquote');
  while($posbq){
    $htmlbegin = left($htm, $posbq);
    $posbqend= strpos($htm, '</blockquote');
    if($posbqend){
      $htmlend = substr($htm, ($posbqend+13));
      $htmlbqlen = $posbqend - ($posbq+12);
    }else{
      $htmlend = '';
      $htmlbqlen = 4000;
    }
    $htmlbq = trim(substr($htm, ($posbq+12), $htmlbqlen));
    $htmlbq = str_replace('<p class="rVersHead">', '[[tmp]]', $htmlbq); // protect verse headings in bq's (Ezra 6:6)
    //$htmlbq = preg_replace('#<p(.*?)>#', '<p class="rQuote">', $htmlbq);
    //$htmlbq = preg_replace('#<p(.*?)>#', '<p class="rQuote" style="text-indent:.01%;text-align:left;">', $htmlbq);
    //$htmlbq = preg_replace('#<p(.*?)>#', '<p style="margin-left:1em;text-indent:'.$bqparaindent.'em;text-align:left;">', $htmlbq);
    //$htmlbq = preg_replace('#<p(.*?)>#', '<p style="margin-left:1em;">', $htmlbq);
    $htmlbq = preg_replace('#<p(.*?)>#', '<p style="margin-left:1.3em;text-indent:.01em;text-align:left;">', $htmlbq);
    $htmlbq = str_replace('[[tmp]]', '<p class="rVersHead" style="text-indent:1.3em;">', $htmlbq);
    $htm = $htmlbegin.$htmlbq.$htmlend;

    $posbq= strpos($htm, '<blockquote');
  }
  /* Output
  die($htm);
  //*/

  //* Tidy
  $config = array(
                  'indent'           => false,
                  'output-xhtml'     => true,
                  'wrap'             => 99999,
                  'preserve-entities'=> true,
                  'show-body-only'   => true,
                  'new-inline-tags' => 'footnote, toc, tocdest'
                 );
  $tidy = new tidy;
  $tidy->parseString($htm, $config, 'utf8');
  $tidy->cleanRepair();
  $htm = $tidy;
  // handle rtl
  $htm = str_replace('dir="rtl"', 'style="direction: rtl;text-align: right;"', (string) $htm);
  $htm = str_replace('<br />'.crlf.'<br /></p>', '</p>', $htm);
  $htm = str_replace('<br /></p>', '</p>', $htm);
  $htm = preg_replace("/\r?\n/m", "", $htm);  // remove line breaks

  /* Output
  die($htm);
  //*/

  return $htm;
}

function loadhtm($docx, $html){
  $docx->embedHTML($html, array('strictWordStyles' => true,
                                'downloadImages' => true,
                                'wordStyles' => array('<h2>' => 'rBookHead',    // must be IDs here
                                                      '<h3>' => 'rChapHead',
                                                      '<h4>' => 'rH4',
                                                      '<p>'  => 'rNormal',
                                                      '.rVersHead' => 'rVersHead',
                                                      '.rVersHeadSm' => 'rVersHeadSm',
                                                      '.rQuote' => 'rQuote',
                                                      '.footnote' => 'rFootNote', // for viewverscomm only
                                                      '.rHP' => 'rHP',
                                                      // the above are all block styles (p's)
                                                      // the below are inline styles (spans)
                                                      '.rNotInText' => 'rNotInText',
                                                      '.rOTQuote' => 'rOTQuote'
                                                      )));
}

function fixcommforWord($com){

  // temporary, until all old whatsnew markers are gone 20180212
  $com = preg_replace('#<a name="marker(.*?)"><\/a>#', '<a id="marker$1"></a>', $com);
  $com = preg_replace('#<a id="marker(.*?)</a>#', '', $com); // remove whatsnew markers

  // misc
  //$com = preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $com);
  //$com = preg_replace('#</ul>\\s?<p>#', '</ul><p>', $com);
  //$com = preg_replace('#</ol>\\s?<p>#', '</ol><p>', $com);
  $com = str_replace('[noparse]', '', $com);
  $com = str_replace('[/noparse]', '', $com);
  //$com = str_replace('[smallcaps]', '<span style="font-variant: small-caps;">', $com);
  //$com = str_replace('[/smallcaps]', '</span>', $com);

  // tryng to handle formatting for paragraph headings
  $com = preg_replace('#<p><strong>([^<]*?)</strong><br />#', '<p style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:20px;text-indent:.01%;">$1</p><p style="margin-top:0;">', $com);
  $com = preg_replace('#<p>\\s?<strong>#', '<p style="text-align:left;text-indent:.01%;"><strong>', $com);
  $com = preg_replace('#<h5 style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:0;"><strong>(.*?)</strong></h5>\\s?<p>#', '<p style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:20px;text-indent:.01%;">$1</p><p style="margin-top:0;">', $com);

  // trying to format LIs
  //$com = str_replace('<br /> &nbsp;</li>', '</li>', $com);

  // this works good, but multi-leveled lists are destroyed
  //$com = preg_replace('#<ul>(.*?)</ul>#', '$1', $com);
  //$com = preg_replace('#<ol>(.*?)</ol>#', '$1', $com);
  //$com = preg_replace('#<li(.*?)>(.*?)</li>#', '<p class="rLi">$2</p>', $com);
  //die($com);

  return $com;
}



?>
