jQuery(document).ready(function ($) {
    let $form = $('.piloboard-form-save-licence');
    $form.on('submit', function (e) {
        let $licence = $form.find('#piloboard_licence').val();
        e.preventDefault();
        $.post({
            url: admin.ajax_url,
            data: {
                action: "send_licence",
                licence: $licence
            },
            success: function (response) {
                window.location.replace(document.location.origin + "/wp-admin");
            },
        });
    })
    $('#piloboard-faq .box-item .title').click(function(){
        $(this).parent().find('.content').slideToggle('open');
        $(this).find('.picture').toggleClass('open');
    })
});