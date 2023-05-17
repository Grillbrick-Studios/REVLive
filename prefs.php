<?php
if(!isset($page)) die('unauthorized access');

$stitle  = "Preferences";
$prefidx=0;
$setcls = (($screenwidth<600)?2:3);
$setcls = 3;
//$imgstyl = 'height:'.(($ismobile)?'26':'26').'px;margin:0 5px;vertical-align:middle;';
$imgstyl = 'height:'.(($ismobile)?"1.3":".9").'rem;margin:0 5px;vertical-align:middle;';
$imgstyl2= 'width:'.(($ismobile)?".9":".7").'rem;margin:0;vertical-align:middle;';

?>
  <span class="pageheader"><?=$stitle?></span>
  <form name="frm" method="post" action="/">
    <?if(!$ismobile){?>
    <p style="font-size:90%;">
    Here are several preferences or settings you can change in order to customize the way this website looks and works.
    Click the icon
    <span style="white-space:nowrap">(<img src="/i/moreinfo.png" alt="" style="width:<?=((!$ismobile)?'1.0em':'.9em')?>" />)</span>
    on the right of each setting for more information on that setting.
    </p><p style="font-size:90%;">All the website settings are &ldquo;sticky,&rdquo; and so when you change your preferences, the website will remember whatever
    settings you choose, even if you close your web browser and come back later.
    <br /><br />
    </p>
    <?}?>
    <table id="settable" style="width:100%;margin:0;padding:0;">

      <!--verse break-->
      <tr class="settr">
        <td class="settd1">Bible Text Mode</td>
        <td class="settd<?=$setcls?>">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr><td><input type="radio" name="versebreak" value="1"<?=fixrad($versebreak==1)?> onclick="setversebreak(this.value);" /></td><td>&nbsp;Verse Break</td></tr>
            <tr><td><input type="radio" name="versebreak" value="0"<?=fixrad($versebreak==0)?> onclick="setversebreak(this.value);" /></td><td>&nbsp;Paragraph</td></tr>
            <tr><td><input type="radio" name="versebreak" value="2"<?=fixrad($versebreak==2)?> onclick="setversebreak(this.value);" /></td><td>&nbsp;Reading</td></tr>
          </table>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>This setting controls how the Bible text is presented.</p>
        <p>If &ldquo;Verse Break&rdquo; is selected, each verse will begin on a new line.</p>
        <p>If &ldquo;Paragraph&rdquo; is selected, the Bible will be presented in paragraph format, with one verse right after another, breaking
        only on new paragraphs.</p>
        <p>If &ldquo;Reading&rdquo; is selected, the Bible will be presented in prose format, with one verse right after another, breaking
        only on new paragraphs. The verse numbers will <span style="color:red;">not</span> be displayed.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <?//if($ismobile){?>
      <!--for mobile users, link commentary or not-->
      <tr class="settr">
        <td class="settd1">Bible Text Only</td>
        <td class="settd<?=$setcls?>">
          <table border="0" cellpadding="0" cellspacing="0">
            <!-- NOTE: due to name change, this switch is backwards! 0=yes, 1=no-->
            <tr><td><input type="radio" name="linkcommentary" value="1"<?=fixrad($linkcommentary==1)?> onclick="setlinkcommentary(this.value);" /></td><td>&nbsp;No</td></tr>
            <tr><td><input type="radio" name="linkcommentary" value="0"<?=fixrad($linkcommentary==0)?> onclick="setlinkcommentary(this.value);" /></td><td>&nbsp;Yes</td></tr>
          </table>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>Activating this feature removes all links to the REV commentary, disables all MyREV functionality, and temporarily hides any markup (i.e., highlighting) in the text.
        What you are left with is a clean version of only the REV Bible text for ease of reading. All user notes and highlights are saved and accessible again once this feature is turned off.</p>
        <p>The purpose for this setting is two-fold. 1) If you are on a mobile or touchscreen device and just want to read the Bible, sometimes it&rsquo;s too easy to inadvertently activate a link instead of scroll the screen. If this setting is on, all links are deactivated so that cannot happen.
        2) If you are doing a presentation or teaching and have many verses highlighted, your highlights may be distracting to your audience. This setting will turn your highlights off.</p>
        <p>If you are a MyREV user and this setting is on, your notes and highlighted verses are still accessible on the MyREV page.</p>

      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>
      <?//}?>

      <!--use OE on v1verse break-->
      <tr class="settr">
        <td class="settd1">Old English on First Verse</td>
        <td class="settd<?=$setcls?>"><input type="checkbox" name="useoefirst" id="useoefirst" value="1" onclick="setuseoefirst(this.checked);"<?=fixchk($useoefirst)?> /></td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>This setting is only in effect if the "Bible Viewing Mode" is set to "Paragraph" or "Reading" mode. What it does is replace the first letter of
        the first verse in every chapter with an Old English graphic representation of that letter.
        For example, with this box checked, Genesis 1:1 looks like this:</p>
        <p style="text-indent:0;"><img src="/i/letters/i<?=$colors[0]?>.png" alt="" border="0" style="float:left;margin-top:-3px;margin-left:4px;height:2.1em" />n the beginning God created the heavens and the earth.</p>
        <p>It is purely cosmetic.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--paragraph style-->
      <tr class="settr">
        <td class="settd1">Paragraph style</td>
        <td class="settd<?=$setcls?>">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr><td><input type="checkbox" name="paraindent" value="1"<?=fixchk((($parachoice==3 || $parachoice==4)?1:0))?> onclick="setparachoice(document.frm);setparagraphs();" /></td><td>&nbsp;Indented</td></tr>
            <tr><td><input type="checkbox" name="parjustify" value="1"<?=fixchk((($parachoice==2 || $parachoice==4)?1:0))?> onclick="setparachoice(document.frm);setparagraphs();" /></td><td>&nbsp;Justified</td></tr>
          </table>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>These settings determines how paragraphs are displayed.</p>
        <p>If &ldquo;Indent&rdquo; is checked, the first line of most paragraphs will be indented. </p>
        <p>If &ldquo;Justify&rdquo; is checked, paragraphs will be justified, meaning both the right and left edges will be lined up.</p>
        <?if($ismobile==0){?>
        <p>Here is a bigger block of sample text:</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed interdum leo dictum, cursus erat sed, ultricies magna.
        Praesent libero arcu, rhoncus vel auctor aliquam, vulputate sed risus. Praesent sodales malesuada ex eget lobortis.
        Sed pellentesque arcu diam, ut faucibus justo mattis vel. Suspendisse luctus nulla ut eros tincidunt elementum.
        Donec scelerisque a arcu ac placerat. Donec a mi suscipit, tempor nisi ac, suscipit orci. Praesent auctor libero id
        orci vulputate porta. Suspendisse non pellentesque est. Fusce dictum condimentum lacus sed ornare. In nec purus nec
        est pharetra sagittis. Sed ac ante quis nunc vestibulum rhoncus et ac risus. Nullam turpis eros, faucibus vitae pretium
        condimentum, porttitor et sapien. Integer tincidunt dui eleifend scelerisque placerat.</p>
        <?}?>
        <p>You can immediately see the effect of these settings here in this help text.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--columns-->
      <tr class="settr">
        <td class="settd1">Bible View Columns</td>
        <td class="settd<?=$setcls?>">
          <select name="viewcols" class="setsel<?=(($screenwidth<600)?1:2)?>" onchange="setviewcols(this.value);settestviewcols();">
            <option value="1"<?=fixsel($viewcols, "1")?>>1 Column</option>
            <option value="2"<?=fixsel($viewcols, "2")?>>2 Columns</option>
            <option value="3"<?=fixsel($viewcols, "3")?>>3 Columns</option>
            <?if(!$ismobile){?>
            <option value="4"<?=fixsel($viewcols, "4")?>>4 Columns</option>
            <option value="5"<?=fixsel($viewcols, "5")?>>5 Columns</option>
            <?}?>
          </select>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>If you are using a computer or tablet to view this website, you might want to view the Bible in multiple columns. This may make it easier to read.
        This is an example of what the current setting looks like:</p>
        <p><strong>Genesis 1</strong></p>
        <div id="coltest" class="col<?=$viewcols?>container">
          <p><sup class="versenumcomm">1</sup>In the beginning God created the heavens and the earth.</p>
          <p><sup class="versenumcomm">2</sup>And the earth was formless and empty, and darkness was on the face of the deep. And the Spirit of God was hovering over the face of the waters.</p>
          <p><sup class="versenum">3</sup>God said, &ldquo;Let there be light,&rdquo; and there was light.
          <sup class="versenum">4</sup>And God saw the light, that it was good. And God separated the light from the darkness.
          <sup class="versenum">5</sup>And God called the light &ldquo;day,&rdquo; and the darkness he called &ldquo;night.&rdquo; And there was evening and there was morning, one day.</p>
        </div>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--different bible font-->
      <tr class="settr">
        <td class="settd1">Different Bible Font</td>
        <td class="settd<?=$setcls?>">
          <input type="checkbox" name="diffbiblefont" id="diffbiblefont" value="1" onclick="setdiffbiblefont(this.checked);"<?=fixchk($diffbiblefont)?> />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>If this setting is on (checked), you can have the REV Bible text display in a different font family, size, and line spacing.
           To change them, use the Quick Settings menu available in the navigation drop-down at the top of the page while you are viewing the Bible.
        </p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--font family-->
      <tr class="settr">
        <td class="settd1">System Font</td>
        <td class="settd<?=$setcls?>">
          <select name="fontfamily" class="setsel<?=(($screenwidth<600)?1:2)?>" onchange="setfontfamily(this.value, this.selectedIndex);$('view').style.fontFamily=this.value;">
            <optgroup label="Serifed Fonts">
              <option value="merriweather"<?=fixsel($fontfamily, "merriweather")?>>Merriweather</option>
              <option value="times new roman"<?=fixsel($fontfamily, "times new roman")?>>Times New Roman</option>
              <option value="caladea"<?=fixsel($fontfamily, "caladea")?>>Caladea</option>
              <option value="ibm plex serif"<?=fixsel($fontfamily, "ibm plex serif")?>>IBM Plex Serif</option>
            </optgroup>
            <optgroup label="Non-Serifed Fonts">
              <option value="arial"<?=fixsel($fontfamily, "arial")?>>Arial</option>
              <option value="roboto"<?=fixsel($fontfamily, "roboto")?>>Roboto</option>
              <option value="montserrat"<?=fixsel($fontfamily, "montserrat")?>>Montserrat</option>
              <option value="balsamiq sans"<?=fixsel($fontfamily, "balsamiq sans")?>>Balsamiq Sans</option>
            </optgroup>
          </select>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>There are several fonts you can choose from.
        The default font is Merriweather but feel free to choose from among several alternative font styles.
        If you prefer a non-serifed font, try Arial.</p>
        <p>You may want to view the Bible text in a different font, or have the font size or line spacing be different.
        To set those font settings separately, see the &ldquo;Quick Settings&rdquo; in the navigation drop-down menu while you are viewing the Bible.  
        </p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--font size-->
      <tr class="settr">
        <td class="settd1">Font Size</td>
        <td class="settd<?=$setcls?>">
          <input type="image" name="x1" src="/i/larger<?=$colors[0]?>.png" onclick="return settextsize(1);" title="increase" style="<?=$imgstyl?>" />&nbsp;&nbsp;&nbsp;
          <input type="image" name="x2" src="/i/smaller<?=$colors[0]?>.png" onclick="return settextsize(-1);" title="decrease" style="<?=$imgstyl?>" />&nbsp;&nbsp;&nbsp;
          <input type="image" name="x3" src="/i/reset<?=$colors[0]?>.png" onclick="return settextsize(99);" title="reset" style="<?=$imgstyl?>" />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>You may adjust the font size by clicking the increase
        <span style="white-space:nowrap">(<img src="/i/larger<?=$colors[0]?>.png" alt="" style="<?=$imgstyl2?>" />)</span> or decrease
        <span style="white-space:nowrap">(<img src="/i/smaller<?=$colors[0]?>.png" alt="" style="<?=$imgstyl2?>" />)</span> arrows.
        You can return the font size to its original default setting by clicking the reset
        <span style="white-space:nowrap">(<img src="/i/reset<?=$colors[0]?>.png" alt="" style="<?=$imgstyl2?>" />)</span> icon.
        The effect of clicking the icons is immediate.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--line height-->
      <tr class="settr">
        <td class="settd1">Line Height</td>
        <td class="settd<?=$setcls?>">
          <input type="image" name="x4" src="/i/larger<?=$colors[0]?>.png" onclick="return setlineheight(1);" title="increase" style="<?=$imgstyl?>" />&nbsp;&nbsp;&nbsp;
          <input type="image" name="x5" src="/i/smaller<?=$colors[0]?>.png" onclick="return setlineheight(-1);" title="decrease" style="<?=$imgstyl?>" />&nbsp;&nbsp;&nbsp;
          <input type="image" name="x6" src="/i/reset<?=$colors[0]?>.png" onclick="return setlineheight(99);" title="reset" style="<?=$imgstyl?>" />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>You may adjust the line spacing by clicking the increase
        <span style="white-space:nowrap">(<img src="/i/larger<?=$colors[0]?>.png" alt="" style="<?=$imgstyl2?>" />)</span> or decrease
        <span style="white-space:nowrap">(<img src="/i/smaller<?=$colors[0]?>.png" alt="" style="<?=$imgstyl2?>" />)</span> arrows.
        You can return the line spacing to its original default setting by clicking the reset
        <span style="white-space:nowrap">(<img src="/i/reset<?=$colors[0]?>.png" alt="" style="<?=$imgstyl2?>" />)</span> icon.
        The effect of clicking the icons is immediate.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <?if(!$ismobile){?>
      <!--commentary link style-->
      <tr class="settr">
        <td class="settd1">Commentary Link Style</td>
        <td class="settd<?=$setcls?>">
          <select name="commlinkstyl" class="setsel<?=(($screenwidth<600)?1:2)?>" onchange="setcommlinkstyl(this.value);$('comlinktest').className='comlink'+prfcommlinkstyl">
            <option value="0"<?=fixsel($commlinkstyl, 0)?>>red verse number only</option>
            <option value="1"<?=fixsel($commlinkstyl, 1)?>>+<?=$colors[5]?> on hover</option>
            <option value="2"<?=fixsel($commlinkstyl, 2)?>>+underline on hover</option>
            <option value="3"<?=fixsel($commlinkstyl, 3)?>>+<?=$colors[5]?> +underline on hover</option>
          </select>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>You may choose how verses that have commentary appear when you hover over them with your mouse. The default is simply to have the verse number appear in red.
        The red verse number is not a changeable preference and will automatically show up for every verse with commentary.
        But you can choose to have the verse also be underlined, colored, or both when you hover the mouse over it.
        Sometimes the red verse numbers may be difficult to see depending on your display. This was intentional because we did not want the
        commentary indicators to be too obvious or distracting when just reading the Bible. But if you prefer to have a more visible indication,
        you may choose to have added effects to help you see which verses have commentary. Following is a sample so you can see the effect here:</p>
        <p><strong>Genesis 1</strong><br /><sup class="versenumcomm">1</sup><a id="comlinktest" class="comlink<?=$commlinkstyl?>" onclick="return false">In the beginning God created the heavens and the earth.</a></p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>
      <?}?>

      <!--strongs lexicon-->
      <tr class="settr">
        <td class="settd1">Strong&rsquo;s Lexicon Site</td>
        <td class="settd<?=$setcls?>">
          <select name="lexicon" class="setsel<?=(($screenwidth<600)?1:2)?>" onchange="setlexicon(this.value);">
            <option value="1"<?=fixsel($lexicon, "1")?>>BlueLetterBible.org</option>
            <option value="2"<?=fixsel($lexicon, "2")?>>BibleHub.com</option>
            <option value="3"<?=fixsel($lexicon, "3")?>>StudyLight.org</option>
          </select>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>In many commentary entries there are references to Greek or Hebrew words. If there is a Strong's reference number present, it will be converted to a hyperlink.
        This preference determines which of the three available Bible research websites you will be taken to when the link is clicked.
        The site will open in a new browser window or tab, and load the site with the available information on the Greek or Hebrew word.</p>
        <p>You may choose BlueLetterBible.org, BibleHub.com, or StudyLight.com.</p>
        </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--commentary/appendix/wordstudy links in new tab-->
      <?if(!$inapp){?>
      <tr class="settr">
        <td class="settd1">Open Commentary Links in New Tab</td>
        <td class="settd<?=$setcls?>">
          <input type="checkbox" name="commnewtab" id="commnewtab" value="1" onclick="setcommnewtab(this.checked);"<?=fixchk($commnewtab)?> />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>Some people may want the commentary<?=(($revws)?', appendix, and word study':' and appendix')?> links to open in a new browser window or tab.
        If you desire this behavior, click this checkbox to turn this setting &ldquo;on&rdquo;.
        Depending upon your personal browser settings, your browser will open the commentary in a new tab in the same window
        or else in a new window. This is off by default, meaning the commentary (or appendix<?=(($revws)?' or word study':'')?>) will be loaded into the same window,
        and there will be a &ldquo;Go Back&rdquo; button to return you to where you were before.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>
      <?}?>

      <!--verse break-->
      <tr class="settr">
        <td class="settd1">Chapter Heading Links</td>
        <td class="settd<?=$setcls?>">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr><td><input type="radio" name="versnavwhat" value="1"<?=fixrad($versnavwhat==1)?> onclick="setversenavwhat(this.value);" /></td><td>&nbsp;Chapter links</td></tr>
            <tr><td><input type="radio" name="versnavwhat" value="0"<?=fixrad($versnavwhat==0)?> onclick="setversenavwhat(this.value);" /></td><td>&nbsp;Verse links</td></tr>
            <tr><td><input type="radio" name="versnavwhat" value="2"<?=fixrad($versnavwhat==2)?> onclick="setversenavwhat(this.value);" /></td><td>&nbsp;Both</td></tr>
          </table>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>This setting controls what you will see when you click on a Chapter Heading, IE: &ldquo;Ephesians Chapter 2.&rdquo; You have the choice of seeing links
        to the other chapters in the book you are reading, links to the verses in the book/chapter you are reading, or both.
        The default setting is to have links to the verses only.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--always display chapter heading links-->
      <tr class="settr">
        <td class="settd1">Always Show Chapter Heading Links</td>
        <td class="settd<?=$setcls?>">
          <input type="checkbox" name="viewversnav" id="viewversnav" value="1" onclick="setviewversnav(this.checked);"<?=fixchk($viewversnav)?> />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>If this setting is enabled, every time you navigate to a book/chapter, the Chapter Heading links (see above) will already be displayed.
        There will be no need to click the Chapter Heading to see the links.
        This can aid in navigating the site when you are quickly trying to get to a particular chapter or verse.
        Instead of scrolling with your finger trying to find a verse, simply tap the verse number and you will automatically be taken to the verse.</p>
        <p>Note that the chapter heading links are always available by clicking or tapping on any chapter heading.
        But turning this setting on will always show them, no extra click needed.
        The downside to having them always displayed is that large books with many chapters and verses will have a lot of links, which may be distracting. The choice is yours.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--uppercase OT quotes-->
      <tr class="settr">
        <td class="settd1">Uppercase OT Quotes</td>
        <td class="settd<?=$setcls?>">
          <input type="checkbox" name="ucaseot" id="ucaseot" value="1" onclick="setucaseot(this.checked);"<?=fixchk($ucaseot)?> />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>This setting determines how Old Testament scripture that is quoted in the New Testament is displayed. If the box is checked, OT quotes will be in bold uppercase:</p>
        <p>But he answered and said, &ldquo;It is written, <strong>MAN DOES NOT LIVE BY BREAD ALONE, BUT BY EVERY WORD THAT PROCEEDS OUT OF THE MOUTH OF GOD.</strong>&rdquo;</p>
        <p>If it is not checked, it will only be bold:</p>
        <p>But he answered and said, &ldquo;It is written, <strong>Man does not live by bread alone, but by every word that proceeds out of the mouth of God.</strong>&rdquo;</p>
        <p>This setting is experimental. It is off by default.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <?if($ismobile){?>
      <!--set swipe nav for mobile devices-->
      <tr class="settr">
        <td class="settd1">Swipe Navigation</td>
        <td class="settd<?=$setcls?>">
          <table border="0" cellpadding="0" cellspacing="0">
            <tr><td><input type="radio" name="swipenav" value="1"<?=fixrad($swipenav==1)?> onclick="setswipenav(this.value);" /></td><td>&nbsp;Prev/Next</td></tr>
            <tr><td><input type="radio" name="swipenav" value="2"<?=fixrad($swipenav==2)?> onclick="setswipenav(this.value);" /></td><td>&nbsp;Show/Hide menu</td></tr>
            <tr><td><input type="radio" name="swipenav" value="0"<?=fixrad($swipenav==0)?> onclick="setswipenav(this.value);" /></td><td>&nbsp;Off</td></tr>
          </table>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>If you are on a mobile device, this setting determines what happens when you swipe left or right on the screen.
        The default behavior is to navigate to the next or previous chapter.
        You can also set it to show or hide the left menu. Sometimes having this setting turned on can interfere with some browsers&rsquo; default behavior, especially on Apple devices.
        Rather than swiping from the edge of the screen, you can try swiping across the middle of the screen.
        If you cannot get it to work well for you, you can turn this setting off and navigate the site by tapping the left/right arrows at the top of the screen.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>
      <?}?>

      <!--animated scrolling-->
      <tr class="settr">
        <td class="settd1">Animation</td>
        <td class="settd<?=$setcls?>">
          <input type="checkbox" name="chkscrollynav" id="chkscrollynav" value="1"<?=fixchk($scrollynav)?> onclick="setscrollynav(this.checked);" />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>This setting controls whether some things on the REV website are animated or not.  For example, if the box is checked, the help sections on this page will
        scroll into view rather than instantly snap open. Also, links to the &ldquo;top&rdquo; will scroll to the top rather than instantly position you there.
        Other places that are affected are the links to verses on the commentary pages, and the chapter heading links to verses will scroll
        down to the verse.</p>
        <p>This setting is purely cosmetic.  It is on by default. But if you are using a computer or device that is older or slow and the animations do not display smoothly,
        you can turn this setting off, and the screen will instantly snap to the correct place.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--Color theme-->
      <tr class="settr">
        <td class="settd1">Color Theme</td>
        <td class="settd<?=$setcls?>">
          <img src="/i/mnu_theme_day<?=$colors[0]?>.png" alt="" style="<?=$imgstyl?>height:22px;" /><input type="checkbox" id="chkcolortheme1" value="0"<?=fixchk($colortheme==0)?> onclick="setcolortheme2(0);location.reload();" /> Normal<br />
          <img src="/i/mnu_theme_sep<?=$colors[0]?>.png" alt="" style="<?=$imgstyl?>height:22px;" /><input type="checkbox" id="chkcolortheme3" value="2"<?=fixchk($colortheme==2)?> onclick="setcolortheme2(2);location.reload();" /> Sepia<br />
          <img src="/i/mnu_theme_drk<?=$colors[0]?>.png" alt="" style="<?=$imgstyl?>height:22px;" /><input type="checkbox" id="chkcolortheme2" value="1"<?=fixchk($colortheme==1)?> onclick="setcolortheme2(1);location.reload();" /> Dark Mode<br />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>There are three color themes you can choose from. &ldquo;Normal,&rdquo; &ldquo;Sepia,&rdquo; and &ldquo;Dark Mode.&rdquo;
        If you are reading in a dimly lit environment you may prefer Dark Mode for less strain on your eyes.</p>
        <p>Click or tap the checkbox for the color theme you prefer.</p>
        <p>When you change this setting, the &ldquo;Preferences&rdquo; page will be reloaded.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--show export links-->
      <tr class="settr">
        <td class="settd1">Show Export Icons</td>
        <td class="settd<?=$setcls?>">
          <input type="checkbox" name="showpdflinks" id="showpdflinks" value="1" onclick="setshowpdflinks(this.checked);location.reload();"<?=fixchk($showpdflinks)?> />
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>It is a feature of the REV website that you can download almost any page, whether a book or chapter of the Bible, or a particular commentary or appendix,
        in PDF and Microsoft Word format.  But some people may not have any interest in doing so, particularly if they are using a phone or tablet, and seeing the icons
        <span style="white-space:nowrap">(<img src="/i/pdf<?=$colors[0]?>.png" alt="" style="width:19px;" /> <img src="/i/docx<?=$colors[0]?>.png" alt="" style="width:16px;" />)</span>
        on almost every page may be distracting to them. This setting allows you to turn them off.</p>
        <p>When you change this setting, the &ldquo;Preferences&rdquo; page will be reloaded.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <?
      if($showpdflinks){
        $expprefs   = explode(';', (isset($_COOKIE['rev_expprefs']))?$_COOKIE['rev_expprefs']:'2;1');
        $expfontsize   = $expprefs[0];
        $expmargintype = $expprefs[1];
      ?>
      <!--export font size-->
      <tr class="settr">
        <td class="settd1">Export Font Size</td>
        <td class="settd<?=$setcls?>">
          <select name="expfontsize" class="setsel<?=(($screenwidth<600)?1:2)?>" onchange="setexpprefs(document.frm);">
            <option value="1"<?=fixsel(1,$expfontsize)?>>small</option>
            <option value="2"<?=fixsel(2,$expfontsize)?>>medium</option>
            <option value="3"<?=fixsel(3,$expfontsize)?>>large</option>
          </select>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>This setting determines the relative size of the font for PDF and Microsoft Word exports.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <!--export gutter-->
      <tr class="settr">
        <td class="settd1">Export Gutter</td>
        <td class="settd<?=$setcls?>">
          <select name="expmargintype" class="setsel<?=(($screenwidth<600)?1:2)?>" onchange="setexpprefs(document.frm);">
            <option value="1"<?=fixsel(1,$expmargintype)?>>none</option>
            <option value="2"<?=fixsel(2,$expmargintype)?>>for single sided printing</option>
            <option value="3"<?=fixsel(3,$expmargintype)?>>for double sided printing</option>
          </select>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>For PDF exports, you may choose whether you want a &ldquo;gutter&rdquo; or not.
        If you plan to print the documents and put them in a 3&ndash;ring binder, choose a gutter based on whether your printer supports single or double&ndash;sided printing.</p>
        <p>This setting has no effect on MS Word exports. It's easy enough to manage margins and gutters from within MS Word itself.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <script>
         function setexpprefs(f){
           var expfontsize = f.expfontsize[f.expfontsize.selectedIndex].value;
           var expmargintype = f.expmargintype[f.expmargintype.selectedIndex].value;
           setCookie('rev_expprefs', expfontsize+';'+expmargintype, 90);
         }
      </script>
      <?} // end of export prefs?>

      <!--reset preferences cookie-->
      <tr class="settr">
        <td class="settd1">Reset Preferences</td>
        <td class="settd<?=$setcls?>">
          <button style="font-size:1.0em" onclick="if(confirm('Are you sure you want to reset all your preferences?')){resetcookies();location.href='/';return false;}else return false;">Reset</button>
        </td>
        <td style="width:5%;vertical-align:top"><?=helpicon($prefidx)?></td>
      </tr>
      <tr id="prefhelptr<?=$prefidx?>"><td colspan="3"><div id="prefhelpdiv<?=$prefidx++?>">
        <p>Although this is very rare, sometimes your preferences may get corrupted due to unforseen technical complications
        and then things just don't seem to work quite right. If this happens, click the button to reset your preferences.
        This will reset everything to the default settings and allow you to start over.</p>
        <p>If this happens, we apologize for any inconvenience in having to re-enter your preferences.</p>
      </div></td></tr>
      <tr><td colspan="3" class="settd_spc"></td></tr>

      <tr><td colspan="3">
        <p>We hope you enjoy using the website and that it will benefit you in your walk with God and His Son Jesus Christ.</p>
        <p>God Bless You!</p>
      </td></tr>


    </table>

