<?php
if(empty($userid) || $userid==0 || empty($superman) || $superman==0) {print('<h3>unauthorized access</h3>');return;}
error_reporting(E_ALL);
set_time_limit(480); // 8 minutes

$processytstf  =((isset($_REQUEST['pstf']))?1:0);
$processytbuu  =((isset($_REQUEST['pbuu']))?1:0);
$processyttot  =((isset($_REQUEST['ptot']))?1:0);
$processpodbean=((isset($_REQUEST['ppod']))?1:0);
$processcastos =((isset($_REQUEST['pcas']))?1:0);
$msg='';


$baseUrl = 'https://www.googleapis.com/youtube/v3/';
$apiKey = 'AIzaSyAb5ypTMCgEHEEYVNioizxfuZmlfe94G0k';

if($processytstf==1){
  $vcnt=0;

  // STF Online ChannelID
  $channelid = 'UC-MXW2THKq5jwekJL0tp-Ug';
  processchannel($channelid);

  $msg.= (($vcnt==0)?'':'<span style="color:green;">').'YouTube SandT_VF: '.$vcnt.' video(s) added.'.(($vcnt==0)?'':'</span>').'<br />';
}

if($processytbuu==1){
  $vcnt=0;

  // BU ChannelID
  $channelid = 'UC6KcEHihLz_o6G4uFw0Ersw';
  processchannel($channelid);

  $msg.= (($vcnt==0)?'':'<span style="color:green;">').'YouTube BU: '.$vcnt.' video(s) added.'.(($vcnt==0)?'':'</span>').'<br />';
}

if($processyttot==1){
  $vcnt=0;

  // TorT ChannelID
  $channelid = 'UCL2hIcr45LIiTbPW3o5FmYw';
  processchannel($channelid);

  $msg.= (($vcnt==0)?'':'<span style="color:green;">').'YouTube SandT: '.$vcnt.' video(s) added.'.(($vcnt==0)?'':'</span>').'<br />';
}

if($processpodbean==1){
  $acnt=0;
  //$url = 'https://spirittruth.podbean.com/feed.xml';
  $url = 'https://feed.podbean.com/spirittruth/feed.xml';
  $xmlf = curl_get_contents($url);
  //$xmlf = my_curl_local($url, 2, true);
  if($xmlf){
    $xmlf = str_replace('itunes:duration', 'duration', $xmlf); // this is hack!!
    $xml  = simplexml_load_string($xmlf, 'SimpleXMLElement', LIBXML_NOCDATA);
    $json = json_encode($xml);
    $audios= json_decode($json, true);

    foreach($audios['channel']['item'] as $audio){

      $resourcetype= 3; // podbean audio
      $title       = processsqltext($audio['title'], 200, 0, 'missing title!');
      $description = $audio['description'];
      $publishedon = $audio['pubDate'];
      $source      = processsqltext('PodBean', 200, 0, 'PodBean');
      $identifier  = processsqltext($audio['enclosure']['@attributes']['url'], 200, 0, 'missing identifier!');
      $duration    = processsqltext($audio['duration'], 10, 1, '');
      $externalurl = processsqltext($audio['link'], 200, 1, '');

      if(strpos($description, '<p><strong>Find us online at') !== false){
        $description = substr($description, 0, strpos($description, '<p><strong>Find us online at'));
      }
      if(strpos($description, '<strong>Find us online at') !== false){
        $description = substr($description, 0, strpos($description, '<strong>Find us online at'));
      }
      $description = processsqlcomm(left($description, 2900), 1, '');

      $publishedon = date_format(date_create($publishedon),"Y-m-d");
      $publishedon = processsqltext($publishedon, 20, 1, '');

      $sql = 'select 1 from resource where resourcetype = 3 and identifier = '.$identifier.' ';
      $row = rs($sql);
      if(!$row){
        $sql = 'insert into resource (resourcetype, title, description, publishedon, source, duration, identifier, externalurl) values ('.
               $resourcetype.', '.$title.', '.$description.', '.$publishedon.', '.$source.', '.$duration.', '.$identifier.', '.$externalurl.') ';
        //print('<br />'.$sql.'<br />');
        $insert = dbquery($sql);
        $acnt++;
      }else break;
    }
  }else{
    $msg.= 'error with PodBean feed...</br>';
  }
  $msg.= (($acnt==0)?'':'<span style="color:green;">').'PodBean: '.$acnt.' audio(s) added.'.(($acnt==0)?'':'</span>').'<br />';

}

