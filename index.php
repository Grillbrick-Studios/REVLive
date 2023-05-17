<?php

//print('hello from the new REV website');
//phpinfo();
//die();

$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

  $content = array(       // page
    "viewbible.php",      //  0 // for viewing the bible
    "editverscomm.php",   //  1 // for editing a bible verse and its commentary
    "resupdate.php",      //  2 // update resources
    "srch.php",           //  3 // search the REV/commentary
    "viewcomm.php",       //  4 // for viewing commentary
    "viewverscomm.php",   //  5 // for viewing a verse and its commentary
    "editbook.php",       //  6 // for editing a bible book and its commentary
    "editornotes.php",    //  7 // manage editor notes
    "editappxintro.php",  //  8 // for editing appendices and introductions
    "prefs.php",          //  9 // preferences
    "viewbookcomm.php",   // 10 // for viewing book commentary
    "manageappxintro.php",// 11 // for creating intros/appx's and activating/ordering them
    "biblebookstatus.php",// 12 // easy viewing of book intro, outline, and locked status
    "sopslanding.php",    // 13 // SOPS landing page
    "viewappxintro.php",  // 14 // for viewing appendices and introductions
    "stats.php",          // 15 // website statistics, for logged in users only
    "manageips.php",      // 16 // for superusers to manage non-logged ips
    "sopsmanage.php",     // 17 // manage locked sops sessions
    "useredits.php",      // 18 // user edits
    "edituseredit.php",   // 19 // edit user edit
    "whatsnew.php",       // 20 // what's new
    "mapips.php",         // 21 // for mapping known ips
    "exports.php",        // 22 // for mass exports to MSWord
    "blockedips.php",     // 23 // These IPs are blocked from accessing the site
    "outline.php",        // 24 // for book outlines
    "revblog.php",        // 25 // REV blog
    "viewrevblog.php",    // 26 // view REV blog
    "editrevblog.php",    // 27 // edit REV blog
    "runsql.php",         // 28 // upload and run sql <rsw)
    "donate.php",         // 29 // donate
    "exportes.php",       // 30 // exports to other programs
    "exportgen.php",      // 31 // generate exportfiles (admin)
    "dumpzsite.php",      // 32 // website backup
    "topics.php",         // 33 // Topics
    "chronology.php",     // 34 // Chronology
    "sitemap.php",        // 35 // generate sitemap
    "resources.php",      // 36 // resources
    "resourceedit.php",   // 37 // resource edit
    "playlists.php",      // 38 // playlists
    "playlistedit.php",   // 39 // playlist edit
    "myrevmanage.php",    // 40 // manage myrev account
    "myrev.php",          // 41 // myrev stuff
    "settings.php",       // 42 // settings
    "myrevusers.php",     // 43 // myrev
    "bibliography.php",   // 44 // bibliography
    "peernotes.php"       // 45 // peernotes  // !!COULD BE TROUBLE!!
    );
require_once $docroot."/includes/pagetop.php";
require_once $docroot."/".$content[$page];
require_once $docroot."/includes/pagebot.php";
?>
