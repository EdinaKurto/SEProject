function validateForm(formName) {
    var isValid = $(formName).validate({
        focusCleanup: true,
        errorElement: "em",
        rules: {
            username: "required",
            birthday: "required",
            fullname: "required",
            signup_email: {
                required: true,
                email: true
            },
            password_signup: {
                required: true,
                minlength: 2,
                maxlength: 10
            },
            repeat_password_signup: {
                required: true,
                equalTo: "#password_signup"
            }
        },
        messages: {
            username: "Please enter your username.",
            birthday: "Please enter your date of birth.",
            fullname: "Please enter your full name.",
            signup_email: {
                required: "Please enter your email address.",
                email: "Please enter a valid email address."
            },
            password_signup: {
                required: "Please provide a password.",
                minlength: "Password must be at least 2 characters long.",
                maxlength: "Password cannot exceed 10 characters."
            },
            repeat_password_signup: {
                required: "Please repeat your password.",
                equalTo: "Passwords do not match. Please try again."
            }
        }
    }).form(); 
    return isValid; 
}

var FormValidation = {
    serialize_form(form) {
        let result = {};
        $.each(form.serializeArray(), function () {
            result[this.name] = this.value;
        });
        return result;
    },

    validate: function (form_selector, form_rules, form_messages, form_submit_handler) {
        var form_object = $(form_selector);
        var error = $(".alert-danger", form_object);
        var success = $(".alert-success", form_object);

        form_object.validate({
            focusCleanup: true,
            errorElement: "em",
            rules: form_rules,
            messages: form_messages,
            submitHandler: function (form, event) {
                event.preventDefault();
                success.show();
                error.hide();
                if (form_submit_handler)
                    form_submit_handler(FormValidation.serialize_form(form_object));
            },
        });
    },
};