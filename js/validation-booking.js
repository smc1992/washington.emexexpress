$(document).ready(function(){
    $('#logistics_booking_form').on('submit', function(e){
        e.preventDefault(); // Stop default submission

        var error = false;

        // Grab all form values from multi-step form
        var formData = {
            // Step 1: Shipment Details
            origin: $('#origin').val().trim(),
            destination: $('#destination').val().trim(),
            service: $('#service').val().trim(),
            goods_type: $('#goods_type').val().trim(),
            package_type: $('#package_type').val().trim(),
            dimension_unit: $('input[name="dimension_unit"]:checked').val(),
            weight_unit: $('input[name="weight_unit"]:checked').val(),
            goods_value: $('#goods_value').val().trim(),
            
            // Dynamic cargo dimensions (multiple packages)
            // Collect all package data (up to 10 packages)
        };

        // Add dynamic package data
        for (var i = 1; i <= 10; i++) {
            if ($('#pieces_' + i).length && $('#pieces_' + i).val()) {
                formData['pieces_' + i] = $('#pieces_' + i).val().trim();
                formData['length_' + i] = $('#length_' + i).val().trim();
                formData['width_' + i] = $('#width_' + i).val().trim();
                formData['height_' + i] = $('#height_' + i).val().trim();
                formData['weight_' + i] = $('#weight_' + i).val().trim();
                formData['weight_type_' + i] = $('input[name="weight_type_' + i + '"]:checked').val();
                formData['stackable_' + i] = $('#stackable_' + i).is(':checked') ? 'Yes' : 'No';
                formData['turnable_' + i] = $('#turnable_' + i).is(':checked') ? 'Yes' : 'No';
            }
        }

        // Step 2: Shipper Information
        formData.shipper_name = $('#shipper_name').val().trim();
        formData.shipper_email = $('#shipper_email').val().trim();
        formData.shipper_phone = $('#shipper_phone').val().trim();
        formData.shipper_address = $('#shipper_address').val().trim();
        formData.shipper_city = $('#shipper_city').val().trim();
        formData.shipper_country = $('#shipper_country').val().trim();

        // Step 3: Receiver Information
        formData.receiver_name = $('#receiver_name').val().trim();
        formData.receiver_email = $('#receiver_email').val().trim();
        formData.receiver_phone = $('#receiver_phone').val().trim();
        formData.receiver_address = $('#receiver_address').val().trim();
        formData.receiver_city = $('#receiver_city').val().trim();
        formData.receiver_country = $('#receiver_country').val().trim();

        // Step 4: Logistics Options
        formData.transport_mode = $('#transport_mode').val();
        formData.incoterms = $('#incoterms').val();
        formData.delivery_speed = $('#delivery_speed').val();
        formData.insurance = $('input[name="insurance"]:checked').val();
        
        // Special handling (multiple checkboxes)
        var specialHandling = [];
        $('input[name="special[]"]:checked').each(function() {
            specialHandling.push($(this).val());
        });
        formData.special = specialHandling.join(', ');

        // Step 5: Schedule & Payment
        formData.pickup_date = $('#pickup_date').val();
        formData.pickup_time = $('#pickup_time').val();
        formData.delivery_deadline = $('#delivery_deadline').val();
        formData.payment_method = $('#payment_method').val();
        formData.billing_address = $('#billing_address').val().trim();
        formData.notes = $('#notes').val().trim();
        formData.agree_terms = $('#agree_terms').is(':checked');

        // Reset errors on click
        $('#shipper_name,#shipper_email,#shipper_phone').on('click', function(){
            $(this).removeClass("error_input");
        });

        // Validation
        if(formData.shipper_name.length === 0){
            error = true;
            $('#shipper_name').addClass("error_input");
        }

        if(formData.shipper_email.length === 0 || formData.shipper_email.indexOf('@') === -1){
            error = true;
            $('#shipper_email').addClass("error_input");
        }

        if(formData.shipper_phone.length === 0){
            error = true;
            $('#shipper_phone').addClass("error_input");
        }

        // Terms validation
        if(!formData.agree_terms){
            error = true;
            $('#agree_terms').addClass("error_input");
        }

        // If no error, submit via API
        if(error === false){
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.attr({'disabled' : true}).text('Sending...');

            // Use BookingAPI to submit
            const bookingAPI = new BookingAPI();
            
            bookingAPI.submitBooking(formData).then(function(result) {
                if(result.success) {
                    submitBtn.text('Success ✓');
                    $('#success_message').fadeIn(500);
                    
                    // Show booking ID to user
                    if(result.booking_id) {
                        $('#success_message').html(
                            '<strong>✅ Booking Submitted Successfully!</strong><br>' +
                            'Booking ID: <strong>' + result.booking_id + '</strong><br>' +
                            'We will contact you within 24 hours with your quote.'
                        );
                    }
                    
                    // Reset form after 3 seconds
                    setTimeout(function() {
                        $('#logistics_booking_form')[0].reset();
                        submitBtn.removeAttr('disabled').text('Request Quote');
                    }, 3000);
                    
                } else {
                    $('#error_message').html(
                        '<strong>❌ Submission Failed</strong><br>' +
                        result.message + '<br>' +
                        'Please try again or contact us directly at ops@emexexpress.de'
                    ).fadeIn(500);
                    submitBtn.removeAttr('disabled').text('Request Quote');
                }
            }).catch(function(error) {
                $('#error_message').html(
                    '<strong>❌ Network Error</strong><br>' +
                    'Unable to connect to our servers. Please check your connection and try again.'
                ).fadeIn(500);
                submitBtn.removeAttr('disabled').text('Request Quote');
            });
        }
    });
});
