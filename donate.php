<?php
if(!isset($page)) die('unauthorized access');
?>
<span class="pageheader">Donate to the REV Project</span>
<div style="margin:0 auto;max-width:720px;font-size:90%;">
<?if(!$ismobile){?>
<img src="/i/Donate.png" style="max-width:100%;" alt="Donate to the REV" border="0">
<?}?>
<p>The REV translation combines current scholarship and textual studies from the original languages to deliver an accurate rendering of the text while also being sensitive to the need for optimal readability in modern English. This version is translated from a Biblical Unitarian perspective and includes a commentary explaining translation decisions and the interpretation of many difficult Bible passages.</p>
<p>Our mission in providing this translation to the world free-of-charge is that we hope it brings you greater appreciation and insight into the Scriptures, helping you to increase in the knowledge of God's will.</p>
<p>For donations from outside of the United States, click the button below.<br /><br />
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=QW68ZTJC72C6W&amp;source=url" target="_blank" style="border-radius:9px;background-color:#078843;color:white;padding:10px;">International Donations</a>
</p>
<iframe id="form-db4f17eb-48dc-4725-967d-9b1c2db6c88a" class="blackbaud-donation-form" title="Donation Form"
        style="background-color: white; max-width: 600px; min-width: 320px; min-height: 1200px; width: 100%; height: 100%; border: none;">
</iframe>
<script>
    // chance's form
    var url = 'https://host.nxt.blackbaud.com/donor-form?formId=db4f17eb-48dc-4725-967d-9b1c2db6c88a&envid=p-N5TCmu6znEqFATYRSBKtsg';
    //var url = 'https://host.nxt.blackbaud.com/donor-form?svcid=renxt&formId=db4f17eb-48dc-4725-967d-9b1c2db6c88a&envid=p-N5TCmu6znEqFATYRSBKtsg';

    // michelle's form
    //var url = 'https://host.nxt.blackbaud.com/donor-form?svcid=renxt&formId=f3ac0903-8bd7-4fe1-b244-072aaded43a5&envid=p-N5TCmu6znEqFATYRSBKtsg';

    var iframe = document.getElementById('form-db4f17eb-48dc-4725-967d-9b1c2db6c88a');
    var bbemlParser = new RegExp('[?&]bbeml=([^&#]*)').exec(document.location.search);
    var bbeml = bbemlParser ? decodeURI(bbemlParser[1]) || 0 : '';
    iframe.src = url + '&referral=' + document.referrer + '&bbeml=' + bbeml;
</script>

<p>From all of us here at Spirit & Truth Fellowship, we appreciate your interest in the Revised English Version; and it is our sincere desire that this translation serves you in your continued walk with the Lord.</p>
</div>
<br />
<div style="margin:0 auto;max-width:280px;">
<p class="spc"><small><strong>Spirit &amp; Truth</strong><br />
<a rel="noreferrer noopener" href="http://stfonline.org/" target="_blank">http://stfonline.org</a><br />
STF@STFonline.org<br />
765-349-2330<br />
P.O. Box 1737<br />
Martinsville, Indiana 46151 US</small></p></div>

<?
logview($page,0,0,0,0);
?>
