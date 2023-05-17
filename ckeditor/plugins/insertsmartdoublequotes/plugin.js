/**
 * @file insert smart double quotes
 */

(function()
{
  // The pasteparagraph command definition.
  var insertSmartDoubleQuotes =
  {
    exec : function( editor )
    {
      //editor.fire( 'paste', { 'html' : '&ldquo;&rdquo;' } );
      editor.insertHtml( '&ldquo;&rdquo;' );
      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'insertsmartdoublequotes',
  {
    init : function( editor )
    {
      var commandName = 'insertSmartDoubleQuotes',
        command = editor.addCommand( commandName, insertSmartDoubleQuotes );

      editor.ui.addButton( 'InsertSmartDoubleQuotes',
        {
          label : 'Insert smart double quotes',
          command : commandName,
          icon:this.path+"/midverseheader.png"
        });
    },
  });
})();


