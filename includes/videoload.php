<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

$resid = ((isset($_REQUEST['id']))?$_REQUEST['id']:0);
$resid = (int) $resid;
if($resid==0) die('no resid');

$sql = 'select identifier, thumbnail from resource where resourceid = '.$resid.' ';
$row = rs($sql);
if(!$row) die('invalid resid');
$identifier = $row[0];
$thumbnail = $row[1];

?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <script>
  var prfcolortheme=0;
  var resid = <?=$resid?>;
  var identifier = '<?=$identifier?>';
  var resinitsiz = ((window.innerWidth<520)?160:210);

  function process(){
    var resinitsiz = window.innerWidth;
    if(resinitsiz<211) parent.sizres(resid, identifier);
  }
  </script>
</head>
<body style="margin:0;padding:0;">
  <img src="<?=$thumbnail?>" alt="" border=0 style="width:100%">
  <div style="position:fixed;z-index:999;padding:0;margin:0;top:0;left:0;right:0;bottom:0;" onclick="process();"><img src="/i/videoplay2.png" style="cursor:pointer;width:50px;margin:0;position:absolute;top:50%;left:50%;transform: translate(-50%, -50%);opacity:0.7;" /></div>
</body>
</html>
