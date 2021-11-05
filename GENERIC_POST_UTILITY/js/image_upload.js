jQuery( document ).ready(function($) {
    
//    var field_copy = $('.GENERIC_image_repeater.hidden')
//        .clone().show().removeClass( 'hidden' );
//    $('.GENERIC_image_repeater.hidden').remove();
    
//    global repeat_fields
    
    console.log('image_upload.js');
    console.log( repeat_fields );
    
    /*
     * Select/Upload image(s) event
     */
    $('body').on('click', '.GENERIC_image_button', function(){

        var button = $(this);
        repeat_id = $(this).data( 'repeat_id' );

        var custom_uploader = wp.media({
            title: 'Add Image(s)',
            library : {
                // uncomment the next line if you want to attach image to the current post
                // uploadedTo : wp.media.view.settings.post.id, 
                type : 'image'
            },
            button: {
                text: 'Use Image(s)' // button label text
            },
            multiple: true
        });
        
        custom_uploader.on('select', function() { // it also has "open" and "close" events 
            
            var attachments = custom_uploader.state().get('selection');

            attachments.each(function(attachment) {
                
                var img_url = attachment['attributes']['sizes']['medium']['url'];
                var attachment_id = attachment['id'];

                if( ! attachment_id ) return;

                //var new_copy = $(field_copy).clone();
                var new_field = $( repeat_fields[ repeat_id ] ).clone();
                $( new_field )
                    .find( '.GENERIC_display_image' )
                    .html('<img class="GENERIC_image" src="' + img_url + '" style="" />');

                $( new_field )
                    .find('.GENERIC_image_id')
                    .val( attachment_id );

                $( button )
                    .closest( '.GENERIC_upload_field' )
                    .find( '.GENERIC_repeater_wrap' )
                    .append( new_field );

            });
            
        });

        custom_uploader.open();
        
    });

    $('body').on('click', '.GENERIC_remove_image', function(){
        $(this)
            .closest( '.GENERIC_image_repeater' )
            .remove();
    });


});