<?if($userid>0){?>
  <br />
  <div style="display:table">
    <div class="setdivu">
      Hello, <?=$username?>:<br />
      <?=usermenu('break')?>
    </div>
<?if($superman==1){?>
    <div class="setdivu">
      <?=adminmenu('break')?>
    </div>
<?}?></div><?}?>
<div style="margin:0 auto;text-align:center">
  &nbsp;<br />&nbsp;<br />
  <small>(<a id="prefbottom" onclick="return scrolltotop('prefbottom')">top</a>)</small>
</div>

  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" />
  </form>
<script>

  for(var idx=0;idx<<?=$prefidx?>;idx++){
    var dv = $('prefhelpdiv'+idx);
    dv.style.display = 'inline-block';
    dv.style.fontSize = '90%';
    dv.style.paddingBottom='7px';
    dv.style.overflow='hidden';
    $('prefhelptr'+idx).style.display='none';
    dv.style.display = 'none';
    dv.style.height=0;
  }
  var increment=((ismobile)?20:14);
  var timdelay =((ismobile)?10:7);
  var growtimerid, shrinktimerid;

  function toggleprefhelp(idx){
    var dv = $('prefhelpdiv'+idx);
    if(prfscrollynav==1){
      var hite= parseInt(dv.style.height);
      window.clearTimeout(growtimerid);
      window.clearTimeout(shrinktimerid);
      if(hite==0 || $('prefhelptr'+idx).style.display=='none'){ //grow it
        $('prefhelptr'+idx).style.display=''; // on
        dv.style.display='inline-block';
        dv.style.height='0px';
        growhelp(dv, idx);
      }else{ //shrink it
        shrinkhelp(dv, idx);
      }
    }else{
      var vis= $('prefhelptr'+idx).style.display;
      if(vis=='none'){ //grow it
        $('prefhelptr'+idx).style.display=''; // on
        dv.style.display='inline-block';
        dv.style.height = dv.scrollHeight+'px';
      }else{
        $('prefhelptr'+idx).style.display='none';
      }
    }
    return false;
  }

  function growhelp(dv, idx2){
    var dvh = parseInt(dv.style.height);
    if(dvh < (dv.scrollHeight-10)){
      dv.style.height = (dvh+increment)+'px';
      growtimerid =setTimeout(function(){growhelp(dv, idx2);}, timdelay);
    }else{
      dv.style.height = dv.scrollHeight+'px';
    }
  }

  function shrinkhelp(dv,idx2){
    if(ismobile==1){
      dv.style.height = '0px';
      dv.style.display='none';
      $('prefhelptr'+idx2).style.display='none';
    }else{
      var dvh = parseInt(dv.style.height);
      if(dvh > 0){
        if(dvh<increment) dvh=increment;
        dv.style.height = (dvh-increment)+'px';
        shrinktimerid =setTimeout(function(){shrinkhelp(dv, idx2);}, timdelay);
      }else{
        dv.style.height = '0px';
        dv.style.display='none';
        $('prefhelptr'+idx2).style.display='none';
      }
    }
  }

  function settestviewcols(){
    $('coltest').className = 'col'+prfviewcols+'container';
  }

</script>


<?

logview($page,$test,$book,$chap,$vers);

function helpicon($idx){
  global $ismobile;
  $str='<input type="image" name="help'.$idx.'" src="/i/moreinfo.png" style="padding:0 7px 7px 7px;height:'.((!$ismobile)?'1.3em':'1.7em').';border:0;cursor:pointer;" onclick="return toggleprefhelp('.$idx.')" title="click for more info" />';
  return $str;
}
?>

