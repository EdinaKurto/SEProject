var RestClient = {
  get: function (url, callback, error_callback) {
    let token = localStorage.getItem("token");
    $.ajax({
      url: Constants.get_api_base_url() + '/' + url,
      type: "GET",
      headers: {
        Authentication: token,
      },
      success: function (response) {
        if (callback) callback(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {

          RestClient.handleErrorResponse(jqXHR);
        
      },
    });
  },
  request: function (url, method, data, callback, error_callback) {
    let token = localStorage.getItem("token");
    $.ajax({
      url: Constants.get_api_base_url() + '/' + url,
      type: method,
      headers: {
        Authentication: token,
      },
      data: data,
    })
      .done(function (response, status, jqXHR) {
        if (callback) callback(response);
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        if (error_callback) {
          error_callback(jqXHR);
        } else {
          RestClient.handleErrorResponse(jqXHR);
        }
      });
  },
  post: function (url, data, callback, error_callback) {
    // method used for creating a new entity
    RestClient.request(url, "POST", data, callback, error_callback);
  },
  delete: function (url, data, callback, error_callback) {
    // method used for deleting an entity
    RestClient.request(url, "DELETE", data, callback, error_callback);
  },
  put: function (url, data, callback, error_callback) {
    //  method used for updating an entity
    RestClient.request(url, "PUT", data, callback, error_callback);
  },
  patch: function (url, data, callback, error_callback) {
    // method used for updating an entity
    RestClient.request(url, "PATCH", data, callback, error_callback);
  },
  handleErrorResponse: function(jqXHR) {
    if (jqXHR.status === 401) {
      window.location.hash = "#error_page"; // Navigate to 401.html section
    } else if (jqXHR.status === 403) {
      window.location.hash = "#error_page"; // Navigate to 403.html section
    } else {
      toastr.error(jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : "An error occurred.");
    }
  },
  uploadFile: function (url, formData, callback, error_callback) {
    let token = localStorage.getItem("token");
    $.ajax({
        url: Constants.get_api_base_url() + '/' + url,
        type: "POST",
        headers: {
            Authentication: token,
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (callback) callback(response);
        },
        error: function (jqXHR) {
            if (error_callback) error_callback(jqXHR);
            else toastr.error("Image upload failed.");
        }
    });
  }
};