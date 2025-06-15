$(document).ready(function () {
    function toggleHeaderFooter() {
        const hash = window.location.hash; 
        if (hash === "#login" || hash === "#signin") {
            $("header, footer").hide();
        } else {
            $("header, footer").show();
        }
    }
    toggleHeaderFooter();
    $(window).on("hashchange", function () {
        toggleHeaderFooter();
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll(".nav-link");

    function updateActiveLink() {
        const currentPage = window.location.hash || "#dashboard";
        navLinks.forEach(link => {
            link.classList.remove("active");
            if (link.getAttribute("href") === currentPage) {
                link.classList.add("active");
            }
        });
    }

    updateActiveLink();
    window.addEventListener("hashchange", updateActiveLink);
});

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