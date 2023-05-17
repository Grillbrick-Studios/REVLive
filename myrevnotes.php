<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($myrevid) || $myrevid==0) die('unauthorized access');

$loc  = ((isset($_REQUEST['loc']))?$_REQUEST['loc']:'18|1|40|1|1'); // default to Rob, Mat 1:1
$ar   = explode('|', $loc);
$test = (int) ((isset($ar[1]))?$ar[1]:1);
$book = (int) ((isset($ar[2]))?$ar[2]:40);
$chap = (int) ((isset($ar[3]))?$ar[3]:1);
$vers = (int) ((isset($ar[4]))?$ar[4]:1);
$isvers = (($chap>0&&$vers>0)?1:0);
$viewmode  = ((isset($_REQUEST['viewmode']))?$_REQUEST['viewmode']:'view');

$timessubmitted=((isset($_REQUEST['timessubmitted']))?$_REQUEST['timessubmitted']:0);
if($timessubmitted==0 && $myrevshoweditorfirst==1) $viewmode='edit';

//print('test>'.$test.'&lt;<br />');
//print('book>'.$book.'&lt;<br />');
//print('chap>'.$chap.'&lt;<br />');
//print('vers>'.$vers.'&lt;<br />');

$sqlerr = '';
$oper=((isset($_REQUEST['oper']))?$_REQUEST['oper']:'xx');
if($isvers){
  if($oper=='sav' || $oper=='del'){
    $row = rs('select marginnote
              from myrevdata
              where myrevid = '.$myrevid.'
              and testament = '.$test.'
              and book = '.$book.'
              and chapter = '.$chap.'
              and verse = '.$vers.' ');
    //if($row) $oldmarginnote = '\''.$row[0].'\'';
    if($row) $oldmarginnote = $row[0];
    else $oldmarginnote = '';
    $mynotes = $_POST['myrevnotes'];
    $marginnote = ((isset($_POST['marginnote']))?$_POST['marginnote']:'');
    if(strlen($mynotes.'')>60000) $mynotes = truncateHtml($mynotes, 60000);
    $mynotes = processlocalsqlcomm($mynotes, 1, '');
    $marginnote = processlocalsqlcomm($marginnote, 1, '');
    if(strlen($marginnote)>480) $marginnote = substr($marginnote, 0, 480).'\'';
    $sql = 'select highlight
            from myrevdata
            where myrevid = '.$myrevid.'
            and testament = '.$test.'
            and book = '.$book.'
            and chapter = '.$chap.'
            and verse = '.$vers.' ';
    $row = rs($sql);
    if($oper=='del' || $row){
      if($oper=='del' || ($row[0]==0 && $mynotes=='null' && $marginnote=='null'))
        $sql = 'delete from myrevdata
                where myrevid = '.$myrevid.'
                and testament = '.$test.'
                and book = '.$book.'
                and chapter = '.$chap.'
                and verse = '.$vers.' ';
      else{
        $sql = 'update myrevdata set
                lastupdate = UTC_TIMESTAMP(),
                myrevnotes = '.$mynotes.',
                marginnote = '.$marginnote.'
                where myrevid = '.$myrevid.'
                and testament = '.$test.'
                and book = '.$book.'
                and chapter = '.$chap.'
                and verse = '.$vers.' ';
        logview(301,$test,$book,$chap,$vers);
      }
    }else{
      if($mynotes != 'null' || $marginnote != 'null'){
        $sql = 'insert into myrevdata (myrevid, testament, book, chapter, verse, lastupdate, marginnote, myrevnotes) values ('.
                $myrevid.', '.$test.', '.$book.', '.$chap.', '.$vers.', UTC_TIMESTAMP, '.$marginnote.','.$mynotes.') ';
        logview(301,$test,$book,$chap,$vers);
      }else $sqlerr = 'nothing to save';
    }
    //print($sql);
    //die();
    $update = dbquery($sql);
    if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
    $timessubmitted += 1;
  }
}else{
  if($oper=='sav' || $oper=='del'){
    $mynotes = $_POST['myrevnotes'];
    if(strlen($mynotes.'')>60000) $mynotes = truncateHtml($mynotes, 60000);
    $mynotes = (($oper=='del')?'null':processlocalsqlcomm($mynotes, 1, ''));
    $sql = 'update myrevusers set
            notes = '.$mynotes.'
            where myrevid = '.$myrevid.' ';
    $qry = dbquery($sql);
    if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
    $timessubmitted += 1;
    logview(301,$test,$book,$chap,$vers);
  }
}
if($sqlerr=='') $sqlerr = '&nbsp;';

if($isvers){
  $btitle = getbooktitle($test,$book, (($screenwidth>=480)?0:1));
  $stitle = $myrevname.'&rsquo;s Notes on '.$btitle.(($test<2)?' '.$chap.':'.$vers:'');
  $sql = 'select ifnull(rd.myrevnotes, \'\'), ifnull(rd.highlight, 0) highlight, ifnull(rd.marginnote, \'\') marginnote, v.testament, if(v.versetext=\'-\', v.commentary, v.versetext) versetext
          from verse v
          left join myrevdata rd on rd.myrevid = '.$myrevid.' and v.testament = rd.testament and v.book = rd.book and v.chapter = rd.chapter and v.verse = rd.verse
          where v.testament = '.$test.'
          and v.book = '.$book.'
          and v.chapter = '.$chap.'
          and v.verse = '.$vers.' ';
  $row = rs($sql);
  $mynotes = $row[0];
  $hlite = $row[1];
  $marginnote=$row[2];
  $tst   = $row[3];
  $verse = $row[4];
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
  $verse = str_replace('~', '', $verse);
  if($tst>1 && strlen($verse) > 500) $verse = truncateHtml($verse, 400);
}else{
  $stitle = $myrevname.'&rsquo;s Workspace';
  $sql = 'select notes from myrevusers where myrevid = '.$myrevid.' ';
  $row = rs($sql);
  $mynotes = $row[0];
  $hlite = 0;
  $verse = '';
}
//print($sql);
if($oper=='del'){
  print('<!DOCTYPE html>'.crlf.'<script>');
  if($isvers){?>
    try{$('hl_<?=$loc?>').style.backgroundColor='transparent';}catch(e){}
    try{
      var spans = parent.document.getElementsByClassName('hl_<?=$loc?>');
      for(var i=0; i<spans.length; i++) {
        spans[i].style.backgroundColor = 'transparent';
        spans[i].setAttribute('data-hlite', 0);
      }
    }catch(e){}
  <?}?>
     try{
       var qry = '<?=$loc?>';
       parent.reloadmyrevnotes(qry);
     }catch(e){parent.document.location.reload()}
   parent.rlbfadeout();
   </script>
<?
  exit();
}


?>
<!DOCTYPE html>
<html>
<head>
  <title>MyREV Note Editor</title>
  <link rel="stylesheet" type="text/css" href="/includes/style.min.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
      print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
  <script>
<?
  print('var myrevmode = \''.$myrevmode.'\';'.crlf);
  print('var myrevsort = \''.$myrevsort.'\';'.crlf);
  print('var myrevpagsiz = '.$myrevpagsiz.';'.crlf);
  print('var myrevshownotes = '.$myrevshownotes.';'.crlf);
  print('var myrevclick = '.$myrevclick.';'.crlf);
  print('var myrevshowkey = \''.$myrevshowkey.'\';'.crlf);
  print('var myrevshoweditorfirst = \''.$myrevshoweditorfirst.'\';'.crlf);
  print('var myrevsid = \''.$myrevsid.'\';'.crlf);
  print('var prfcommnewtab = '.(($inapp==1)?0:1).';'.crlf);
  print('var ismobile = \''.$ismobile.'\';'.crlf);

  print('var colors = Array(');
  for($i=0;$i<sizeof($colors);$i++){
    print('\''.$colors[$i].'\'');
    if($i<(sizeof($colors)-1)) print(',');
  }
  print(');'.crlf);

  print('var hlcolors = Array(');
  for($i=0;$i<sizeof($hilitecolors);$i++){
    print('\''.$hilitecolors[$i].'\'');
    if($i<(sizeof($hilitecolors)-1)) print(',');
  }
  print(');'.crlf);

  print('var myrevkeys = Array(');
  for($i=0;$i<sizeof($myrevkeys);$i++){
    print('\''.$myrevkeys[$i].'\'');
    if($i<(sizeof($myrevkeys)-1)) print(',');
  }
  print(');'.crlf);
?>
    var cookieexpiredays = 180;
    var prffontsize   = <?=$fontsize?>;
    var prflineheight = <?=$lineheight?>;
    var prffontfamily ='<?=$fontfamily?>';
  </script>
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/myrevjs.js?v=<?=$fileversion?>"></script>
</head>
<body style="font-family:'<?=$fontfamily?>', 'times new roman', serif;font-size:<?=$fontsize?>em;line-height:1em;background-color:<?=$colors[2]?>;opacity:0;transition:opacity .2s;">

<!--wrapper-->
<div id="wrapper" style="overflow:hidden; height:100%; width:100%;">
<form name="frm" method="post" action="/myrevnotes.php" style="padding:0;margin:0;">

<!--header-->
<div id="myheader" style="position:absolute;top:0;left:0;right:0;text-align:center;padding:7px 0;">
<h3 style="display:inline-block;width:70%;text-align:center;margin:0;"><?=$stitle?></h3>
<span style="display:inline-block;float:right;cursor:pointer;" onclick="olClose('<?=$viewmode?>');"><img src="/i/redx.png" style="width:20px;" alt="" /></span>
<?
  if($isvers){
    if($viewmode=='view' && $timessubmitted>0 && $mynotes==''){
      $mynotes = '<p style="color:red;">You have no notes for this verse. Click &ldquo;Edit&rdquo; to add a note.</p>';
    }else if(($myrevshoweditorfirst==1 && $timessubmitted==0) || ($viewmode=='view' && $timessubmitted==0 && $mynotes==''))
      $viewmode = 'edit';
    print('<div id="pverse" style="margin:8px 0 0 0;font-size:90%;text-align:left;"><span id="hl_'.$loc.'" onclick="showhilightdiv(\''.$loc.'\', hlite);" style="'.(($test>1)?'display:inline-block;':'').'background-color:'.$hilitecolors[$hlite].';transition:.4s;cursor:pointer;" title="'.$myrevkeys[$hlite].'">'.$verse.'</span> ');
    if($test<2) print('<a onclick="parent.location.href=\'/'.str_replace(' ', '-', $btitle).'/'.$chap.'/nav'.$vers.'\'" class="comlink0"><img src="/i/bible_icon'.$colors[0].'.png" style="width:1.1em;margin-bottom:-3px;" alt="Bible" title="back to Bible" /></a> ');
    if(!$inapp && $test<2)
      print(getothertranslationlink($btitle, $chap, $vers, 0));
    print('</div>');
    if($viewmode=='view') print('<hr style="background-color:'.$colors[0].';color:'.$colors[0].';height:1px;margin:5px;">');
  }else{
    print('<p id="pverse" style="margin:8px 0 0 0;font-size:90%;text-align:left;font-style:italic;">Your Workspace is not associated with a Bible verse. It is accessible from anywhere.</p>');
    if($myrevshoweditorfirst==0 && $viewmode=='view' && $timessubmitted > 0 && $mynotes==''){
      $mynotes = '<p style="color:red;">Your Workspace is empty. Click &ldquo;Edit&rdquo; to add a note.</p>';
    }else if($viewmode=='view' && $timessubmitted==0 && $mynotes=='')
      $viewmode = 'edit';
    if($viewmode=='view') print('<hr style="background-color:'.$colors[0].';color:'.$colors[0].';height:1px;margin:5px;">');
  }
?>
</div><!--end header-->

<!--body-->
<div id="mynotes" style="position:absolute;overflow-y:auto;left:0;right:0;top:50px;bottom:50px;margin-top:15px;padding:0;font-size:100%;line-height:<?=$lineheight?>em;">
<?if($viewmode=='edit'){
    print(printsqlerr($sqlerr));
    if($isvers && $test<2) print('<br /><small>Margin note:</small><div style="border-bottom:1px solid '.$colors[7].';"><textarea name="marginnote" id="marginnote">'.$marginnote.'</textarea></div>');
    print('<small>Details:</small><textarea name="myrevnotes" id="myrevnotes" style="width:98%;height:500px;">'.$mynotes.'</textarea>');
  }else{
    if($isvers && $test<2 && $marginnote!='') print('<div style="margin-top:4px;line-height:1.1em;"><small>Margin note:</small> <span class="marginnote" style="display:inline-block;margin-left:0;">'.$marginnote.'</span></div>');
    print($mynotes.'<input type="hidden" name="myrevnotes" value="" />');  // for delete
    logview(303,$test,$book,$chap,$vers);
  }
?>

</div><!--end body-->

<!--footer-->
<div style="position:absolute;bottom:0;left:0;right:0;height:50px;">

  <p style="text-align:center;margin:9px 0;">
  <?
    //print('vm: '.$viewmode.'<br />');
    if($viewmode=='edit'){
      print('<input type="submit" name="btnview" class="gobackbutton" style="cursor:pointer;width:80px;" value="View" onclick="return changeview(document.frm, \'view\');" /> ');
      print('<input type="submit" name="btnsubmit" class="gobackbutton" style="cursor:pointer;width:80px;" value="Save" onclick="return validate(document.frm);" /> ');
    }else
      print('<input type="submit" name="btnsubmit" class="gobackbutton" style="cursor:pointer;width:80px;" value="Edit" onclick="return changeview(document.frm, \'edit\');" /> ');
    print('<input type="button" name="btnclosee" class="gobackbutton" style="cursor:pointer;width:80px;" value="Close" onclick="olClose(\''.$viewmode.'\');">&nbsp;&nbsp;');

    if($screenwidth>=480 && !$inapp){
      print('&nbsp;&nbsp;
             <a onclick="return valexport(document.frm, \'msw\',\''.$loc.'\')" title="Export to MSW"><img src="/i/myrevmsw'.$colors[0].'.png" style="width:1.8em;margin-bottom:-10px;" alt="MSW" /></a>&nbsp;
             <a onclick="return valexport(document.frm, \'pdf\',\''.$loc.'\')" title="Export as PDF"><img src="/i/myrevpdf'.$colors[0].'.png" style="width:1.8em;margin-bottom:-12px;" alt="PDF" /></a>&nbsp;');
    }
    print('<a onclick="return valdel(document.frm)" title="Delete"><img src="/i/myrev_trash'.$colors[0].'.png" style="width:1.7em;margin-bottom:-8px;" alt="Delete" /></a>');
?>
</p>


</div><!--end footer-->

  <input type="hidden" name="dirt" value="0" />
  <input type="hidden" name="loc" value="<?=$loc?>" />
  <input type="hidden" name="timessubmitted" value="<?=$timessubmitted?>">
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="viewmode" value="<?=$viewmode?>" />
</form>
</div><!--end wrapper-->


  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
  <script>

     var myrevid = <?=$myrevid?>;
     var isvers = <?=$isvers?>;
     var screenwidth = <?=$screenwidth?>;
     var testament = <?=$test?>;
     var loc = '<?=$loc?>';
     var timessubmitted = <?=$timessubmitted?>;
     function $(el) {return parent.document.getElementById(el);}
     function $$(el){return document.getElementById(el);}

     function addLoadEvent(func) {
       //https://gist.github.com/dciccale/4087856
       var b=document,c='addEventListener';
       b[c]?b[c]('DOMContentLoaded',func):window.attachEvent('onload',func);
     }

     function olClose(mode) {
       if(mode===undefined) mode='edit';
       if(mode=='edit'){
         var msg = checkdirt('clos');
         if(msg) {if(!confirm(msg)) return;}
       }
       try{parent.goback+=timessubmitted;}catch(e){}; // does not work on viewbible
       if(isvers==1 && timessubmitted>0){
         try{
           var qry = loc;
           parent.reloadmyrevnotes(qry);
         }catch(e){parent.document.location.reload()}
       }
       $$('lmyrevdiv').style.opacity=0;
       $$('lmyrevdiv').style.display='none';
       parent.rlbfadeout();
     }

     var checkforchanges = true;

     function valdel(f){
<?if($isvers){?>
       if(confirm('\nThis will completely remove this verse from your list of verses,\nthe highlighting color, the margin note, and your note.\nIt cannot be undone.\n\nAre you sure you want to do this?')){
<?}else{?>
       if(confirm('\nThis will clear your workspace.\nIt cannot be undone.\n\nAre you sure you want to do this?')){
<?}?>
         deleteall(CKEDITOR.instances.myrevnotes);
         f.oper.value = "del";
         f.submit();
         return true;
       }else return false;
     }

     function valexport(f, expto, dat){
       var msg = checkdirt('expt');
       if(msg) {if(!confirm(msg)) return;}
       if(expto=='msw')
         var href = '/docx_phpdocx.php?what=myrev&dat='+dat;
       else{ // pdf
         var href = '/pdf.php?what=myrev&dat='+dat;
       }
       location.href=href;
       return false;
     }

     function validate(f){
       checkforchanges = false;
       f.oper.value = "sav";
       return true;
     }
     function changeview(f,mode){
       if(mode=='view'){
         var msg = checkdirt('clos');
         if(msg) {if(!confirm(msg)) return false;}
       }
       f.timessubmitted.value = parseInt(f.timessubmitted.value) + 1;
       f.viewmode.value = mode;
       return true;
     }

     function checkdirt(what){
       if(!checkforchanges) return;
       var f = document.frm;
       var dirty = 0;
       if(f.dirt.value==1) dirty = 1;
       for (var i in CKEDITOR.instances) {
         if(CKEDITOR.instances[i].checkDirty()) dirty = 1;
       }
       if(what=='clos')
         msg = 'You have unsaved changes.\nIf you click \'OK\' those changes will be LOST.\n\nIf you WANT TO SAVE your notes, click \'Cancel\', then the \'Save\' button.\n';
       else
         msg = 'You have unsaved changes.\nIf you click \'OK\' those changes will not be printed.\n\nIf you WANT TO SAVE your notes first, click \'Cancel\', then the \'Save\' button.\n';
       if(dirty==1)return msg;
       else return '';
     }

     function setdirt(){
       var f = document.frm;
       f.dirt.value = 1;
       try{parent.sopschanges=1;}
       catch(e){}
     }

     function resizeditor(){
       ydim = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
       ydim-= $$('pverse').clientHeight+((isvers==1&&testament<2)?150:20);
       ydim = Math.max(ydim, 400);
       xdim = $$('pverse').clientWidth;
       $$('mynotes').style.top = ($$('myheader').clientHeight-20) + 'px';
<?if($viewmode=='edit'){?>
       try{
         CKEDITOR.instances.myrevnotes.resize( (xdim), (ydim-140));
       }catch(e){setTimeout('resizeditor()', 100)}
<?}?>
      document.body.style.opacity = 1;
     }
     window.onload=setTimeout('resizeditor()', 100);
     window.onresize=function(){resizeditor();};

<?if($viewmode=='edit'){
    if(!$isvers==0 && $test<2){?>

    CKEDITOR.replace( 'marginnote',
    {
      extraPlugins: 'colorbutton,button,panelbutton,panel,floatpanel,autocorrect',
      toolbar :
      [
        { name: 'basicstyles', items : [<?=(($userid==1)?'\'Source\',\'-\', ':'')?> 'AutoCorrect','-','RemoveFormat','Bold','Italic','Underline','Strike','-','TextColor','BGColor' ] },
      ],
      removePlugins : 'elementspath',
      resize_enabled : false,
      height : '52',
      enterMode : CKEDITOR.ENTER_BR
    }
    );
    CKEDITOR.instances.marginnote.on('change', function(){setdirt();});

  <?}?>

    CKEDITOR.replace( 'myrevnotes',
    {
        forcePasteAsPlainText: 'allow-word',
        entities_greek: false,
        toolbarCanCollapse: false,
        extraPlugins: 'colorbutton,button,panelbutton,panel,floatpanel,autocorrect,smallcapify',
        toolbar :
        [
  <?if($screenwidth>=480){?>
          { name: 'document',    items : [ <?=(($userid==1)?'\'Source\',\'-\', ':'')?>'AutoCorrect'] },
          { name: 'clipboard',   items : [ 'Undo','Redo' ] },
  <?}?>
          { name: 'tools',       items : [ 'Maximize','Symbol' ] },
          { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','smallcapify','-','TextColor','BGColor','RemoveFormat' ] },
          { name: 'paragraph',   items : [ 'JustifyLeft','JustifyCenter' ] },
          { name: 'lists',       items : [ 'NumberedList','BulletedList','Outdent','Indent' ] },
  <?if($screenwidth>=480){?>
          { name: 'styles',      items : [ 'Format'] },
          { name: 'links',       items : [ 'Image','Link','Unlink' ] },
  <?}?>
        ],
        height : '340'
    }
    );
    CKEDITOR.instances.myrevnotes.on('change', function(){setdirt();});
<?}?>

    function endfocus(ckei){
      var range = ckei.createRange();
      range.moveToElementEditEnd(range.root);
      ckei.getSelection().selectRanges([range]);
      ckei.focus();
    }
    function deleteall(ckei){
<?if($viewmode=='edit'){?>
      ckei.setData('', function(){ckei.resetDirty();});
<?}?>
    }
    function dolocalhilight(qry, idx){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          if(isvers==1){
            var ret = JSON.parse(xmlhttp.responseText);
            var rcol = ret.color;
            cspan =  $$('hl_'+qry);
            cspan.style.backgroundColor = rcol;
            cspan.title = myrevkeys[idx];
            hlite = idx;
            // parent pages
            try{
              var spans = parent.document.getElementsByClassName(ret.spanclass);
              for(var i=0; i<spans.length; i++) {
                spans[i].style.backgroundColor = ret.color;
                spans[i].setAttribute('data-hlite', idx);
                spans[i].title = myrevkeys[idx];
                if(chngtxt==1)
                  spans[i].innerHTML=myrevkeys[idx]+'&nbsp;';
              }
            }catch(e){}
            hidelocalmyrevdiv();
          }
        }
      }
      xmlhttp.open('GET','/jsonmyrevtasks.php?task=hlit&ref='+qry+'|'+idx,true);
      xmlhttp.send();
    }

    var ismobile = <?=$ismobile?>, cursorX, cursorY, hlite=<?=$hlite?>;
    var chngtxt = ((parent.chngtxt==1)?1:0);

    function hidelocalmyrevdiv(){
      $$('lmyrevdiv').style.opacity = 0;
      setTimeout('$$(\'lmyrevdiv\').style.display = \'none\'', 400);
    }

    function showhilightdiv(qry, hlit){
      event.stopPropagation();
      var rdiv = $$('lmyrevdiv');
      if(rdiv.style.display=='block'){
        rdiv.style.opacity = 0;
        setTimeout('$$(\'lmyrevdiv\').style.display = \'none\'', 400);
        return;
      }
      var htm = '<div id="pallete">';
      if(myrevshowkey==1)
        htm += handlecolors(qry, hlit, 1, chngtxt);
      else
        htm += handlecolorsnocap(qry, hlit, 1, chngtxt);
      htm+= '</div>';
      htm+= '<span  style="display:inline-block;float:right;"><a onclick="event.stopPropagation();changepallete(\''+qry+'\','+hlit+',1,0)" title="legend" class="comlink0" style="cursor:pointer;color:'+colors[7]+';"><span id="legend">'+((myrevshowkey==1)?'&laquo;':'&raquo;')+'</span></a></span>';

      rdiv.innerHTML=htm;
      rdiv.style.visibility='hidden';
      rdiv.style.zIndex=100;
      rdiv.style.display='block';
      var dims = gethilightdivcoords(rdiv);
      rdiv.style.top  = dims.top+'px';
      rdiv.style.left = dims.left+'px';
      rdiv.style.visibility='visible';
      rdiv.style.opacity=1;
      hlite=hlit;
    }
    function getCursorX(e) {
      var ret;
      e = e || window.event;
      if (e.pageX || e.pageY) {
        ret = e.pageX;
        cursorY = e.pageY;
      }else{
        ret = e.clientX +
              (document.documentElement.scrollLeft ||
              document.body.scrollLeft) -
              document.documentElement.clientLeft;
      }
      return ret;
    }
    document.onmousemove = function(e){
      cursorX = getCursorX(e);
    }


<?if($oper=='del'){
    if($isvers){?>
    // for parent pages
    try{$('hl_<?=$loc?>').style.backgroundColor='transparent';}catch(e){}
    // for viewbible
    try{
      var spans = parent.document.getElementsByClassName('hl_<?=$loc?>');
      for(var i=0; i<spans.length; i++) {
        spans[i].style.backgroundColor = 'transparent';
        spans[i].setAttribute('data-hlite', 0);
      }
    }catch(e){}

  <?}?>
    parent.location.reload();
<?}else{?>
    setTimeout('try{endfocus(CKEDITOR.instances.myrevnotes)}catch(e){}', 300);
<?}?>

function addclicktoview(){
  try{if($$('lmyrevdiv').style.display=='block') hidelocalmyrevdiv();}catch(e){};
}
function initclicktoview(){
  document.body.addEventListener('click', addclicktoview);
}
addLoadEvent(initclicktoview);

  function updatesops(){
    try{parent.extendfrompopup()}catch(e){};
  }
  addLoadEvent(updatesops);


//alert(timessubmitted);

</script>

<?if($viewmode=='view'){?>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findmycomm.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findstrongs.min.js?v=<?=$fileversion?>"></script>
<?if($revws==1){?>
<script src="/includes/findwordstudy.min.js?v=<?=$fileversion?>"></script>
<?}?>
<script>
  findmycomm.enablePopups = true;
  findmycomm.remoteURL    = '<?=$jsonurl?>';
  findmycomm.startNodeId  = 'mynotes';
  findmycomm.mrlightbox   = 0;
  addLoadEvent(findmycomm.scan);

  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId  = 'mynotes';
  addLoadEvent(findcomm.scan);

  findbcom.startNodeId  = 'mynotes';
  addLoadEvent(findbcom.scan);

  findappx.startNodeId = 'mynotes';
  findappx.apxidx = [<?=loadapxids()?>];
  addLoadEvent(findappx.scan);

  findvers.startNodeId = 'mynotes';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  addLoadEvent(findvers.scan);

  var prflexicon    = <?=$lexicon?>;
  findstrongs.startNodeId = 'mynotes';
  findstrongs.ignoreTags.push('noparse');
  findstrongs.lexicon = prflexicon;
  addLoadEvent(findstrongs.scan);

<?if($revws==1){?>
  findwordstudy.startNodeId = 'mynotes';
  addLoadEvent(findwordstudy.scan);
<?}?>

// this function overrides the one in misc.js ..hopefully
function popgetPosition(node) {
  return {left:cursorX, top:cursorY-10};
}
</script>
<?}?>

<div id="lmyrevdiv" style="display:none;position:absolute;opacity:0;transition:.4s;white-space:nowrap;z-index:89;background-color:<?=$colors[2]?>;padding:6px 4px 2px 4px;border:1px solid <?=$colors[1]?>;border-radius:4px;box-shadow: 0 3px 5px rgba(50,50,50,0.5);"></div>
</body>
</html>
<?
function processlocalsqlcomm($com, $allownull, $default){
  global $site;
  $ret = trim($com);
  if($ret){
    $ret = preg_replace('#(\r\n)+#', ' ', $ret);    // replace crlf
    $ret = preg_replace('#<span dir="rtl"(.*?)>#i', '<span dir="rtl">', $ret);     // remove style from spandir tags
    $ret = preg_replace('#<span lan(.*?)>#', '<span>', $ret);     // remove language from span tags
    //$ret = preg_replace('#<span sty(.*?)>#', '<span>', $ret);     // remove style from span tags
    while(strpos($ret, '<span>')!== false){
      $ret = preg_replace('#<span>(.*?)</span>#', '$1', $ret);      // remove empty span tags
    }
    $ret = preg_replace('#<div sty(.*?)>#', '<div>', $ret);       // remove style from div tags
    $ret = preg_replace('#<div>(.*)</div>#', '$1', $ret);         // remove empty div tags
    $ret = preg_replace('#<meta(.*?)>#', '', $ret);               // remove meta tags
    $ret = preg_replace('#<blockquote sty(.*?)>#', '<blockquote>', $ret); // remove style from blockquote tags
    $ret = preg_replace('#<ul sty(.*?)>#', '<ul>', $ret);         // remove style from ul tags
    $ret = preg_replace('#<ol sty(.*?)>#', '<ol>', $ret);         // remove style from ol tags
    $ret = preg_replace('#<li sty(.*?)>#', '<li>', $ret);         // remove style from li tags
    $ret = preg_replace('#<strong sty(.*?)>#', '<strong>', $ret); // remove style from strong tags
    $ret = preg_replace('#<em sty(.*?)>#', '<em>', $ret);         // remove style from em tags
    $ret = preg_replace('#<p style="fo(.*?)>#', '<p>', $ret);     // remove font style from p tags
    // 20151118 the next statement was added to handle pastes of indented text from Word.
    $ret = preg_replace('#<p style="margin-left(.*?)>(.*?)</p>#', '<blockquote> <p>$2</p> </blockquote>', $ret);
    $ret = preg_replace('#<p style="mar(.*?)>#', '<p>', $ret);    // remove remaining margin styles from p tags

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


