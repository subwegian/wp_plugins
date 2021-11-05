jQuery( document ).ready(function($) {
    
    console.log('repeater_field.js');
    
    //FROM: https://stackoverflow.com/questions/3919928/tinymce-instances-in-jquery-sortable#answer-45379894
    $( '.GENERIC_repeater_wrap.GENERIC_sortable' ).sortable({
        handle: '.GENERIC_sort_handle',
        stop: function (e, ui) {
          $(ui.item).find('textarea').each(function () {
             tinymce.execCommand('mceRemoveEditor', false, $(this).attr('id'));
             tinymce.execCommand('mceAddEditor', true, $(this).attr('id'));
          });
        }
    });
    
    $(document).on('tinymce-editor-setup', function (event, editor) {
      editor.settings.toolbar1 = 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,fullscreen';
    });
    
    // global
    repeat_fields = {};
    $('.GENERIC_repeater_wrap .GENERIC_repeater.hidden')
            .each( function() {
                var repeat_id = $(this).data( 'repeat_id' );
                //console.log( repeat_id );
                repeat_fields[ repeat_id ] = $(this)
                    .clone()
                    .show()
                    .removeClass( 'hidden' );
                $(this).remove();
            });
    

    // FROM: https://www.riklewis.com/2020/05/adding-a-custom-wordpress-editor-via-javascript/
    var repeat_num = 1;
    $('body').on('click', '.GENERIC_repeater_button', function(){
        repeat_id = $(this).data( 'repeat_id' );
        var new_field = $( repeat_fields[ repeat_id ] ).clone();
        var wysiwyg_id = $(new_field)
            .find('.wp-editor-wrap')
            .find('textarea')
            .attr('id');
        var wysiwyg_name = $(new_field)
            .find('.wp-editor-wrap')
            .find('textarea')
            .attr('name');
        
        console.log( new_field );
        console.log( repeat_fields );

        if( wysiwyg_id ) {
            var new_id = wysiwyg_id+'_'+repeat_num;
            var new_textarea = '<textarea class="wp-editor-area" name="'+wysiwyg_name+'" id="'+new_id+'"></textarea>';
            repeat_num++;
            $( new_field )
                .find('.wp-editor-wrap')
                .replaceWith( new_textarea );
            $( '.'+repeat_id ).append( new_field );
            wp.editor.initialize( new_id, {
                wpautop: true,
                media_buttons: false,
                textarea_rows: 5,
                tinymce: true,
                quicktags: {
                    buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
                }
            });
        } else {
            console.log( 'it is undefined' );
            $( '.'+repeat_id ).append( new_field );
        }
    });

    $('body').on('click', '.GENERIC_remove_button', function(){
        $(this)
            .closest( '.GENERIC_repeater' )
            .remove();
    });

});
