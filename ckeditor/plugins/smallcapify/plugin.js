/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.plugins.add( 'smallcapify', {
  icons: 'smallcapify',
  hidpi: true,
  init: function( editor ) {
    var order = 0;
    // All buttons use the same code to register. So, to avoid
    // duplications, let's use this tool function.
    var addButtonCommand = function( buttonName, buttonLabel, commandName, styleDefiniton ) {
        // Disable the command if no definition is configured.
        if ( !styleDefiniton ){
          alert('here');
          return;
        }

        var style = new CKEDITOR.style( styleDefiniton ),
          forms = contentForms[ commandName ];

        // Put the style as the most important form.
        forms.unshift( style );

        // Listen to contextual style activation.
        editor.attachStyleStateChange( style, function( state ) {
          !editor.readOnly && editor.getCommand( commandName ).setState( state );
        } );

        // Create the command that can be used to apply the style.
        editor.addCommand( commandName, new CKEDITOR.styleCommand( style, {
          contentForms: forms
        } ) );

        // Register the button, if the button plugin is loaded.
        if ( editor.ui.addButton ) {
          editor.ui.addButton( buttonName, {
            label: buttonLabel,
            command: commandName,
            toolbar: 'basicstyles,' + ( order += 10 )
          } );
        }
      };

    var contentForms = {
        smallcapify: [
          'sub',
          [ 'span', function( el ) {
            return el.styles['font=size']=='110%'; // I do not understand this.
          } ]
        ]
      },
      config = editor.config;

    addButtonCommand( 'smallcapify', 'Render as small caps', 'smallcapify', config.coreStyles_subscript );
    editor.setKeystroke( [
      [ CKEDITOR.CTRL + 83 /*S*/, 'smallcapify' ]
    ] );
  }
} );

// Basic Inline Styles.
//CKEDITOR.config.coreStyles_subscript = { element: 'span', overrides: 'sub' };
CKEDITOR.config.coreStyles_subscript = {
  element: 'span',
  styles: { 'font-size':'110%', 'font-variant': 'small-caps' },
  overrides: 'sub'
};

