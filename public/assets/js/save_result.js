$('.save-btn').click(function(){
        var formData = $(".frm").serialize();
        var url = save_result_url;

    $.ajax({
            type: "POST",
            url: url,
            data: formData,
             dataType: "json",
            success: function(data)
            {
                  showMsg(data.messages);
          }
    });
    })