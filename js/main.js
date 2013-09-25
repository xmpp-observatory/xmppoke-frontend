$(document).ready(function () {
    $('.dropdown-toggle').dropdown();
    $('.my-popover').popover({
        placement: 'top',
        html: true,
        trigger: 'hover'
    }).click(function(e) {
        e.preventDefault();
    });
    jQuery.timeago.settings.allowFuture = true;
    jQuery("time.timeago").timeago();

    $('.hash-select').on('change', function(e) {
        $(this).children("option:selected").each(function () {
            $($(this).data("hash-taget")).text($(this).data("hash"));
        });
        return true;
    });

    $('.collapse-group .btn').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $collapse = $this.closest('.collapse-group').find('.collapse');
        $collapse.collapse('toggle');
    });

    $('button.pem').click(function(){
        var pem =  window.open('','PEM','width=600,height=800');
        var html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"></head><body><div class="container"><div class="span6"><pre>' + $(this).data("pem") + '</pre></div></div></body></html>';
        pem.document.open();
        pem.document.write(html);
        pem.document.close();

        return false;
    });
});