if($processcastos==1){
  $acnt = 0;
  $url = 'https://truthortradition.castos.com/feed';
  $xmlf = curl_get_contents($url);
  //$xmlf = my_curl_local($url, 2, true);
  if($xmlf){
    $xmlf = str_replace('itunes:duration', 'duration', $xmlf); // this is hack!!
    $xml  = simplexml_load_string($xmlf, 'SimpleXMLElement', LIBXML_NOCDATA);
    $json = json_encode($xml);
    $audios= json_decode($json, true);
    foreach($audios['channel']['item'] as $audio){

      //print_r($audio);
      //die();

      $resourcetype= 3; // audio
      $title       = processsqltext($audio['title'], 200, 0, 'missing title!');
      //$description = processsqlcomm(left(((isset($audio['description'][0]))?$audio['description'][0]:''), 1900), 1, '');
      $description = processsqlcomm(left(((isset($audio['description']))?$audio['description']:''), 1900), 1, '');
      $publishedon = ((isset($audio['pubDate']))?$audio['pubDate']:'');
      $source      = processsqltext('Castos', 200, 0, 'Castos');
      $duration    = $audio['duration'];
      $identifier  = processsqltext($audio['enclosure']['@attributes']['url'], 200, 0, 'missing identifier!');
      $thumbnail   = '\'/i/wow_logo.png\'';

      if(is_array($audio['guid']))
        $externalurl = processsqltext($audio['guid'][0], 200, 1, '');
      else
        $externalurl = processsqltext($audio['guid'], 200, 1, '');

      if($duration){
        if(left($duration, 3)=='00:') $duration = substr($duration, 3);
      }
      $duration = processsqltext($duration, 10, 1, '');

      $publishedon = date_format(date_create($publishedon),"Y-m-d");
      $publishedon = processsqltext($publishedon, 20, 1, '');

      $sql = 'select 1 from resource where resourcetype in (3,4) and identifier = '.$identifier.' ';
      $row = rs($sql);
      if(!$row){
        $sql = 'insert into resource (resourcetype, title, description, publishedon, source, identifier, duration, externalurl, thumbnail) values ('.
               $resourcetype.', '.$title.', '.$description.', '.$publishedon.', '.$source.', '.$identifier.', '.$duration.', '.$externalurl.', '.$thumbnail.') ';
        //print('<br />'.$sql.'<br />');
        $insert = dbquery($sql);
        $acnt++;
      }else break;
    }
  }else{
    $msg.= 'error with Castos feed...</br>';
  }
  $msg.= (($acnt==0)?'':'<span style="color:green;">').'Castos: '.$acnt.' audio(s) added.'.(($acnt==0)?'':'</span>').'<br />';

}

if($processytstf==0 && $processytbuu==0 && $processyttot==0 && $processpodbean==0 && $processcastos==0)
  $msg.= 'Nothing to do..';



?>
<span class="pageheader">Update Resources</span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>
<div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:90%;">
<p>&nbsp;</p>
<form name="frm" method="post" action="/" onsubmit="validate(this);">

  <table class="gridtable">
    <tr>
      <td colspan="2" style="text-align:left;"><small><span style="color:red"><b><?=$msg?></b></span></small></td>
    </tr>
    <tr>
      <td><input type="checkbox" name="pstf" value="1" /></td>
      <td style="text-align:left;">Youtube: SpiritAndTruth_VF</td>
    </tr>
    <tr>
      <td><input type="checkbox" name="pbuu" value="1" /></td>
      <td style="text-align:left;">Youtube: Biblical Unitarian</td>
    </tr>
    <tr>
      <td><input type="checkbox" name="ptot" value="1" /></td>
      <td style="text-align:left;">Youtube: Spirit And Truth</td>
    </tr>
    <tr>
      <td><input type="checkbox" name="ppod" value="1" disabled /></td>
      <td style="text-align:left;">PodBean Audio <span style="color:red;">&lt;=nope</span></td>
    </tr>
    <tr>
      <td><input type="checkbox" name="pcas" value="1" /></td>
      <td style="text-align:left;">Castos Audio <small>..also seminars</small></td>
    </tr>
    <tr>
      <td colspan="2"><input type="submit" name="btnSub" value="Submit" onclick="validate(document.frm)" /></td>
    </tr>
  </table>


  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="oper" value="" />
</form>
  </div>
  <script>
  function validate(f){
    f.oper.value='process';
    return true;
  }
  </script>
