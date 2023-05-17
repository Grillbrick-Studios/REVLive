/**
 * @file insert longdash bbcode
 */

(function()
{
  // The pastelongdash command definition.
  var pasteLongdash =
  {
    exec : function( editor )
    {
      //editor.fire( 'paste', { 'text' : '[fn]' } );
      editor.insertText( '[longdash]' );
      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'pastelongdash',
  {
    init : function( editor )
    {
      var commandName = 'pastelongdash',
        command = editor.addCommand( commandName, pasteLongdash );

      editor.ui.addButton( 'PasteLongdash',
        {
          label : 'Insert Longdash Marker',
          command : commandName,
          icon:this.path+"/longdash.png"
        });
    },
  });
})();


