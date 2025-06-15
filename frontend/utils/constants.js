var Constants = {
    get_api_base_url: function () {
        if (location.hostname === "localhost" ) {
            // Correct backend API path for local development 
            return "http://localhost/SEProject/backend";
        } else {
            // Update this for production deployment if needed
            return "/SEProject/backend";
        }
    },
    USER_ROLE: "user",
    ADMIN_ROLE: "admin"
};

// Enhanced error logging for debugging
window.addEventListener('error', function(event) {
    console.error('Global JS Error:', event.message, 'at', event.filename + ':' + event.lineno);
});
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled Promise rejection:', event.reason);
});