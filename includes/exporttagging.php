
<?php

//
// regex from Jon to find anomalies.  I should figure it out.
//
// ([a-z0-9][A-Z])|([a-z0-9][.,/?;:'!’”“’‘"]+?[A-Z])|([.,/?;:!”]+?[a-zA-Z])|\s{2,4}| [,.;:?!]|\s+[?!;:.,][ ]|[.] [a-z]
//

//
//
//
function parsescripturerefs($txt, $whch){
  $breg = '#\b(?:Genesis|Ge|Gen|Exodus|Ex|Exo|Exod|Lev|Leviticus|Le|Numbers|Nu|Num|Deut|Deuteronomy|Dt|Deu|De|Joshua|Js|Jos|Josh|Judges|Jg|'.
                'Jdg|Ju|Jdgs|Judg|Ruth|Ru|Rut|1 Samuel|1Samuel|1 Sa|1Sa|1 Sam|1Sam|1 S|1S|2 Samuel|2Samuel|2 Sa|2Sa|2 Sam|2Sam|2 S|2S|'.
                '1 Kings|1Kings|1 Ki|1Ki|1 King|1King|1 Kin|1Kin|1 Kngs|1Kngs|1 K|1K|2 Kings|2Kings|2 Ki|2Ki|2 King|2King|2 Kin|2Kin|2 Kngs|2Kngs|2 K|2K|'.
                '1 Chron|1Chron|1 Chronicles|1Chronicles|1 Ch|1Ch|1 Chr|1Chr|2 Chron|2Chron|2 Chronicles|2Chronicles|2 Ch|2Ch|2 Chr|2Chr|Ezra|Ez|Ezr|'.
                'Nehemiah|Neh|Ne|Esther|Es|Est|Esth|Job|Jb|Psalms|Ps|Psa|Pss|Psalm|Proverbs|Pr|Prov|Pro|Ecc|Ecclesiastes|Ec|Eccl|Eccles|'.
                'Song|Song of Solomon|SoS|Song of Songs|Song of Sol|Sng|SS|Isaiah|Isa|Jeremiah|Jer|Je|Lam|Lamentations|La|Lament|Ezekiel|Ek|Ezek|Eze|'.
                'Daniel|Da|Dan|Dl|Dnl|Hosea|Ho|Hos|Joel|Jl|Joel|Joe|Amos|Am|Amos|Amo|Obadiah|Ob|Oba|Obd|Odbh|Obad|Jonah|Jh|Jon|Jnh|Micah|Mi|Mic|'.
                'Nahum|Na|Nah|Hab|Habakkuk|Hb|Hk|Habk|Zeph|Zephaniah|Zp|Zep|Ze|Haggai|Ha|Hag|Hagg|Zech|Zechariah|Zc|Zec|Malachi|Ml|Mal|Mlc|'.
                'Matthew|Mt|Matt|Mat|Ma|Mark|Mk|Mrk|Luke|Lk|Luk|Lu|John|Jn|Joh|Jo|Acts|Ac|Act|Romans|Ro|Rom|Rmn|Rmns|'.
                '1 Cor|1Cor|1 Corinthians|1Corinthians|1 Co|1Co|1 C|1C|2 Cor|2Cor|2 Corinthians|2Corinthians|2 Co|2Co|2 C|2C|Gal|Galatians|Ga|Gltns|'.
                'Eph|Ephesians|Ep|Ephn|Phil|Philippians|Phi|Ph|Col|Colossians|Co|Colo|Cln|Clns|1 Thess|1Thess|1 Thessalonians|1Thessalonians|1 Th|1Th|1 Thes|1Thes|1 T|1T|'.
                '2 Thess|2Thess|2 Thessalonians|2Thessalonians|2 Th|2Th|2 Thes|2Thes|2 T|2T|1 Tim|1Tim|1 Timothy|1Timothy|1 Ti|1Ti|'.
                '2 Tim|2Tim|2 Timothy|2Timothy|2 Ti|2Ti|Titus|Ti|Tit|Tt|Ts|Philemon|Pm|Phm|Phile|Philm|Phlm|Pm|Philem|Hebrews|He|Heb|Hw|James|Jm|Jam|Jas|Ja|'.
                '1 Peter|1Peter|1 Pe|1Pe|1 Pet|1Pet|1 P|1P|2 Peter|2Peter|2 Pe|2Pe|2 Pet|2Pet|2 P|2P|1 John|1John|1 Jo|1Jo|1 Jn|1Jn|1 J|1J|'.
                '2 John|2John|2 Jo|2Jo|2 Jn|2Jn|2 J|2J|3 John|3John|3 Jo|3Jo|3 Jn|3Jn|3 J|3J|Jude|jd|jde|jud|Rev|Revelation|Re|Rvltn)\.? '.
                '(?:(?:\d{1,3}(?::\d+)?)(?:ff|a|b)?(?:-\d+(?::\d+)?)?(?:(?:, ?(?:\d{1,3}(?::\d+)?)(?:ff|a|b)?(?:-\d+(?::\d+)?)?)|(?:(?:(?:;|,)? and|;) '.
                '+(?:\d{1,3}(?::\d+))(?:ff|a|b)?(?:-\d+(?::\d+)?)?))*)\b#';
  $vreg = '#(?:\d{1,3}(?::\d+)?)(?:ff|a|b)?(?:-\d+(?::\d+)?)?#';
  $params = array('',0,0,0,0,0);

  $refscnt = preg_match_all($breg.'im',$txt,$refs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);   // PREG_OFFSET_CAPTURE  or PREG_PATTERN_ORDER
  $runningoffset = 0;
  $booklen=0;
  foreach($refs[0] as $refgroup){ // for each hit returned
    $reference = $refgroup[0];
    $offset = $refgroup[1];

    $parsedbookname = parsebookname($reference);
    $params[0] = getbookabbr($parsedbookname, $params);
    $params[2]=0;$params[3]=0;//$params[4]=0;$params[5]=0;

    // remove book name so $vreg won't find '1' John, '2' Cor, etc
    $vref = preg_replace('#'.$parsedbookname.'\.?\s#mi', '', $reference);

    $refcnt = preg_match_all($vreg, $vref, $match, PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);
    $refnum=0;
    $runningoffset-=$booklen;
    $booklen=0;
    $havechap=0;
    foreach($match[0] as $refx){
      $params[4]=0;$params[5]=0;
      $refpos = $refx[1];
      $ref = $refx[0];
      // fill params
      $params = fillparams($ref, $params, $havechap);

      if($refnum==0){ // include book name
        $booklen = strpos($reference, $ref);
        $ref = substr($reference, 0, $booklen).$ref;
      }
      for($ni=2;$ni<=5;$ni++) $params[$ni] = intval($params[$ni]);
      switch($whch){
      case 1: //eSword
        $replacestr = '<ref>'.$params[0].' '.$params[2];
        if($params[3]> 0) $replacestr .= ':'.$params[3];
        if($params[3]==0 && $params[4]> 0 && $params[5]==0) $replacestr .= '-'.$params[4];
        if($params[3]> 0 && $params[4]==0 && $params[5]> 0) $replacestr .= '-'.$params[5];
        if($params[4]> 0 && $params[5]> 0) $replacestr .= '-'.$params[4].':'.$params[5];
        $replacestr .= '</ref>';
        if(strpos($replacestr, ':')===FALSE) $replacestr=$ref; // eSword can't handle refs without verses.
        break;
      case 2: // MySword
        $replacestr = '<a href="b'.$params[1].'.'.$params[2];
        if($params[3]> 0) $replacestr .= '.'.$params[3];
        if($params[3]==0 && $params[4]> 0 && $params[5]==0) $replacestr .= '-'.$params[4];
        if($params[3]> 0 && $params[4]==0 && $params[5]> 0) $replacestr .= '-'.$params[5];
        if($params[4]> 0 && $params[5]> 0) $replacestr .= '-'.$params[4].'.'.$params[5];
        $replacestr .= '">'.$ref.'</a>';
        break;
      case 4: // theWord
        $replacestr = '{\field{\*\fldinst HYPERLINK "tw://bible.*?id='.$params[1].'.'.$params[2];
        if($params[3]> 0) $replacestr .= '.'.$params[3];
        if($params[3]==0 && $params[4]> 0 && $params[5]==0) $replacestr .= '.1-'.$params[4].'.1';
        if($params[3]> 0 && $params[4]==0 && $params[5]> 0) $replacestr .= '-'.$params[5];
        if($params[4]> 0 && $params[5]> 0) $replacestr .= '-'.$params[4].'.'.$params[5];
        $replacestr .= '"}{\fldrslt '.$ref.'}}';
        break;
      case 6: // SwordSearcher
        // TODO
        break;
      }
      $txt = substr_replace($txt, $replacestr, $runningoffset+$offset+$refpos, strlen($ref));
      $runningoffset+=strlen($replacestr)-strlen($ref)+(($refnum==0)?$booklen:0);
      $refnum++;
    }
  }
  return $txt;
}

