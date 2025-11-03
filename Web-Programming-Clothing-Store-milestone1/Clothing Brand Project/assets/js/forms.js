var contactInquiries = [];


$("#contact-form").validate({
    rules: {
        name: "required",
        email: {
            required: true,
            email: true
        },
        subject: "required",
        message: "required"
    },
    messages: {
        name: "Please enter your name",
        email: "Please enter a valid email address",
        subject: "Please enter a subject",
        message: "Please enter your message"
    },
    submitHandler: function(form, event) {
        event.preventDefault();
        // Assuming blockUI and unblockUI are similar UI feedback functions for processing.
        blockUI("#contact-message");

        let data = serializeContactForm(form);
        contactInquiries.push(data);
        $("#contact-form")[0].reset(); // Reset form after submission
        console.log(contactInquiries); // For debugging purposes

        unblockUI("#contact-message");
    }
});

serializeContactForm = (form) => {
    let result = {};
    $.each($(form).serializeArray(), function() {
        result[this.name] = this.value; 
    });
    return result;
};
