<?php
if(!isset($page)) die('unauthorized access');?>
<a id="botbot"></a><br />
<span id="bottomspacing" style="display:inline-block;width:3px;">&nbsp;</span>
</div><!-- view -->
<?if($userid==0 && $site=='www.revisedenglishversion.com'){?>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-60484481-1"></script>;
<script>
 window.dataLayer = window.dataLayer || [];
 function gtag(){dataLayer.push(arguments);}
 gtag('js', new Date());

 gtag('config', 'UA-60484481-1');
</script>
<?}?>
<script>
<?
  if($parachoice % 2==0 && ($page==0 || $page==4 || $page==5 || $page==9 || $page==10 || $page==14 || $page==20 || $page==25)){?>
  // in javascript
  function hyphenate(){
    Hyphenator_Loader.init(
      // https://github.com/mnater/Hyphenator/blob/wiki/en_PublicAPI.md
      {"en":"hyphenationalgorithm",},"/includes/hyphenator.js",{minwordlength:5,useCSS3hyphenation:true,safecopy:true,intermediatestate:'hidden'}
    );
  }
  addLoadEvent(hyphenate);
  <?}?>

function spacebottom(){
  $('bottomspacing').style.height = (window.innerHeight-320)+'px';
}
addLoadEvent(spacebottom);

function addclicktoview(){
  if($('biblenav').style.height!='0px'){sizenavto(0);}
  if(isexpanded==1){excol(isexpanded);};
  try{if($('myrevdiv').style.display=='block') myrevhidePopup();}catch(e){};
}
function initclicktoview(){
  $('view').addEventListener('click', addclicktoview);
}
addLoadEvent(initclicktoview);


var tz_offset_minutes = new Date().getTimezoneOffset();
tz_offset_minutes = ((tz_offset_minutes==0)?0:-tz_offset_minutes);
setCookie('rev_timezone', tz_offset_minutes, cookieexpiredays);
var timezone = '<?=$timezone?>';
var hdrheight = <?=$hdrheight?>;

// I'll do this later
//var tutorials=[];

function lightbox(setlbcookie){
  var txt='', lightbox=document.createElement('DIV'), videokey='_D_4rejDxe0';
  lightbox.id='lightbox';
  lightbox.style.opacity=0;
  lightbox.style.transition='.8s';
  txt = '<div style="position:relative;top:50%;transform:translateY(-50%);width:90%;max-width:800px;left:0;right:0;margin:auto;padding:10px;background-color:<?=$colors[2]?>;border:1px solid <?=$colors[6]?>;border-radius:8px;text-align:center;">';
  txt+= '<h3 style="text-align:center;margin:0;">Welcome to the Online REV!</h3>';
  txt+= '<p style="margin:7px 0;line-height:1.3em;text-align:left;">To learn the basics of how to navigate the Online REV, please take a minute to watch this short video. Otherwise, click &ldquo;Close&rdquo; at the bottom. God bless you!</p>';
  txt+= '<div style="position:relative;height:0;overflow:hidden;padding-bottom:56.25%;z-index:999;">'+
        '<iframe  width="425" height="344" src="https://www.youtube.com/embed/'+videokey+'" frameborder="0" allowfullscreen '+
        'style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:999;"></iframe></div>';
  if(setlbcookie==1) txt+='<p style="margin:5px 0;"><input type="checkbox" id="noshowbox" value="1" /> <small>Never show again</small></p>';
  txt+= '<p style="margin:5px 0;"><input type="button" class="gobackbutton" value="Close" onclick="if('+setlbcookie+'){if($(\'noshowbox\').checked) setCookie(\'rev_lightbox_permanent\', 1, 900); else setCookie(\'rev_lightbox_session\',1,.010417);}lbfadeout();" /></p>';
  txt+= '</div>';
  lightbox.innerHTML = txt;
  document.body.appendChild(lightbox);
  setTimeout('lbfadein()', 100);
}

var lightboxpermanent = (getCookie('rev_lightbox_permanent')||0);
var lightboxsession = (getCookie('rev_lightbox_session')||0);
var lightboxseen = ((lightboxpermanent || lightboxsession)?1:0);

// for testing
//lightboxseen=1;
//lightboxseen=0;