function parsenoparse($txt){
  $txt = str_replace('[/noparse]', '', $txt);
  $havenoparse = strpos($txt, '[noparse]');
  while($havenoparse!==false){
    $offset = 3; // 3 chars for book
    if(substr($txt, $havenoparse+9, 1)=='#') $offset = 1; // for strongs
    if(substr($txt, $havenoparse+9, 4)=='<em>') $offset = 7; // 4 for <em> and 3 for book
    $txt = substr($txt, 0, $havenoparse).substr($txt, $havenoparse+9, $offset).'<noparse/>'.substr($txt, $havenoparse+9+$offset);
    $havenoparse = strpos($txt, '[noparse]');
  }
  return $txt;
}

function parseSScommentaryrefs($txt){
  $creg = '#\b(?:(?:commentary|commentaries) on )(?:Gen|Ge|Genesis|Exod|Ex|Exo|Exodus|Lev|Leviticus|Le|Num|Nu|Numbers|Deut|Deuteronomy|Dt|Deu|De|Josh|Js|Jos|Joshua|'.
          'Judg|Jg|Jdg|Ju|Jdgs|Judges|Ruth|Ru|Rut|1 Sam|1Sam|1 Sa|1Sa|1 Samuel|1Samuel|1 S|1S|2 Sam|2Sam|2 Sa|2Sa|2 Samuel|2Samuel|2 S|2S|'.
          '1 Kings|1Kings|1 Ki|1Ki|1 King|1King|1 Kin|1Kin|1 Kngs|1Kngs|1 Kgs|1Kgs|1 Kng|1Kng|1 K|1K|2 Kings|2Kings|2 Ki|2Ki|2 King|2King|2 Kin|2Kin|2 Kngs|2Kngs|2 Kng|2Kng|2 K|2K|'.
          '1 Chron|1Chron|1 Chronicles|1Chronicles|1 Ch|1Ch|1 Chr|1Chr|2 Chron|2Chron|2 Chronicles|2Chronicles|2 Ch|2Ch|2 Chr|2Chr|Ezra|Ez|Ezr|Neh|Nehemiah|Ne|'.
          'Est|Es|Esther|Esth|Job|Jb|Ps|Psalms|Psa|Pss|Psalm|Prov|Pr|Proverbs|Pro|Ecc|Ecclesiastes|Ec|Eccl|Eccles|Song|Song of Solomon|SoS|Song of Songs|Song of Sol|Sol|Sng|SS|'.
          'Isa|Isaiah|Jer|Jeremiah|Je|Lam|Lamentations|La|Lament|Ezek|Ek|Ezekiel|Ezk|Eze|Dan|Da|Daniel|Dl|Dnl|Hos|Ho|Hosea|Joel|Jl|Joe|Amos|Am|Amos|Amo|'.
          'Obad|Ob|Oba|Obd|Odbh|Obadiah|Jonah|Jh|Jon|Jnh|Micah|Mi|Mic|Nahum|Na|Nah|Hab|Habakkuk|Hb|Hk|Habk|Zeph|Zephaniah|Zp|Zep|Ze|Hag|Ha|Haggai|Hagg|'.
          'Zech|Zechariah|Zc|Zec|Mal|Ml|Malachi|Mlc|Matt|Mt|Matthew|Mat|Ma|Mark|Mar|Mk|Mrk|Luke|Lk|Luk|Lu|John|Jn|Joh|Jhn|Jo|Acts|Ac|Act|Rom|Ro|Romans|Rmn|Rmns|'.
          '1 Cor|1Cor|1 Corinthians|1Corinthians|1 Co|1Co|1 C|1C|2 Cor|2Cor|2 Corinthians|2Corinthians|2 Co|2Co|2 C|2C|Gal|Galatians|Ga|Gltns|Eph|Ephesians|Ep|Ephn|'.
          'Phil|Philippians|Php|Phi|Ph|Col|Colossians|Co|Colo|Cln|Clns|1 Thess|1Thess|1 Thessalonians|1Thessalonians|1 Th|1Th|1 Thes|1Thes|1 T|1T|'.
          '2 Thess|2Thess|2 Thessalonians|2Thessalonians|2 Th|2Th|2 Thes|2Thes|2 T|2T|1 Tim|1Tim|1 Timothy|1Timothy|1 Ti|1Ti|2 Tim|2Tim|2 Timothy|2Timothy|2 Ti|2Ti|'.
          'Titus|Ti|Tit|Tt|Ts|Philm|Phm|Phile|Philemon|Phlm|Pm|Philem|Heb|He|Hebrews|Hw|James|Jm|Jam|Jas|Ja|1 Pet|1Pet|1 Pe|1Pe|1 Peter|1Peter|1 P|1P|'.
          '2 Pet|2Pet|2 Pe|2Pe|2 Peter|2Peter|2 P|2P|1 John|1John|1 Jo|1Jo|1 Jn|1Jn|1 J|1J|2 John|2John|2 Jo|2Jo|2 Jn|2Jn|2 J|2J|3 John|3John|3 Jo|3Jo|3 Jn|3Jn|3 J|3J|'.
          'Jude|jd|jde|jud|Rev|Revelation|Re|Rvltn)\.? '.
          '(?:(?:\d{1,3}(?::\d+)?)(?:-\d+(?::\d+)?)?(?:(?: ?, ?(?:\d{1,3}(?::\d+)?)(?:-\d+(?::\d+)?)?)|(?: ?(?:(?:(?:;|,)? and|;)) '.
          '?(?:\d{1,3}(?::\d+))(?:-\d+(?::\d+)?)?))*(?:(?:(?:;|,)? and|;) '.
          '(?:Gen|Ge|Genesis|Exod|Ex|Exo|Exodus|Lev|Leviticus|Le|Num|Nu|Numbers|Deut|Deuteronomy|Dt|Deu|De|Josh|Js|Jos|Joshua|Judg|Jg|Jdg|Ju|Jdgs|Judges|Ruth|Ru|Rut|'.
          '1 Sam|1Sam|1 Sa|1Sa|1 Samuel|1Samuel|1 S|1S|2 Sam|2Sam|2 Sa|2Sa|2 Samuel|2Samuel|2 S|2S|1 Kings|1Kings|1 Ki|1Ki|1 King|1King|1 Kin|1Kin|1 Kngs|1Kngs|1 Kgs|1Kgs|1 Kng|1Kng|1 K|1K|'.
          '2 Kings|2Kings|2 Ki|2Ki|2 King|2King|2 Kin|2Kin|2 Kngs|2Kngs|2 Kng|2Kng|2 K|2K|1 Chron|1Chron|1 Chronicles|1Chronicles|1 Ch|1Ch|1 Chr|1Chr|'.
          '2 Chron|2Chron|2 Chronicles|2Chronicles|2 Ch|2Ch|2 Chr|2Chr|Ezra|Ez|Ezr|Neh|Nehemiah|Ne|Est|Es|Esther|Esth|Job|Jb|Ps|Psalms|Psa|Pss|Psalm|'.
          'Prov|Pr|Proverbs|Pro|Ecc|Ecclesiastes|Ec|Eccl|Eccles|Song|Song of Solomon|SoS|Song of Songs|Song of Sol|Sol|Sng|SS|Isa|Isaiah|Jer|Jeremiah|Je|'.
          'Lam|Lamentations|La|Lament|Ezek|Ek|Ezekiel|Ezk|Eze|Dan|Da|Daniel|Dl|Dnl|Hos|Ho|Hosea|Joel|Jl|Joe|Amos|Am|Amos|Amo|Obad|Ob|Oba|Obd|Odbh|Obadiah|Jonah|Jh|Jon|Jnh|'.
          'Micah|Mi|Mic|Nahum|Na|Nah|Hab|Habakkuk|Hb|Hk|Habk|Zeph|Zephaniah|Zp|Zep|Ze|Hag|Ha|Haggai|Hagg|Zech|Zechariah|Zc|Zec|Mal|Ml|Malachi|Mlc|'.
          'Matt|Mt|Matthew|Mat|Ma|Mark|Mar|Mk|Mrk|Luke|Lk|Luk|Lu|John|Jn|Joh|Jhn|Jo|Acts|Ac|Act|Rom|Ro|Romans|Rmn|Rmns|1 Cor|1Cor|1 Corinthians|1Corinthians|1 Co|1Co|1 C|1C|'.
          '2 Cor|2Cor|2 Corinthians|2Corinthians|2 Co|2Co|2 C|2C|Gal|Galatians|Ga|Gltns|Eph|Ephesians|Ep|Ephn|Phil|Philippians|Php|Phi|Ph|Col|Colossians|Co|Colo|Cln|Clns|'.
          '1 Thess|1Thess|1 Thessalonians|1Thessalonians|1 Th|1Th|1 Thes|1Thes|1 T|1T|2 Thess|2Thess|2 Thessalonians|2Thessalonians|2 Th|2Th|2 Thes|2Thes|2 T|2T|'.
          '1 Tim|1Tim|1 Timothy|1Timothy|1 Ti|1Ti|2 Tim|2Tim|2 Timothy|2Timothy|2 Ti|2Ti|Titus|Ti|Tit|Tt|Ts|Philm|Phm|Phile|Philemon|Phlm|Pm|Philem|Heb|He|Hebrews|Hw|'.
          'James|Jm|Jam|Jas|Ja|1 Pet|1Pet|1 Pe|1Pe|1 Peter|1Peter|1 P|1P|2 Pet|2Pet|2 Pe|2Pe|2 Peter|2Peter|2 P|2P|1 John|1John|1 Jo|1Jo|1 Jn|1Jn|1 J|1J|'.
          '2 John|2John|2 Jo|2Jo|2 Jn|2Jn|2 J|2J|3 John|3John|3 Jo|3Jo|3 Jn|3Jn|3 J|3J|Jude|jd|jde|jud|Rev|Revelation|Re|Rvltn)\.? '.
          '(?:(?:\d{1,3}(?::\d+)?)(?:-\d+(?::\d+)?)?(?:(?: ?, ?(?:\d{1,3}(?::\d+)?)(?:-\d+(?::\d+)?)?)|(?: ?(?:(?:(?:;|,)? and|;)) '.
          '?(?:\d{1,3}(?::\d+))(?:-\d+(?::\d+)?)?))*))*)\b#';

  $breg = '#(?:Gen|Ge|Genesis|Exod|Ex|Exo|Exodus|Lev|Leviticus|Le|Num|Nu|Numbers|Deut|Deuteronomy|Dt|Deu|De|Josh|Js|Jos|Joshua|Judg|Jg|Jdg|Ju|Jdgs|Judges|Ruth|Ru|Rut|'.
          '1 Sam|1Sam|1 Sa|1Sa|1 Samuel|1Samuel|1 S|1S|2 Sam|2Sam|2 Sa|2Sa|2 Samuel|2Samuel|2 S|2S|1 Kings|1Kings|1 Ki|1Ki|1 King|1King|1 Kin|1Kin|1 Kngs|1Kngs|1 Kgs|1Kgs|1 Kng|1Kng|1 K|1K|'.
          '2 Kings|2Kings|2 Ki|2Ki|2 King|2King|2 Kin|2Kin|2 Kngs|2Kngs|2 Kng|2Kng|2 K|2K|1 Chron|1Chron|1 Chronicles|1Chronicles|1 Ch|1Ch|1 Chr|1Chr|'.
          '2 Chron|2Chron|2 Chronicles|2Chronicles|2 Ch|2Ch|2 Chr|2Chr|Ezra|Ez|Ezr|Neh|Nehemiah|Ne|Est|Es|Esther|Esth|Job|Jb|Ps|Psalms|Psa|Pss|Psalm|'.
          'Prov|Pr|Proverbs|Pro|Ecc|Ecclesiastes|Ec|Eccl|Eccles|Song|Song of Solomon|SoS|Song of Songs|Song of Sol|Sol|Sng|SS|Isa|Isaiah|Jer|Jeremiah|Je|'.
          'Lam|Lamentations|La|Lament|Ezek|Ek|Ezekiel|Ezk|Eze|Dan|Da|Daniel|Dl|Dnl|Hos|Ho|Hosea|Joel|Jl|Joe|Amos|Am|Amos|Amo|Obad|Ob|Oba|Obd|Odbh|Obadiah|Jonah|Jh|Jon|Jnh|'.
          'Micah|Mi|Mic|Nahum|Na|Nah|Hab|Habakkuk|Hb|Hk|Habk|Zeph|Zephaniah|Zp|Zep|Ze|Hag|Ha|Haggai|Hagg|Zech|Zechariah|Zc|Zec|Mal|Ml|Malachi|Mlc|'.
          'Matt|Mt|Matthew|Mat|Ma|Mark|Mar|Mk|Mrk|Luke|Lk|Luk|Lu|John|Jn|Joh|Jhn|Jo|Acts|Ac|Act|Rom|Ro|Romans|Rmn|Rmns|1 Cor|1Cor|1 Corinthians|1Corinthians|1 Co|1Co|1 C|1C|'.
          '2 Cor|2Cor|2 Corinthians|2Corinthians|2 Co|2Co|2 C|2C|Gal|Galatians|Ga|Gltns|Eph|Ephesians|Ep|Ephn|Phil|Philippians|Php|Phi|Ph|Col|Colossians|Co|Colo|Cln|Clns|'.
          '1 Thess|1Thess|1 Thessalonians|1Thessalonians|1 Th|1Th|1 Thes|1Thes|1 T|1T|2 Thess|2Thess|2 Thessalonians|2Thessalonians|2 Th|2Th|2 Thes|2Thes|2 T|2T|'.
          '1 Tim|1Tim|1 Timothy|1Timothy|1 Ti|1Ti|2 Tim|2Tim|2 Timothy|2Timothy|2 Ti|2Ti|Titus|Ti|Tit|Tt|Ts|Philm|Phm|Phile|Philemon|Phlm|Pm|Philem|Heb|He|Hebrews|Hw|'.
          'James|Jm|Jam|Jas|Ja|1 Pet|1Pet|1 Pe|1Pe|1 Peter|1Peter|1 P|1P|2 Pet|2Pet|2 Pe|2Pe|2 Peter|2Peter|2 P|2P|1 John|1John|1 Jo|1Jo|1 Jn|1Jn|1 J|1J|'.
          '2 John|2John|2 Jo|2Jo|2 Jn|2Jn|2 J|2J|3 John|3John|3 Jo|3Jo|3 Jn|3Jn|3 J|3J|Jude|jd|jde|jud|Rev|Revelation|Re|Rvltn)\.? '.
          '(?:(?:\d{1,3}(?::\d+)?)(?:-\d+(?::\d+)?)?(?:(?: ?, ?(?:\d{1,3}(?::\d+)?)(?:-\d+(?::\d+)?)?)|(?: ?(?:(?:(?:;|,)? and|;)) '.
          '?(?:\d{1,3}(?::\d+))(?:-\d+(?::\d+)?)?))*)\b#';

  $vreg = '#(?:\d{1,3}(?::\d+)?)#';

  $params = array('',0,0,0);
  $runningoffset = 0;
  $refccnt = preg_match_all($creg.'im',$txt,$crefs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);   // PREG_OFFSET_CAPTURE  or PREG_PATTERN_ORDER
  foreach($crefs[0] as $refgroup){ // for each hit returned
    //print('in main loop<br />');
    $reference = $refgroup[0];
    $offset = $refgroup[1];

    if(strpos($reference, 'commentary on')===0)  {$reference = substr($reference, 14);$offset+=14;$strcomm='commentary on';}
    if(strpos($reference, 'commentaries on')===0){$reference = substr($reference, 16);$offset+=16;$strcomm='commentaries on';}
    //print('offset: '.$offset.'<br />');
    //print('reference: '.$reference.'<br />');

    $vrefoffset=0;
    $refbcnt = preg_match_all($breg.'im',$reference,$brefs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);   // PREG_OFFSET_CAPTURE  or PREG_PATTERN_ORDER
    foreach($brefs[0] as $refbgroup){ // for each book returned
      //print('<br />in bref loop<br />');
      $breference = $refbgroup[0];
      //print('breference: '.$breference.'<br />');

      $refnum=0;
      $havebook=0;
      $havechap=0;

      $parsedbookname = parsebooknameSS($breference);
      //print('parsedbookname: &gt;'.$parsedbookname.'&lt;<br />');

      $params[0] = getbookabbr($parsedbookname, $params);
      $params[2] = 0;
      $params[3] = 0;

      // remove book name so $vreg won't find '1' John, '2' Cor, etc
      $vref = substr($breference, (strlen($parsedbookname)+1));
      //print('vref: '.$vref.'<br />');

      $refvcnt = preg_match_all($vreg, $vref, $vmatch, PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);
      foreach($vmatch[0] as $refx){ // for each verseref returned
        //print('<br />in vref loop<br />');
        $ref = $refx[0];
        //print('refx[1]: '.$refx[1].'<br />');
        //print('refnum: '.$refnum.'<br />');
        //print('ref: &gt;'.$ref.'&lt;<br />');
        // fill params
        $params = fillparamsSS($ref, $params, $havebook, $havechap);

        if($refnum==0){ // include book name
          $ref = substr($breference, 0, strpos($breference, $ref)).$ref;
        }
        // may work on this later
        //if($vrefoffset==0){$reference=$strcomm.' '.$reference;$ref=$strcomm.' '.$ref;}

        //print('ref: &gt;'.$ref.'&lt;<br />');
        $lenref = strlen($ref);
        //print('strlenref: &gt;'.$lenref.'&lt;<br />');
        for($ni=2;$ni<=3;$ni++) $params[$ni] = intval($params[$ni]);
        $replacestr = '<a href="sscmt:REVCom|'.$params[0].' '.$params[2].':'.$params[3].'">'.$ref.'</a>';
        //print('replacestr: &gt;'.$replacestr.'&lt;<br />');

        // need to add diff between end of last ref and start of current ref. ', ' or '; ' or ' and ' or '; and '
        $sposrefref = strpos($reference, $ref);
        $vrefoffset+=$sposrefref;
        $txt = substr_replace($txt, $replacestr, ($offset+$runningoffset+$vrefoffset), $lenref);
        $runningoffset+=strlen($replacestr)-$lenref;
        $vrefoffset+=$lenref;
        $reference = substr($reference, $lenref+$sposrefref);

        //print('reference: '.$reference.'<br />');
        //print('vrefoffset: '.$vrefoffset.'<br />');
        //print('com: '.$txt.'<hr>');
        $refnum++;
      }
    }
  }
  return $txt;
}

