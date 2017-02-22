$('.upload-podcast').click(function(){
    var $button = $(this);
    $.ajax({
        url: '/save-forever',
        type: 'POST',
        data: {
            link: $button.attr('data-link'),
            name: $button.attr('data-name')
        },
        success: function(data){

        }
    });
});

$('#que-hace-link').click(function(e){
    e.preventDefault();
   $('#que-hace-list').slideToggle();
});

