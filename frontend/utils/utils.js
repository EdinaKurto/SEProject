const Utils = {
    init_spapp: function () {
        var app = $.spapp({
            defaultView: "#dashboard",
            templateDir: "views/"

        });

        app.route({
            view: "profile",
            onReady: function () {
                UserService.get_user();
                ProductService.handleNavbarSearch();
                OrderService.getUserOrders();
                UserService.updateDashboardLinkBasedOnRole();
            }
          });
          
        app.run();
        
      app.route({
        view: "product",
        onReady: function () {
          UserService.updateDashboardLinkBasedOnRole();
          ProductService.renderProductDetails();
          ProductService.handleNavbarSearch();

          // Always reset button listeners to avoid duplicates
          function resetButtonListeners(id) {
            const oldBtn = document.getElementById(id);
            if (!oldBtn) return;
            const newBtn = oldBtn.cloneNode(true);
            oldBtn.parentNode.replaceChild(newBtn, oldBtn);
            return newBtn;
            
          }

          // Add to Cart
          const addToCartBtn = resetButtonListeners("addToCartBtn");
          if (addToCartBtn) {
            addToCartBtn.addEventListener("click", function () {
              const quantity = parseInt(document.getElementById("quantity").value);
              if (!quantity || quantity < 1) {
                toastr.warning("Please enter a valid quantity.");
                return;
              }
              CartService.addToCartFromLocal(quantity);
              $.spapp('navigate', '#cart');
            });
          }

          // Add to Wishlist
          const addToWishlistBtn = resetButtonListeners("addToWishlistBtn");
          if (addToWishlistBtn) {
            addToWishlistBtn.addEventListener("click", function () {
              const quantity = parseInt(document.getElementById("quantity").value);
              if (!quantity || quantity < 1) {
                toastr.warning("Please enter a valid quantity.");
                return;
              }
              const productId = localStorage.getItem("product_id");
              WishlistService.addToWishlist(productId, quantity);
              $.spapp('navigate', '#wishlist');
            });
          }
        }
      });


        app.route({
          view: "admin_dashboard",
          onReady: function () {
            UserService.updateDashboardLinkBasedOnRole();
            ProductService.handleNavbarSearch();
            ProductService.init();
            ProductService.getAllProducts();
            OrderService.getAllOrders();
          }
        });

        app.route({
          view: "dashboard",
          onReady: function () {
            UserService.updateDashboardLinkBasedOnRole();
            ProductService.handleNavbarSearch();
            ProductService.renderDashboardProducts();
            ProductService.loadUserProductViews();
            ProductService.loadDashboardSummary(); 
          }
        });

        app.route({
          view: "browse",
          onReady: function () {
            UserService.updateDashboardLinkBasedOnRole();
            ProductService.handleNavbarSearch();
            ProductService.renderCategoryCheckboxes();
            const searchInput = document.getElementById("navbar-search-input");
            const searchTerm = localStorage.getItem("products_search_term") || "";
            localStorage.removeItem("products_search_term");
            if (searchInput) searchInput.value = searchTerm;
            ProductService.loadProducts(searchTerm ? { search: searchTerm } : {});
          }
        });

        app.route({
          view: "cart",
          onReady: function () {
            OrderService.initCheckoutFormValidation();
            CartService.loadTotalValue();
            UserService.updateDashboardLinkBasedOnRole();
            ProductService.handleNavbarSearch();
            OrderService.initCheckoutFormValidation();

            // Wait until #cartItems exists
            const waitForCartItems = setInterval(() => {
              const container = document.getElementById("cartItems");

              if (container) {
                clearInterval(waitForCartItems);
                CartService.getCart();
              }
            }, 50); // check every 50ms
          }
        });


        app.route({
          view: "wishlist",
          onReady: function () {
            UserService.updateDashboardLinkBasedOnRole();
            ProductService.handleNavbarSearch();
            WishlistService.getWishlist();
          }
        });


    },
    block_ui: function (element) {
        $(element).block({
            message: '<div class="spinner-border text-primary" role="status"></div>',
            css: {
                backgroundColor: "transparent",
                border: "0",
            },
            overlayCSS: {
                backgroundColor: "#000",
                opacity: 0.25,
            },
        });
    },
    unblock_ui: function (element) {
        $(element).unblock({});
    },

       datatable: function (table_id, columns, data, pageLength=15) {
       if ($.fn.dataTable.isDataTable("#" + table_id)) {
         $("#" + table_id)
           .DataTable()
           .destroy();
       }
       $("#" + table_id).DataTable({
         data: data,
         columns: columns,
         pageLength: pageLength,
         lengthMenu: [2, 5, 10, 15, 25, 50, 100, "All"],
       });
     },
     parseJwt: function(token) {
       if (!token) return null;
       try {
         const payload = token.split('.')[1];
         const decoded = atob(payload);
         return JSON.parse(decoded);
       } catch (e) {
         console.error("Invalid JWT token", e);
         return null;
       }
     }  
};
