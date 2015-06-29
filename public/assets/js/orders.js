
    $('.submit').click(function(){
        var formData = $(".frm-patient").serialize();
        var url = patient_submit_url

    $.ajax({
            type: "POST",
            url: url,
            data: formData,
             dataType: "json",
            success: function(data)
            {
               $('.id').val(data.id);
               $('.order_id').val(data.order_id);
               $('.id').text(data.id);
               $('.order-id').text(data.order_id);

               showMsg(data.messages);
          }
    });
    })
    $(function() {
        $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
    });
    
     $(document).ready(function() {        
       $("#search").fcbkcomplete({
                    json_url: fcbk_search_url,
                    addontab: true,                   
                    maxitems: 1,
                    height: 5,
                    cache: true,
                    width:418
                });      
    }); 
    
    function getSearch(id){
    console.log(id);
        url = pat_search_rul;
        data = {id:id};
           $.ajax({
            type: "POST",
            url: url,
            data: data,
             dataType: "json",
            success: function(data)
            {
               $('.id').val(data.id);
               $('.id').text(data.id);
               $('input[name=first_name]').val(data.first_name);
               $('input[name=last_name]').val(data.last_name);
               $('input[name=phone_number]').val(data.phone_number);
               $('input[name=address]').val(data.address);
               $('input[name=birthday]').val(data.birthday);
               $('input[name=sex]').val(data.sex);
               $('input[name=email]').val(data.email);
               $('input[name=m_r_no]').val(data.m_r_no);
               $('.order_id').val('');
               $('.order-id').text('');
               $('input:checkbox').removeAttr('checked');
          }
        });
        
        }