<?
//
//
//  video functions
//
//
function processchannel($chid){
  global $baseUrl, $apiKey, $vcnt;
  $params = [
      'id'=> $chid,
      'part'=> 'contentDetails',
      'key'=> $apiKey
  ];
  $url = $baseUrl . 'channels?' . http_build_query($params);
  $json = json_decode(curl_get_contents($url), true);

  $playlist = $json['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

  $params = [
      'part'=> 'snippet',
      'playlistId' => $playlist,
      'maxResults'=> '20',
      'key'=> $apiKey
  ];

  $url = $baseUrl . 'playlistItems?' . http_build_query($params);
  $json = json_decode(curl_get_contents($url), true);

  // have to sort the JSON array by publishedAt desc
  // I'm not sure how this works, but it does...
  $videos = array();
  foreach($json['items'] as $video){
    array_push($videos, $video['snippet']);
  }

  // sort alphabetically by publishedAt
  usort($videos, 'comparepubdate');
  // reverse it
  $videos = array_reverse($videos);

  $identifiers = '';
  foreach($videos as $video){
    $tcnt = processvideo($video);
    if($tcnt==0) break;
    $vcnt ++;
    $identifiers.= ','.$video['resourceId']['videoId'];
  }
  if($identifiers!=''){
    $identifiers = substr($identifiers, 1);
    updateDuration($identifiers);
  }
}

function comparepubdate($a, $b){
  return strnatcmp($a['publishedAt'], $b['publishedAt']);
}


function processvideo($v){
  $resourcetype= 1; // youtube video
  $title       = processsqltext($v['title'], 200, 0, 'missing title!');
  $description = $v['description'];
  $publishedon = $v['publishedAt'];
  $source      = processsqltext($v['channelTitle'], 40, 1, '');
  $identifier  = processsqltext($v['resourceId']['videoId'], 20, 1, '');
  $externalurl = processsqltext('https://www.youtube.com/watch?v='.str_replace('\'', '', $identifier).'&rel=0', 200, 1, '');
  $thumbnail   = processsqltext($v['thumbnails']['medium']['url'], 200, 1, '');

  $source = str_replace('biblicalunitarian', 'BiblicalUnitarian', $source);
  //$source = str_replace('STF Online Fellowships', 'STFOnline', $source);
  $source = str_replace('Spirit & Truth Online Fellowships', 'SpiritAndTruth_VF', $source);
  $source = str_replace('Spirit & Truth', 'SpiritAndTruth', $source);

  if(strpos($description, "\xF0") !== false){
    $description = substr($description, 0, strpos($description, "\xF0"));
  }

  $description = processsqlcomm('<p>'.str_replace("\n", '</p><p>', left($description, 2900)).'</p>', 1, '');

  $publishedon = substr($publishedon, 0, strpos($publishedon, "T"));
  $publishedon = processsqltext($publishedon, 20, 1, '');

  $sql = 'select 1 from resource where resourcetype = 1 and identifier = '.$identifier.' ';
  $row = rs($sql);
  if(!$row){
    $sql = 'insert into resource (resourcetype, title, description, publishedon, source, identifier, externalurl, thumbnail) values ('.
           $resourcetype.', '.$title.', '.$description.', '.$publishedon.', '.$source.', '.$identifier.', '.$externalurl.', '.$thumbnail.') ';
    //print('<br />'.$sql.'<br />');
    $insert = dbquery($sql);
    return 1;
  }else return 0;

}

function updateDuration($identifiers){

  global $apiKey;
  $dur = curl_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=$identifiers&key=$apiKey");
  $VidDuration =json_decode($dur, true);
  foreach ($VidDuration['items'] as $vidTime){
    $identifier = $vidTime['id'];
    $VidDuration= $vidTime['contentDetails']['duration'];
    $tim = convtime($VidDuration);
    $sql = 'update resource set duration = \''.$tim.'\' where identifier = \''.$identifier.'\' ';
    //print($sql.'<br />'.crlf);
    $update = dbquery($sql);
  }
}

function convtime($yt){
    $yt=str_replace(['P','T'],'',$yt);
    foreach(['D','H','M','S'] as $a){
        $pos=strpos($yt,$a);
        if($pos!==false) ${$a}=substr($yt,0,$pos); else { ${$a}=0; continue; }
        $yt=substr($yt,$pos+1);
    }
    if($D>0){
        $M=str_pad($M,2,'0',STR_PAD_LEFT);
        $S=str_pad($S,2,'0',STR_PAD_LEFT);
        return ($H+(24*$D)).":$M:$S"; // add days to hours
    } elseif($H>0){
        $M=str_pad($M,2,'0',STR_PAD_LEFT);
        $S=str_pad($S,2,'0',STR_PAD_LEFT);
        return "$H:$M:$S";
    } else {
        $S=str_pad($S,2,'0',STR_PAD_LEFT);
        return "$M:$S";
    }
}

function curl_get_contents($url)
{
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 90);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($curl);
  curl_close($curl);
  return $data;
}


//
//
// not using
//
//
function my_curl_local($url, $timeout=2, $error_report=FALSE)
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
          echo "CURL FAIL: $url<br />TIMEOUT=$timeout<br />CURL_ERRNO=$err<br />";
          var_dump($inf);
      }
      return FALSE;
  }

  // ON SUCCESS
  return $htm;
}