function parsebooknameSS($bknam){
  $bknam = str_replace('.', '', $bknam);
  $bknam = trim($bknam);
  $chr2 = substr($bknam, 0, 2);
  $bknam = substr($bknam, 2);
  $bknam = str_replace(' ', '', $bknam);
  $bknam = preg_replace('#(\d+)#', '', $bknam);
  $bknam = $chr2.$bknam;
  return   substr($bknam, 0, strpos($bknam, ':'));
}



//
// fill the params[] array
//
function fillparams($ref, $params, &$havechap){

  // strip out ff, a, b
  $ref = str_replace('ff', '', $ref);
  $ref = preg_replace('#(\d+)[a|b]#', '$1', $ref);

  $arref = explode('-', $ref);
  $idx1=0;
  foreach($arref as $ref1){
    if(strpos($ref1, ':') !== FALSE){
      $arrf = explode(':', $ref1);
      $havechap=0;
      $idx2=0;
      foreach($arrf as $ref2){
        // left side of colon: chapter
        if($idx1==0 && $idx2==0 && $havechap==0){$havechap=1; $params[2] = $ref2;}
        // right side of colon: verse
        if($idx1==0 && $idx2==1){$params[3] = $ref2;}
        if($idx1==1 && $idx2==0){$params[4] = $ref2;}
        if($idx1==1 && $idx2==1){$params[5] = $ref2;}
        $idx2++;
      }
    }else{ // no colon
      // 1st iteration, left side of '-'
      if($idx1==0 && $havechap==0){$params[2] = $ref1;}
      if($idx1==0 && $havechap==1){$params[3] = $ref1;}

      // 2nd iteration, right side of '-'
      if($idx1==1 && $havechap==0){$params[4] = $ref1;}
      if($idx1==1 && $havechap==1){$params[5] = $ref1;}
    }
    $idx1++;
  }
  return $params;
}

