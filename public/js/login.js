$(document).ready(function() {
    $('form[name="login"]').on('submit', function(e){
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: '/login',
            data: $(this).serialize(),
            success: function(data) {
                if(data.result == 0) {
                    $('.js-error').html('<div class="alert alert-danger">'+data.message+'</div>');
                } else {
                    window.location = data.redirectTo;
                }
            },
            error: function (xhr, desc, err) 
            {
                console.log("error");
            }
        });
    })
});