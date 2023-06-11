<?php
defined('EmpireCMSAdmin') or die;
?>
<script type="text/javascript" src="https://cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/ace/1.18.0/ace.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/ace/1.18.0/ext-language_tools.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/ace/1.18.0/mode-php.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/ace/1.18.0/snippets/php.min.js"></script>

<script>
    var $code = $('#code').hide();

    var $editor = $('<div/>').css({
        width: '100%',
        height: $code.height()
    }).insertAfter($code);

    var editor = ace.edit($editor[0] , {
        fontSize: 12,
        //highlightActiveLine: false,
        printMargin: false,
        showGutter: true,
        wrap: true,
        enableMultiselect: false
    });

    editor.container.style.lineHeight = 1.5;

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

</script>
