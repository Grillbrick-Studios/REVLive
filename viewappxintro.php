<?php
if(!isset($page)) die('unauthorized access');

//
// This is page 14, used for viewing appendices, infos, and wordstudies
//
$stitle = 'REV '.(($test==-1)?'Menu':(($test==2)?'Information':(($test==3)?'Appendices':'Word Studies')));
$back=(($showback==1)?'<button onclick="history.go(-goback);" class="gobackbutton">Go Back</button>':(($glogid>0||$showclosetab==1)?'<button onclick="window.close();" class="gobackbutton">Close Tab</button>':''));
$msg='';

if($test==4 && $revws==0){
  if($userid>0){
    $msg='<br /><small><span style="color:red">This is not live. Only logged in users can see it.</span></small><br />';
  }else{
    $msg='<br /><small><span style="color:red">Sorry, the Word Study section of the REV website is not yet live.</span></small><br />';
    $test=-1;
  }
}
$hlite=0;
$myrevnotes='-';
$loc='';
$which='';
$haveedwork = 0;
$havepeerwork = 0;
$canaddpeernote = 0;

$showedit = (($edit==1)?'inline':'none');
if($test != -1){
  if($book != 0){
    $arcomfn = array();
    $comfncnt= 0;
    $sql = 'select active, title from book where testament = '.$test.' and book = '.$book.' ';
    $row = rs($sql);
    $active = $row[0];
    $title  = $row[1];
    $sql = 'select comfootnotes, commentary from verse where testament = '.$test.' and book = '.$book.' and chapter = 1 and verse = 1 ';
    $row = rs($sql);
    $comfootnotes= $row['comfootnotes'];
    $commentary = $row['commentary'];
    $commentary = (($commentary)?$commentary:'No Content!');
    $commentary = preg_replace('#<p><strong>([^<]*?)</strong><br />#', '<h5 style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:25px;">$1</h5><p style="margin-top:0;padding-top:0">', $commentary);
    // handle new commentary footnotes
    $comfootnotes = getfootnotes($test, $book, 1, 1, 'com');
    //

    $commentary = processcommfordisplay($commentary, 0);
    $commentary = processcomfootnotes($arcomfn, $commentary, $comfootnotes, $comfncnt, 1);
    $commentary = str_replace('~~docroot~~',$jsonurl, $commentary);
    $commentary = str_replace('[longdash]','<img src="/i/longdash.png" style="height:6px;width:60px;" />', $commentary);

    if($showpdflinks==1 || $back!=''){
      print('<div id="pagetop">');
      if($back!='') print($back);
      if($userid>0 && $appxedit==1) print(' '.editlink('elnk0',$showedit,$mitm,8,$test,$book,1,1).(($active)?'':notifynotpublished));
      if($showpdflinks==1) print(getexportlinks((($test==2)?'info':(($test==3)?'appx':'word')),$test,$book,1,1, 1));
      print('</div>');
    }
    print('<div style="margin:0 auto;text-align:center;">'.$msg.'</div>');

    $loc = $myrevid.'|'.$test.'|'.$book.'|'.$chap.'|'.$vers;
    $bookmarks = ((isset($_COOKIE['rev_bookmarks']))?$_COOKIE['rev_bookmarks']:'');
    $arbmks = explode(';', $bookmarks??'');

    //
    // peernotes
    //
    $showpeerdot = '';
    $havepeerwork = 0;
    $canaddpeernote = 0;
    if($userid>0 && $peernotes>0){
      $sql = 'select ifnull(editnote, \'\') editnote, length(ifnull(editdetails, \'\')) havepnote, ifnull(resolved, 0) resolved
              from peernotes
              where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' '.(($peernotesshowall==1)?'':'and resolved = 0 ');
      $row = rs($sql);
      $showdot=0;
      if($row){
        $peernote = $row[0];
        if($peernote!=''){
          $peernote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $peernote);
          $peernote = '<div id="pn_'.$loc.'" class="peernote" style="display:'.(($viewpeernotes==1)?'block':'none').';margin:0 0 3px 12px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);">'.$peernote.'</div>';
        }else $peernote = '<div id="pn_'.$loc.'" class="peernote" style="display:none;margin:0 0 3px 12px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);"></div>';
        $showdot = (($row['havepnote']>0)?1:0);
      }else $peernote = '<div id="pn_'.$loc.'" class="peernote" style="display:none;margin:0 0 3px 12px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);"></div>';
      $showpeerdot = '<img id="peerimg_'.$loc.'" data-havepeernote="'.$showdot.'" src="/i/skinnyyeldot.png" style="display:'.(($showdot==1 && $viewpeernotes==1)?'inline':'none').';width:.4em;" alt="There is a reviewernote for this verse." />';
      $row = rs('select length(ifnull(peerworknotes,\'\')) from myrevusers where myrevid = '.$myrevid.' ');
      $havepeerwork = (($row[0]>0)?1:0);
      $row = rs('select 1 from book_peer where testament = '.$test.' and book = '.$book.' and userid = '.$userid.' ');
      $canaddpeernote = (($row)?1:0);
    }

    //
    // editornotes
    //
    $editnote='';
    $haveedwork = 0;
    $haveeddetl = 0;
    $showeditdot = '';
    if($userid>0 && $editorcomments==1){
      $sql = 'select ifnull(editnote, \'\') editnote, ifnull(editdetails, \'\') details, ifnull(resolved, 0) resolved
              from editnotes
              where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' '.(($ednotesshowall==1)?'':'and resolved = 0 ');
      $row = rs($sql);
      if($row){
        $editnote = $row[0];
        if($editnote!=''){
          $editnote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $editnote);
          $editnote = '<div id="bn_'.$loc.'" class="editnote" style="display:'.(($viewedcomments==1)?'block':'none').';margin:0 0 3px 12px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);">'.$editnote.'</div>';
        }else $editnote = '<div id="bn_'.$loc.'" class="editnote" style="display:none;margin:0 0 3px 12px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);"></div>';
        $haveeddetl = (($row['details']!='')?1:0);
      }else $editnote = '<div id="bn_'.$loc.'" class="editnote" style="display:none;margin:0 0 3px 12px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);"></div>';
      $row = rs('select length(ifnull(notes,\'\')) from myrevusers where myrevid = -1 ');
      $haveedwork = (($row[0]>0)?1:0);
      $showeditdot = '<img id="edimg_'.$loc.'" data-haveednote="'.$haveeddetl.'" src="/i/skinnyreddot.png" style="display:'.(($haveeddetl==1 && $viewedcomments==1)?'inline':'none').';width:.4em;" alt="You have a note for this verse." />';
    }

    //
    // myrevnotes
    //
    if($myrevid>0 && $test!=2){
      $sql = 'select highlight, ifnull(myrevnotes, \'-\') myrevnotes
              from myrevdata
              where myrevid = '.$myrevid.' and testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
      $row = rs($sql);
      if($row){
        $hlite = $row[0];
        $myrevnotes = $row[1];
      }
      $row = rs('select ifnull(notes, \'\') from myrevusers where myrevid = '.$myrevid.' ');
      $wsdot = ((strlen($row[0])>0)?1:0);
      $nonote=(($myrevnotes=='-')?1:0);
      $which = (($test==3)?'Appendix':'Word Study');
      if($myrevnotes=='-'){$myrevnotes = '<small>You have no notes for this '.$which.'.</small>';}
      $bmk = $page.','.$test.','.$book.',1,1';
      $isbmked = ((in_array($bmk, $arbmks))?1:0);
      $myrevbutton = '<a onclick="rlightbox(\'note\',\''.$loc.'\');" title="Edit my note"><img id="mrnotesicon_'.$loc.'" src="/i/myrev_notes'.$colors[0].(($nonote==0)?'_DOT':'').'.png" style="width:1.8em;margin-bottom:-8px;" alt="edit" /></a>';
      $workspacebutton = '<a onclick="rlightbox(\'note\',\''.$myrevid.'|0|0|0|0\');" title="My workspace"><img src="/i/myrev_workspace'.$colors[0].(($wsdot==1)?'_DOT':'').'.png" style="width:1.8em;margin-bottom:-8px;" alt="My Workspace" /></a>';
      print($peernote);
      print($editnote);

      print('<small><img id="mrimg_'.$loc.'" data-isbmked="'.$isbmked.'" src="/i/skinnybluedot.png" style="display:'.(($nonote==0)?'inline':'none').';width:.4em;" alt="" />'.$showpeerdot.$showeditdot.'<a onclick="expandcollapsediv(\'myrev\')">My notes <span id="moreless">'.(($myrevshownotes==1)?'&laquo;':'&raquo;').'</span></a></small>');
      print('&nbsp;&nbsp;<span id="hl_'.$loc.'" style="display:inline-block;min-width:100px;padding:3px;color:'.$colors[7].';background-color:'.$hilitecolors[$hlite].';transition:.3s;border:1px solid '.$colors[3].';border-radius:4px;cursor:pointer;font-size:80%;" onclick="showhilightdiv(\''.$loc.'\', \'\');" class="hl_'.$loc.'" data-hlite="'.$hlite.'">&nbsp;'.$myrevkeys[$hlite].'</span>');

      print('<div id="myrev" style="text-align:left;height:'.(($myrevshownotes==1)?'auto':'0').';padding:3px;margin:0;overflow-y:auto;transition:height .4s ease-in;">');
      print('<div id="myrevnotes" style="font-size:90%;margin-bottom:4px;">'.$myrevnotes.'</div>');
      print('<div>'.$myrevbutton.'&nbsp;&nbsp;'.$workspacebutton.'&nbsp;&nbsp;&nbsp;<small><a href="/myrev" title="go to MyREV">MyREV</a></small>&nbsp;&nbsp;&nbsp;');
      print('</div>');
      print('</div>');
      print('<hr style="border-top:1px solid '.$colors[3].';margin-top:2px;">');
    }

    print($commentary);
    displaycomfootnotes($comfncnt, $arcomfn, 1);
    print(appendresources($test, $book, $chap, $vers));
    $toplink = '&nbsp;&nbsp;&nbsp;<a id="verybottom" onclick="return scrolltotop(\'verybottom\');">top</a>&nbsp;&nbsp;&nbsp;';
    print('<br /><div style="margin:0 auto;text-align:center;">'.str_replace('linkid', 'linkx', $prevlink).'&nbsp;'.$toplink.'&nbsp;'.str_replace('linkid', 'linkx', $nextlink).'</div>');
    if($canedit==1 || $appxedit==1){
      print('<div style="text-align:center;margin:0 auto;">');
      print(displayedits(8,$test,$book,$chap,$vers));
      print('</div>');
    }

    logview($page,$test,$book,1,1);
  }else{
    print('<div style="text-align:center;"><div style="display:inline-block;text-align:left;">'.$msg);
    print('<br />'.(($test==2)?'Information':(($test==3)?'Here are the available Appendices':'Here are the available Word Studies')).':');
    if($userid==1){
      print('&nbsp;&nbsp;<a class="toplink" href="/exportsql.php?test='.$test.'&amp;book=0&amp;chap=0&amp;vers=0">sql</a>');
    }
    print('<br /><br />');
    $sql = 'select book, ifnull(tagline,title), active from book
            where testament = '.$test.(($edit==1)?'':' and active = 1').'
            order by '.(($test==4)?'2':'sqn').' ';
    $itmcount=0;
    $apx = dbquery($sql);
    while($row = mysqli_fetch_array($apx)){
      $tmp = cleanquotes($row[1]);
      if(strpos($tmp, ':')) $tmp = trim(substr($tmp, strpos($tmp,':')+1));
      print('<a href="/'.(($test==2)?'Information':(($test==3)?'Appendix':'Wordstudy')).'/'.(($test!=4)?$row[0].'/':'').str_replace(' ','_', preg_replace("/[^ \-\p{L}]+/u", "", $tmp)).(($ismobile)?'/bb':'').'">'.$row[1].'</a>');
      if($userid>0 && $canedit==1) print(editlink('elnk'.$itmcount,$showedit,$mitm,8,$test,$row['book'],1,1));
      print((($userid>0 && $row['active']==0)?notifynotpublished:''));
      if($showpdflinks) print(getexportlinks((($test==2)?'info':(($test==3)?'appx':'word')),$test,$row[0],1,1,1));
      print('<br />'.$mobilespc.crlf);
      $itmcount++;
    }
    if ($itmcount==0) {
      print('Sorry, there are no items to display.<br />');
    }
    if ($test==4 && $itmcount<10) {
      print('<small>..more coming soon</small><br />');
    }
    print('</div></div>');
  }
}else{
  print($msg);
  mainmenu();
}

