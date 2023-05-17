/**
 * @file insert mid verse header market
 */

(function()
{
  // The pasteparagraph command definition.
  var pasteMidVerseHeader =
  {
    exec : function( editor )
    {
      //editor.fire( 'paste', { 'text' : '[mvh]' } );
      editor.insertText('[mvh]');
      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'pastemidverseheader',
  {
    init : function( editor )
    {
      var commandName = 'pasteMidVerseHeader',
        command = editor.addCommand( commandName, pasteMidVerseHeader );

      editor.ui.addButton( 'PasteMidVerseHeader',
        {
          label : 'Insert mid-verse header marker',
          command : commandName,
          icon:this.path+"/midverseheader.png"
        });
    },
  });
})();


