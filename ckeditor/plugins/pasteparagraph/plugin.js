/**
 * @file insert footnote bbcode
 */

(function()
{
  // The pasteparagraph command definition.
  var pasteParagraph =
  {
    exec : function( editor )
    {
      //editor.fire( 'paste', { 'text' : '[pg]' } );
      editor.insertText('[pg]');
      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'pasteparagraph',
  {
    init : function( editor )
    {
      var commandName = 'pasteParagraph',
        command = editor.addCommand( commandName, pasteParagraph );

      editor.ui.addButton( 'PasteParagraph',
        {
          label : 'Insert in-verse paragraph marker',
          command : commandName,
          icon:this.path+"/paragraph.png"
        });
    },
  });
})();


