/**
 * @Add RTL span
 */

(function()
{
  // The RTL command definition.
  var RTL =
  {
    exec : function( editor ) {
      var selected_text = editor.getSelection().getSelectedText(); // Get Text
      var newElement = new CKEDITOR.dom.element("span");           // Make span
      newElement.setAttributes({dir: 'RTL'})                       // Set Attributes
      newElement.setText(selected_text);                           // Set text to element
      editor.insertElement(newElement);                            // Add Element
    }
  };

  // Register the plugin.
  CKEDITOR.plugins.add( 'RTL',
  {
    init : function( editor )
    {
      var commandName = 'RTL',
        command = editor.addCommand( commandName, RTL );

      editor.ui.addButton( 'RTL',
        {
          label : 'Make Hebrew RTL',
          command : commandName,
          icon:this.path+"/rtl.png"
        });
    },
  });
})();


