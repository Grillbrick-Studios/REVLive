<?php
  //
  // this file is the config file for ckeditor for editing commentary
  //
?>
<script>
  CKEDITOR.replace( 'commentary',
  {
    entities_greek: false,
    toolbarCanCollapse: false,
    toolbar :
    [
    { name: 'document',   items : [<?=(($superman==1)?'\'Source\',':'')?>'AutoCorrect']},
    { name: 'clipboard',  items : ['PasteFromWord','-','Undo','Redo']},
    { name: 'tools',      items : ['Maximize','Symbol','RTL','InsertWhatsNewMarker','PasteFootnote']},
    { name: 'basicstyles',items : ['Bold','Italic','Underline','Strike','Superscript','smallcapify','RemoveFormat']},
    { name: 'paragraph',  items : ['Table','Blockquote','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'lists',      items : ['NumberedList','BulletedList','Outdent','Indent']},
    { name: 'styles',     items : ['Format']},
    { name: 'links',      items : ['Link','Unlink','-','PasteTOC','PasteTOCD']},
    ],
    extraAllowedContent: 'noparse',
    width : '100%',
    height : '380'
  }
  );

  // allow paste from word to work
  CKEDITOR.on("instanceReady", function(event) {
    event.editor.on("beforeCommandExec", function(event) {
        // Show the paste dialog for the paste buttons and right-click paste
        if (event.data.name == "paste") {
            event.editor._.forcePasteDialog = true;
        }
        // Don't show the paste dialog for Ctrl+Shift+V
        if (event.data.name == "pastetext" && event.data.commandData.from == "keystrokeHandler") {
            event.cancel();
        }
    })
  });
  CKEDITOR.instances.commentary.on('change', function () {
    // operations
    try{setdirt();}catch(e){};
  });

  //alert(CKEDITOR.version);
</script>