function fillparamsSS($ref, $params, &$havebook, &$havechap){

  $idx1=0;
  if(strpos($ref, ':') !== FALSE){
    $arrf = explode(':', $ref);
    $havechap=0;
    $idx2=0;
    foreach($arrf as $ref1){
      // left side of colon: chapter
      if($idx1==0 && $idx2==0 && $havechap==0){$havechap=1; $params[2] = $ref1;}
      // right side of colon: verse
      if($idx1==0 && $idx2==1){$params[3] = $ref1;}
      $idx2++;
    }
  }else{ // no colon
    // 1st iteration, left side of '-'
    if($idx1==0 && $havechap==0){$params[2] = $ref;}
    if($idx1==0 && $havechap==1){$params[3] = $ref;}
  }
  $idx1++;
  return $params;
}

//
// parse out the book name
//
function parsebookname($ref){
  $ref = str_replace(' and ', '; ', $ref);
  $ref = str_replace('ff', '; ', $ref);
  $ref = preg_replace('#(\d+)[a|b]#', '$1', $ref);

  for($ni=strlen($ref)-1;$ni>0;$ni--){
    if(ctype_alpha(substr($ref, $ni, 1))){
      $ref = substr($ref, 0, $ni+1);
      break;
    }
  }
  return $ref;
}

