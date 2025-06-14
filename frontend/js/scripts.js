// app.route({
//     view: "login",
//     load: "login.html",
//     onCreate: function () {},
//     onReady: function () {},
// });
// var app = $.spapp({
//     defaultView: "#login",
//     templateDir: "../views/",
//     pageNotFound: "error_404",
//     reloadView: true,
// });

// loginForm = function () {
//     FormValidation.validate("login-form", {
//             email: {
//                 required: true,
//                 email: true
//             },
//             password: {
//                 required: true,
//                 minlength: 3
//             }
//         },
//         {
//             email: {
//                 required: "Please enter your email address.",
//                 email: "Please enter a valid email address."
//             },
//             password: {
//                 required: "Please provide a password.",
//                 minlength: "Password must be at least 2 characters long.",
//                 maxlength: "Password cannot exceed 10 characters."
//             }
//         }, function (data) {
//         Utils.block_ui("#login-form");
//         RestClient.post(
//             "login",
//             data,
//             function (response) {
//                 window.localStorage.setItem("token", response.token);
//                 window.localStorage.setItem("user_id", response.id);
//                 window.localStorage.setItem("user", response.first_name);
//                 Utils.unblock_ui("login-button");
//                 toastr.success("You logged in successfully.");
//                 window.location.hash = "#profile";
//             },
//             function (error) {
//                 toastr.error("Error occurred while logging into your account.");
//             }
//         );
//         Utils.unblock_ui("#login-form")
//     });
// };
$(document).ready(function () {
    function toggleHeaderFooter() {
        const hash = window.location.hash; // Get the current URL hash
        if (hash === "#login" || hash === "#signin") {
            $("header, footer").hide();
        } else {
            $("header, footer").show();
        }
    }

    // Run on page load
    toggleHeaderFooter();

    // Run when navigation occurs
    $(window).on("hashchange", function () {
        toggleHeaderFooter();
    });
});



document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll(".nav-link");

    function updateActiveLink() {
        const currentPage = window.location.hash || "#dashboard"; // Default to dashboard
        navLinks.forEach(link => {
            link.classList.remove("active");
            if (link.getAttribute("href") === currentPage) {
                link.classList.add("active");
            }
        });
    }

    // Update active link when page loads
    updateActiveLink();

    // Listen for hash changes (SPA navigation)
    window.addEventListener("hashchange", updateActiveLink);
});

/* function display_user_profile() {

    RestClient.get("users/current", function(response) {
        console.log("User Data:", response); // Debugging
  
        // Update Profile Picture (Use default if null)
        let profileImg = document.querySelector("#profile img");
        profileImg.src = response.image
    ? "http://localhost/web_project/backend" + response.image
    : "assets/images/ava3.webp";
  
        // Update Profile Information in the card
        document.querySelector("#profile h5").textContent = response.name || "N/A";
        document.querySelector("#profile p.text-muted.mb-4").textContent = (Number(response.role_id) === 1 ? "Customer" : "Administrator");
        
        // Update Detailed Profile Information
        let profileFields = document.querySelectorAll("#profile .col-sm-9 p");
        profileFields[0].textContent = response.name || "N/A"; // Full Name
        profileFields[1].textContent = response.username || "N/A"; // Username
        profileFields[2].textContent = response.email || "N/A"; // Email
        profileFields[3].textContent = response.date_of_birth || "N/A"; // Date of Birth
        profileFields[4].textContent = response.address || "N/A"; // Address
  
        // Update Edit Modal Form Fields
        document.querySelector("#edit_name").value = response.name || "";
        document.querySelector("#edit_username").value = response.username || "";
        document.querySelector("#edit_email").value = response.email || "";
        document.querySelector("#edit_date_of_birth").value = response.date_of_birth || "";
        document.querySelector("#edit_address").value = response.address || "";
  
    }, function(error) {
        console.error("Error fetching user data:", error);
    });
  
  } */

  function displaySelectedImage(event, elementId) {
    const selectedImage = document.getElementById(elementId);
    const fileInput = event.target;

    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            selectedImage.src = e.target.result;
        };

        reader.readAsDataURL(fileInput.files[0]);
    }
}

$(document).on('change', '.order-status-dropdown', function () {
  const orderId = $(this).data('order-id');
  const newStatusId = $(this).val();
  OrderService.updateOrderStatus(orderId, newStatusId);
});

$(document).off('click', '.delete-order-btn').on('click', '.delete-order-btn', function () {
  const orderId = $(this).data('order-id');
  OrderService.openDeleteConfirmationDialog(orderId);
});

/* document.querySelector('#edit_profile_form button.btn').addEventListener('click', function () {
    const formData = new FormData();
    const imageInput = document.querySelector("#profile_picture");

    if (imageInput.files.length > 0) {
        formData.append("profile_picture", imageInput.files[0]);

        fetch("http://localhost/web_project/backend/rest/users/upload_image", {
            method: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("token")
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                // Update user image visually
                document.querySelector("#profile img").src = data.image_url;
                toastr.success("Profile picture updated!");
                display_user_profile(); // Refresh user info
            } else {
                toastr.error("Image upload failed.");
            }
        })
        .catch(err => {
            console.error("Upload error:", err);
            toastr.error("Something went wrong while uploading image.");
        });
    } else {
        toastr.warning("Please choose an image file first.");
    }
}); */
