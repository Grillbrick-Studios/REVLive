<?php
if(!isset($page)) die('unauthorized access');
?>
<div id="scrolltop0" style="line-height:.93;margin:-5px 0 12px 0;font-family:merriweather, serif;font-size:1.4em;">
  <div style="display:inline-block;width:36px;text-align:center;padding:4px;background-color:#337efe;color:white">R<br />E<br />V</div>
  <div style="float:right;padding:4px;color:#337efe;">Revised<br />English<br />Version<sup style="font-size:40%;">&reg;</sup></div>
</div>
<form name="frmnav" action="/" method="post">
<?

$showedit = (($edit==1)?"inline":"none");

// $armenuitems is defined at the top of functions.php
//print('mitm: '.$mitm);
for($midx=0, $size=count($armenuitems);$midx<$size;$midx++){
  switch($armenuitems[$midx]){
    case 1: // info
      $bln = ($mitm==1);
      print(navlinkf(1,$bln,'About the REV','About').crlf);
      break;
    case 7: // Search
      $bln = ($mitm==7);
      print(navlinkf(7,$bln, 'Search bible text or commentary/appendices','Search').crlf);
      break;
    case 8: // WhatsNew
      $bln = ($mitm==8);
      print(navlinkf(8,$bln,'Recent additions/modifications','What&rsquo;s New'.gethilite('wn', 14)).crlf);
      break;
    case 9: // REV Blog
      if ($revblog==1 || ($userid>0 && $showdevitems==1)) {  // showdevitems is a pref for logged in users
        $bln = ($mitm==9);
        print(navlinkf(9,$bln,'REV Blog','REV Blog'.gethilite('blog', 14)).crlf);
      }
      break;
    case 11: // Donate
      $bln = ($mitm==11);
      print(navlinkf(11,$bln,'Donate to the REV Project','Donate',1,0).crlf);
      break;
    case 12: // Appx
      $bln = ($mitm==12);
      print(navlinkf(13,$bln,'Appendices','Appendices').crlf);
      break;
    case 13: // back to bible
      print('<a href="/bcuk" class="amenu'.(($mitm==13)?' hilite':'').'" style="margin-bottom:5px;" title="Last viewed Bible passage" rel="nofollow">Bible</a>'.crlf);
      break;
    case 14: // help
      $bln = ($mitm==14);
      print(navlinkf(14,$bln,'Help','Help').crlf);
      break;
    case 15: // Resources
      $bln = ($mitm==15);
      print(navlinkf(15,$bln,'Resources','Resources'.gethilite('res', 14)).crlf);
      break;
    case 16: // Topics
      if ($revtp==1 || ($userid>0 && $showdevitems==1)) {  // showdevitems is a pref for logged in users
        $bln = ($mitm==16);
        print(navlinkf(16,$bln,'Topics','Topics',1,0).crlf);
      }
      break;
    case 17: // Chronology
      if ($revch==1 || ($userid>0 && $showdevitems==1)) {  // showdevitems is a pref for logged in users
        $bln = ($mitm==17);
        print(navlinkf(17,$bln,'Chronology','Chronology',1,0).crlf);
      }
      break;
    case 18: // MyREV
      $bln = ($mitm==18);
      print(navlinkf(18,$bln,'MyREV','MyREV',1,0).crlf);
      break;
    default: break;
  };
};

?>

<input type="hidden" name="mitm" value="<?=$mitm?>" />
<input type="hidden" name="page" value="<?=$page?>" />
<input type="hidden" name="test" value="<?=$test?>" />
<input type="hidden" name="book" value="<?=$book?>" />
<input type="hidden" name="chap" value="<?=$chap?>" />
<input type="hidden" name="vers" value="<?=$vers?>" />
<input type="hidden" name="lgid" value="0" />
<input type="hidden" name="sopssession" value="" />
<input type="hidden" name="temp" value="" />
<input type="image" name="invis" src="/i/invis.gif" alt="invisible" />
</form>

<script>

  function navigate(m,p,t,b,c,v){
    var f = document.frmnav;
    f.mitm.value = m;
    f.page.value = p;
    f.test.value = t;
    f.book.value = b;
    f.chap.value = c;
    f.vers.value = v;
    f.target = ((prfcommnewtab==1 && p==5)?'_blank':'_self');
    f.submit();
    return false;
    }

