/**
 * @file insert mid verse header market
 */

(function()
{
  // The pasteparagraph command definition.
  var pasteMidVerseSuperscript =
  {
    exec : function( editor )
    {
      //editor.fire( 'paste', { 'text' : '[mvh]' } );
      editor.insertText('[mvs]');
      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'pastemidversesuperscript',
  {
    init : function( editor )
    {
      var commandName = 'pasteMidVerseSuperscript',
        command = editor.addCommand( commandName, pasteMidVerseSuperscript );

      editor.ui.addButton( 'PasteMidVerseSuperscript',
        {
          label : 'Insert mid-verse superscript marker',
          command : commandName,
          icon:this.path+"/midversesuperscript.png"
        });
    },
  });
})();


