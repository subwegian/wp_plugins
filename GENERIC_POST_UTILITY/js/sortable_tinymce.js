
var this_id = '';

jQuery( document ).ready(function($) {

    //FROM: https://stackoverflow.com/questions/3919928/tinymce-instances-in-jquery-sortable#answer-45379894
    $( '.meta-box-sortables' ).sortable({

        start: function(e, ui) {
            $(this).find('.GENERIC_tinymce').each(function () {
                this_id = $( this ).attr('id');
                tinyMCE.execCommand( 'mceRemoveEditor', false, this_id );
            });
        },
        stop: function(e,ui) {
            $(this).find('.GENERIC_tinymce').each(function () {
                tinyMCE.execCommand( 'mceAddEditor', true, this_id );
            });
        }

    });
});


// THIS ALSO WORKS

// jQuery( document ).ready(function($) {

//     console.log( 'it is running' );
    
//     //FROM: https://stackoverflow.com/questions/3919928/tinymce-instances-in-jquery-sortable#answer-45379894
//     $( '.meta-box-sortables' ).sortable({
//         stop: function (e, ui) {
//             $(this).find('.GENERIC_tinymce').each(function () {
//                 var this_id = $( this ).attr('id');

//                 tinyMCE.get( this_id ).destroy();
//                 eval($(this).parents('.item').find('script').html());
//                 $(this).closest('.tmce-active').find('.switch-tmce').trigger('click');
//             });
//         }
//     });
//     console.log( 'GENERIC-admin-script' );
// });