//
// get acceptable book abbreviation
//
function getbookabbr($nam, &$params){
  global $bookaliases;
  $nam = '~'.strtolower(str_replace(' ', '', $nam)).'~';
  for($ni=1;$ni<=66;$ni++){
    if(strpos($bookaliases[$ni][1], $nam, 0)!==FALSE){
      $params[1] = $ni;
      return $bookaliases[$ni][0];
    }
  }
  return 'unknown';
}

//
//
//
function loadbooknames($booknames){
  $sql = 'select bwabbr from book where testament in (0,1) order by testament, book ';
  $books = dbquery($sql);
  $ni=1;
  while($row = mysqli_fetch_array($books)){
    $booknames[$ni] = $row[0];
    $ni++;
  }
  $booknames[50] = 'Php';
  $booknames[59] = 'Jas';
  return $booknames;
}

//
//
//
function loadaliases($bookaliases){
  $sql = 'select bwabbr, aliases from book where testament in (0,1) order by testament, book ';
  $books = dbquery($sql);
  $ni=1;
  while($row = mysqli_fetch_array($books)){
    $bookaliases[$ni][0] = $row[0];
    $bookaliases[$ni][1] = strtolower($row[1]);
    $ni++;
  }
  $bookaliases[50][0] = 'Php';
  $bookaliases[59][0] = 'Jas';
  $bookaliases[62][0] = '1Jn'; // fix for eSword Android
  $bookaliases[63][0] = '2Jn';
  $bookaliases[64][0] = '3Jn';
  return $bookaliases;
}

