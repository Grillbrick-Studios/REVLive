<?php
if(empty($userid) || empty($superman) || $userid==0 || $superman==0) die('unauthorized access');
ini_set('max_execution_time', 480); //480 seconds = 8 minutes
if($userid==1){
  error_reporting(E_ALL);
}
$stitle = 'Map IPs';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$sort = (isset($_POST['sort']))?$_POST['sort']:3;
$fillempty = (isset($_POST['fillempty']))?1:0;
$clearorphans = (isset($_POST['clearorphans']))?1:0;

$usegeoloc = 1;
$geolocapi = 'f545848a2c3f40b5890bced8666c92e5';

// update ipcrossref
$msg2='';
if($fillempty==1){
  $sql = 'select remoteip, count(remoteip)
          from viewlogs
          where remoteip not in (select ipaddress from ipcrossref)
          group by remoteip having count(remoteip) > 9 ';
  $ips = dbquery($sql);
  $ni=0;$errcnt=0;
  while($row = mysqli_fetch_array($ips)){
    $ip = $row[0];
    $chkagain=0;
    if($usegeoloc==1){
      $jsonurl = 'https://api.ipgeolocation.io/ipgeo?apiKey='.$geolocapi.'&ip='.$ip.'&fields=geo';
      $json = my_curl_local($jsonurl,5,1);
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

    //$chkagain=1; // testing
    // documentation: https://ip-api.com/docs/api:json

    if($usegeoloc==0 || $chkagain==1){
      $jsonurl = 'http://ip-api.com/json/'.$ip.'?fields=status,message,countryCode,region,regionName,city,query';
      $json = my_curl_local($jsonurl,5,1);
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
  $msg2 = $ni.' IPs processed, '.$errcnt.' failures';
  $pagnum=1;

}
if($clearorphans==1){
  $sql = 'delete from ipcrossref where ipaddress not in (select remoteip from viewlogs)';
  $del = dbquery($sql);
}

$msg = "";
$sqlerr='';
$range = ((isset($_POST['range']))?$_POST['range']:'un');

if($oper=='sav'){
  $ni=0;
  while(isset($_POST['mapip'.$ni])){
    if($_POST['dirty'.$ni] == 1){
      $ip = $_POST['mapip'.$ni];
      if(isset($_POST['mapdelete'.$ni]) && ($_POST['mapdelete'.$ni] > 0)){
        $sql = 'delete from ipcrossref where id = \''.$_POST['mapipid'.$ni].'\' ';
        $update = dbquery($sql);
        if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
      }else{
        $ipid = $_POST['mapipid'.$ni];
        $ip = ((isset($_POST['mapip'.$ni]))?$_POST['mapip'.$ni]:'!none!');
        $ipl= ((isset($_POST['mapiploc'.$ni]))?$_POST['mapiploc'.$ni]:'!!fill me in');
        if($ipid>0)
          $update = dbquery('update ipcrossref set ipaddress = '.processsqltext($ip,  20, 0, '!none!').', iplocation = '.processsqltext($ipl,  36, 0, '!!fill me in').' where id = \''.$ipid.'\'; ');
        else
          $insert = dbquery('insert into ipcrossref(ipaddress, iplocation) values ('.processsqltext($ip,  20, 0, '!none!').', '.processsqltext($ipl,  36, 0, '!!fill me in').'); ');
      }
      //$msg.=$ni.'<br />';
    }
    $ni++;
  }
  $sqlerr = $msg;
  if($sqlerr=='') $sqlerr = datsav.' '.$msg2;
}


$lastcleaned = getsettingvalue('mappingclean', 'time');
//print($lastcleaned);
$lastcleaned = strtotime($lastcleaned);
$nextcleaning= date('Y-m-d g:i a', strtotime('+50 minutes', $lastcleaned));
$nowtime = strtotime(date('Y-m-d H:i:s'));
$diffminutes = date('i', ($nowtime-$lastcleaned));
$servertimezone = date_default_timezone_get();
try {
  $dateTime = new DateTime($nextcleaning ?? '', new DateTimeZone($servertimezone));
  $dateTime->setTimezone(new DateTimeZone($timezone));
  $nextcleaning = $dateTime->format('g:i a');
} catch (Exception $e) {
    $nextcleaning = 'failed';
  }

$pagnum    = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
$pagitmcnt = 50;
if($range=='un'){
  $sql = 'select count(*)
          from viewlogs vl
          where vl.remoteip not in (select ipaddress from ipcrossref)
          group by vl.remoteip
          having count(vl.remoteip) > 9 ';
}else{
  $sql = 'select count(*)
          from ipcrossref
          where iplocation between \''.$range.'!!!\' and \''.$range.'zzzzz\' ';
}
$row = rs($sql);
if($row) $rsss= $row[0];
else $rsss = 0;
$pagtot = ceil($rsss/$pagitmcnt);
if($pagnum>1 && $pagnum>$pagtot) $pagnum=$pagtot;
$limit  = 'limit '.(($pagnum-1)*$pagitmcnt).', '.$pagitmcnt.' ';

$sql = 'select count(*) from ipcrossref ';
$row = rs($sql);
$totcr = $row[0];

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<form name="frm" method="post" action="/">
  <table class="gridtable">
    <tr><td colspan="9"><small>System cleanup in <span style="color:red;"><?=(50-(int)$diffminutes)?> min</span>. at <?=$nextcleaning?> &nbsp;|&nbsp; Server TZ: <?=$servertimezone?> &nbsp;|&nbsp; Tot Xrefs: <?=$totcr?></small></td></tr>
    <tr><td colspan="9">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr>
      <td colspan="6">
        <input type="reset" name="btnresetx" value="Reset">
        <input type="submit" name="btnsubmitx" value="Submit" onclick="return validate(document.frm);">&nbsp;
        <small>Sort </small><select name="sort" id="sort" onchange="document.frm.submit();">
          <option value="1"<?=fixsel($sort, 1)?>>IP</option>
          <option value="2"<?=fixsel($sort, 2)?>>Loc</option>
          <option value="3"<?=fixsel($sort, 3)?>>Visits</option>
          <option value="4"<?=fixsel($sort, 4)?>>Last</option>
        </select>
        <input type="radio" name="range" value="!"<?=fixrad($range==='!')?> onclick="document.frm.submit();"><small>!&nbsp;</small>
        <input type="radio" name="range" value="un"<?=fixrad($range==='un')?> onclick="document.frm.submit();"><small>unMpd&nbsp;</small>
        <input type="radio" name="range" value="("<?=fixrad($range==='(')?> onclick="document.frm.submit();"><small>(unkn)&nbsp;</small>
        <input type="checkbox" name="fillempty" value="1" /><small>map</small>
        <input type="checkbox" name="clearorphans" value="1" /><small>clr orphans<br /></small><br />
        <?
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i=0; $i<26; $i++) {
          $chr = substr($str, $i, 1);
          print('<input type="radio" name="range" value="'.strtolower($chr).'"'.fixrad($range===strtolower($chr)).' onclick="document.frm.pagnum.value=1;document.frm.submit();">'.$chr.'&nbsp;');
        }
        ?>
      </td>
    </tr>
    <tr>
      <td colspan="6"><?=pagination($pagnum, $pagitmcnt, $pagtot);?></td>
    </tr>
    <tr><td>cnt</td><td>IP Address</td><td>Location</td><td>Visits</td><td>Last</td><td><a onclick="chkall(document.frm)">Del</a></td></tr>
<?
$ni = 0;
if($range=='un'){
  $sql = 'select -1 id, vl.remoteip ipaddress, \'not mapped\' iplocation, count(vl.remoteip) cnt, (select max(viewtime) from viewlogs where remoteip = vl.remoteip) lst
          from viewlogs vl
          where vl.remoteip not in (select ipaddress from ipcrossref)
          group by vl.remoteip
          having count(vl.remoteip) > 9
          order by '.($sort+1).' '.(($sort>2)?'desc':'').' ';
}else{
  $sql = 'select id, ipaddress, iplocation ';
  $sql.=', (select count(*) from viewlogs where remoteip = ipaddress) cnt, (select max(viewtime) from viewlogs where remoteip = ipaddress) lst ';
  $sql.='from ipcrossref
         where iplocation between \''.$range.'!!!\' and \''.$range.'zzzzz\'
         order by '.($sort+1).' '.(($sort>2)?'desc':'').' ';
}
// these are expensive sql statements..
//print($sql);

$sql.=$limit;


$ips = dbquery($sql);
while($row = mysqli_fetch_array($ips)){
?>
    <tr>
      <td align="right"><?=$ni+1?></td>
      <td>
        <input type="hidden" name="mapipid<?=$ni?>" value="<?=$row['id']?>" />
        <input type="hidden" name="dirty<?=$ni?>" value="0" />
        <input type="text" name="mapip<?=$ni?>" value="<?=$row['ipaddress']?>" size="12" onchange="setdirt(<?=$ni?>)" />
        <a href="http://whatismyipaddress.com/ip/<?=$row['ipaddress']?>" target="_blank" title="look up" onclick="this.text='oo'"><img src="/i/magglass32.png" alt="" border="0" style="width:<?=((!$ismobile)?'1.0em':'.9em')?>"></a>
      </td>
      <td>
        <input type="text" name="mapiploc<?=$ni?>" value="<?=$row['iplocation']?>" size="23" onchange="setdirt(<?=$ni?>)" />
      </td>
      <td align="right"><?=$row['cnt']?></td>
      <td><?=rtrim(date('n/j/y ga', strtotime(converttouserdate2($row['lst'], $timezone))), 'm')?></td>
      <td><input type="checkbox" name="mapdelete<?=$ni?>" value="1" onclick="setdirt(<?=$ni?>)" /></td>
    </tr>
<?
  $ni++;
}
?>
    <tr>
      <td align="right"><?=$ni+1?></td>
      <td>
        <input type="hidden" name="mapipid<?=$ni?>" value="-1" />
        <input type="hidden" name="dirty<?=$ni?>" value="0" />
        <input type="text" name="mapip<?=$ni?>" value="" size="12" onchange="setdirt(<?=$ni?>)" />
        </td>
      <td>
        <input type="text" name="mapiploc<?=$ni?>" value="" size="23" onchange="setdirt(<?=$ni?>)" />
        </td>
      <td colspan="3"><input type="hidden" name="mapdelete<?=$ni?>" value="0" /></td>
    </tr>
    <tr>
      <td colspan="6">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);">
        <small> <?=(($ni+1)*4+33).' form fields';?></small>
      </td>
    </tr>
<?
  if($userid==1){
    print('<tr><td colspan="6"><a href="https://app.ipgeolocation.io/" target="_blank">rsw ipGeoLocation usage</a> <small>(rostwoods google)</small></td></tr>');
    //print('<tr><td colspan="6"><a href="https://ipstack.com/usage" target="_blank">rsw ipStack usage</a></td></tr>');
  }
?>
  </table>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="pagnum" value="<?=$pagnum?>" />
  <input type="hidden" name="oper" value="" />
</form>
<script>

  function setdirt(ni){
    document.frm['dirty'+ni].value = 1;
  }

  function chkall(f){
    var chk = (!f.mapdelete0.checked);
    for(var i=0;i<<?=$ni?>;i++){
      f['mapdelete'+i].checked = chk;
      f['dirty'+i].value = 1;
    }
  }

  function validate(f){
    var haveset=0;
    for(var i=0;i<<?=$ni?>;i++){
      if(f['mapdelete'+i].checked) haveset = 1;
    }
    if(haveset==1 && !confirm('Are you sure you want to remove the checked IP\'s?\n\nThis is not undoable')) return false;
    f.oper.value = 'sav';
    return true;
  }

  function dopage(pnum){
    document.frm.pagnum.value=pnum;
    document.frm.submit();
  }

</script>
<?
// function copied to /includes/pagebot.php
// do I need this one?
// maybe.
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
            echo "CURL FAIL: $url TIMEOUT=$timeout, CURL_ERRNO=$err"."<br /><br />";
            var_dump($inf);
            echo "<br /><br />";
        }
        return FALSE;
    }

    // ON SUCCESS
    return $htm;
}


  function pagination($pnum, $pitmcnt, $ptot){
    global $colors;
    $ret='<div style="text-align:center;margin:8px auto;font-size:90%;">';
    if($pnum-1 > 0){
      //$ret.= ' <a onclick="dopage(1);">first</a> ';
      $ret.= ' <a onclick="dopage('.($pnum-1).');">&laquo;prev</a> ';
    }else{
      $ret.= ' <span style="color:'.$colors[7].'">&laquo;prev</span> ';
    }
    $ret.= 'Page ';
    $ret.= '<select onchange="dopage(this.selectedIndex+1);">';
    for($ni=1;$ni<=$ptot;$ni++){
      $ret .= '<option'.fixsel($ni, $pnum).'>'.$ni.'</option>';
    }
    $ret.= '</select>';
    $ret.= ' of '.$ptot;
    if($pnum+1 <= $ptot){
      $ret.= ' <a onclick="dopage('.($pnum+1).');">next&raquo;</a> ';
      //$ret.= ' <a onclick="dopage('.$ptot.');">last</a> ';
    }else{
      $ret.= ' <span style="color:'.$colors[7].'">next&raquo;</span> ';
    }
    $ret.= '</div>';
    return $ret;
  }

?>