if(lightboxseen==0) setTimeout('lightbox(1);', 500);
else if(lightboxpermanent==0) setCookie('rev_lightbox_session',1,.010417); // 15 minutes

function lbfadein(){
  var lb = document.getElementById('lightbox');
    lb.style.opacity=1;
}
function lbfadeout(){
  var lb = document.getElementById('lightbox');
  lb.style.opacity=0;
  setTimeout("closelb()", 800);
}
function closelb(){
  var lb = document.getElementById('lightbox');
  lb.parentNode.removeChild(lb);
}

<?
if($myrevid>0)
  print('setCookie(\'myrevsid\', myrevsid, cookieexpiredays);');

if($superman==1 && $showpdflinks){
  print('$("rsw").innerHTML += "; mem:'.formatBytes(memory_get_peak_usage()).'";'.crlf);
  print('$("rsw").innerHTML += "; db:'.$dbhits.'";'.crlf);
  $pgsize = ob_get_length();
  print('$("rsw").innerHTML += "; sz:'.number_format($pgsize/1000, 1).'kb";'.crlf);
  print('$("rsw").innerHTML += "; tm:'.number_format((microtime(true) - $time_start), 3).'";'.crlf);
}
print('</script>');
print('</body></html>');
//
//
// END of HTML generation
//
//

// this removes files older than x minutes from the export directory
// this seems like a lot of code to do something so simple...
$lastcleaned = getsettingvalue('exportclean', 'time');
$lastcleaned = date_create($lastcleaned);
$currenttime = date_create(null ?? '');
$interval = date_diff($lastcleaned, $currenttime);
$minutes = abs($interval->format('%i'));
$delminutes = 7;

if($minutes >= $delminutes){
  $update = dbquery('update settings set sometime = \''.date("Y-m-d H:i").'\' where settingname = \'exportclean\'');
  $path = $docroot.'/export/';
  if ($handle = opendir($path)) {
    while (false !== ($file = readdir($handle))) {
      $filelastmodified = filemtime($path . $file);
      // DANGEROUS!  MUST check for '..' and '.'
      if(($file!='.' && $file!='..' && $file!='pdfwork' && $file!='expdown' && $file!='library') && ((time() - $filelastmodified) > (60 * $delminutes))){
        if(is_dir($path.$file) && left($file, 4)=='tmp_') deltree($path.$file, 1);
        else unlink($path.$file);
      }
    }
    closedir($handle);
  }
}

$lastcleaned = getsettingvalue('mappingclean', 'time');
$lastcleaned = date_create($lastcleaned);
$currenttime = date_create(null ?? '');
$interval = date_diff($lastcleaned, $currenttime);
$minutes = abs($interval->format('%i'));