<?if($userid>0 && ($canedit==1 || $appxedit==1 || $editorcomments==1 || $peernotes==1)){?>
  function showedit(v){
    var dsply=((v==1)?'inline':'none');
    var vl=0;
    while ($('elnk'+vl) != null){
      $('elnk'+vl).style.display = dsply;
      vl++;
    }
    var bk=0;
    while ($('book'+bk) != null){
      $('book'+bk).style.display = dsply;
      bk++;
    }
    $('editcbx').checked=v;
    setSessionCookie('rev_edit', ((v==1)?1:0));
  }
  function showednotes(typ, chd, myd, tst, buk, chp, vrs){
    var cchp=1, cvrs=1, baseid='', id='', dsply1='', dsply2='', mnt, dot, dat, clr;
    dsply1=((chd==1)?'block':'none');
    dsply2=((chd==1)?'inline-block':'none');
    var prefix = 'mn_'; //catchall
    switch(typ){
    case 'edn': prefix='bn_';break;
    case 'mrv': prefix='mn_';break;
    case 'peer': prefix='pn_';break;
    }
    if(vrs==-1) vrs=1;
    else{ // single verse
      try{
        id=myd+'|'+tst+'|'+buk+'|'+chp+'|'+vrs;
        mnt=$(prefix+id);
        if(mnt.innerHTML!='') mnt.style.display=((page==1)?dsply2:dsply1);
        switch(typ) {
        case 'edn':
          dot=$('edimg_'+id);
          dat=dot.getAttribute('data-haveednote');
        break;
        case 'mrv':
          dot=$('mrimg_'+id);
          dat=dot.getAttribute('data-havemrnote');
          break;
        case 'peer':
          dot=$('peerimg_'+id);
          dat=dot.getAttribute('data-havepeernote');
          break;
        }
        if(dat==1) dot.style.display=dsply2;
      }catch(e){}
      cchp=0;
    }
    if(chp==-1) chp=1;
    while(cchp==1){
      baseid=myd+'|'+tst+'|'+buk+'|'+chp+'|';
      if($(prefix+baseid+'1')==null) cchp=0;
      while(cchp==1 && cvrs==1){
        try{
          id=baseid+vrs;
          mnt=$(prefix+id);
          if(mnt.innerHTML!='') mnt.style.display=dsply1;
          switch(typ){
          case 'edn':
              dot=$('edimg_'+id);
              dat=dot.getAttribute('data-haveednote');
              break;
          case 'mrv':
              dot=$('mrimg_'+id);
              dat=dot.getAttribute('data-havemrnote');
              // this toggles colors. Jerry said no.
              var span ='hl_'+myd+'|'+tst+'|'+buk+'|'+chp+'|'+vrs;
              var spans = document.getElementsByClassName(span);
              for(var i=0; i<spans.length; i++) {
                clr = spans[i].getAttribute('data-hlite');
                spans[i].style.backgroundColor = ((chd==1)?hlcolors[clr]:'transparent');
              }

              break;
          case 'peer':
              dot=$('peerimg_'+id);
              dat=dot.getAttribute('data-havepeernote');
              break;
          }
          if(dat==1) dot.style.display=dsply2;
          vrs++;
        }catch(e){cvrs=0}
      }
      cvrs=1;chp++;vrs=1;
    }
  }
<?}else $edit=0;?>

  function scrollnav(id){
    var nav = $('nav');
    nav.scrollTop = $('scrolltop'+id).offsetTop-80;
  }

</script>
<?

print('<hr />');
$imgstyl= 'margin-top:8px;border:0;vertical-align:middle;';
print('<a href="/pref" title="Settings" rel="nofollow"><img src="/i/mnu_preferences'.$colors[0].'.png" alt="settings" style="'.$imgstyl.'width:1.6em;" /></a> ');
print('<a href="/expt" title="REV Exports" rel="nofollow"><img src="/i/download_export'.$colors[0].'.png" alt="bookmarks" style="'.$imgstyl.'width:1.6em;" /></a> ');
print('<input type="image" src="/i/mnu_theme_day'.$colors[0].'.png" style="'.$imgstyl.'width:30px;margin-left:3px;" title="Day theme" alt="Day theme" onclick="setcolortheme2(0);location.reload();" />');
print('<input type="image" src="/i/mnu_theme_sep'.$colors[0].'.png" style="'.$imgstyl.'width:30px;margin-left:3px;" title="Sepia theme" alt="Sepia theme" onclick="setcolortheme2(2);location.reload();" />');
print('<input type="image" src="/i/mnu_theme_drk'.$colors[0].'.png" style="'.$imgstyl.'width:30px;margin-left:3px;" title="Dark theme" alt="Dark theme" onclick="setcolortheme2(1);location.reload();" />');
print('<br />');

print('<span style="font-size:70%;color:'.$colors[7].';line-height:1;"><br /><small>&copy; 2013-'.date('Y').'<br />Spirit &amp; Truth</small></span><br />');
if($userid>0){
  print('<hr />');
  print('<span style="display:inline-block;padding:4px 2px;font-size:75%;">');
  print('Hi, '.$username.'! ');
  if($canedit==1 || $appxedit==1) print('<label for="editcbx">Edit</label><input type="checkbox" name="edit" id="editcbx" class="cbx" value="1" onclick="showedit(this.checked);"'.fixchk($edit).' />');
  print('</span>');
  print('<div class="setdivu">'.usermenu('break').'</div>');
  if($superman==1) print('<br /><div class="setdivu">'.adminmenu('break').'</div>');
  print('<div style="width:100px;height:100px;float:left;"></div>'); // spacer
}

function navlinkf($mi,$hl,$tl,$tx){
  // $mi = menuitem, mitm
  // $hl = hilite or not
  // $tl = title for link
  // $tx = text for link

  $tmplink='';
  switch($mi){
    case 1: $tmplink='info/1'; break;
    case 7: $tmplink='srch'; break;
    case 8: $tmplink='wnew'; break;
    case 9: $tmplink='blog'; break;
    case 11:$tmplink='donate'; break;
    case 12:$tmplink='expt'; break;
    case 13:$tmplink='appx'; break;
    case 14:$tmplink='info/3'; break;
    case 15:$tmplink='resources'; break;
    case 16:$tmplink='topics'; break;
    case 17:$tmplink='chronology'; break;
    case 18:$tmplink='myrev'; break;
    default: break;
  }
  $lnk = '<a href="/'.$tmplink.'"';
  $lnk.= ' class="amenu'.(($hl)?' hilite':'').'" ';
  $lnk.= 'style="margin-bottom:5px;"';
  $lnk.= (($tl)?' title="'.$tl.'"':'').' rel="nofollow">';
  $lnk.= $tx.'</a>';
  return $lnk;
}
?>
