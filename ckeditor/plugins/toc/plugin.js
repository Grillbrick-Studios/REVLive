/**
 * @file insert footnote bbcode
 */

(function()
{
  // The pasteTOC command definition.
  var pasteTOC =
  {
    exec : function( editor )
    {
      var fragment = editor.getSelection().getNative();
      editor.insertHtml('[tocX]'+fragment+'[/tocX]');
      return true;
    }
  };
  var pasteTOCD =
  {
    exec : function( editor )
    {
      var fragment = editor.getSelection().getNative();
      editor.insertHtml('[tocdestX]'+fragment+'[/tocdestX]');
      return true;
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'toc',
  {
    init : function( editor )
    {
      var commandName = 'pasteTOC',
        command = editor.addCommand( commandName, pasteTOC );

      editor.ui.addButton( 'PasteTOC',
        {
          label : 'Insert TOC',
          command : commandName,
          icon:this.path+"/toc.png"
        });
      commandName = 'pasteTOCD',
        command = editor.addCommand( commandName, pasteTOCD );

      editor.ui.addButton( 'PasteTOCD',
        {
          label : 'Insert TOCDest',
          command : commandName,
          icon:this.path+"/tocd.png"
        });
    },
  });
})();