if($minutes >= 50){
  $update = dbquery('update settings set sometime = \''.date("Y-m-d H:i").'\' where settingname = \'mappingclean\'');

  // clean up orphaned myrevdata
  $sql = 'delete from myrevdata where myrevid not in (select myrevid from myrevusers) ';
  $del = dbquery($sql);

  // delete viewlogs older than 30 days
  $sql = 'delete from viewlogs where viewtime < DATE_ADD(UTC_TIMESTAMP(),INTERVAL -30 DAY)';
  $del = dbquery($sql);

  // delete expired sops sessions
  $sql = 'update verse set edituserid = 0, editsession = null, lockeduntil = null where testament in (0,1) and edituserid > 0 and lockeduntil is not null and lockeduntil < DATE_ADD(UTC_TIMESTAMP(),INTERVAL -6 HOUR)';
  $del = dbquery($sql);

  // delete rows from ipcrossref for which there are no viewlogs
  $sql = 'delete from ipcrossref where ipaddress not in (select remoteip from viewlogs)';
  $del = dbquery($sql);

  // delete non-whatsnew and non ednote editlogs older than 365 days
  $sql = 'delete from editlogs 
          where whatsnew = 0 
          and `comment` not like \'%Book status changed%\' 
          and editdate < DATE_ADD(UTC_TIMESTAMP(),INTERVAL -365 DAY)
          and (concat(testament, \'|\', book,\'|\', chapter, \'|\', verse) not in
            (select concat(en.testament, \'|\', en.book,\'|\', en.chapter, \'|\', en.verse)
             from editnotes en)
          or userid in (1,9))';
  $del = dbquery($sql);
  
  // delete non-whatsnew editlogs for Rob (1) and Cris(9) older than 365 days
  //$sql = 'delete from editlogs 
  //        where whatsnew = 0 
  //        and editdate < DATE_ADD(UTC_TIMESTAMP(),INTERVAL -365 DAY)
  //        and userid in (1,9)';
  //$del = dbquery($sql);

  // delete editlogs older than the date of the 76th whatsnew editlog
  $row = rs('select tmp.editdate from (SELECT * FROM editlogs WHERE whatsnew = 1 ORDER BY editdate desc LIMIT 76) tmp order by editdate limit 1');
  if($row){
    // remove whatsnew markers..
    $cutdate = $row[0];

    $sql = 'select logid, page, testament, book, chapter, verse from editlogs where whatsnew = 1 and editdate < \''.$cutdate.'\'';
    $result = dbquery($sql);
    while($row = mysqli_fetch_array($result)){

      switch($row['page']){
      case 1:
        $bookname = 'verse';
        $whr = 'testament = '.$row['testament'].' and book = '.$row['book'].' and chapter = '.$row['chapter'].' and verse = '.$row['verse'];
        break;
      case 6:
        $bookname = 'book';
        $whr = 'testament = '.$row['testament'].' and book = '.$row['book'];
        break;
      case 8:
        $bookname = 'verse';
        $whr = 'testament = '.$row['testament'].' and book = '.$row['book'].' and chapter = 1 and verse = 1';
        break;
      default:
        $bookname = 'verse';
        $whr = 'testament = '.$row['testament'].' and book = '.$row['book'].' and chapter = '.$row['chapter'].' and verse = '.$row['verse'];
      }
      $lid = $row['logid'];
      $rep = '<a id="marker'.$lid.'" name="marker'.$lid.'"></a>';
      $sql = 'update '.$bookname.' set commentary = replace(commentary, \''.$rep.'\', \'\') where '.$whr;
      $upd = dbquery($sql);
      $rep = '<a name="marker'.$lid.'" id="marker'.$lid.'"></a>';
      $sql = 'update '.$bookname.' set commentary = replace(commentary, \''.$rep.'\', \'\') where '.$whr;
      $upd = dbquery($sql);
      $rep = '<a name="marker'.$lid.'"></a>';
      $sql = 'update '.$bookname.' set commentary = replace(commentary, \''.$rep.'\', \'\') where '.$whr;
      $upd = dbquery($sql);
      $rep = '<a id="marker'.$lid.'"></a>';
      $sql = 'update '.$bookname.' set commentary = replace(commentary, \''.$rep.'\', \'\') where '.$whr;
      $upd = dbquery($sql);
    }

    //$sql = 'delete from editlogs where editdate < \''.$cutdate.'\'';
    //$del = dbquery($sql);
  }
  $del = dbquery('delete from editlogsviewed where logid not in (select logid from editlogs)');

  //
  // Handle IP mappings
  //
  $sql = 'select remoteip, count(remoteip)
          from viewlogs
          where remoteip not in (select ipaddress from ipcrossref)
          group by remoteip having count(remoteip) > 9 ';
  $ips = dbquery($sql);
  $ni=0;$errcnt=0;
  //
  // see code in mapips.php
  //
  $usegeoloc=1;
  $geolocapi = 'f545848a2c3f40b5890bced8666c92e5';
  while($row = mysqli_fetch_array($ips)){
    $ip = $row[0];
    $chkagain=0;
    if($usegeoloc==1){
      $jsonurl = 'https://api.ipgeolocation.io/ipgeo?apiKey='.$geolocapi.'&ip='.$ip.'&fields=geo';
      $json = my_curl($jsonurl,5,1);
      if($json){
        $details = json_decode($json, true);
        $location = '';
        if(!isset($details['ip'])){ // if this is not set, there is an error
          $chkagain=1;
        }else{
          if(isset($details['city']) && $details['city'] != '') $location.=$details['city'];
          else $chkagain = 1;
          if($details['country_code2']=='US'){
            $location.=(($location!='')?', ':'').convertState(strtoupper($details['state_prov']));
          }else{
            $tmp = $details['state_prov'];
            if(strlen($tmp) > 13) $tmp = substr($tmp, 0, 12).'..';
            $location.=(($location!='' && $tmp!='')?', ':'').$tmp;
          }
          if(trim($location)==''){$location = '(unknown)';$chkagain=1;}
          $location.=', '.(($details['country_code2'])?$details['country_code2']:'!UNKN');
        }
      }else{$chkagain=1;}
    }

    if($usegeoloc==0 || $chkagain==1){
      $jsonurl = 'http://ip-api.com/json/'.$ip.'?fields=status,message,countryCode,region,regionName,city,query';
      $json = my_curl($jsonurl,5,1);
      if($json){
        $details = json_decode($json, true);
        $location = '';
        if($details['status']!='success'){
          $location = '(unknown)';
        }else{
          if(isset($details['city'])) $location.=$details['city'];
          if($details['countryCode']=='US'){
            $location.=(($location!='')?', ':'').$details['region'];
          }else{
            $tmp = $details['regionName'];
            if(strlen($tmp) > 13) $tmp = substr($tmp, 0, 12).'..';
            $location.=(($location!='' && $tmp!='')?', ':'').$tmp;
          }
          if(trim($location)==''){$location = '(unknown)';}
          $location.=', '.(($details['countryCode'])?$details['countryCode']:'!UNKN');
        }
      }else{$location = '(unknown)';}
    }

    $location = trim($location);
    if($location=='') $location = '(unknown)';
    //$location = uxxxtf8_encode($location);
    $location = mb_convert_encoding($location, 'UTF-8');
    $location = processsqltext($location,  36, 0, '!!fill me in');

    $ip = processsqltext($ip,  20, 0, '!none!');

    $row = rs('select \'x\' from ipcrossref where ipaddress = '.$ip.'; ');
    if($row)
      $update = dbquery('update ipcrossref set iplocation = '.$location.' where ipaddress = '.$ip.'; ');
    else
      $insert = dbquery('insert into ipcrossref(ipaddress, iplocation) values ('.$ip.', '.$location.'); ');
    $ni++;
  }

}
mysqli_close($db);

//
//
//
function deltree($dir, $deldir) {
   $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file") && !is_link($dir)) ? deltree("$dir/$file", $deldir) : unlink("$dir/$file");
    }
    return (($deldir==1)?rmdir($dir):'');
  }

function my_curl($url, $timeout=2, $error_report=FALSE)
{
  $curl = curl_init();

  // HEADERS FROM FIREFOX - APPEARS TO BE A BROWSER REFERRED BY GOOGLE
  $header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
  $header[] = "Cache-Control: max-age=0";
  $header[] = "Connection: keep-alive";
  $header[] = "Keep-Alive: 300";
  $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
  $header[] = "Accept-Language: en-us,en;q=0.5";
  $header[] = "Pragma: "; // browsers keep this blank.

  // SET THE CURL OPTIONS - SEE http://php.net/manual/en/function.curl-setopt.php
  curl_setopt($curl, CURLOPT_URL,            $url);
  curl_setopt($curl, CURLOPT_USERAGENT,      'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6');
  curl_setopt($curl, CURLOPT_HTTPHEADER,     $header);
  curl_setopt($curl, CURLOPT_REFERER,        'http://www.google.com');
  curl_setopt($curl, CURLOPT_ENCODING,       'gzip,deflate');
  curl_setopt($curl, CURLOPT_AUTOREFERER,    TRUE);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
  curl_setopt($curl, CURLOPT_TIMEOUT,        $timeout);

  // RUN THE CURL REQUEST AND GET THE RESULTS
  $htm = curl_exec($curl);
  $err = curl_errno($curl);
  $inf = curl_getinfo($curl);
  curl_close($curl);

  // ON FAILURE
  if (!$htm)
  {
      // PROCESS ERRORS HERE
      if ($error_report)
      {
          echo "CURL FAIL: $url TIMEOUT=$timeout, CURL_ERRNO=$err"."<br /><br />";
          var_dump($inf);
          echo "<br /><br />";
      }
      return FALSE;
  }

  // ON SUCCESS
  return $htm;
}



?>

