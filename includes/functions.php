<?php
const crlf = "\r\n";     // should be PHP_EOL
const datsav = '<span style="color:green"><em>Success!</em></span>';
const notifyinactive = ' <span style="color:red;cursor:pointer" title="inactive">**</span>';
const notifynotpublished = ' <small>(<span style="color:red;">not published</span>)</small>';

$srchstring = 'Enter a Verse';
$metatitle = '';
$metadesc = '';
$sqlerr = '';
$userid  = 0;
$canedit = 0;
$appxedit= 0;
$resedit = 0;
$fromedit= 0;
$chronedit= 0;

// ednotes
$editorcomments= 0;
$ednotesshowall= 0;
$viewedcomments= 0;

// peernotes
$peernotes= 0;
$peernotesshowall= 0;
$viewpeernotes= 0;

$timezone = 'America/New_York';
$myrevid = 0;
$screenwidthsmall=640; // arbitrary

$showback = 0;        // used to determine whether or not to show a back button on appx's
$showclosetab = 0;    // used to determine whether or not to show a close tab button on appx's
$showtochapter= 0;    // used to determine whether or not to show a "back to chapter" button on viewverscomm
$vnav = 0;            // from the quicknav box in viewbible.php
$vhed = 0;            // for going from outline to heading
$glogid = 0;          // for navigating to internal anchors from the WhatsNew page

$srchbook = '';

$useragent = strtolower(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'unknown');
$ismobile  = (strpos($useragent, "iphone") || strpos($useragent, "android") || strpos($useragent, "mobile"))?1:0;
$isios     = (strpos($useragent, "iphone") || strpos($useragent, "ipad") || strpos($useragent, "ipod"))?1:0;
$isbingbot = (strpos($useragent, "www.bing.com/bingbot.htm"))?1:0;
$isgoogbot = (strpos($useragent, "www.google.com/bot.html"))?1:0;
//$isgoogbot = 1; // testing

$issafari  = ((stripos( $useragent, 'Chrome')===false && stripos( $useragent, 'safari')!==false)?1:0);
if($issafari){ // Safari, so reset cookie expiration dates
  $cookietoset = array('rev_lastbibleloc','rev_lastloc','rev_lightbox_permanent','rev_preferences','myrevsid','rev_myrevprefs','rev_wnblog');
  foreach($cookietoset as $value){
    if(isset($_COOKIE[$value])){
      $tmpvalue = $_COOKIE[$value];
      setcookie($value, $tmpvalue, time() + (86400 * 180), "/");
    }
  }
}

$myrevmode = (($ismobile)?'compact':'full');
$myrevsort = 'canon';
$myrevpagsiz = (($ismobile)?20:50);
$myrevshownotes = 0;
$myrevclick = 0; // this is for public users. the default for myrev users is to show the myrev popup.
$myrevkeys=[];
$myrevshowkey=0;
$myrevshoweditorfirst=0;
$history = '';

$inapp=0;
if(isset($_COOKIE['rev_inapp'])) $inapp = $_COOKIE['rev_inapp'];

// these are loaded from the db below
$sopsislive = 0;  // Step On Prevention System
$sopstimeout=999; // sops timeout
$sopstimeoutding=0; // if 1, sops will ding at < 20 seconds
$sopstimeoutextend=60; // sops extend minutes
$revws = 0; // word studies
$revtp = 0; // topics
$revch = 0; // chronology
$revblog=0; // REV Blog
$viewsrc=0; // set to 1 for viewing source
$fileversion = '1.43'; // used to force reloading of possibly cached js and css files

// two cookies for devs to force the system to be mobile or in app
if(isset($_COOKIE['rev_rswismobile'])) $ismobile = $_COOKIE['rev_rswismobile'];
if(isset($_COOKIE['rev_rswinapp'])) $inapp = $_COOKIE['rev_rswinapp'];

// the next line reads the prefs cookie. if no cookie, sets default prefs
$arprefs   = explode(';', (isset($_COOKIE['rev_preferences']))?$_COOKIE['rev_preferences']??'':'1;1;'.(($ismobile==1)?'1.3':'1').';1.3;merriweather;'.(($ismobile==1)?'1':'0').';0;1;1;0;0;0;1;1;0;0;0;'.(($ismobile==1)?0:1).';2;1;0;0');
$viewcols  = $arprefs[0];        // how many columns
$versebreak= $arprefs[1];        // this has become viewing mode, versbreak, paragraph, reading
$fontsize  = $arprefs[2];        // font size
$lineheight= $arprefs[3];        // spacing between lines 
$fontfamily= $arprefs[4];        // default merriweather
$swipenav  = $arprefs[5];        // for mobile users, what happens when screen is swiped l/r
$useoefirst= $arprefs[6];        // Old English letter for 1st char on vs1
$parachoice= $arprefs[7];        // indent, justified
$navonchap = $arprefs[8];        // for the center biblenav
$commnewtab= $arprefs[9];        // open comm links in new tab
$colortheme= $arprefs[10];       // color theme: light, sepia, dark
$commlinkstyl = ((!$ismobile)?$arprefs[11]:0); // red vs#, blue, underline, both
$lexicon      = $arprefs[12];    // which online lexicon to use for Strongs #s
$scrollynav   = $arprefs[13];    // animation, smooth scrolling
$showdevitems = $arprefs[14];    // show dev items
$showcommlinks= (($ismobile)?$arprefs[15]:0); // mobile users only, the little commentary icons.
$viewversnav  = $arprefs[16];
$showpdflinks = $arprefs[17];
$versnavwhat  = $arprefs[18];
$linkcommentary= $arprefs[19];
$ucaseot   = $arprefs[20];       // upper-case OT quotes in NT
$diffbiblefont = ((isset($arprefs[21]))?$arprefs[21]:0);

if($diffbiblefont==1){
  $arbibleprefs   = explode(';', (isset($_COOKIE['rev_biblefontprefs']))?$_COOKIE['rev_biblefontprefs']??'':$fontsize.';'.$lineheight.';'.$fontfamily.';');
  $biblefontsize  = $arbibleprefs[0];   // font size
  $biblelineheight= $arbibleprefs[1];   // spacing between lines 
  $biblefontfamily= $arbibleprefs[2];   // default merriweather
}else{
  $biblefontsize  = $fontsize;          // font size
  $biblelineheight= $lineheight;        // spacing between lines 
  $biblefontfamily= $fontfamily;        // default merriweather
}

// colors[0] = file extension
// colors[1] = main font color
// colors[2] = background
// colors[3] = soft div border
// colors[4] = menu text color
// colors[5] = comlink hover color
// colors[6] = highlight
// colors[7] = subtle
//
// !!!IMPORTANT!!! colors are also defined in functionsjson.php
//
switch($colortheme){
case 1: // black background
    $colors = array('_LOD','#ddd','#000','#666','#ddd','yellow', '#666','#aaa');
    $eventcolors = array('_LOD','#909090','#878787','#808080','#777777','#707070', '#676767','#977','none');
    $hilitecolors = array('transparent','#664','#464','#446', '#644','#444');
    break;
case 2; // sepia background
    $colors = array('_SEP','#5f4b32','#fbf0d9','#bda78e','#5f4b32','blue','#dbd0b9','#bda78e');
    $eventcolors = array('_LOD','#fee','#efe','#eef','#ffe','#eff', '#fef','#fdd','none');
    //$hilitecolors = array('transparent','#fee','#efe','#eef','#ffe','#ddd');
    $hilitecolors = array('transparent','#ded7c9','#efe','#eef','#ffe','#ddd');
    break;
default: // white background
    $colors = array('','#000','#fff','#ccc','#525252','blue','#ddd','#aaa');
    $eventcolors = array('_LOD','#fee','#efe','#eef','#ffe','#eff', '#fef','#fdd','none');
    $hilitecolors = array('transparent','#ff9','#dfd','#def','#fdd','#ddd');
    break;
}

$screenwidth= ((isset($_COOKIE['rev_screenwidth']))?$_COOKIE['rev_screenwidth']:(($ismobile==1)?480:1280));

$mobilespc = (($ismobile)?'<span style="display:block;height:.5em"></span>':'');

$edit = ((isset($_COOKIE["rev_edit"]))?$_COOKIE["rev_edit"]:0);
$resshowedit = ((isset($_COOKIE["rev_resedit"]))?$_COOKIE["rev_resedit"]:0);
$chronshowedit = ((isset($_COOKIE["rev_chronedit"]))?$_COOKIE["rev_chronedit"]:0);
$myrevsid  = ((isset($_COOKIE["myrevsid"]))?$_COOKIE["myrevsid"]:'public');
$bcuk=0; // to read lastbibleviewed cookie

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
  // no SSL request
  $jsonurl = 'http://'.$site;
}else{
  $jsonurl = 'https://'.$site;
}

/*
// these should be cleaned up
1 = Information
7 = Search
8 = WhatsNew
9 = REV Blog
11= Donate
12= Appx
13= Back to Bible
14= Help
15= Resources
16= Topics
17= Chronology
18= MyREV
*/
// used in nav.php
$armenuitems = array(13,7,18,16,12,8,9,15,17,14,1,11);

$db = opendb();

// enable features
$result = dbquery('select settingtype, settingname, somenumber from settings where settingtype in (\'variable\', \'switch\') ');
while($row = mysqli_fetch_array($result)) {
  if($row[0]=='switch')
    eval('$'.$row[1].' = (int) '.$row[2].';');
  else
    eval('$'.$row[1].' = '.$row[2].';');
}

$autoblock = getsettingvalue('autoblock', 'num');