//
// parse Strongs numbers
//
function parsestrongs($txt, $whch){
  $sreg = '#\#\\d{1,7}\\b#';
  $refscnt = preg_match_all($sreg.'im',$txt,$refs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);   // PREG_OFFSET_CAPTURE  or PREG_PATTERN_ORDER
  $runningoffset = 0;
  foreach($refs[0] as $ref){ // for each hit returned
    $reference = $ref[0];
    $offset = $ref[1];
    $workref = substr($reference, 1);
    if(substr($workref, 0, 1)=='0'){
      $workref = 'H'.substr($workref, 1);
    }else{
      $workref = 'G'.$workref;
    }
    switch($whch){
    case 1: // eSword
      $replacestr = '<num>'.$workref.'</num>';
      break;
    case 2: // MySword
      $replacestr = '<a href="s'.$workref.'">'.$reference.'</a>';
      break;
    case 4: // theWord
      $replacestr = '{\field{\*\fldinst HYPERLINK "tw://[strong]?'.$workref.'"}{\fldrslt '.$workref.'}}';
      break;
    case 6: // SwordSearcher
      $workref = substr($reference, 1);
      if(substr($workref, 0, 1)=='0'){ // Hebrew
        $workref = substr($workref, 1);
        //$replacestr = '<a href="ssbook:SHebrew|'.$workref.'">'.$reference.'</a>';
        $replacestr = '<a href="ssbook:SHebrew|'.$workref.'">H'.$workref.'</a>';
      }else{ // Greek
        $workref = $workref;
        //$replacestr = '<a href="ssbook:SGreek|'.$workref.'">'.$reference.'</a>';
        $replacestr = '<a href="ssbook:SGreek|'.$workref.'">G'.$workref.'</a>';
      }
      break;
    }
    $txt = substr_replace($txt, $replacestr, $runningoffset+$offset, strlen($reference));
    $runningoffset+=strlen($replacestr)-strlen($reference);
  }
  return $txt;
}

