/**
 * @file insert footnote bbcode
 */

(function()
{
  // The pastefootnote command definition.
  var pasteFootnote =
  {
    exec : function(editor)
    {
      // original
      //editor.insertText( '[fn]' );

      // new
      var dummy = editor.document.createElement('span');
      dummy.appendText('[xx]');
      editor.insertElement(dummy);
      editor.insertText( '[fn]' );

      var content = editor.getData();
      var tmp = ' '+content.substring(0, content.indexOf('[xx]'));
      var fnidx = 0;
      while(tmp.indexOf('[fn]')>0){
        tmp = tmp.replace('[fn]', '');
        fnidx++;
      }

      dummy.remove();
      //alert(fnidx);

      //alert(editor.name);
      var fntyp = ((editor.name=='commentary')?'com':'vrs'); // how to tell whether com or vrs?
      $('div'+fntyp+'footnotes').style.display='block';
      addfootnote(fntyp, fnidx);

      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'pastefootnote',
  {
    init : function( editor )
    {
      var commandName = 'pastefootnote',
        command = editor.addCommand( commandName, pasteFootnote );

      editor.ui.addButton( 'PasteFootnote',
        {
          label : 'Insert Footnote Marker',
          command : commandName,
          icon:this.path+"/footnote_edit3.png"
        });
    },
  });
})();