?>

  <script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>

  <script src="/includes/findmycomm.min.js?v=<?=$fileversion?>"></script>
  <script>
    findmycomm.enablePopups = true;
    findmycomm.remoteURL    = '<?=$jsonurl?>';
    findmycomm.startNodeId  = 'myrevnotes';
    findmycomm.mrlightbox   = 1;
  </script>

  <script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
  <script>
    findcomm.enablePopups = true;
    findcomm.remoteURL    = '<?=$jsonurl?>';
    findcomm.startNodeId = 'view';
  </script>

  <script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
  <script>
    findbcom.startNodeId  = 'view';
  </script>

  <script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
  <script>
    findappx.startNodeId = 'view';
    findappx.apxidx = [<?=loadapxids()?>];
  </script>

  <script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
  <script>
    findvers.startNodeId = 'view';
    findvers.remoteURL = '<?=$jsonurl?>';
    findvers.navigat = false;
  </script>

  <script src="/includes/findstrongs.min.js?v=<?=$fileversion?>"></script>
  <script>
    findstrongs.startNodeId = 'view';
    findstrongs.ignoreTags.push('noparse');
    findstrongs.lexicon = prflexicon;
  </script>

  <script src="/includes/findwordstudy.min.js?v=<?=$fileversion?>"></script>
  <script>
    findwordstudy.startNodeId = 'view';

    var myrevid = <?=$myrevid?>;
    addLoadEvent(findmycomm.scan);
    addLoadEvent(findcomm.scan);
    addLoadEvent(findbcom.scan);
    addLoadEvent(findappx.scan);
    addLoadEvent(findvers.scan);
    addLoadEvent(findstrongs.scan);
    addLoadEvent(findwordstudy.scan);

