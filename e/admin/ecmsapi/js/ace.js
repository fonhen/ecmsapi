require.config({
    paths: {
        "jquery": ["https://cdn.staticfile.org/jquery/1.12.4/jquery.min"],
        "ace": ["https://cdn.staticfile.org/ace/1.4.11"]
    }
});

require(['jquery' , 'ace/ace'] , function($ , ace){
    var $code = $('#code').hide();
    
    var $editor = $('<div/>').css({
        width: '100%',
        height: $code.height()
    }).insertAfter($code);
    
    //console.log($code.val());
    
    var editor = ace.edit($editor[0]);
    
    editor.setValue($code.val());
    editor.clearSelection();
    
    ace.require(['ace/mode-php'] , function(){
        editor.session.setMode("ace/mode/php");
    });
    
    if($code.data('autofocus')){
        editor.focus();
    }
    
    $('form').submit(function(){
        $code.val(editor.getValue());
        return true;
    });
});