//
// parse Appendix references
//
function parseappx($txt, $whch, $arappx){
  $sreg = '#\\b(see\\s{1}(appendix|appx)\\s{1}\#?)\\d{1,3}\\b#';
  $refscnt = preg_match_all($sreg.'im',$txt,$refs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);
  $runningoffset = 0;
  foreach($refs[0] as $ref){ // for each hit returned
    $reference = $ref[0];
    $offset = $ref[1];
    $workref = preg_replace('#[^\d.]#mi', '', $reference);
    switch($whch){
    case 1: //
      $replacestr = ''; // eSword does not seem to handle linking to reference books
      break;
    case 2: // MySword
      $replacestr = '<a href="k-REV_Appx '.$arappx[$workref].'">'.$reference.'</a>';
      break;
    case 4: // theWord
      // TODO
      $replacestr = '{\field{\*\fldinst HYPERLINK "tw://bk.ra7?tid='.$workref.'"}{\fldrslt '.$reference.'}}';
      break;
    case 6: // SwordSearcher
      $replacestr = '<a href="ssbook:REVAppx|'.processtitleforSSexport($arappx[$workref]).'">'.$reference.'</a>';
      break;
    }
    $txt = substr_replace($txt, $replacestr, $runningoffset+$offset, strlen($reference));
    $runningoffset+=strlen($replacestr)-strlen($reference);
  }
  return $txt;
}

