jQuery(document).ready(function ($) {
    if (formidable2rdb) {
        if (formidable2rdb.message && formidable2rdb.message.message && formidable2rdb.message.type) {
            var $view = '<div data-notify="container" class="col-xs-11 col-sm-3 alert-fmj alert-{0}" role="alert">' +
                '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
                '<span data-notify="icon"></span> ' +
                '<span data-notify="title">{1}</span> ' +
                '<span data-notify="message">{2}</span>' +
                '<div class="progress" data-notify="progressbar">' +
                '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                '</div>' +
                '<a href="{3}" target="{4}" data-notify="url"></a>' +
                '</div>';
            if( formidable2rdb.view){
                $view = formidable2rdb.view;
            }
            var notification_content = {
                message: formidable2rdb.message.message
            };
            if (formidable2rdb.message.title) {
                notification_content["title"] = formidable2rdb.message.title;
            }
            $.notify(notification_content, {
                type: formidable2rdb.message.type,
                newest_on_top: true,
                animate: {
                    enter: 'animated fadeInRight',
                    exit: 'animated fadeOutRight'
                },
                placement: {
                    from: 'top',
                    align: 'right'
                },
                offset: {
                    x: 50,
                    y: 60
                },
                template: $view,
                z_index: 999999,
                delay: 5000
            });
        }
    }
});