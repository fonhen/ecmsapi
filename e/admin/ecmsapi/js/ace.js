require.config({
    paths: {
        "jquery": ["https://cdn.staticfile.org/jquery/1.12.4/jquery.min"],
        "ace": ["https://cdn.staticfile.org/ace/1.4.11"]
    }
});

require(['jquery' , 'ace/ace' , 'ace/ext-language_tools' , 'ace/mode-php' , 'ace/snippets/php'] , function($ , ace){
    var $code = $('#code').hide();
    
    var $editor = $('<div/>').css({
        width: '100%',
        height: $code.height()
    }).insertAfter($code);
    
    var editor = ace.edit($editor[0]);
    
    editor.setValue($code.val());
    editor.clearSelection();
    
    setTimeout(function(){
        editor.session.setMode("ace/mode/php");
        editor.setOptions({
            enableBasicAutocompletion: true,
            enableSnippets: true,
            enableLiveAutocompletion: true
        });
    } , 100);
    
    editor.commands.addCommand({
        name: 'save',
        bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
        exec: function(editor) {
            $('form').submit();
        },
        readOnly: true
    });
    
    
    if($code.data('autofocus')){
        editor.focus();
    }
    
    $('form').submit(function(){
        $code.val(editor.getValue());
        return true;
    });
});