//
// parse Word Studies
//
function parsewords($txt, $whch){
  global $revws, $arws;
  if($revws==0) return $txt;
  if($whch==4){ // theWord, rtf
    $sreg = "#(word (study|studies) on )((, | | and |, and )?(\\\'93.+?(?=\\\'94)((\.|,|;)?\\\'94))+)+#";
    $refscnt = preg_match_all($sreg.'i',$txt,$refs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);
    $taglen = 4;
    $righttag = '\\\'94';
  }else{
    $liveserver=1;
    $sreg = '#(word (study|studies) on )((, | | and |, and )?(“.+?(?=”)((\.|,|;)?”))+)+#';
    $refscnt = preg_match_all($sreg.'i',$txt,$refs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);
    $taglen = 1;
    $righttag = '”';
    if($refscnt==0){
      $sreg = '#(word (study|studies) on )((, | | and |, and )?(&ldquo;.+?(?=&rdquo;)((\.|,|;)?&rdquo;))+)+#';
      $refscnt = preg_match_all($sreg.'i',$txt,$refs,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);
      $liveserver=0;
      $taglen = 7;
      $righttag = '&rdquo;';
    }
  }
  $runningoffset = 0;
  //print('<br />debugging parsewords()');
  //print('<br />hits: '.$refscnt);
  foreach($refs[0] as $topref){ // for each hit returned
    $reference = $topref[0];
    $offset = $topref[1];
    if($whch==4){ // theWord, rtf
      $sxreg = "#(\\\'93.+?(?=\\\'94)((\.|,|;)?\\\'94))+#";
    }else{
      if($liveserver==1) $sxreg = '#(“.+?(?=”)((\.|,|;)?”))+#';
      else $sxreg = '#(&ldquo;.+?(?=&rdquo;)((\.|,|;)?&rdquo;))+#';
    }
    $refinnercnt = preg_match_all($sxreg.'i',$reference,$refx,PREG_OFFSET_CAPTURE+PREG_PATTERN_ORDER);
    //print('<br />innerhits: '.$refinnercnt);
    foreach($refx[0] as $refx){ // for each hit returned
      $innerref = $refx[0];
      $inneroff = $refx[1];
      $word = substr($innerref, $taglen);
      $word = substr($word, 0, strpos($word, $righttag));
      $word = preg_replace('#(\.|,|;)#i', '', $word);
      switch($whch){
      case 1: //
        $replacestr = ''; // eSword does not seem to handle linking to reference books
        break;
      case 2: // MySword
        $replacestr = '<a href="k-REV_WS Word Study on &ldquo;'.$word.'&rdquo;">'.$word.'</a>';
        break;
      case 4: // theWord
        // need to get the bookid from the array by looking for $word
        $idx = 0;
        for($nj=1;$nj<=sizeof($arws);$nj++){
          if($arws[$nj][1]==$word){
            $idx = $arws[$nj][0];
            break;
          }
        }
        $replacestr = '{\field{\*\fldinst HYPERLINK "tw://bk.rw7?tid='.$idx.'"}{\fldrslt '.$word.'}}';
        break;
      }
      $txt = substr_replace($txt, $replacestr, $runningoffset+$offset+$inneroff+$taglen, strlen($word));
      $runningoffset+=strlen($replacestr)-strlen($word);
    }
  }
  return $txt;
}

?>
