<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

$stitle = 'MyREV Tutorial';

$widthcut = 900;

if($screenwidth>=$widthcut){
  $numpix = 13;
  $filmod = '';
}else{
  $numpix = 15;
  $filmod = 'mobile';
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>MyREV Tutorial</title>
  <link rel="stylesheet" type="text/css" href="/includes/style<?=$colors[0]?>.css?v='.$fileversion.'" />
  <style>
    #pix{
      max-width: 90%;
      margin:0 auto;
      text-align:center;
    }
    .tutpic{
      max-width: 100%;
      border:1px solid <?=$colors[3]?>;
    }
  </style>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;width:99%;">

<div style="text-align:center;padding:7px 0;">
<h3 style="display:inline-block;width:70%;text-align:center;margin:0;"><?=$stitle?></h3>
<span style="float:right;cursor:pointer;" onclick="olClose('');"><img src="/i/redx.png" style="width:20px;" alt="" /></span>
</div>
<?if($screenwidth>=$widthcut){?>
    <div style="float:left;height:100%;min-width:30px;cursor:pointer;" onclick="changepic(-1)">
      <img src="/i/mnu_prev<?=$colors[0]?>.png" alt="" style="position:absolute;padding:50px 0;width:1.8em;top:40%;" />
    </div>
    <div style="float:right;height:100%;min-width:30px;cursor:pointer;" onclick="changepic(1)">
      <img src="/i/mnu_next<?=$colors[0]?>.png" alt="" style="position:absolute;padding:50px 0;width:1.8em;top:40%;" />
    </div>
    <div id="pix">
      <img id="tutpic" class="tutpic" src="/i/myrevtutorial/tutorial1<?=$filmod?>.png" alt="tutorial">
    </div>
  <div style="position:absolute;bottom:0;width:100%;text-align:center;margin:9px auto;">
    <?=drawdots($numpix)?>
    <input type="button" name="btnclos" value="Done" class="gobackbutton" style="cursor:pointer;width:80px;margin-top:8px;" onclick="olClose('');">
  </div>
<?}else{?>
  <div id="pix">
    <img id="tutpic" class="tutpic" src="/i/myrevtutorial/tutorial1<?=$filmod?>.png" alt="tutorial">
  </div>
  <div style="position:absolute;bottom:0;width:100%;text-align:center;margin:9px auto;">
    <?=drawdots($numpix)?>
    <input type="image" src="/i/mnu_prev<?=$colors[0]?>.png" alt="" style="width:2em;padding-right:14px;margin-bottom:-11px;" onclick="changepic(-1)" />
    <input type="button" name="btnclos" value="Done" class="gobackbutton" style="cursor:pointer;width:80px;margin-top:8px;" onclick="olClose('');">
    <input type="image" src="/i/mnu_next<?=$colors[0]?>.png" alt="" style="width:2em;padding-left:14px;margin-bottom:-11px;" onclick="changepic(1)" />
  </div>
<?}?>

<script>
  function $(el) {return parent.document.getElementById(el);}
  function $$(el){return document.getElementById(el);}

  function olClose(locn) {
    var msg = '';
    if(msg) {if(confirm(msg)) return;}
    parent.rlbfadeout();
  }

  var numpix=<?=$numpix?>;
  var filmod='<?=$filmod?>';
  var picidx = 1;

  function changepic(idx){
    picidx+= idx;
    if(picidx==0) picidx = numpix;
    if(picidx==(numpix+1)) picidx = 1;
    $$('tutpic').src = '/i/myrevtutorial/tutorial'+picidx+filmod+'.png';
    for(ni=1;ni<=numpix;ni++){
      $$('rad'+ni).disabled= ((ni!=picidx)?true:false);
      $$('rad'+ni).checked = ((ni==picidx)?true:false);
    }
    var vdim = window.innerHeight;
    $$('tutpic').style.maxHeight = (vdim-130)+'px';
  }

  function resizeme(){
    var vdim = window.innerHeight;
    $$('pix').style.height = (vdim-50)+'px';
    $$('tutpic').style.maxHeight = (vdim-130)+'px';
  }
  window.onload=function(){resizeme();}
  window.onresize=function(){resizeme();}

</script>

</body>
</html>
<?
function drawdots($num){
  $ret='';
  for($ni=1;$ni<=$num;$ni++){
    $ret.= (($ni>1)?'&nbsp;':'').'<input type="radio" name="rad'.$ni.'" id="rad'.$ni.'" value="1"'.(($ni==1)?' checked':' disabled').' />';
  }
  return $ret.'<br />';
}