<?if($myrevid>0 && $test!=2){?>

    var chngtxt = 1;
    var canaddpeernote = <?=$canaddpeernote?>;

    function showhilightdiv(qry, clink){
      event.stopPropagation();
      var rdiv = $('myrevdiv');
      if(rdiv.style.display=='block'){
        myrevhidePopup();
        return;
      }
      var hvedws = <?=(($haveedwork==1)?1:0)?>;
      var hvpnws = <?=(($havepeerwork==1)?1:0)?>;
      var classname = 'hl_'+qry;
      hlit = document.getElementsByClassName(classname)[0].getAttribute('data-hlite');
      rdiv.innerHTML=gethilightdivcontents(qry, hlit, clink, 0,0,1,<?=(($editorcomments==1)?1:0)?>,hvedws,<?=(($peernotes>0)?1:0)?>,hvpnws);
      rdiv.style.visibility='hidden';
      rdiv.style.display='block';
      var dims = gethilightdivcoords(rdiv);
      rdiv.style.top  = dims.top+'px';
      rdiv.style.left = dims.left+'px';
      rdiv.style.visibility='visible';
      rdiv.style.opacity=1;
    }

    function reloadmyrevnotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var rddiv = $('myrevnotes');
          var notes = ((ret.myrevnotes)?ret.myrevnotes:'<small>You have no notes for this '+which+'<small>')
          rddiv.innerHTML = notes;
          var mrimg = $('mrimg_'+qry);
          mrimg.style.display=((ret.myrevnotes!='')?'inline':'none');
          mrimg.setAttribute('data-havemrnote', ((ret.myrevnotes!='')?1:0));
          var mrimg = $('mrnotesicon_'+qry);
          var img = '/i/myrev_notes'+colors[0]+((ret.myrevnotes)?'_DOT':'')+'.png';
          mrimg.src = img;
          setTimeout('findmycomm.scan();', 100);
          setTimeout('findcomm.scan();', 200);
          setTimeout('findbcom.scan();', 300);
          setTimeout('findappx.scan();', 400);
          setTimeout('findvers.scan();', 500);
          setTimeout('findstrongs.scan();', 600);
          setTimeout('findwordstudy.scan();', 700);  // doesn't work, maybe because of the curly quotes
          $('myrev').style.height = (rddiv.scrollHeight+69)+'px';
        }
      }

      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=data&ref='+qry,true);
      xmlhttp.send();
    }
    function reloadeditnotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if(xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var note = ret.editnote;
          var resolved = ret.resolved;
          try{ // to catch from editing workspace
            var bndiv = $('bn_'+qry);
            bndiv.innerHTML = ((resolved==1)?'&reg; ':'')+note;
            bndiv.style.display = (((ednotesshowall==1 || resolved==0) && note!='')?'inline-block':'none');
            var edimg = $('edimg_'+qry);
            edimg.style.display=(((ednotesshowall==1 || resolved==0) && ret.editdetails!='')?'inline':'none');
            edimg.setAttribute('data-haveednote', ((ret.editdetails!='')?1:0));
          }catch(e){}
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=edata&ref='+qry,true);
      xmlhttp.send();
    }

    function expandcollapsediv(id){
      excoldiv(id); // in misc.js
      var div = $(id);
      if(div.style.height=='0px'){
        $('moreless').innerHTML='&raquo;';
        setmyrevshownotes(0);
      }else{
        $('moreless').innerHTML='&laquo;';
        setmyrevshownotes(1);
      }
    }

    <?if($userid > 0){?>
    //
    // Used if an editor is viewing the page
    // They can manage their flags
    //
    function handleflag(logid, ni, donone){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var don = ret.donone;
          var flg = ret.flagged;
          //alert('don: '+don);
          //alert('flg: '+flg);
          if(don==1){
            $('td'+ni).style.backgroundColor=colors[6];
          }else{
            if($('td'+ni).innerHTML.includes('None'))
              $('td'+ni).style.backgroundColor=colors[6];
            var td = $('eflag'+ni);
            var tdc= ((flg==0)?'transparent':'#ffdddd');
            td.style.backgroundColor=tdc;
            var fg = $('iflag'+ni);
            var sfg= ((flg==0)?'30':'100');
            fg.style.opacity=sfg+'%';
          }
        }
      }
      var qs = '?id='+logid+'&doocmt=0&donone='+donone;
      //alert(qs);
      xmlhttp.open('GET','/jsonflagedit.php'+qs,true);
      xmlhttp.send();
    }
    <?}?>

    var div = $('myrev');
      try{
      if(div.style.height=='auto'){div.style.height = div.scrollHeight+'px';}
      var which = '<?=$which?>';
      setTimeout('initmyrevpopup(\'myrevdiv\');', 500);
    }catch(e){}
<?}?>

  </script>

  <script>
  <?if($glogid>1){?>
    setTimeout('scrolltopos(\'toptop\',\'marker<?=$glogid?>\')', 300);
  <?}?>
  var goback=1;
  var toffset=0; // used for TOC
  </script>