//
// check for blocked ip
//
$arblockedips = array();
$result = dbquery('select ipaddress, id, reason from blockedips');
while($row = mysqli_fetch_array($result)) {
  array_push($arblockedips, array($row[0], $row[1], $row[2]));
}
$ip = ((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'unknown');
for ($i=0;$i<sizeof($arblockedips);$i++) {
  $testip = str_replace('*','',$arblockedips[$i][0]);
  if(strpos($ip, $testip)===0){
    $update = dbquery('update blockedips set hitcount = hitcount+1, lasthit = UTC_TIMESTAMP() where id='.$arblockedips[$i][1].' ');
    exit(0);break;
  }
}

// stop querystring svg injection attempts
if(strpos($_SERVER["REQUEST_URI"], 'svg')!==FALSE){
  logview(452,0,0,0,0,'<span style="color:red">SVG hack attempt</span>');
  if($autoblock==1) $block = dbquery('insert into blockedips (ipaddress, hitcount, lasthit, reason, comment) values (\''.$ip.'\', 1, UTC_TIMESTAMP(), 2, \'autoblocked (svg)\')');
  exit(0);
}

$qs = ((isset($_GET['qs']))?str_replace('\'','',str_replace(';','',mb_strtolower($_GET['qs']))):'');
//$qs = str_replace(' ', '_', $qs);
$qs = preg_replace('/\s+/', '', $qs); // remove any newlines and spaces
//print('qs: '.$qs);
//die();
if($qs!=''){
  $arqs = explode('/', $qs??'');
  $mitm=13; // default to viewbible
  $page=0;$test=-1;$book=0;$chap=0;$vers=0;$idx=0;$comm=0;$bcomm=0;$blog=0;$pref=0;$outl=0;
  if(strlen($arqs[0])==1){
    switch ($arqs[0]) {
      case 'i': $arqs[0]= 'info';break; // to info section
      case 'a': $arqs[0]= 'appx';break; // to appendices
      case 'w': $arqs[0]= 'word';break; // to word studies
      case 'c': $arqs[0]= 'comm';break; // to commentary
      case 'b': $arqs[0]= 'blog';break; // to REV blog
      case 'f': $arqs[0]= 'figu';break; // to FoS (word studies)
      case 'n': $arqs[0]= 'wnew';break; // to Whats New
      case 'h': $arqs[0]= 'help';break; // to help, an info doc
      case 's': $arqs[0]= 'srch';break; // to search
      case 'p': $arqs[0]= 'pref';break; // to preferences
      case 'e': $arqs[0]= 'expt';break; // to exports
      case 'r': $arqs[0]= 'reso';break; // to resources
      case 't': $arqs[0]= 'topi';break; // to topics
      case 'm': $arqs[0]= 'myre';break; // to myrev
      case 'o': $arqs[0]= 'outl';break; // to outline
    }
  }
  if(left($arqs[0],4)=='frap'){ // requests coming from the REV app
    $inapp = 1;
    setcookie('rev_inapp', $inapp, time() + 21600, "/");  // 6 hours
    array_shift($arqs); // get rid of 1st array element
    if(!isset($arqs[0])) $arqs[0]='bcuk'; // if no elements, send them to last bible loc
  }
  if(left($arqs[0],4)=='pref'){ // user wants preferences
    $pref=1;
    array_shift($arqs);
    if(!isset($arqs[0])) $arqs[0]=''; // make sure it's set
  }
  switch (left($arqs[0],4)) { // figure out where user wants to go. $idx will be the index of $arqs to check
    case 'comm': $idx=1;$comm=1;break;                    // commentary / book / chap / vers
    case 'book': $idx=1;$bcomm=1;break;                   // book commentary / book
    case 'outl': $idx=1;$outl=1;break;                    // outline / book
    case 'word':                                          // word studies / id or part of name      
    case 'figu': $idx=1; $mitm=0;if(!isset($arqs[$idx])) {$arqs[$idx]='xxx';};break;
    case 'wnew': $idx=99;$mitm=8;$page=20;break;          // whatsnew
    case 'enot': $idx=99;$page=7;break;                   // editor notes

    // next four are to catch old stuff
    case 'otst':
    case 'ntst':
    case 'ocom':
    case 'ncom': $idx=99;$bcuk=1;$qs='';break;            // abort, send to bcuk

    case 'sear':
    case 'srch': $idx=99;$mitm=7;$page=3;break;           // search page
    case 'wrds': $idx=99;$mitm=0;$page=14;$test=4;break;          // list of wordstudies
    case 'dont':
    case 'dona': $idx=99;$mitm=11;$page=29;break;         // donate page
    case 'expo':
    case 'expt': $idx=99;$mitm=0;$page=30;break;         // exports page
    case 'bcuk': $idx=99;$bcuk=1;$qs='';break;            // last bible location
    case 'reso': $idx=99;$mitm=15;$page=36;break;         // resources 
    case 'chro': $idx=99;$mitm=17;$page=34;break;         // chronology
    case 'logi': $idx=99;$mitm=18;$page=40;break;                  // login (myrev)
    case 'bibl': $idx=99;$mitm=0;$chap=1;$page=44;break;  // bibliography
    case 'abbr': $idx=99;$mitm=0;$chap=0;$page=44;break;  // bibliography abbreviations
    case 'myre':
      if(sizeof($arqs)==6){
        $test=$arqs[2];
        $book=$arqs[3];
        $chap=$arqs[4];
        $vers=$arqs[5];
      }
      $idx=99;$page=41;$mitm=18;break;
    case 'play':
      $mitm=15;$page=36;$test=-1;$havepl=0;
      if(isset($arqs[1]) && !is_numeric($arqs[1])){
        $row = rs('select playlistid from playlist where playlisttitle like \''.left($arqs[1], 9).'%\'');
        if($row){$book=$row[0];$havepl=1;}
      }
      if($havepl==0) $book=((isset($arqs[1]))?(int) preg_replace('/\D/', '', $arqs[1]):0);
      $idx=99;
      break;
    case 'topi':
      $mitm=16;$page=33;$havet = 0;
      if(isset($arqs[1]) && !is_numeric($arqs[1])){
        $row = rs('select topicid from topic where topic like \''.left($arqs[1], 7).'%\'');
        if($row){$book=$row[0];$havet=1;}
      }
      if($havet==0) $book=((isset($arqs[1]))?(int) preg_replace('/\D/', '', $arqs[1]):0);
      $idx=99;
      break;
    case 'blog':
      $test=9;
      if(isset($arqs[1])){
        $page=26;
        $book=(int) preg_replace('/\D/', '', $arqs[1]);
      }else{$page=25;}
      if($book==0) $book=-1; // can't remember why I did this
      $mitm=9;$idx=99;
  }
  if(isset($arqs[$idx]) && (left($arqs[$idx],5)!= 'robot')){
    $tmpbook = str_replace('-', ' ', $arqs[$idx]);
    $tmpbook = str_replace('_', ' ', $tmpbook);
    $srchbook= str_replace(' ', '', $tmpbook);
    // first check for bible book
    $row = rs('select testament, book, chapters from book where aliases like \'%~'.$srchbook.'~%\' and testament in (0,1) ');
    if($row){                     // we've found a book
      $test = $row[0];            // grab the testament
      $book = $row[1];            // grab the book
      $page = (($comm==0)?0:4);   // set page, 0=viewbible, 4=viewcomm
      if($outl==1){               // book outline
        $page=24;$chap=0;$vers=0; 
      }elseif($bcomm==1){         // book commentary
        $page=10;$chap=0;$vers=0;
        if(isset($arqs[$idx+1])){ // see if we have a logid for whatsnew
          $glogid = (int) preg_replace('/\D/', '', $arqs[$idx+1]); 
          if($glogid==0) $glogid=-1; // invalid whatsnew logid
        }
      }else{ // continue checking params
        if(isset($arqs[$idx+1])){ // we have a chapter
          $chap = (int) preg_replace('/\D/', '', $arqs[$idx+1]); // remove non-numbers
          if($chap==0) $chap=-1; // load entire book
          elseif($chap>$row[2]) $chap = $row[2]; // if chap>chaps_in_book, set to last
        }else $chap=1;  // default to chap 1
        if(isset($arqs[$idx+2])){ // we have a verse
          if(left($arqs[$idx+2],3)=='nav'){ // user navigating to verse
            $vnav=-1; // temp flag
            if(isset($arqs[$idx+3]) && $arqs[$idx+3]=='ct'){$showclosetab=1;}
          }
          if(left($arqs[$idx+2],4)=='head'){ // from outline, navigating to heading
            $vhed=-1; // temp flag
            if(isset($arqs[$idx+3]) && $arqs[$idx+3]=='ct'){$showclosetab=1;}
          }
          if(isset($arqs[$idx+3]) && $arqs[$idx+3]=='tc'){$showtochapter=1;}
          if(isset($arqs[$idx+4]) && $arqs[$idx+4]=='fe'){$fromedit=1;}
          if($arqs[$idx+2]=='ct'){
            $showclosetab=1;
            $arqs[$idx+2]=0;
          }
          if($comm==0) $page=(($vnav==0 && $vhed==0)?5:0);
          $vers = (int) preg_replace('/\D/', '', $arqs[$idx+2]);
          if($vers==0) {$page=0;};
          $row = rs('select max(verse) from verse where testament='.$test.' and book='.$book.' and chapter='.$chap.' ');
          if($vers>$row[0]) $vers=$row[0];
          if($vnav==-1) $vnav=$vers;
          if($vhed==-1) $vhed=$vers;
          if(isset($arqs[$idx+3])){
            // marker from whatsnew
            $glogid = (int) preg_replace('/\D/', '', $arqs[$idx+3]);
            if($glogid==0) $glogid=-1;
          }
        }else $vers = -1;
      }
    }else{ // check for intro/appendix/wordstudy
      $look=0;
      if(left($arqs[0],4)=='info'){$look=1;$test=2;$mitm=1; $page=14;$idx=1;}
      if(left($arqs[0],3)=='app') {$look=1;$test=3;$mitm=12;$page=14;$idx=1;}
      if(left($arqs[0],4)=='word' || 
         left($arqs[0],4)=='figu'){$look=1;$test=4;$mitm=0;$page=14;$idx=1;}
      if($look==1){
        if(isset($arqs[$idx])){
          if(is_numeric($arqs[$idx])){ // search for info, appx, ws by number
            $book = $arqs[$idx];
            $row = rs('select book from book where testament='.$test.' and book='.$book.(($edit==1)?' ':' and active=1 '));
            if(!$row) $book=1;
            else if($row[0]==3) $mitm=14;
          }else{ // search for info, appx, or ws by title
            //$row = rs('select book from book where testament='.$test.' and ifnull(tagline, title) like \'%'.str_replace('-',' ',$arqs[$idx]).'%\''.(($edit==1)?' ':' and active=1 '));
            $row = rs('select book from book where 
                       testament='.$test.' 
                       and (ifnull(title, \'\') like \'%'.str_replace('-',' ',$arqs[$idx]).'%\' 
                       or ifnull(tagline, \'\') like \'%'.str_replace('-',' ',$arqs[$idx]).'%\')'.
                       (($edit==1)?' ':' and active=1 '));
            if($row) $book = $row[0];
          }
          if($arqs[sizeof($arqs)-1]=='bb') $showback=1;
          if($arqs[sizeof($arqs)-1]=='ct') $showclosetab=1;
          if(isset($arqs[$idx+1])){
            $glogid = (int) preg_replace('/\D/', '', $arqs[$idx+1]);
            if($glogid==0) $glogid=-1;
          }
        }
      }else{
        // hopefully bad input from srchtext on quicknav
        $vnav = -2;
      }
    }
  }
  if($pref==1) $page=9;
  if(!isset($vers)) $vers=1;  // safety...
}else{ // $qs is not set
  $arloc = explode(';', ((isset($_COOKIE['rev_lastloc']))?$_COOKIE['rev_lastloc']??'':'3;0;1;40;1;0'));
  $mitm = (isset($_POST["mitm"]))?$_POST["mitm"]:$arloc[0];
  if($arloc[1]==13) $arloc[1]=0;
  if($page!=-1) $page = (isset($_POST["page"]))?$_POST["page"]:$arloc[1];
  $test = (isset($_POST["test"]))?$_POST["test"]:$arloc[2];
  $book = (isset($_POST["book"]))?$_POST["book"]:$arloc[3];
  $chap = (isset($_POST["chap"]))?$_POST["chap"]:$arloc[4];
  $vers = (isset($_POST["vers"]))?$_POST["vers"]:$arloc[5];
  $qs   = (isset($_POST["qs"]))?$_POST["qs"]:'';
  // trying to load bible page on first load
  // if there is no referrer, load bible cookie
  // this might cause issues...
  $ref =  ((isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:'xxx');
  if($viewsrc==0 && strpos($ref, 'revised')===FALSE && strpos($ref, 'revdev')===FALSE && strpos($ref, 'revbible')===FALSE && $page!=14) $bcuk=1;
}

if($bcuk==1){
  $arloc = explode(';', ((isset($_COOKIE['rev_lastbibleloc']))?$_COOKIE['rev_lastbibleloc']??'':'3;0;1;40;1;0'));
  if(sizeof($arloc)<5) $arloc = explode(';', '3;0;1;40;1;0');
  $mitm = $arloc[0];
  $page = $arloc[1];
  $test = $arloc[2];
  $book = $arloc[3];
  $chap = $arloc[4];
  $vers = -1; //$arloc[5];
}
if($inapp==1) $commnewtab=0;

checkmyrevlogin();

if($userid==1){
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

getmetadata($page,$test,$book,$chap,$vers);
updatehistory();

$logexcludeips = array();
$result = dbquery('select ipaddress, ipid from logexcludeips');
while($row = mysqli_fetch_array($result)) {
  array_push($logexcludeips, array($row[0], $row[1]));
}

$navstring = $test.','.$book.','.$chap.','.$vers;

$tzoffset = ((isset($_COOKIE['rev_timezone']))?$_COOKIE['rev_timezone']:0);
$isdst = date('I');
$timezone = timezone_name_from_abbr("", $tzoffset*60);
if($timezone=='') $timezone = 'America/New_York'; // yeouch!

//
//
// misc functions
//
//

function opendb(){
  global $dbserv, $dbuser, $dbpass, $dbname;
  //mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
  mysqli_report(MYSQLI_REPORT_ERROR);
  $dtb = mysqli_connect ($dbserv, $dbuser, $dbpass, $dbname) or die ("Could not connect to REV database");
  mysqli_query($dtb, 'SET NAMES utf8');
  mysqli_query($dtb, 'SET CHARACTER SET utf8');
  mysqli_query($dtb, 'SET sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'))');
  mysqli_set_charset($dtb, 'utf8');
  return $dtb;
}

function dbquery($q) {
  global $db, $sqlerr, $page, $dbhits;
  // at least some injection protection
  $q = str_replace('<scr', '', $q);
  $q = str_replace('cast(', '', $q);
  $result = mysqli_query($db, $q);
  if ($result === false) {
    $sqlerr = mysqli_error($db);
    file_put_contents("logs/mysql.log", date("m/d/Y H:i", time())."\r\nScript name: ".$_SERVER['PHP_SELF'].": Page: ".$page."\r\nMySQL error: ".$sqlerr."\r\nQuery string: ".$q."\r\n\r\n" , FILE_APPEND);
  }
  $dbhits++;
  return $result;
}

function rs($s){
  return mysqli_fetch_array(dbquery($s));
}

function logview($p,$t,$b,$c,$v,$m=''){
  global $userid, $ismobile, $logexcludeips, $inapp, $isbingbot, $isgoogbot;
  $ip = ((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'unknown');
  if($isbingbot==1) $ip = '0.0.0.1';
  if($isgoogbot==1) $ip = '0.0.0.2';
  $m = trim(str_replace('\'', '\\\'',substr($m??'', 0, 97)));
  $sql = 'insert into viewlogs(userid, remoteip, page, testament, book, chapter, verse, viewtime, mobile, misc) values ('.
         (($inapp==1)?-7:$userid).',\''.$ip.'\','.$p.','.$t.','.$b.','.$c.','.$v.',UTC_TIMESTAMP(),'.$ismobile.','.(($m=='')?'null':'\''.$m.'\'').') ';

  $ins = 1;
  if(is_array($logexcludeips))
    $max = sizeof($logexcludeips);
  else $max=0;
  for ($i=0;$i<$max;$i++) {
    $testip = str_replace('*','',$logexcludeips[$i][0]);
    if(strpos($ip, $testip) === 0){
      $ins=0;
      $update = dbquery('update logexcludeips set hits = hits+1, lastview = UTC_TIMESTAMP() where ipid='.$logexcludeips[$i][1].' ');
      break;
    }
  }
  if($ins) $insert = dbquery($sql);
}

function checkmyrevlogin(){
  global $ismobile, $myrevsid, $myrevid, $myrevname, $myrevmode, $myrevsort, $myrevpagsiz, $myrevshownotes, $myrevclick, $myrevshoweditorfirst;
  global $myrevkeys, $myrevshowkey, $hilitecolors, $history;
  global $userid, $username, $canedit, $appxedit, $resedit, $superman, $chronedit, $editorcomments, $ednotesshowall, $viewedcomments, $viewmrcomments;
  global $peernotes, $peernotesshowall, $viewpeernotes;
  if($myrevsid=='public'){
    $myrevid = 0;
    $myrevname = "";
    $history = ((isset($_COOKIE['rev_history']))?$_COOKIE['rev_history']:'');
    $bookmarks = ((isset($_COOKIE['rev_bookmarks']))?$_COOKIE['rev_bookmarks']:'');
  }else{
    $sql = 'select myrevid, myrevname, myrevkeys, history, ifnull(bookmarks, \'\') bookmarks, userid, ifnull(permissions, \'0:0:0:0:0:0:0\') permissions, ifnull(revusername,\'\') revusername from myrevusers where cursession = \''.$myrevsid.'\' ';
    $row = rs($sql);
    if($row){
      $myrevid   = $row['myrevid'];
      $myrevname = $row['myrevname'];
      $update     = dbquery('update myrevusers set lastaccessed = UTC_TIMESTAMP(), ipaddress = \''.((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'unknown').'\' where myrevid = '.$myrevid.' ');

      // myrev prefs cookie
      $arrdrprefs = explode(';', (isset($_COOKIE['rev_myrevprefs']))?$_COOKIE['rev_myrevprefs']??'':(($ismobile==1)?'compact':'full').';canon;'.(($ismobile==1)?'20':'50').';0;1;0;0;0;1;1;0;1');
      $myrevmode = $arrdrprefs[0];
      $myrevsort = $arrdrprefs[1];
      $myrevpagsiz = $arrdrprefs[2];
      $myrevshownotes = isset($arrdrprefs[3])?$arrdrprefs[3]:0;
      $myrevclick = isset($arrdrprefs[4])?$arrdrprefs[4]:1;
      $ednotesshowall = isset($arrdrprefs[5])?$arrdrprefs[5]:0;  // show all ednotes, or resolved only
      $myrevshowkey = isset($arrdrprefs[6])?$arrdrprefs[6]:0;
      $myrevshoweditorfirst = isset($arrdrprefs[7])?$arrdrprefs[7]:0;
      $viewedcomments = isset($arrdrprefs[8])?$arrdrprefs[8]:1;
      $viewmrcomments = isset($arrdrprefs[9])?$arrdrprefs[9]:1;
      $peernotesshowall = isset($arrdrprefs[10])?$arrdrprefs[10]:0;  // show all peernotes, or resolved only
      $viewpeernotes = isset($arrdrprefs[11])?$arrdrprefs[11]:1;

      $tmp = $row['myrevkeys'];
      if($tmp===null) $tmp = 'Clear'.substr('~~~~~~~~~~',0,sizeof($hilitecolors));
      $myrevkeys = explode('~',$tmp??'');
      for($ni=0;$ni<sizeof($myrevkeys);$ni++){
        $myrevkeys[$ni] = (($myrevkeys[$ni]=='')?'&nbsp;':$myrevkeys[$ni]);
      }
      $history = (($row['history'])?$row['history']:((isset($_COOKIE['rev_history']))?$_COOKIE['rev_history']:''));
      $bookmarks = $row['bookmarks'];
      if($row['userid']!=0){
        $perms = explode(':', $row['permissions']);
        $userid   = $row['userid'];
        $username = (($row['revusername']=='')?$myrevname:$row['revusername']);
        $superman = $perms[0];
        $canedit  = $perms[1];
        $appxedit = $perms[2];
        $resedit  = $perms[3];
        $chronedit= $perms[4];
        $editorcomments= $perms[5];
        $peernotes= isset($perms[6])?$perms[6]:0;
      }else{
        $userid   = 0;
        $username = $myrevname;
        $superman = 0;
        $canedit  = 0;
        $appxedit = 0;
        $resedit  = 0;
        $chronedit= 0;
        $editorcomments= 0;
        $peernotes= 0;
      }
    }else{
      $myrevsid = 'public';
      $myrevid = 0;
      $myrevname = '';
      $history = ((isset($_COOKIE['rev_history']))?$_COOKIE['rev_history']:'');
      $bookmarks = ((isset($_COOKIE['rev_bookmarks']))?$_COOKIE['rev_bookmarks']:'');
      $userid   = 0;
      $username = "";
      $canedit  = 0;
      $appxedit = 0;
      $resedit  = 0;
      $chronedit= 0;
      $editorcomments= 0;
      $ednotesshowall= 0;
      $viewedcomments= 0;
      $viewmrcomments = 0;
      $peernotes= 0;
      $peernotesshowall= 0;
      $viewpeernotes= 0;
      $superman = 0;
    }
  }
  setcookie('rev_bookmarks', $bookmarks, time() + (86400 * 180), "/"); // jsonbookmarks.php reads the cookie...
}

function getmetadata($p, $t, $b, $c, $v){
  global $site, $metatitle, $metadesc, $srchstring, $screenwidth, $screenwidthsmall;

  $script = basename($_SERVER['PHP_SELF']);
  if($script=='jsonbiblenav.php') return;

  //print('sw:'.$screenwidth.'<br />');
  //print('swsm:'.$screenwidthsmall.'<br />');

  // need to construct title, keywords, description
  $metatitle = 'Revised English Version';
  $metadesc = 'The Revised English Version of the Bible and Commentary';

  // check for sql injection attempt
  if(!(preg_match('#^-?[0-9]{1,3}$#', $p) &&
       preg_match('#^-?[0-9]{1,4}$#', $t) &&  // allow 4-digit testament for logdetail.php
       preg_match('#^-?[0-9]{1,3}$#', $b) &&
       preg_match('#^-?[0-9]{1,3}$#', $c) &&
       preg_match('#^-?[0-9]{1,3}$#', $v))){
    $erstr = '<span style="color:red">Hack atmpt: ~~</span>';
    if(!(preg_match('#^-?[0-9]{1,3}$#', $p))) logview(452,0,0,0,0,str_replace('~~', 'pg', $erstr));
    if(!(preg_match('#^-?[0-9]{1,4}$#', $t))) logview(452,0,0,0,0,str_replace('~~', 'tt', $erstr));
    if(!(preg_match('#^-?[0-9]{1,3}$#', $b))) logview(452,0,0,0,0,str_replace('~~', 'bk', $erstr));
    if(!(preg_match('#^-?[0-9]{1,3}$#', $c))) logview(452,0,0,0,0,str_replace('~~', 'ch', $erstr));
    if(!(preg_match('#^-?[0-9]{1,3}$#', $v))) logview(452,0,0,0,0,str_replace('~~', 'vs', $erstr));
    die('');
  }
  $sql = 'select versetext from verse where testament = '.$t.' and book = '.$b.' and chapter = '.(($c<1)?1:$c).' and verse = '.(($v<1)?1:$v).' ';
  $row = rs($sql);
  $vtxt = (($row)?stripverse(left($row[0].'', 300)):'');
  $sql = 'no';
  switch ($p) {
  case 0: //viewbible
    if($b>0){
      $bibleref = getbooktitle($t, $b, 0).(($c>0)?' '.$c:'');
      $metatitle = $bibleref.', REV Bible and Commentary';
      $metadesc = $bibleref.' REV - '.left($vtxt, 90).' - Bible verse';
    }
    break;
  case 4:  //viewcomm
    if($b>0){
      $bibleref = getbooktitle($t, $b, 0).' '.$c;
      $metatitle = $bibleref.', REV Bible and Commentary';
      $metadesc = $bibleref.' REV and Commentary - '.left($vtxt, 90).' - Bible verse';
    }
    break;
  case 5: //viewverscomm
    $bibleref = getbooktitle($t, $b, 0).' '.$c.':'.$v;
    $metatitle = $bibleref.', REV Bible and Commentary';
    $metadesc = $bibleref.' REV and Commentary - '.left($vtxt, 90).' - Bible verse';
    $sql = 'select metadesc from verse
            where testament = '.$t.'
            and book = '.$b.'
            and chapter = '.$c.'
            and verse = '.$v.' ';
    break;
  case 13: // sopslanding
    $bibleref = getbooktitle($t, $b, 0).' '.$c.':'.$v;
    $metatitle = $bibleref.' ..parked';
    break;
  case 14: //viewappxintro
    if($b>0){
      $row = rs('select ifnull(tagline, title) from book where testament = '.$t.' and book = '.$b.' ');
      $bibleref = str_replace('&ldquo;','',$row[0]);
      $bibleref = str_replace('&rdquo;','',$bibleref);
      $metatitle = $bibleref.', REV Bible and Commentary';
      $metadesc = 'REV Commentary - '.$bibleref;
      $sql = 'select metadesc from verse
              where testament = '.$t.'
              and book = '.$b.'
              and chapter = '.$c.'
              and verse = '.$v.' ';
    }
    break;
  case 10: //viewbookcomm
    $bibleref = getbooktitle($t, $b, 0);
    //$metatitle = 'REV - Commentary for '.$bibleref;
    $metatitle = $bibleref.', REV Bible and Commentary';
    $metadesc = 'REV - Commentary for '.$bibleref;
    $sql = 'select metadesc from book
            where testament = '.$t.'
            and book = '.$b.' ';
    break;
  default:
    // do nothing
  };
  if($site!='www.revisedenglishversion.com') $metatitle = '!DEV! '.$metatitle;
  if($sql!='no'){
    $row = rs($sql);
    if($row){if(!is_null($row[0])){$metadesc = 'REV and Commentary - '.$bibleref.' - '.$row[0].(($p<6)?' - Bible verse':'');}}
  }

  // srchstring for nav textbox
  $srchstring='Enter a Verse';
  $getabbr = (($screenwidth<$screenwidthsmall)?1:0);
  switch ($p) {
  case 0: //viewbible
    if($b>0){
      $sql = 'select '.(($getabbr)?'abbr':'title').', chapters from book where testament = '.$t.' and book = '.$b.' ';
      $row = rs($sql);
      if($row) $srchstring = $row[0].(($row['chapters']>1 && $c>0)?' '.$c:'');
    }
    break;
  case 4: //viewcomm
    if($b>0){
      $sql = 'select '.(($getabbr)?'abbr':'title').', chapters from book where testament = '.$t.' and book = '.$b.' ';
      $row = rs($sql);
      if($row) $srchstring = $row[0].(($row['chapters']>1)?' '.$c:'').' Commentary';
    }
    break;
  case 1: //editverscomm
  case 5: //viewverscomm
    $shortabbr = getshortbookabbr($t, $b, 2);
    $sql = 'select '.(($getabbr)?'abbr':'title').', chapters from book where testament = '.$t.' and book = '.$b.' ';
    $row = rs($sql);
    //if($row) $srchstring = $row[0].' '.(($row['chapters']>1)?$c.':':'').$v.(($getabbr)?'':' Commentary');
    if($row) $srchstring = (($getabbr)?$shortabbr:$row[0]).' '.(($row['chapters']>1)?$c.':':'').$v.(($getabbr)?' Cm':' Commentary');
    break;
  case 14: //viewappxintro
    if($b>0){
      $row = rs('select title from book where testament = '.$t.' and book = '.$b.' ');
      $bibleref = str_replace('&ldquo;','',$row[0]);
      $bibleref = str_replace('&rdquo;','',$bibleref);
      $srchstring = (($t==4)?'Wordstudy: ':'').$bibleref;
    }
    break;
  case 10: //viewbookcomm
    $srchstring = getbooktitle($t, $b, $getabbr).' '.(($getabbr==1)?'Intro':'Introduction');
    break;
  default:
    // do nothing
  };

}

function updatehistory(){
  global $page, $test, $book, $chap, $vers, $vhed;
  $arpages = array(0,4,5,10,14,20,25,26,30,33,36,41,24); // see index.php
  if(in_array($page, $arpages)){
    if(($page==0||$page==4||$page==14) && ($book==0)) return;
    processhistory($page.':'.$test.':'.$book.':'.$chap.':'.$vers.':'.$vhed, 0);
  }
}

function processhistory($itm, $jscuk){
  global $myrevid, $history, $jsonurl;
  $ret='';
  $historysize=25;
  $arhistory = explode('~', $history??'');
  if($itm!==$arhistory[0]){
    array_unshift($arhistory, $itm);
    if(count($arhistory)>($historysize+1)) $arhistory = array_slice($arhistory, 0, ($historysize+1));
    $history = join('~', $arhistory);
    if($myrevid>0) // save to database
      $qry = dbquery('update myrevusers set history = '.(($history!==null)?'\''.$history.'\'':'null').' where myrevid = '.$myrevid.' ');
    if($jscuk==0) setcookie('rev_history', $history, time() + (86400 * 180), "/");
    else $ret = '<script>setCookie(\'rev_history\', \''.$history.'\', cookieexpiredays);</script>';
  }
  return $ret;
}

function stripverse($v){
  if(left($v, 4)=='[br]') $v = substr($v, 4);
  $v = str_replace('[fn]',' ', $v);
  $v = str_replace('<em>',' ', $v);
  $v = str_replace('</em>',' ', $v);
  $v = str_replace('<b>',' ', $v);
  $v = str_replace('</b>',' ', $v);
  $v = str_replace('[mvh]',' ', $v);
  $v = str_replace('[pg][hp]',' ', $v);
  $v = str_replace('[pg]',' ', $v);
  $v = str_replace('[bq]',' ', $v);
  $v = str_replace('[/bq]',' ', $v);
  $v = str_replace('[br]',' ', $v);
  $v = str_replace('[hp]',' ', $v);
  $v = str_replace('[lb]',' ', $v);
  $v = str_replace('[hpbegin]',' ', $v);
  $v = str_replace('[hpend]',' ', $v);
  $v = str_replace('[listbegin]',' ', $v);
  $v = str_replace('[listend]',' ', $v);
  $v = str_replace('&ldquo;','', $v);
  $v = str_replace('&rdquo;','', $v);
  $v = str_replace('&lsquo;','', $v);
  $v = str_replace('&rsquo;','', $v);
  $v = str_replace('<strong>','', $v);
  $v = str_replace('</strong>','', $v);
  $v = str_replace('<br />','', $v);
  $v = str_replace(' .','.', $v);
  $v = preg_replace('#\s+#', ' ', $v);
  return $v;
}

function keygen($ln){
  $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $lchars = strlen($chars);
  $skey = '';
  while(strlen($skey)<$ln){
    $idx = mt_rand(0, $lchars);
    $skey.=substr($chars,$idx,1);
  }
  return $skey;
}

function getbooktitle($t,$b, $abr){
  $sql = 'select title, abbr from book where testament = '.$t.' and book = '.$b.' ';
  $row = rs($sql);
  if ($row) return (($abr)?$row[1]:$row[0]);
  else return 'unknown';
}

// return link to external site for other translations
function getothertranslationlink($tit, $ch, $vs, $spc=0){
  global $colors;
  return (($spc==1)?' ':'').'<a href="https://www.biblegateway.com/verse/en/'.str_replace(' ', '_', $tit).'_'.$ch.':'.$vs.'" target="bgateway" title="other translations"><img src="/i/paratranslations'.$colors[0].'.png" style="height:1.1em;margin-bottom:-3px;" alt="see other translations" title="other translations" /></a>';
}

/*
*
*
*
*/
function getbooktagline($t,$b){
  $sql = 'select ifnull(tagline, title) from book where testament = '.$t.' and book = '.$b.' ';
  $row = rs($sql);
  if ($row) return $row[0];
  else return 'unknown';
}

function getshortbookabbr($t,$b, $size=3){
  $sql = 'select ifnull(aliases, abbr) from book where testament = '.$t.' and book = '.$b.' ';
  $row = rs($sql);
  if ($row){
    $abr = explode('~', $row[0]??'');
    $tmp = $abr[0];
    for ($x=0;$x<sizeof($abr);$x++) {
      if((strlen($abr[$x])>0 && (strlen($abr[$x])<strlen($tmp)) && strlen($abr[$x]) >= $size) || strlen($tmp)==0)
        $tmp = $abr[$x];
    }
    if(is_numeric(substr($tmp,0,1))) $tmp = substr($tmp,0,1).ucfirst(substr($tmp,1));
    else $tmp = ucfirst($tmp);
    return $tmp;
  }
  else return '??';
}

function nvl($v,$d){
  if(!isset($v)||is_null($v)) return $d;
  else{
    $v=trim($v);
    if($v=="") return $d;
    else return $v;
  }
}

function fixchk($v){
  return ($v!=0)?" checked=\"checked\"":"";
}

function fixrad($bln){
  return ($bln)?" checked=\"checked\"":"";
}

function fixsel($d, $v){
  return (strtolower($d)==strtolower($v))?" selected=\"selected\"":"";
}
function right($txt, $num){
  return substr($txt, -$num);
}
function left($txt, $num){
  return substr($txt, 0, $num);
}

function fixverse($txt){
  //print($txt.'<br />');
  global $ucaseot, $test;
  $txt = str_replace('[[','<span class="rNotInText">[[', $txt);
  $txt = str_replace(']]',']]</span>', $txt);
  if(strpos($txt, '[[')!==false && strpos($txt, ']]')===false)
    $txt.= '</span>';
  $txt = str_replace('[br]','<br />', $txt);
  $txt = str_replace('[mvhmark]','', $txt);
  $txt = str_replace('[mvsmark]','', $txt);
  $txt = str_replace('[hp]',' ', $txt);
  $txt = str_replace('[lb]',' ', $txt);
  $txt = str_replace('[mvh]',' ', $txt);
  $txt = str_replace('[mvs]',' ', $txt);
  if(left($txt,1)=='~'){
    $txt = str_replace('~','<span class="rNotInText">', $txt);
    $txt = str_replace('<em>','<em class="rNotInText">', $txt);
    $txt.= '</span>';
  }
  if($test==1 && $ucaseot==1 && strpos($txt, '<strong>')!==false){
    $txt = str_replace('&ldquo;', '[ldq]', $txt);
    $txt = str_replace('&rdquo;', '[rdq]', $txt);
    $txt = str_replace('&lsquo;', '[lsq]', $txt);
    $txt = str_replace('&rsquo;', '[rsq]', $txt);
    $txt = str_replace('&mdash;', '[mdh]', $txt);
    $txt = preg_replace_callback('#(.*?)<strong>(.*?)</strong>(.*?)#', function($m){return $m[1].'<strong>'.strtoupper($m[2]).'</strong>'.$m[3];}, $txt);
    $txt = str_ireplace('[ldq]', '&ldquo;', $txt);
    $txt = str_ireplace('[rdq]', '&rdquo;', $txt);
    $txt = str_ireplace('[lsq]', '&lsquo;', $txt);
    $txt = str_ireplace('[rsq]', '&rsquo;', $txt);
    $txt = str_ireplace('[mdh]', '&mdash;', $txt);
  }
  //print($txt.'<br />');
  return $txt;
}

function editlink($idx,$edt,$mi,$pg,$tst,$bk,$ch,$v){
  global $ismobile;
  return '<input type="image" src="/i/edit.gif" id="'.$idx.'" class="edtlink'.(($edt=='none')?'off':'on').(($ismobile)?' edtlinkmob':' edtlinkpc').'" onclick="return navigate('.$mi.','.$pg.','.$tst.','.$bk.','.$ch.','.$v.');" alt="edit" />';
}

function printsqlerr($e){
  if($e) return '<span style="color:red;font-size:90%;font-weight:bold;">'.$e.'</span>';
}

function processfootnotes($arfn, $vers, $ftnotes, $fncnt, $c, $v){
  global $fncnt, $arfn, $book, $vsfncnt;
  $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
  $fword = "[fn]";
  $arfnotes = explode('~~', $ftnotes??'');
  $havefootnote = ((strpos($vers, $fword)>-1)?strpos($vers, $fword):-1);
  $nf = $vsfncnt;
  $arjs = 'fn_b'.$book.'_c'.$c;
  $idstr = 'b'.$book.'_c'.$c.'_';
  while($havefootnote>-1){
    if(isset($arfnotes[$nf]) && $arfnotes[$nf] != ''){
      $arfn[$fncnt] = $v.'~~'.$arfnotes[$nf];
      $fnind = substr($footnoteindicator, ($fncnt%26), 1);
      $tmp = '<sup class="fnmark" id="ft'.$idstr.$fncnt.'" onmouseover="pophandleLinkMouseOver(\''.$fnind.'\', \''.$arjs.'['.$fncnt.']'.'\')" onmouseout="pophandleLinkMouseOut()">'.$fnind.'</sup>';
      $fncnt++;
      $vsfncnt++;
    }else{
      $tmp = '';
    }
    $vers = substr($vers, 0, ($havefootnote)).$tmp.substr($vers, ($havefootnote+4));
    $nf++;
    $havefootnote = ((strpos($vers, $fword)>-1)?strpos($vers, $fword):-1);
  }
  return $vers;
}

function displayfootnotes($fncnt, $arfn, $tbl, $c){
  global $colors, $fncnt, $arfn, $book;
  //die(print_r($arfn));
  if($fncnt > 0){
    $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
    if($tbl) {print('<tr><td colspan=2><hr style="height:1px;color:'.$colors[7].'" />');}
    else {print('<hr />');}
    for($nf=0;$nf<$fncnt;$nf++){
      $artmp = explode('~~', $arfn[$nf]??'');
      $idstr = 'b'.$book.'_c'.$c.'_'.$nf;
      $clk = 'hilitefoot(\'sup'.$idstr.'\', \''.$idstr.'\');';
      $v = substr($footnoteindicator, ($nf%26), 1).'<span style="color:'.$colors[7].'">['.$artmp[0].']</span>';
      print('<div style="display:table-row;">');
      print('<div style="display:table-cell;text-align:right;padding:1px;line-height:1;"><sup id="sup'.$idstr.'" class="supfootnote" onclick="'.$clk.'" title="Click to see where this footnote is referenced">'.$v.'</sup></div>');
      $tmpfn = str_replace(' & ', ' &amp; ', $artmp[1]);
      if(strpos($tmpfn, ' ') > -1){
        $tmpfn = preg_replace('# #', '</span> ', $tmpfn, 1);
      }else $tmpfn.= '</span>';
      //$tmpfn = str_replace('[noparse]','<noparse>', str_replace('[/noparse]','</noparse>',$tmpfn));
      print('<div style="display:table-cell;padding:1px;" class="footnote"> <span style="cursor:pointer;" onclick="'.$clk.'" title="Click to see where this footnote is referenced">'.$tmpfn.'</div>'.crlf);
      print('</div>'.crlf);
    }
    if($tbl){print('</td></tr>'.crlf);}
    else{print(crlf);}

    // popup footnotes
    print('<script>'.crlf);
    $arjs = 'fn_b'.$book.'_c'.$c;
    print('var '.$arjs.' = new Array();'.crlf);
    for($nf=0;$nf<$fncnt;$nf++){
      $artmp = explode('~~', $arfn[$nf]??'');
      print($arjs.'['.$nf.'] = \''.str_replace('\'', '\\\'', $artmp[1]).'\';'.crlf);
    }
    print('</script>'.crlf);

    $fncnt = 0;
  }
}

function getfootnotes($t, $b, $c, $v, $typ){
  $fns = dbquery('select footnote from footnotes where testament = '.$t.' and book = '.$b.' and chapter = '.$c.' and verse = '.$v.' and fntype = \''.$typ.'\' order by fnidx ');
  $ret = '';
  while($row = mysqli_fetch_array($fns)) {
    $ret.= $row[0].'~~';
  }
  $ret = substr($ret, 0, -2);
  return $ret;
}

function savfootnotes($t, $b, $c, $v, $typ, $fnts){
  $arfns = explode('~~', $fnts.'');

  // this is temporary until I can figure out how to use GROUP_CONCAT on the search page
  if(is_null($fnts) || $fnts == '') $fnts = 'null';
  else $fnts = '\''.substr($fnts, 0, 6990).'\'';  // field is varchar(7000)
  $qry = dbquery('update verse set comfootnotes = '.$fnts.' where testament = '.$t.' and book = '.$b.' and chapter = '.$c.' and verse = '.$v.' ');
  // end temp code

  $idx=0;
  $qry = dbquery('delete from footnotes where testament = '.$t.' and book = '.$b.' and chapter = '.$c.' and verse = '.$v.' and fntype = \''.$typ.'\' ');
  for($ni=0;$ni<sizeof($arfns);$ni++){
    $fnt = trim($arfns[$ni]);
    if($fnt!=''){
      $qry = dbquery('insert into footnotes (testament, book, chapter, verse, fntype, fnidx, footnote) values ('.$t.', '.$b.', '.$c.', '.$v.', \''.$typ.'\', '.$idx.', \''.$fnt.'\')');
      $idx++;
    }
  }
}

function processcomfootnotes($arcomfn, $comm, $ftnotes, $comfncnt, $vrs){
  global $comfncnt, $arcomfn, $book, $chap;
  $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
  $fword = "[fn]";
  $arfnotes = explode('~~', $ftnotes??'');
  $havefootnote = ((strpos($comm, $fword)>-1)?strpos($comm, $fword):-1);
  $nf = 0;
  $arjs = 'com_fn_b'.$book.'_c'.$chap.'_v'.$vrs;
  while($havefootnote>-1 && isset($arfnotes[$nf])){
    if($arfnotes[$nf] != ''){
      $fpreidx = trim(substr(' '.$footnoteindicator, (intval($comfncnt/26)%26), 1));
      $fidx=substr($footnoteindicator, ($comfncnt%26), 1);
      $arcomfn[$comfncnt] = $arjs.'_'.$nf.$fidx.'~~'.$arfnotes[$nf];
      $tmp = '<sup class="fnmark" id="ft'.$arjs.'_'.$nf.$fidx.'" onmouseover="pophandleLinkMouseOver(\''.$fpreidx.$fidx.'\', \''.$arjs.'['.$nf.']'.'\')" onmouseout="pophandleLinkMouseOut()">'.$fpreidx.$fidx.'</sup>';
      $comfncnt++;
    }else{
      $tmp = '';
    }
    $comm = substr($comm, 0, ($havefootnote)).$tmp.substr($comm, ($havefootnote+4));
    $nf++;
    $havefootnote = ((strpos($comm, $fword)>-1)?strpos($comm, $fword):-1);
  }
  return str_replace($fword, '', $comm); // clean up remaining errant [fn] tags
}

function displaycomfootnotes($comfncnt, $arcomfn, $vrs){
  global $comfncnt, $arcomfn, $book, $chap;
  if($comfncnt > 0){
    $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
    print('<hr style="height:1px;color:#dddddd;margin-bottom:0;" /><div style="display:table;border-spacing:0;margin-bottom:10px;">');
    $numfns = 0;
    for($nf=0;$nf<$comfncnt;$nf++){
      $artmp = explode('~~', $arcomfn[$nf]??'');
      $clk = 'hilitefoot(\'sup'.$artmp[0].'\', \''.$artmp[0].'\');';
      $fpreidx = trim(substr(' '.$footnoteindicator, (intval($numfns/26)%26), 1));
      $v = '<span style="color:#cccccc">'.$fpreidx.substr($footnoteindicator, ($nf%26), 1).')</span>';
      print('<div style="display:table-row">');
      print('<div style="display:table-cell;text-align:right;padding:1px;line-height:1;"><sup id="sup'.$artmp[0].'" class="supfootnote" onclick="'.$clk.'" title="Click to see where this footnote is referenced">'.$v.'</sup></div>');
      $tmpfn = str_replace(' & ', ' &amp; ', $artmp[1]);
      if(strpos($tmpfn, ' ') > -1){
        $tmpfn = preg_replace('# #', '</span> ', $tmpfn, 1);
      }else $tmpfn.= '</span>';
      $tmpfn = str_replace('[noparse]','<noparse>', str_replace('[/noparse]','</noparse>',$tmpfn));
      print('<div style="display:table-cell;padding:1px;" class="footnote"> <span style="cursor:pointer;" onclick="'.$clk.'" title="Click to see where this footnote is referenced">'.$tmpfn.'</div>'.crlf);
      print('</div>'.crlf);
      $numfns++;
    }

    // popup footnotes
    print('</div><script>'.crlf);
    $arjs = 'com_fn_b'.$book.'_c'.$chap.'_v'.$vrs;
    print('var '.$arjs.' = new Array();'.crlf);
    for($nf=0;$nf<$comfncnt;$nf++){
      $artmp = explode('~~', $arcomfn[$nf]??'');
      $artmp[1] = str_replace('[noparse]','<noparse>', str_replace('[/noparse]','</noparse>',$artmp[1]));
      print($arjs.'['.$nf.'] = \''.str_replace('\'', '\\\'', $artmp[1]).'\';'.crlf);
    }
    print('</script>'.crlf);

    $comfncnt = 0;
    print(crlf);
  }
}

function loadapxids(){
  $ret='';
  $apx = dbquery('select book from book where testament = 3 and active = 1 order by book ');
  while($row = mysqli_fetch_array($apx)){
    $ret.= $row[0].',';
  }
  $ret = substr($ret, 0, (strlen($ret)-1));
  return $ret;
}

function processsqltext($txt, $siz, $allownull, $default){
  $ret = trim($txt??'');
  if($ret){
    $ret = preg_replace('#"+#', '', $ret);          // remove double quotes
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

function processsqlnumb($num, $siz, $allownull, $default){
  $ret = trim($num);
  if(is_numeric($ret)){
    if(!is_numeric($ret)) $ret = $default;
    if($ret > $siz) $ret = $default;
    return $ret;
  }else{
    if($allownull) return 'null';
    else return $default;
  }
}

function processsqlcomm($com, $allownull, $default){
  global $site;
  $ret = trim($com??'');
  if($ret){
    $ret = preg_replace('#(\r\n)+#', ' ', $ret);    // replace crlf
    $ret = preg_replace('#<span dir="rtl"(.*?)>#i', '<span dir="rtl">', $ret);     // remove style from spandir tags
    $ret = preg_replace('#<span lan(.*?)>#', '<span>', $ret);     // remove language from span tags

    $ret = str_replace('<span style="font-size:110%;font-variant:small-caps;">', '<spansmallcaps>', $ret);     // save smallcaps
    $ret = preg_replace('#<span sty(.*?)>#', '<span>', $ret);     // remove style from span tags
    while(strpos($ret, '<span>')!== false){
      $ret = preg_replace('#<span>(.*?)</span>#', '$1', $ret);      // remove empty span tags
    }
    $ret = preg_replace('#<meta(.*?)>#', '', $ret);               // remove meta tags
    $ret = str_replace('<spansmallcaps>', '<span style="font-size:110%;font-variant:small-caps;">', $ret);     // redo smallcaps
    $ret = str_replace(' class="MsoHeader"', '', $ret);           // remove MSW crap

    $ret = preg_replace('#<div sty(.*?)>#', '<div>', $ret);       // remove style from div tags
    $ret = preg_replace('#<div>(.*)</div>#', '$1', $ret);         // remove empty div tags
    $ret = preg_replace('#<blockquote sty(.*?)>#', '<blockquote>', $ret); // remove style from blockquote tags
    $ret = preg_replace('#<ul sty(.*?)>#', '<ul>', $ret);         // remove style from ul tags
    $ret = preg_replace('#<ol sty(.*?)>#', '<ol>', $ret);         // remove style from ol tags
    $ret = preg_replace('#<li sty(.*?)>#', '<li>', $ret);         // remove style from li tags
    $ret = preg_replace('#<strong sty(.*?)>#', '<strong>', $ret); // remove style from strong tags
    $ret = preg_replace('#<em sty(.*?)>#', '<em>', $ret);         // remove style from em tags
    $ret = preg_replace('#<p style="fo(.*?)>#', '<p>', $ret);     // remove font style from p tags
    $ret = preg_replace('#<p class="(.*?)>#', '<p>', $ret);     // remove class from p tags
    $ret = preg_replace('#<p align="(.*?)>#', '<p>', $ret);     // remove align from p tags
    // 20151118 the next statement was added to handle pastes of indented text from Word.
    $ret = preg_replace('#<p style="margin-left(.*?)>(.*?)</p>#', '<blockquote> <p>$2</p> </blockquote>', $ret);
    $ret = preg_replace('#<p style="mar(.*?)>#', '<p>', $ret);    // remove remaining margin styles from p tags
    $ret = str_replace('<p style="text-align:start">', '<p>', $ret);    // 20220717, don't know where these came from
    $ret = str_replace("“", "&ldquo;", $ret);
    $ret = str_replace("”", "&rdquo;", $ret);
    $ret = str_replace("‘", "&lsquo;", $ret);
    $ret = str_replace("’", "&rsquo;", $ret);

    $sdelim = '!~!';
    $ret = preg_replace('#&nbsp;+#', $sdelim, $ret);                       // replace hard spaces with placeholder
    $ret = preg_replace('#(<br />\s+'.$sdelim.')+#', '<br />&nbsp;', $ret);// put back hard space after <br />
    $ret = preg_replace('#('.$sdelim.')+#', ' ', $ret);                      // replace space placeholder
    $ret = preg_replace('#\s+#', ' ', $ret);        // replace repeating spaces
    $ret = preg_replace('#<p> #', '<p>', $ret);     // remove space following <p>, should be a better way
    $ret = preg_replace('#</p>\s+<p>#', '</p><p>', $ret);     // remove space between </p> <p>, should be a better way
    $ret = preg_replace('#<i>(.*?)</i>#', '<em>$1</em>', $ret);   // replace <i> tags with <em>
    $ret = preg_replace('#<b>(.*?)</b>#', '<strong>$1</strong>', $ret); // replace <b> tags with <strong>
    $ret = str_replace('</strong> <strong>', ' ', $ret); // remove unnecessary tags
    $ret = str_replace('</strong><strong>', '', $ret); // remove unnecessary tags
    $ret = str_replace('</em><em>', '', $ret); // remove unnecessary tags
    $ret = str_replace('</em> <em>', ' ', $ret); // remove unnecessary tags
    //* Tidy
    $tcfg = array(
                 'new-inline-tags'  => 'noparse',
                 'indent'           => false,
                 'output-xhtml'     => true,
                 'wrap'             => 99999,
                 'preserve-entities'=> 1,
                 'show-body-only'   => 1
                 );
    $tidy = new tidy;
    $tidy->parseString($ret, $tcfg, 'utf8');
    $tidy->cleanRepair();
    $ret = str_replace(PHP_EOL, ' ', (string) $tidy);
    $ret = str_replace(crlf, ' ', $ret);
    $ret = trim($ret);                              // re-trim
    if(strlen($ret)==0){                            // everything has been removed
      if($allownull) return 'null';
      else $ret = $default;
    }
    $ret = preg_replace('#\'#', '\\\'', $ret);      // escape single quotes
    return '\''.$ret.'\'';
  }else{
    if($allownull) return 'null';
    else return '\''.$default.'\'';
  }
}

function processsqlvers($vrs, $allownull, $default){
  $ret = trim($vrs);
  if($ret){
    $ret = preg_replace('#<span(.*?)>#', '<span>', $ret);     // remove style from span tags
    $ret = preg_replace('#<span>(.*?)</span>#', '$1', $ret);      // remove span tags
    $ret = preg_replace('#<blockquote sty(.*?)>#', '<blockquote>', $ret); // remove style from blockquote tags
    $ret = preg_replace('#<blockquote>(.*?)</blockquote>#', '$1', $ret);  // remove blockquote tags
    $ret = preg_replace('#<ul sty(.*?)>#', '<ul>', $ret);         // remove style from ul tags
    $ret = preg_replace('#<ul>(.*?)</ul>#', '$1', $ret);          // remove ul tags
    $ret = preg_replace('#<ol sty(.*?)>#', '<ol>', $ret);         // remove style from ol tags
    $ret = preg_replace('#<ol>(.*?)</ol>#', '$1', $ret);          // remove ol tags
    $ret = preg_replace('#<li sty(.*?)>#', '<li>', $ret);         // remove style from li tags
    $ret = preg_replace('#<li>(.*?)</li>#', '$1', $ret);          // remove li tags
    $ret = preg_replace('#<i>(.*?)</i>#', '<em>$1</em>', $ret);   // replace <i> tags with <em>
    $ret = preg_replace('#<b>(.*?)</b>#', '<strong>$1</strong>', $ret); // replace <b> tags with <strong>
    $ret = preg_replace('#<strong sty(.*?)>#', '<strong>', $ret); // remove style from strong tags
    $ret = preg_replace('#<em sty(.*?)>#', '<em>', $ret);         // remove style from em tags
    $ret = preg_replace('#<p sty(.*?)>#', '<p>', $ret);           // remove style from p tags
    $ret = preg_replace('#<p>(.*?)</p>#', '$1', $ret);            // remove p tags
    $ret = preg_replace('#(\r\n)+#', ' ', $ret);    // replace crlf
    $ret = preg_replace('#&nbsp;+#', ' ', $ret);    // replace hard spaces
    $ret = preg_replace('#\s+#', ' ', $ret);        // replace repeating spaces
    $ret = str_replace("“", "&ldquo;", $ret);
    $ret = str_replace("”", "&rdquo;", $ret);
    $ret = str_replace("‘", "&lsquo;", $ret);
    $ret = str_replace("’", "&rsquo;", $ret);
    $ret = str_replace(" [hp]", "[hp]", $ret);      // these are great, but I think there's a place where
    $ret = str_replace("[hp] ", "[hp]", $ret);
    $ret = str_replace("<o:p>", "", $ret);          // pastes from MSW
    $ret = str_replace("</o:p>", "", $ret);

    $ret = str_replace('<br />', '', $ret);         // might be from Renee hitting ENTER
    $ret = str_replace('<em> </em>', ' ', $ret);    // glitch in ckeditor
    $ret = trim($ret);                              // re-trim
    if(strlen($ret)==0){                            // everything has been removed
      if($allownull) return 'null';
      else $ret = $default;
    }
    $ret = preg_replace('#\'#', '\\\'', $ret);      // escape single quotes
    return '\''.$ret.'\'';
  }else{
    if($allownull) return 'null';
    else return '\''.$default.'\'';
  }
}

//
// used in viewbible and viewcomm
// viewappxintro has its own version
//
function setprevnextlinks($root){
  global $prevlink, $nextlink,$prevlinkact, $nextlinkact;
  global $page, $test, $book, $chap, $vers, $vnav, $vhed;;
  if($test>-1 && $book>0){
    $sql = 'select title from book where testament in (0,1) and book = '.$book.' ';
    $curbook=($row=rs($sql))?str_replace(' ','-',$row[0]):0;
    $sql = 'select title from book where testament in (0,1) and book = '.($book+1).' ';
    $nextbook=($row=rs($sql))?str_replace(' ','-',$row[0]):0;
    $sql = 'select title from book where testament in (0,1) and book = '.($book-1).' ';
    $prevbook=($row=rs($sql))?str_replace(' ','-',$row[0]):0;

    if($chap<1){ // reading entire book, or book commentary
      $link = '<a id="[id]" href="'.$root.'/[bk]'.(($root!='/book' && $root!='/outline')?'/all':'').'" title="[tit]">';
      if($nextbook) $nextlink = str_replace('[bk]', $nextbook, str_replace('[tit]', 'next', $link)).$nextlinkact.'</a>';
      if($prevbook) $prevlink = str_replace('[bk]', $prevbook, str_replace('[tit]', 'previous', $link)).$prevlinkact.'</a>';
    }else{ // we have a chapter
      $sql = 'select chapters from book where testament in (0,1) and book = '.$book.' ';
      $row = rs($sql);
      if($row[0] > $chap) $nextchap = ($chap+1);
      else $nextchap = 1;
      if($chap>1) $prevchap = ($chap-1);
      else{
        $sql = 'select chapters from book where testament in (0,1) and book = '.($book-1).' ';
        $prevchap =($row=rs($sql))?$row[0]:0;
      }
      if($vers<1 || $vnav!=0 || $vhed!=0){ // no verse
        $link = '<a id="[id]" href="'.$root.'/[bk]/[ch]" title="[tit]">';
        if($nextchap>$chap)
          $nextlink = str_replace('[ch]', ($chap+1), str_replace('[bk]', $curbook, str_replace('[tit]', 'next', $link))).$nextlinkact.'</a>';
        else if($nextbook)
          $nextlink = str_replace('[ch]', 1, str_replace('[bk]', ($nextbook), str_replace('[tit]', 'next', $link))).$nextlinkact.'</a>';
        if($chap==1)
          $prevlink =($prevchap>0)?str_replace('[ch]', $prevchap, str_replace('[bk]', ($prevbook), str_replace('[tit]', 'previous', $link))).$prevlinkact.'</a>':$prevlink;
        else
          $prevlink = str_replace('[ch]', ($chap-1), str_replace('[bk]', $curbook, str_replace('[tit]', 'previous', $link))).$prevlinkact.'</a>';
      }else{ // we have a verse
        $link = '<a id="[id]" href="'.$root.'/[bk]/[ch]/[vs]" title="[tit]">';
        if($vers==1){
          $thebook = (($chap==1)?$prevbook:$curbook);
          $thechap = $prevchap;
          $sql = 'select count(*) from verse where testament in (0,1) and book = '.(($chap==1)?($book-1):$book).' and chapter = '.$thechap.' ';
          $prevvers =($row=rs($sql))?$row[0]:0;
        }else{
          $thebook = $curbook;
          $thechap = $chap;
          $prevvers = $vers-1;
        }
        $prevlink = (($prevvers>0)?str_replace('[vs]', $prevvers, str_replace('[ch]', $thechap, str_replace('[bk]', ($thebook), str_replace('[tit]', 'previous', $link)))).$prevlinkact.'</a>':$prevlink);
        $sql = 'select count(*) from verse where testament in (0,1) and book = '.$book.' and chapter = '.$chap.' ';
        $maxvers = ($row=rs($sql))?$row[0]:0;
        if($vers<$maxvers){
          $thebook = $curbook;
          $thechap = $chap;
          $nextvers = ($vers+1);
        }else{
          $thebook = (($nextchap==1)?$nextbook:$curbook);
          $thechap = $nextchap;
          $nextvers=1;
        }
        $nextlink = (($thebook!='0' && $nextvers>0)?str_replace('[vs]', $nextvers, str_replace('[ch]', $thechap, str_replace('[bk]', ($thebook), str_replace('[tit]', 'next', $link)))).$nextlinkact.'</a>':$nextlink);
      }
    }
    $prevlink = str_replace('[id]', 'prevlinkid', $prevlink);
    $nextlink = str_replace('[id]', 'nextlinkid', $nextlink);
  }
}

function setprevnextlinksAI($root){
  global $prevlink, $nextlink, $prevlinkact, $nextlinkact;
  global $edit, $page, $test, $book;
  if($test > -1 && $book > 0){
    $sql = 'select '.(($test==4)?'ifnull(tagline,title)':'sqn').' from book where testament = '.$test.' and book = '.$book.' ';
    $row = rs($sql);
    $ttmp = $row[0];
    if($test==4) // wordstudies are sorted by the word itself
      $sql = 'select book, ifnull(tagline,title) from book where testament = '.$test.' and ifnull(tagline,title) > \''.$ttmp.'\''.(($edit)?'':' and active = 1 ').' order by 2 ';
    else // everything else (info and appx) are sorted by sqn
      $sql = 'select book, ifnull(tagline,title) from book where testament = '.$test.' and sqn > '.$ttmp.(($edit)?'':' and active = 1 ').' order by sqn ';
    $row = rs($sql);
    $nextbook =($row)?$row[0]:0;
    if($nextbook){
      $tmp = cleanquotes($row[1]);
      if(strpos($tmp, ':')) $tmp = trim(substr($tmp, strpos($tmp,':')+1));
      $nextbooktxt =str_replace(' ',(($test==4)?'_':'-'), preg_replace("/[^ \-\p{L}]+/u", "", $tmp));
    }
    if($test==4)
      $sql = 'select book, ifnull(tagline,title) from book where testament = '.$test.' and ifnull(tagline,title) < \''.$ttmp.'\''.(($edit)?'':' and active = 1 ').' order by 2 desc ';
    else
      $sql = 'select book, ifnull(tagline,title) from book where testament = '.$test.' and sqn < '.$ttmp.(($edit)?'':' and active = 1 ').' order by sqn desc ';
    $row = rs($sql);
    $prevbook =($row)?$row[0]:0;
    if($prevbook){
      $tmp = cleanquotes($row[1]);
      if(strpos($tmp, ':')) $tmp = trim(substr($tmp, strpos($tmp,':')+1));
      $prevbooktxt =str_replace(' ',(($test==4)?'_':'-'), preg_replace("/[^ \-\p{L}]+/u", "", $tmp));
    }
    if($test<4){
      $link = '<a id="[id]" href="'.$root.'/[bk]/[txt]" title="[tit]">';
    }else{
      $link = '<a id="[id]" href="'.$root.'/[txt]" title="[tit]">';
    }
    if($nextbook) $nextlink = str_replace('[txt]', $nextbooktxt, str_replace('[bk]', $nextbook, str_replace('[tit]', 'next', $link))).$nextlinkact.'</a>';
    if($prevbook) $prevlink = str_replace('[txt]', $prevbooktxt, str_replace('[bk]', $prevbook, str_replace('[tit]', 'previous', $link))).$prevlinkact.'</a>';
    $prevlink = str_replace('[id]', 'prevlinkid', $prevlink);
    $nextlink = str_replace('[id]', 'nextlinkid', $nextlink);
  }
}

function logedit($pg,$tt,$bo,$ch,$vs,$ui,$cm,$wn,$vd=null,$cd=null,$fd=null,$cfd=null){
  // $pg = page
  // $tt = testament
  // $bo = book
  // $ch = chapter
  // $vs = verse
  // $ui = userid
  // $cm = comment
  // $wn = whatsnew
  // $vd = verse diffs
  // $cd = comm diffs
  // $fd = footnote diffs
  // $cfd= commfootnote diffs
  if($vd!=null) $vd = '\''.$vd.'\'';
  else $vd = 'null';
  if($fd!=null) $fd = '\''.$fd.'\'';
  else $fd = 'null';
  if($cfd!=null) $cfd = '\''.$cfd.'\'';
  else $cfd = 'null';
  if($cd!=null) $cd = '\''.$cd.'\'';
  else $cd = 'null';
  $sql = 'insert into editlogs(page,testament,book,chapter,verse,editdate,userid,comment,whatsnew, versdiff, footdiff, commfootdiff, commdiff) values ('.
          $pg.','.$tt.','.$bo.','.$ch.','.$vs.',UTC_TIMESTAMP(),'.$ui.','.processsqltext(((trim($cm)=='' && ($ui==1 || $ui==9))?'formatting':$cm),200,1,'').','.$wn.','.$vd.','.$fd.','.$cfd.','.$cd.') ';
  //die($sql);
  $log = dbquery($sql);
  $row = rs('select max(logid) from editlogs');
  return $row[0];
}

function displayedits($pg,$tt,$bo,$ch,$vs){
  global $timezone, $ismobile, $screenwidth, $userid, $colors;
  $ret = '<br /><br /><small>Edit log: <small>(last 6 mo, newest at top)</small></small><br />';
  $ret.= '<table style="font-size:80%;margin:auto;border-collapse:separate;border-spacing:5px 2px;">';
  $ret.= '<tr><td><b>Who</b></td><td><b>When</b></td><td><b>Chngs</b></td><td><b>WN</b></td><td><b>Comment</b></td><td><b>Flg</b></td></tr>';

  $sql = 'select ifnull(ifnull(u.revusername, u.myrevname), \'-unknown-\') uname,
                 e.editdate, e.whatsnew, e.logid, ifnull(e.comment, \'-none-\') comment, ifnull(elv.userid, 0) logviewed, ifnull(elv.flagged, 0) flagged,
                 ifnull(e.versdiff, \'-\') versdiff, ifnull(e.footdiff, \'-\') footdiff, ifnull(e.commfootdiff, \'-\') commfootdiff, ifnull(e.commdiff, \'-\') commdiff
          from editlogs e
          left join myrevusers u on (u.userid = e.userid and u.myrevid > 0)
          left join editlogsviewed elv on (elv.userid = '.$userid.' and elv.logid = e.logid)
          where page = '.$pg.' and testament = '.$tt.' and book = '.$bo.' '.
          (($pg==1)?' and chapter = '.$ch.' and verse = '.$vs.' ':'').
          'order by e.editdate desc ';

  $logs = dbquery($sql);
  if(!mysqli_num_rows($logs)) $ret.= '<tr><td colspan="6">-none-</td></tr>';
  $ni=0;
  while($row = mysqli_fetch_array($logs)){
    $ret.= '<tr><td>'.$row['uname'].'</td><td>'.converttouserdate($row['editdate'], $timezone).'</td>';
    $bgc = (($row['logviewed']>0)?'background-color:'.$colors[6].';':'');
    if($row['versdiff'] != '-' || $row['footdiff'] != '-' || $row['commdiff'] != '-' || $row['commfootdiff'] != '-'){
      $ret.= '<td style="text-align:center;'.$bgc.'" id="td'.$ni.'"><a onclick="olOpen(\'/viewuseredit.php?logid='.$row['logid'].'&idx='.$ni.'\','.(($screenwidth>900)?800:600).', 600, '.(($ismobile)?1:0).');$(\'td'.$ni.'\').style.backgroundColor=\''.$colors[6].'\';" title="details"><img src="/i/magglass32.png" border="0" style="width:21px" alt="details" /></a></td>';
    }else{
      $ret.= '<td id="td'.$ni.'" style="text-align:center;'.$bgc.'"><a onclick="handleflag('.$row['logid'].','.$ni.',1);" class="comlink0" style="cursor:pointer;">None</a></td>';
    }
    $ret.= '<td style="text-align:center;">'.(($row['whatsnew']==1)?'<img src="/i/checkmark.png" style="width:20px;" alt="" />':'-').'</td><td>'.$row['comment'].'</td>';
    $flgd = (($row['flagged']==1)?'background-color:#ffdddd;':'');
    $ret.= '<td id="eflag'.$ni.'" style="text-align:center;cursor:pointer;'.$flgd.'" onclick="handleflag('.$row['logid'].','.$ni.',0);"><img src="/i/flagedit.png" id="iflag'.$ni.'" style="width:'.((!$ismobile)?'1.0em':'.9em').';opacity:'.(($row['flagged']==1)?'1.00':'.30').';" alt="" /></td>';
    $ret.= '</tr>'.crlf;
    $ni++;
  }
  $ret.= '</table>';
  return $ret;
}

function converttouserdate($date, $userTimeZone = 'America/New_York'){
  $format = 'n/j/Y g:i A';
  $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime($date ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}
// should combine with above..
function converttouserdate2($date, $userTimeZone = 'America/New_York'){
  $format = 'n/j/Y g:i:s A';
  $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime($date ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}

function getuserfiletimestamp($userTimeZone = 'America/New_York', $inctime = 0){
  switch($inctime){
  case 1:
    $format = '_Ymd_His'; break;
  case 2:
    $format = '_Ymd_Hi'; break;
  default:
    $format = '_Ymd'; break;
  }
  $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime(null ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}

function getsettingvalue($nam, $typ){
  global $timezone;
  switch($typ){
    case 'time':
        $fld = 'sometime';
        $ret = new DateTime(null ?? '', new DateTimeZone('UTC'));
        $ret = $ret->format('Y-m-d H:i:s');
        break;
    case 'num':    $fld = 'somenumber';$ret = '0'; break;
    case 'string':
    default:       $fld = 'somestring';$ret = 'text';
  }
  $row = rs('select '.$fld.' from settings where settingname = \''.$nam.'\' ');
  if($row){
    return $row[0];
  }else{
    $insert = dbquery('insert into settings(settingname, '.$fld.') values (\''.$nam.'\',\''.$ret.'\')');
    return $ret;
  }
}

function savesettingvalue($nam, $typ, $val){
  switch($typ){
    case 'time':
        $fld = 'sometime';
        break;
    case 'num':    $fld = 'somenumber'; break;
    case 'string':
    default:       $fld = 'somestring';
  }
  $save = dbquery('update settings set '.$fld.' = '.$val.' where settingname = \''.$nam.'\' ');
}

function createworkdir(){
  global $docroot;
  $dirname = 'tmp_'.keygen(12);
  $oldmask = umask(0);
  mkdir($docroot.'/export/'.$dirname, 0777);
  umask($oldmask);
  return $dirname.'/';
}

function mainmenu(){
  global $mobilespc, $revws, $revch, $revtp, $userid, $showdevitems, $armenuitems, $showcommlinks;

  print('<br />Please make a selection.<br /><br />');
  for($midx=0, $size=count($armenuitems);$midx<$size;$midx++){
    switch($armenuitems[$midx]){
    case 1: // info
      print('<a href="/info/1">About</a><br />'.$mobilespc);break;
    case 2: // Old Testament
      print('<a href="/otst"><b>Old Testament</b></a><br />'.$mobilespc);break;
    case 3: // New Testament
      print('<a href="/ntst"><b>New Testament</a></b><br />'.$mobilespc);break;
    case 4: // OT Commentary
      print('<a href="/ocom">OT Commentary</a><br />'.$mobilespc);break;
    case 5: // NT Commentary
      print('<a href="/ncom">NT Commentary</a><br />'.$mobilespc);break;
    case 6: // Appendices
      print('<a href="/appx">Appendices</a><br />'.$mobilespc);break;
    case 7: // Search
      print('<a href="/srch">Search</a><br />'.$mobilespc);break;
    case 8: // Whatsnew
      print('<a href="/wnew">What\'s New'.gethilite('wn', 16).'</a><br />'.$mobilespc);break;
    case 9: // REVBlog
      print('<a href="/blog">REV Blog'.gethilite('blog', 16).'</a><br />'.$mobilespc);
      break;
    case 10: // Word Studies
      if ($revws==1 || ($userid>0 && $showdevitems==1)) {
        print('<a href="/word">Word Studies</a><br />'.$mobilespc);
      }; break;
    case 11: // donate
      print('<a href="/dont">Donate</a><br />'.$mobilespc);break;
    case 12: // export
      print('<a href="/expt">Export</a><br />'.$mobilespc);break;
    case 13: // back to bible
      print('<a href="/bcuk">Bible</a><br />'.$mobilespc);break;
    case 14: // help
      print('<a href="/info/3">Help</a><br />'.$mobilespc);break;
    case 15: // Resources
      print('<a href="/reso">Resources</a><br />'.$mobilespc);break;
    case 16: // topics
      if ($revtp==1 || ($userid>0 && $showdevitems==1)) {
        print('<a href="/topi">Topics</a><br />'.$mobilespc);
      }; break;
    case 17: // chronology
      if ($revch==1 || ($userid>0 && $showdevitems==1)) {
        print('<a href="/chro">Chronology</a><br />'.$mobilespc);
      }; break;
    default: break;
    };
  };
}

function gethilite($src='wn', $siz=11){
  global $timezone, $userid, $revws, $showdevitems;
  switch($src){
  case 'wn':
      $sql = 'select max(e.editdate)
              from editlogs e
              where e.whatsnew = 1 '.
              (($revws==0 && ($userid==0 || ($userid>0 && $showdevitems==0)))?'and testament != 4 ':'').'
              and e.comment != \'-\'';
      $cookidx=0;
      break;
  case 'blog':
      $sql = 'select max(blogdate)
              from revblog
              where active=1
              and blogtext != \'no blog text\'';
      $cookidx=1;
      break;
  case 'res':
      $sql = 'select max(publishedon)
              from resource
              where active=1 and finalized = 1';
      $cookidx=2;
      break;
  }
  $row = rs($sql);
  if($row[0] != null){
    $editdate = strtotime($row[0].' UTC');
    $arwnblog   = explode(';', (isset($_COOKIE['rev_wnblog']))?$_COOKIE['rev_wnblog']??'':((time()-(3*86400)).';'.(time()-(3*86400)).';'.(time()-(3*86400))));
    if(sizeof($arwnblog)==2) array_push($arwnblog, (time()-(3*86400)));
    if($editdate > $arwnblog[$cookidx]) $ret = ' <img src="/i/bluedot.png" height="'.$siz.'" width="'.$siz.'" alt="bluedot" />';
    else $ret = '';
  }else
    $ret = '';
  return $ret;
}

function gethilite2(){
  global $timezone, $userid, $revws, $showdevitems, $revblog;
  $sql = 'select ifnull(max(e.editdate), 0)
          from editlogs e
          where e.whatsnew = 1 '.
          (($revws==0 && ($userid==0 || ($userid>0 && $showdevitems==0)))?'and testament != 4 ':'').'
          and e.comment != \'-\'';
  $row = rs($sql);
  $wndat = (($row)?$row[0]:0);
  $sql = 'select ifnull(max(blogdate), 0)
          from revblog
          where active=1
          and blogtext != \'no blog text\'';
  $row = rs($sql);
  $bldat = ((($revblog==1)&&$row)?$row[0]:0);
  //print('bldat: '.$bldat);
  $sql = 'select ifnull(max(publishedon), 0)
          from resource
          where active=1 and finalized = 1';
  $row = rs($sql);
  $resdat = (($row)?$row[0]:0);

  $arwnblog   = explode(';', (isset($_COOKIE['rev_wnblog']))?$_COOKIE['rev_wnblog']??'':((time()-(3*86400)).';'.(time()-(3*86400)).';'.(time()-(3*86400))));
  if(sizeof($arwnblog)==2) array_push($arwnblog, (time()-(3*86400)));
  if(strtotime($wndat.' UTC') > $arwnblog[0] || strtotime($bldat.' UTC') > $arwnblog[1] || strtotime($resdat.' UTC') > $arwnblog[2])
    $ret = '_dot';
  else $ret = '';
  return $ret;
}

function gettimezoneabbr($tz){
  switch($tz){
  case 'America/New_York':
    $ret = 'EST';
    break;
  case 'America/Chicago':
    $ret = 'CST';
    break;
  case 'America/Denver':
    $ret = 'MST';
    break;
  case 'America/Los_Angeles':
    $ret = 'PST';
    break;
  default:
    $ret = 'EST';
  }
  return $ret;
}

function getexportlinks($w,$t,$b,$c,$v,$s){
  global $page, $colors, $userid, $ismobile, $site, $inapp;
  $pdfurl = '/pdf.php?what='.$w.'&amp;test='.$t.'&amp;book='.$b.'&amp;chap='.$c.'&amp;vers='.$v;
  $mswurl = '/docx_phpdocx.php?what='.$w.'&amp;test='.$t.'&amp;book='.$b.'&amp;chap='.$c.'&amp;vers='.$v;
  $target = ' target="'.(($ismobile)?'_blank':'_self').'"';
  $ret='&nbsp;&nbsp;<a onclick="return dload(\''.$pdfurl.'\','.$c.');" title="Export as PDF"><img src="/i/pdf'.$colors[0].'.png" alt="PDF" style="border:0;width:19px;" /></a>';
  $ret.='&nbsp;&nbsp;<a onclick="return dload(\''.$mswurl.'\','.$c.');" title="Export as MS Word doc"><img src="/i/docx'.$colors[0].'.png" alt="MSWord" style="border:0;width:16px;" /></a>';
  if($userid==1 && $s==1){
    $ret.='&nbsp;&nbsp;<a class="toplink" href="/exportsql.php?test='.$t.'&amp;book='.$b.'&amp;chap='.$c.'&amp;vers='.$v.'&amp;fpag='.$page.'">sql</a>';
  }
  return (($inapp==1)?'':$ret);
}

function processcommfordisplay($com, $removemarkers=0){
  global $parachoice;
  $indented = (($parachoice==3 || $parachoice==4)?1:0);

  // temporary, until all old whatsnew markers are gone 20180212
  $com = preg_replace('#<a name="marker(.*?)"></a>#', '<a id="marker$1"></a>', $com);

  // misc
  $com = preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $com);
  $com = str_replace('[noparse]', '<noparse>', $com);
  $com = str_replace('[/noparse]', '</noparse>', $com);

  // tryng to handle formatting for paragraph headings
  $com = preg_replace('#<p>\\s?<strong>#', '<p style="text-indent:0;margin-top:0;"><strong>', $com);
  $com = preg_replace('#</h5>\\s?<p>#', '</h5><p style="margin-top:0;padding-top:0;'.(($indented==1)?'text-indent:1.4em;':'').'">', $com);
  $com = preg_replace('#<p>\\s?<a id="marker([^<]*?)"><\/a>\\s?<strong>#', '<p style="text-indent:0;margin-top:0;"><a id="marker$1"></a><strong>', $com);

  if($removemarkers==1){
    $com = preg_replace('#<a id="marker(.*?)</a>#', '', $com); // remove whatsnew markers
  }
  return $com;
}

function usermenu($brk=' | '){
  global $navstring, $mitm, $showdevitems, $canedit, $userid, $editorcomments, $revblog, $peernotes;
  $brk = (($brk=='break')?'<br />':$brk);
  $devidx = (($brk=='<br />')?0:1);
  
  if(1==2){ // too slow.
    $noednotes = (($editorcomments==0)?' and page != 7 ':'');
    $nopeernotes = (($peernotes==0)?' and page != 310 ':'');
    $noblog    = (($revblog==1)?'':' and page != 27 ');
    $sql = 'select count(*)
            from editlogs
            where (editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -1 DAY)'.$noednotes.$nopeernotes.$noblog.'
            and logid not in (select logid from editlogsviewed where userid = '.$userid.'))
            or logid in (select logid from editlogsviewed where userid = '.$userid.' and flagged = 1)';

    $row = rs($sql);
    $etot = $row[0];
    $etot = ' ('.$etot.')';
  }else $etot='';

  $tmp='';
  $tmp.= '<a onclick="return navigate('.$mitm.',18,'.$navstring.');" title="Track REV edits">'.(($brk=='<br />')?'Edit Tracking':'Edit Track').'</a>'.$etot.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',15,'.$navstring.');" title="Site Statistics">'.(($brk=='<br />')?'Site Statistics':'Statistics').'</a>'.$brk;
  if($editorcomments){
    $tmp.= '<a onclick="return navigate('.$mitm.',7,'.$navstring.');" title="Editor notes">'.(($brk=='<br />')?'Editor Notes':'Ed Notes').'</a>'.$brk;
  }
  if($peernotes){
    $tmp.= '<a onclick="return navigate('.$mitm.',45,'.$navstring.');" title="Rev\'r notes">'.(($brk=='<br />')?'Reviewer Notes':'Rv Notes').'</a>'.$brk;
  }
  if($canedit){
    $tmp.= '<a onclick="return navigate('.$mitm.',12,0,0,0,0);" title="Bible book status">'.(($brk=='<br />')?'Book Status':'Book Status').'</a>'.$brk;
  }
  $tmp.= '<a onclick="return navigate('.$mitm.',22,'.$navstring.');" title="Export the REV to MS Word">'.(($brk=='<br />')?'MSWord Exports':'MSW Expts').'</a>'.$brk;
  $tmp.= (($brk=='<br />')?'':'Dev').'<input type="checkbox" name="showdevitems'.$devidx.'" id="showdevitems'.$devidx.'" value="1" onclick="setshowdevitems(this.checked);location.reload();"'.fixchk($showdevitems).' /> '.(($brk=='<br />')?'<label for="showdevitems'.$devidx.'">Show Dev Itms</label>':'');
  return $tmp;
}
function adminmenu($brk=' | '){
  global $navstring, $mitm, $userid, $colors, $ismobile, $inapp;
  $brk = (($brk=='break')?'<br />':$brk);
  $row = rs('select count(*) from blockedips');
  $bkcnt = $row[0];
  $bcolor=(($bkcnt>0)?' style="color:red;"':'');
  $tmp = '';
  $tmp.= '<span style="color:red">'.(($brk=='<br />')?'Superuser':'SU').'</span>:'.(($brk=='<br />')?$brk:' ');
  $tmp.= '<a onclick="return navigate('.$mitm.',43,'.$navstring.');" title="REV Users">'.(($brk=='<br />')?'REV Users':'REV Users').'</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',17,'.$navstring.');" title="Manage SOPS sessions">SOPS</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',16,'.$navstring.');" title="Manage Excluded (non-logged) IP Addresses">'.(($brk=='<br />')?'Non-logged IPs':'NLGd IPs').'</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',21,'.$navstring.');" title="Manage Mapped IP Addresses">'.(($brk=='<br />')?'Mapped IPs':'MAPd IPs').'</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',32,'.$navstring.');" title="Back up the REV website and database">'.(($brk=='<br />')?'Backup Site':'BKUP').'</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.', 2,'.$navstring.');" title="Update Resources">'.(($brk=='<br />')?'Update Resources':'UpdRes').'</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',31,'.$navstring.');" title="Generate Exports">'.(($brk=='<br />')?'Generate Exports':'Exprts').'</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',42,'.$navstring.');" title="settings">'.(($brk=='<br />')?'Site Settings':'Settings').'</a>';
  $tmp.= '<br /><span style="color:red">Super SU</span>:'.(($brk=='<br />')?$brk:' ');
  $tmp.= '<a onclick="return navigate('.$mitm.',28,'.$navstring.');" title="run sql">Run SQL</a>'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',23,'.$navstring.');"'.$bcolor.' title="Manage Blocked IP Addresses">'.(($brk=='<br />')?'Blocked IPs':'BLKd IPs').'</a> ('.$bkcnt.')'.$brk;
  $tmp.= '<a onclick="return navigate('.$mitm.',35,'.$navstring.');" title="sitemap">Gen SiteMap</a>';
  $tmp.= $brk.'<input type="checkbox" name="rswismobile'.(($brk=='<br />')?0:1).'" id="rswismobile'.(($brk=='<br />')?0:1).'" value="1" onclick="toggleismobile(this.checked);location.reload();"'.fixchk($ismobile).' /> <label for="rswismobile'.(($brk=='<br />')?0:1).'">Mobile</label>';
  $tmp.= $brk.'<input type="checkbox" name="rswinapp'.(($brk=='<br />')?0:1).'" id="rswinapp'.(($brk=='<br />')?0:1).'" value="1" onclick="toggleinapp(this.checked);location.reload();"'.fixchk($inapp).' /> <label for="rswinapp'.(($brk=='<br />')?0:1).'">App</label>';
  return $tmp;
}

function cleanquotes($txt){
  $txt = str_replace("“", "&ldquo;", $txt);
  $txt = str_replace("”", "&rdquo;", $txt);
  $txt = str_replace("‘", "&lsquo;", $txt);
  $txt = str_replace("’", "&rsquo;", $txt);
  $txt = str_replace('&ldquo;', '', $txt);
  $txt = str_replace('&rdquo;', '', $txt);
  $txt = str_replace('&lsquo;', '', $txt);
  $txt = str_replace('&rsquo;', '', $txt);
  return $txt;
}

function formatBytes($bytes, $precision=2) {
  $units = array("b", "kb", "mb", "gb", "tb");
  $bytes= max($bytes, 0);
  $pow  = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow  = min($pow, count($units) - 1);
  $bytes /= (1 << (10 * $pow));

  return round($bytes, $precision) . " " . $units[$pow];
}

function replacediacritics($str){
  $table = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a',
                 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE',
                 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c',
                 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D',
                 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e',
                 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E',
                 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g',
                 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H',
                 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i',
                 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J',
                 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l',
                 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N',
                 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o',
                 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O',
                 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r',
                 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S',
                 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T',
                 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u',
                 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U',
                 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue',
                 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W',
                 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z',
                 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a',
                 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd',
                 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i',
                 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm',
                 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's',
                 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h',
                 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch',
                 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju',
                 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
  return strtr($str, $table);
}

//
// the following 2 functions are for highlighting differences in edits
//
function diff($old, $new){
  $maxlen=0;
  foreach($old as $oindex => $ovalue){
    $nkeys = array_keys($new, $ovalue);
    foreach($nkeys as $nindex){
      $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
        $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
      if($matrix[$oindex][$nindex] > $maxlen){
        $maxlen = $matrix[$oindex][$nindex];
        $omax = $oindex + 1 - $maxlen;
        $nmax = $nindex + 1 - $maxlen;
      }
    }
  }
  if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
  return array_merge(
    diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
    array_slice($new, $nmax, $maxlen),
    diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}

function htmlDiff($old, $new){
  $old = str_replace('<p>', '<p> ', $old);
  $new = str_replace('<p>', '<p> ', $new);
  $old = str_replace('</p>', ' </p>', $old);
  $new = str_replace('</p>', ' </p>', $new);
  //$old = '<p>~</p> '.$old;
  //$new = '<p>~</p> '.$new;
  $ret='';
  $diff = diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
  foreach($diff as $k){
    if(is_array($k))
      $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
              (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
    else $ret .= $k . ' ';
  }
  // testing, sometimes it's missing a closing del tag
  $ret = str_replace(' <ins>', '</del> <ins>', $ret);

  /* Tidy
  $tcfg = array(
               'new-inline-tags'  => 'ins, del',
               'indent'           => false,
               'output-xhtml'     => true,
               'wrap'             => 99999,
               'preserve-entities'=> 1,
               'show-body-only'   => 1
               );
  $tidy = new tidy;
  $tidy->parseString($ret, $tcfg, 'utf8');
  $tidy->cleanRepair();
  $ret = str_replace(PHP_EOL, ' ', $tidy);
  */
  $ret = str_replace(PHP_EOL, ' ', $ret);
  $ret = str_replace(crlf, ' ', $ret);
  $ret = trim($ret); // re-trim
  //$ret = substr($ret, 9);
  return $ret;
}

function grabinfo($bk){
  global $userid, $canedit, $showedit, $showpdflinks;
  $arcomfn = array();
  $comfncnt= 0;
  $sql = 'select comfootnotes, commentary from verse where testament = 2 and book = '.$bk.' and chapter = 1 and verse = 1 ';
  $row = rs($sql);
  $comfootnotes= $row['comfootnotes'];
  $commentary = $row['commentary'];
  $commentary = (($commentary)?$commentary:'No Content!');
  $commentary = preg_replace('#<p><strong>([^<]*?)</strong><br />#', '<h5 style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:25px;">$1</h5><p style="margin-top:0;padding-top:0">', $commentary);

  $commentary = processcommfordisplay($commentary, 0);
  $commentary = processcomfootnotes($arcomfn, $commentary, $comfootnotes, $comfncnt, 1);

  if($userid>0 && $canedit==1){
    print('<div id="pagetop">');
    print(editlink('elnk0',$showedit,1,8,2,$bk,1,1));
    if($showpdflinks==1) print(getexportlinks('info',2,$bk,1,1, 1));
    print('</div>');
  }

  print($commentary);
  displaycomfootnotes($comfncnt, $arcomfn, 1);
}

function replacgreekhtml($cm){
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

function handleTOC($com){
  global $tocerror, $test, $book, $chap, $vers;
  $origcom = $com;
  $toc=0;
  $pos = strpos($com, '[toc');
  while($pos!==FALSE){
    $toc = intval(substr($com, $pos+4, 3));
    if($toc>0){
      $tocid = '_'.$test.'_'.$book.'_'.$chap.'_'.$vers.'_'.$toc;
      $toclen = (($toc>9)?2:1);
      $replace = '<a id="toc'.$tocid.'" onclick="scrolltopos(this.id, \\\'tocdest'.$tocid.'\\\', -toffset);">';
      $com = substr_replace($com, $replace, $pos, (5+$toclen)); // 5=strlen('[toc]')
      $pos = strpos($com, '[/toc'.$toc.']');
      if($pos===false){$tocerror='missing closing tag for [toc'.$toc.']';return $origcom;}
      $com = substr_replace($com, '</a>', $pos, (6+$toclen));   // 6=strlen('[/toc]')
      $pos = strpos($com, '[tocdest'.$toc.']');
      if($pos===false){$tocerror='missing TOC destination for [toc'.$toc.']';return $origcom;}
      $replace = '<a id="tocdest'.$tocid.'" class="comlink0" onclick="scrolltopos(this.id, \\\'toc'.$tocid.'\\\', -(toffset+12));">';
      $com = substr_replace($com, $replace, $pos, (9+$toclen)); // 9=strlen('[tocdest]')
      $pos = strpos($com, '[/tocdest'.$toc.']');
      if($pos===false){$tocerror='missing closing tag for [tocdest'.$toc.']';return $origcom;}
      $com = substr_replace($com, '</a>', $pos, (10+$toclen));  // 10=strlen('[/tocdest]')
    }else{$tocerror='need a number > 0 for each toc';return $origcom;}

    $pos = strpos($com, '[toc');
    $toc++;
  }
  return $com;
}

function undoTOC($com){
  if(strpos($com??'', 'a id="toc_') > 0){
    $com= preg_replace('#<a id="toc_(\d+)_(\d+)_(\d+)_(\d+)_(\d+)(.*?)>#', '[toc$5]', $com);
    $com= preg_replace('#\[toc(\d+)\](.*?)</a>#', '[toc$1]$2[/toc$1]', $com);
    $com= preg_replace('#<a id="tocdest_(\d+)_(\d+)_(\d+)_(\d+)_(\d+)(.*?)>#', '[tocdest$5]', $com);
    $com= preg_replace('#\[tocdest(\d+)\](.*?)</a>#', '[tocdest$1]$2[/tocdest$1]', $com);
  }
  return $com;
}

function undoTOCforMSW($com){
  if(strpos($com??'', 'a id="toc_') > 0){
    $com= preg_replace('#<a id="toc_(.*?)>#', '<toc id="$1">', $com);
    $com= preg_replace('#<toc id="(.*?)">(.*?)</a>#', '<toc id="$1">$2</toc>', $com);
    $com= preg_replace('#<a id="tocdest_(.*?)>#', '<tocdest id="$1">', $com);
    $com= preg_replace('#<tocdest id="(.*?)">(.*?)</a>#', '<tocdest id="$1">$2</tocdest>', $com);
  }
  return $com;
}

function getplaylisttypename($pltypid){
    switch($pltypid){
    case 1: $lbl='Video';break;
    case 3: $lbl='Audio';break;
    case 4: $lbl='Article';break;
    case 5: $lbl='Seminar';break;
    case 7: $lbl='Library';break;
    default: $lbl='unknown';break;
    };
  return $lbl;
}

function appendresources($t, $b, $c, $v){
  global $resedit, $page, $navstring, $ismobile, $screenwidth;

  $ret='';
  $ni=0;
  $sql = 'select r.resourceid, r.resourcetype, r.title, r.identifier, r.description, r.externalurl,
          ifnull(r.thumbnail, \'nopic\') thumbnail, r.active, r.source, r.duration, r.publishedon, r.resviews,
          r.playlistid, r.finalized, r.editcomment, r.edituserid
          from resource r join resourceassign ra on (ra.resourceid = r.resourceid)
          where '.(($resedit==1)?'1=1 ':'active=1 ').'
          and ra.testament = '.$t.'
          and ra.book = '.$b.'
          and ra.chapter = '.$c.'
          and ra.verse = '.$v.'
          order by sqn, publishedon ';
  $med = dbquery($sql);
  while($row = mysqli_fetch_array($med)){
    $ret.= assembleresource($row, $ni, $page);
    $ni++;
  }
  if($ni>0){
    //$ret = '<hr><div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:96%;"><p style="text-indent:0;"><strong>Additional resource'.(($ni>1)?'s':'').':</strong></p>'.$ret.'</div><br />';
    $rtop = '<hr><div style="width:100%;max-width:720px;text-align:left;padding:0;margin:0;font-size:96%;"><p style="text-indent:0;"><strong>Additional resource'.(($ni>1)?'s':'').':</strong> ';
    if($resedit==1) $rtop.= '<a onclick="olOpen(\'/resourcebyref.php?navstr='.$navstring.'&rloadp=1\','.(($ismobile==1)?$screenwidth+20:600).', 600);" title="Order or delete Resources"><img src="/i/tv.png" width="16" /></a>';
    $ret = $rtop.'</p>'.$ret.'</div><br />';
  }
  return $ret;
}

function assembleresource($row, $ni, $pg=0){
  global $resedit, $resshowedit, $screenwidth, $colors, $colortheme;

  $rid = $row['resourceid'];
  $vidinitsiz = (($screenwidth<520)?160:210);
  $rtyp = $row['resourcetype'];
  $duration = (($row['duration'])?' <span style="font-size:80%;color:'.$colors[7].';">('.$row['duration'].')</span>':'');
  $strresid = ''; //(($resedit==1 && $resshowedit==1)?' <span style="font-size:80%;color:'.$colors[7].';">(ID: '.$row['resourceid'].')</span>':'');
  switch(strtolower($row['source']??'')){
    case 'biblicalunitarian': $src='BU'; break;
    case 'spiritandtruth_vf': $src='SandT VF'; break;
    case 'spiritandtruth': $src='SandT'; break;
    case 'podbean': $src='Podbean'; break;
    case 'castos': $src='Castos'; break;
    case 'seminar (stfpodcast)': $src='Seminar'; break;
    default: $src='';
  }
  $source = (($resedit==1 && $resshowedit==1 && $src!='')?' <span style="font-size:80%;color:'.$colors[7].';">(src: '.$src.')</span>':'');
  //$pubdate  = (($resedit==1 && $resshowedit==1)?' <span style="font-size:80%;color:'.$colors[7].';">(Pub: '.$row['publishedon'].')</span>':'');
  $pubdate  = ' <span style="font-size:80%;color:'.$colors[7].';">(Pub: '.$row['publishedon'].')</span>';
  $resviews = (($resedit==1 && $resshowedit==1)?' <span style="font-size:80%;color:'.$colors[7].';">(views: '.$row['resviews'].')</span>':'');
  $delres   = (($pg==33 && $resedit==1 && $resshowedit==1)?' <a onclick="if(confirm(\'Are you sure you want to disassociate\nthis resource from this topic?\')) {document.fte2.delres.value='.$row[1].';document.fte2.submit();}" title="delete this item from this topic"><img src="/i/del.png" style="width:1.0em;" alt="" /></a>':'');
  if($pg==36)
    $editlink = (($resedit==1 && $resshowedit==1)?' <a onclick="gotopage(document.frm,'.$rid.',37)"><img src="/i/edit.gif" width="14" alt="edit" /></a>':'');
  else
    $editlink = (($resedit==1 && $resshowedit==1)?' <a onclick="document.frmnav.temp.value='.$rid.';navigate(15,37,0,0,0,0);"><img src="/i/edit.gif" width="14" alt="edit" /></a>':'');
  $ret = '<table class="gridtable" style="width:100%;text-align:left;margin:0 0 28px 0;">';
  $ret.= '<tr><td style="width:10%;text-align:center;vertical-align:middle;white-space:nowrap;">';
  switch($row['resourcetype']){
  case 1: // youtube
    $ret.= '<a onclick="sizres('.$rid.', \''.$row['identifier'].'\')"><img src="/i/video.png" alt="Video" style="width:24px;" /> <img id="exconvid'.$rid.'" src="/i/expandvideo.png" alt="expand/contract" style="width:20px;" /></a>';
    $ret.= '<td style="vertical-align:middle;width:90%;"><strong><noparse>'.$row['title'].'</noparse></strong>'.$source.$duration.$strresid.$pubdate.$resviews.$delres.'</td></tr>';
    $ret.= '<tr><td colspan="2" style="max-width:'.($screenwidth-60).'px;padding:0 3px;">';
    $ret.= '<div style="width:100%;max-width:712px;text-align:left;margin:0;overflow:auto;">';
    $ret.= '<span class="video-wrapper" id="video'.$rid.'" style="width:'.$vidinitsiz.'px;margin:3px 2px 3px 0;float:left;transition:width .6s;"><span class="video-container">'.
           '<iframe id="frm'.$rid.'" width="640" height="385" src="/includes/videoload.php?id='.$rid.'" frameborder="0" allow="autoplay;fullscreen" scrolling="no" style="overflow:hidden;"></iframe>'.
           '</span></span>';
    $ret.= ((left($row['description'], 3)=='<p>')?'<p style="margin-top:0">'.substr($row['description'], 3):$row['description']);
    $ret.= '<p style="text-indent:0"><a href="https://www.youtube.com/watch?v='.$row['identifier'].'&rel=0" target="_blank" onclick="stopifplaying('.$rid.');">Watch on Youtube</a> <img src="/i/popout'.$colors[0].'.png" style="width:18px" alt="popout" /></p>';
    $ret.= '</div></td></tr>';
    break;
  case 2: // mp4
    $ret.= 'working on it.</td></tr>';
    break;
  case 3:
  case 4: // audio
    $ret.= '<a onclick="toggleplaypause('.$ni.','.$rid.');"><img id="ispeaker'.$ni.'" src="/i/audio.png" alt="play media" width="22" /></a></td>';
    $ret.= '<td style="vertical-align:middle;width:90%;"><strong><noparse>'.$row['title'].'</noparse></strong>'.$source.$duration.$strresid.$pubdate.$resviews.$delres.'</td></tr>';
    $ret.= '<tr><td colspan="2" style="max-width:'.($screenwidth-40).'px;padding:2px;">';
    $ret.= '<div style="width:100%;max-width:714px;text-align:left;padding:0;">';
    // show player
    $ret.= '<p style="margin:5px;"><audio id="aplayer'.$ni.'" onplay="stopothers('.$ni.','.$rid.');" onpause="stopplayer('.$ni.');" controls="controls" preload="none" src="'.$row['identifier'].'" style="width:95%;max-width:300px;">Sorry, your browser does not support the player. Follow <a href="'.$row['identifier'].'" target="_blank">this link</a>.</audio></p>';
    // don't show player
    //$ret.= '<audio id="aplayer'.$ni.'" preload="none" src="'.$row['identifier'].'" style="display:none;">Sorry, your browser does not support the player. Follow <a href="'.$row['identifier'].'" target="_blank">this link</a>.</audio>';
    $thumb = (($row['thumbnail']!='nopic')?$row['thumbnail']:'/i/sandt_audio.png');
    $ret.= '<img id="iplayer'.$ni.'" src="'.$thumb.'" style="float:left;height:114px;margin:0 5px 0 0;" alt="thumbnail" />';
    //$ret.= '<img id="iplayer'.$ni.'" src="'.$thumb.'" style="float:left;width:74px;margin:0 5px 0 0;" alt="thumbnail" />';
    $ret.= ((left($row['description'], 3)=='<p>')?'<p style="margin-top:0">'.substr($row['description'], 3):$row['description']);
    if($row['externalurl']!==null)
      $ret.= '<p style="text-indent:0;"><a href="'.$row['externalurl'].'" target="_blank" onclick="stopifplaying('.$rid.');">Listen on '.$row['source'].'</a> <img src="/i/popout'.$colors[0].'.png" style="width:18px" alt="popout" /></p>';
    $ret.= '</div></td></tr>';
    break;
  case 5: // article
    $ret.= '<a href="'.$row['externalurl'].'" target="_blank" onclick="incresviews('.$rid.')"><img src="/i/commentary'.$colors[0].'.png" alt="view" width="22" /></a></td>';
    $ret.= '<td style="vertical-align:middle;width:90%;"><strong><noparse>'.$row['title'].'</noparse></strong>'.$strresid.$pubdate.$resviews.$delres.'</td></tr>';
    $ret.= '<tr><td colspan="2" style="max-width:'.($screenwidth-40).'px;padding:0 3px;">';
    $ret.= '<div style="width:100%;max-width:714px;text-align:left;margin:0;padding:0;">';
    $thumb = (($row['thumbnail']!=='nopic')?$row['thumbnail']:'/i/thumbnails/resourcearticle.jpg');
    $ret.= '<a href="'.$row['externalurl'].'" target="_blank" onclick="incresviews('.$rid.')"><img src="'.$thumb.'" style="float:left;width:'.$vidinitsiz.'px;margin:3px 2px 3px 0;" alt="thumbnail" /></a>';
    $ret.= ((left($row['description'], 3)=='<p>')?'<p style="margin-top:0">'.substr($row['description'], 3):$row['description']);
    if($row['externalurl']!==null)
      $ret.= '<p style="text-indent:0;margin-bottom:0;"><a href="'.$row['externalurl'].'" target="_blank" onclick="incresviews('.$rid.')">View the article</a> <img src="/i/popout'.$colors[0].'.png" style="width:18px" alt="popout" /></p>';
    $ret.= '</div></td></tr>';
    break;
  case 6: // book excerpt
    $ret.= 'working on it.</td></tr>';
    break;
  case 7: // library
    $ret.= '<a href="'.$row['externalurl'].'" onclick="incresviews('.$rid.')"><img src="/i/commentary'.$colors[0].'.png" alt="view" width="22" /></a></td>';
    $ret.= '<td style="vertical-align:middle;width:90%;"><strong>Library File: <noparse>'.$row['title'].'</noparse></strong>'.$strresid.$pubdate.$resviews.$delres.'</td></tr>';
    $ret.= '<tr><td colspan="2" style="max-width:'.($screenwidth-40).'px;padding:0 3px;">';
    $ret.= '<div style="width:100%;max-width:714px;text-align:left;margin:0;padding:0;">';
    $thumb = (($row['thumbnail']!=='nopic')?$row['thumbnail']:'/i/thumbnails/resourcelibrary.jpg');
    $ret.= '<a href="'.$row['externalurl'].'" onclick="incresviews('.$rid.')"><img src="'.$thumb.'" style="float:left;width:'.$vidinitsiz.'px;margin:3px 2px 3px 0;" alt="thumbnail" /></a>';
    $ret.= ((left($row['description'], 3)=='<p>')?'<p style="margin-top:0">'.substr($row['description'], 3):$row['description']);
    if($row['externalurl']!==null)
      //$ret.= '<p style="text-indent:0;margin-bottom:0;"><a href="'.$row['externalurl'].'" onclick="incresviews('.$rid.')">Download the file</a></p>';
      $ret.= '<p style="text-indent:0;margin-bottom:0;"><a href="/jsondload.php?fil='.$row['resourceid'].'" onclick="incresviews('.$rid.')">Download the file</a></p>';
    $ret.= '</div></td></tr>';
    break;
  }

  if($resedit==1 && $resshowedit==1){
    $ret.= '<tr><td colspan="2" style="text-align:right;'.(($row['active']==0)?'background-color:#'.(($colortheme==1)?'f88':'fee').';':'').'"><small>ID: '.$row['resourceid'].' | Referenced by: ';

    $sql = 'select count(*) from topic_assoc where resourceid = '.$rid.' ';
    $rcnt= rs($sql);
    $cnt = $rcnt[0];

    $sql = 'select testament, book, chapter, verse
            from resourceassign
            where resourceid = '.$row['resourceid'].'
            order by 1,2,3,4 ';
    $vss = dbquery($sql);
    $nj=0;
    while($rrow = mysqli_fetch_array($vss)){
      if($nj>0) $ret.= '; ';
      $ret.= fixresassign($rrow);
      $nj++;
    }
    if($nj==0) $ret.= '<span style="color:red;">none</span>';
    $ret.= ' <a onclick="olOpen(\'/resourceassign.php?resourceid='.$row['resourceid'].'\',600, 600);" title="assign resource"><img src="/i/assign.png" width="15" alt="assign" /></a>';
    $ret.= ' | Topics:('.$cnt.') ';
    $ret.= ' <a onclick="olOpen(\'/topicassign.php?resourceid='.$row['resourceid'].'\',600, 600);" title="assign topics"><img src="/i/assign.png" width="15" alt="assign" /></a>';
    $ret.= ' | Playlist: ';
    if($row['playlistid'] > 0){
      $sql = 'select playlisttitle from playlist where playlistid = '.$row['playlistid'].' ';
      $rrow = rs($sql);
      $ret.= '&ldquo;'.((strlen($rrow[0])>30)?left($rrow[0], 30).'..':$rrow[0]).'&rdquo;';
    }else $ret.= '<span style="color:red;">none</span>';

    $ret.= ' | '.$editlink;
    $ret.= ' | Active: <img src="/i/'.(($row['active']==1)?'checkmark':'redx').'.png" width="16" alt="x" />';
    $ret.= '<br />Finalized: <img src="/i/'.(($row['finalized']==1)?'checkmark':'redx').'.png" width="16" alt="x" />';
    if($row['editcomment']!=null) $ret.= '<br />Comment: '.$row['editcomment'];
    if($row['edituserid']!=0){
      $sql = 'select ifnull(revusername, myrevname) from myrevusers where myrevid > 0 and userid = '.$row['edituserid'].' ';
      $rrow = rs($sql);
      if($rrow)
        $ret.= '<br />Last edit by: '.$rrow[0];
      else
        $ret.= '<br />Last edit by: unknown';
    }
    $ret.= '</small></td></tr>';
  }
  $ret.= '</table>';
  return $ret;

}
function fixresassign($r){
  $t = $r[0];
  $b = $r[1];
  $c = $r[2];
  $v = $r[3];
  $ret=getbooktitle($t,$b, 0);
  $href='/'.$ret;
  if($t<2 && $c>0){$ret.=' '.$c.':'.$v;$href.='/'.$c.'/'.$v.'/1';}
  if($t<2 && $c==0){$ret.=' Bk Cmtry';$href='/book'.$href.'/ct';}
  if($t==2){$href='/info/'.$b.'/ct';}
  if($t==3){$href='/appx/'.$b.'/ct';}
  if($t==4){$ret='WordStudy: '.$ret;$href='/word'.$href.'/ct';}
  $ret='<a href="'.$href.'" target="_blank">'.$ret.'</a>';
  return $ret;
}

   /**
    * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
    *
    * @param string $text String to truncate.
    * @param integer $length Length of returned string, including ellipsis.
    * @param string $ending Ending to be appended to the trimmed string.
    * @param boolean $exact If false, $text will not be cut mid-word
    * @param boolean $considerHtml If true, HTML tags would be handled correctly
    *
    * @return string Trimmed string.
    */
   function truncateHtml($text, $length=100, $ending='...', $exact=false, $considerHtml=true, $anchor='') {
     if ($considerHtml) {
       // if the plain text is shorter than the maximum length, return the whole text
       // don't do this...20151030 rsw
       if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
         $text.=' ';
         //return $text.$ending.$anchor;
       }
       // splits all html-tags to scanable lines
       preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
       $total_length = strlen($ending);
       $open_tags = array();
       $truncate = '';
       foreach ($lines as $line_matchings) {
         // if there is any html-tag in this line, handle it and add it (uncounted) to the output
         if (!empty($line_matchings[1])) {
           // if it's an "empty element" with or without xhtml-conform closing slash
           if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
             // do nothing
           // if tag is a closing tag
           } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
             // delete tag from $open_tags list
             $pos = array_search($tag_matchings[1], $open_tags);
             if ($pos !== false) {
             unset($open_tags[$pos]);
             }
           // if tag is an opening tag
           } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
             // add tag to the beginning of $open_tags list
             array_unshift($open_tags, strtolower($tag_matchings[1]));
           }
           // add html-tag to $truncate'd text
           $truncate .= $line_matchings[1];
         }
         // calculate the length of the plain text part of the line; handle entities as one character
         $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
         if ($total_length+$content_length> $length) {
           // the number of characters which are left
           $left = $length - $total_length;
           $entities_length = 0;
           // search for html entities
           if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
             // calculate the real length of all entities in the legal range
             foreach ($entities[0] as $entity) {
               if ($entity[1]+1-$entities_length <= $left) {
                 $left--;
                 $entities_length += strlen($entity[0]);
               } else {
                 // no more characters left
                 break;
               }
             }
           }
           $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
           // maximum lenght is reached, so get off the loop
           break;
         } else {
           $truncate .= $line_matchings[2];
           $total_length += $content_length;
         }
         // if the maximum length is reached, get off the loop
         if($total_length>= $length) {
           break;
         }
       }
     } else {
       if (strlen($text) <= $length) {
         return $text.$ending.$anchor;
       } else {
         $truncate = substr($text, 0, $length - strlen($ending));
       }
     }
     // if the words shouldn't be cut in the middle...
     if (!$exact ) {
       // ...search the last occurance of a space...
       $spacepos = strrpos($truncate, ' ');
       if (isset($spacepos)) {
         // ...and cut the text in this position
         $truncate = substr($truncate, 0, $spacepos);
       }
     }
     // add the defined ending to the text
     if($considerHtml) {
       // rsw 20160819 checking for open <a> tags before appending ReadMore link
       $idx=0;
       foreach ($open_tags as $tag) {
         if($tag=='a'){
           $truncate .= '></' . $tag . '>';
           array_splice($open_tags, $idx, 1);
         }
         $idx++;
       }
     }
     $truncate .= $ending.$anchor;
     if($considerHtml) {
       // close all unclosed html-tags
       foreach ($open_tags as $tag) {
         $truncate .= '</' . $tag . '>';
       }
     }
     return $truncate;
   }

function convertState($name) {
  $states = array(
    'AL'=>'ALABAMA',
    'AK'=>'ALASKA',
    'AS'=>'AMERICAN SAMOA',
    'AZ'=>'ARIZONA',
    'AR'=>'ARKANSAS',
    'CA'=>'CALIFORNIA',
    'CO'=>'COLORADO',
    'CT'=>'CONNECTICUT',
    'DE'=>'DELAWARE',
    'DC'=>'DISTRICT OF COLUMBIA',
    'FM'=>'FEDERATED STATES OF MICRONESIA',
    'FL'=>'FLORIDA',
    'GA'=>'GEORGIA',
    'GU'=>'GUAM GU',
    'HI'=>'HAWAII',
    'ID'=>'IDAHO',
    'IL'=>'ILLINOIS',
    'IN'=>'INDIANA',
    'IA'=>'IOWA',
    'KS'=>'KANSAS',
    'KY'=>'KENTUCKY',
    'LA'=>'LOUISIANA',
    'ME'=>'MAINE',
    'MH'=>'MARSHALL ISLANDS',
    'MD'=>'MARYLAND',
    'MA'=>'MASSACHUSETTS',
    'MI'=>'MICHIGAN',
    'MN'=>'MINNESOTA',
    'MS'=>'MISSISSIPPI',
    'MO'=>'MISSOURI',
    'MT'=>'MONTANA',
    'NE'=>'NEBRASKA',
    'NV'=>'NEVADA',
    'NH'=>'NEW HAMPSHIRE',
    'NJ'=>'NEW JERSEY',
    'NM'=>'NEW MEXICO',
    'NY'=>'NEW YORK',
    'NC'=>'NORTH CAROLINA',
    'ND'=>'NORTH DAKOTA',
    'MP'=>'NORTHERN MARIANA ISLANDS',
    'OH'=>'OHIO',
    'OK'=>'OKLAHOMA',
    'OR'=>'OREGON',
    'PW'=>'PALAU',
    'PA'=>'PENNSYLVANIA',
    'PR'=>'PUERTO RICO',
    'RI'=>'RHODE ISLAND',
    'SC'=>'SOUTH CAROLINA',
    'SD'=>'SOUTH DAKOTA',
    'TN'=>'TENNESSEE',
    'TX'=>'TEXAS',
    'UT'=>'UTAH',
    'VT'=>'VERMONT',
    'VI'=>'VIRGIN ISLANDS',
    'VA'=>'VIRGINIA',
    'WA'=>'WASHINGTON',
    'WV'=>'WEST VIRGINIA',
    'WI'=>'WISCONSIN',
    'WY'=>'WYOMING'
  );
  $states = array_flip($states);
  if(array_key_exists($name, $states))
    return $states[$name];
  else return 'XX';
}

?>
