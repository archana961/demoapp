$(document).ready(function() {
    //check email exist/not
    $('#user_email').on('blur', function(e){
        $('.js-email-error').remove();
        $.ajax({
            type: 'POST',
            url: '/check_email',
            data: {'email': $('#user_email').val()},
            success: function(data) {
                if(data.result == 0) {
                    $($('form[name="user"]').find('[name*="email"]')[0]).before('<p class="js-error js-email-error"><span class="alert alert-danger">'+data.message+'</span></p>');
                } 
            },
            error: function (xhr, desc, err){
                console.log("error");
            }
        });
    });

    $('form[name="user"]').on('submit', function(e){
        e.preventDefault();
        let data = new FormData();
        $(this).serializeArray().forEach((object)=>{
            data.append(object.name, object.value);
        });
        if($('#user_profileFile')[0].files[0] != undefined){
            data.append('user[profileFile]', $('#user_profileFile')[0].files[0]);
        }
        $.ajax({
            type: 'POST',
            url: '/registration',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function(data) {
                $('.js-error').remove();
                if(data.result == 0) {
                    for (var key in data.data) {
                        $($('form[name="user"]').find('[name*="'+key+'"]')[0]).before('<p class="js-error"><span class="alert alert-danger">'+data.data[key]+'</span></p>');
                    }
                } else {
                    new swal({
                        text: 'Registered successfully!',
                        type: 'success'
                    }).then(function() {
                        window.location = data.redirectTo;
                    });
                }
            },
            error: function (xhr, desc, err) {
                console.log("error");
            }
        });
    });

});