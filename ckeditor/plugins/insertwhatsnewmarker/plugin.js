/**
 * @file insertwhatsnewmarker
 */

(function()
{
  // The pasteparagraph command definition.
  var insertWhatsNewMarker =
  {
    exec : function( editor )
    {
      //editor.fire( 'paste', { 'html' : '~~~' } );
      editor.insertText('~~~');
      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'insertwhatsnewmarker',
  {
    init : function( editor )
    {
      var commandName = 'insertWhatsNewMarker',
        command = editor.addCommand( commandName, insertWhatsNewMarker );

      editor.ui.addButton( 'InsertWhatsNewMarker',
        {
          label : 'Insert What\'s New marker',
          command : commandName,
          icon:this.path+"/whatsnewmarker.png"
        });
    },
  });
})();


