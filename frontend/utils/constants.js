var Constants = {
    get_api_base_url: function () {
        if (location.hostname === "localhost" ) {
            return "http://localhost/SEProject/backend";
        } else {
            return "https://";
        }
    },
    USER_ROLE: "user",
    ADMIN_ROLE: "admin"
};