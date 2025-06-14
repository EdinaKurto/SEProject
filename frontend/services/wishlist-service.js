var WishlistService = {
  data: [],

  getWishlist: function () {
    RestClient.get("wishlist", function (wishlistData) {
      WishlistService.data = wishlistData;
      WishlistService.renderWishlist(wishlistData);
    }, function (xhr, status, error) {
      toastr.error("Failed to load wishlist.");
      console.error(error);
    });
  },

  renderWishlist: function (items) {
    const container = document.getElementById("wishlistItems");
    container.innerHTML = "";

    if (!items || items.length === 0) {
      container.innerHTML = `<div class="text-white text-center">Your wishlist is empty.</div>`;
      return;
    }

    items.forEach(item => {
let rawImageUrl = (item.images && item.images.length > 0)
  ? item.images[0].image
  : null;

if (rawImageUrl && rawImageUrl.startsWith("https//")) {
  rawImageUrl = rawImageUrl.replace("https//", "https://");
}

const imageUrl = rawImageUrl || 'assets/images/earl_grey_tea.jpg';



      const html = `
      <div class="col-12 mb-3">
        <div class="card shadow-sm" style="border: 1px solid; background-color: linear-gradient(135deg, #53342A, #3E241B);">
          <div class="card-body" style="background: linear-gradient(135deg, #2d3833, #1b2420)">
            <div class="row align-items-center">
              <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                <img src="${imageUrl}" class="img-fluid rounded wishlist-img" alt="${item.name}">
              </div>
              <div class="col-md-4 col-sm-8 mb-3 mb-md-0">
                <h3 class="card-title" style="color: #d6cec4;">${item.name}</h3>
                <div class="mb-2">
                  <span class="fw-bold" style="font-size: 1.25rem; color: #ad9b82;">$${item.price.toFixed(2)}</span>
                </div>
              </div>
              <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <label class="form-label" style="font-size: 1.15rem; color: #d6cec4;">Quantity</label>
                <div class="input-group">
                  <button class="btn btn-outline-danger decrease-qty" type="button">-</button>
                  <input type="number" class="form-control text-center quantity-input" value="${item.cart_quantity}" min="1" data-product-id="${item.product_id}" style="background-color: #d6cec4;">
                  <button class="btn btn-outline-success increase-qty" type="button">+</button>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <button class="btn btn-success mb-2 w-100 add-to-cart-btn"
                        data-product-id="${item.product_id}"
                        style="background-color: #4F625A; border-color: #4F625A;">
                  <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                <button class="btn btn-outline-danger w-100" onclick="WishlistService.removeItemFromWishlist(${item.product_id})">
                  <i class="bi bi-trash"></i> Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>`;
      container.innerHTML += html;
    });

    WishlistService.attachQuantityEvents();
    WishlistService.loadSummary();
    document.getElementById("clearWishlistBtn").addEventListener("click", function () {
      WishlistService.clearWishlist();
   });

      const addAllToCartBtn = document.querySelector(".btn-success.flex-grow-1");

    if (addAllToCartBtn) {
      addAllToCartBtn.onclick = function () {
        WishlistService.addAllToCart();
        WishlistService.clearWishlist();
      };
    }
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.getAttribute('data-product-id');
        const item = WishlistService.data.find(i => i.product_id == productId);
        const quantity = item && item.cart_quantity ? item.cart_quantity : 1;

        RestClient.post("cart/add", {
          product_id: productId,
          quantity: quantity
        }, function () {
          toastr.success("Added to cart!");
          WishlistService.removeItemFromWishlist(productId);
        }, function () {
          toastr.error("Failed to add to cart.");
        });
      });
      WishlistService.loadSummary();
    });

  },

  attachQuantityEvents: function () {
  document.querySelectorAll('.increase-qty').forEach(button => {
    button.addEventListener('click', function () {
      const input = this.parentElement.querySelector('.quantity-input');
      input.value = parseInt(input.value) + 1;

      const productId = input.getAttribute('data-product-id');
      WishlistService.updateQuantity(productId, input.value);
    });
  });

  document.querySelectorAll('.decrease-qty').forEach(button => {
    button.addEventListener('click', function () {
      const input = this.parentElement.querySelector('.quantity-input');
      let currentValue = parseInt(input.value);
      if (currentValue > 1) {
        input.value = currentValue - 1;
        const productId = input.getAttribute('data-product-id');
        WishlistService.updateQuantity(productId, input.value);
      }
    });
  });

  document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function () {
      const productId = input.getAttribute('data-product-id');
      const newQuantity = parseInt(input.value);

      if (isNaN(newQuantity) || newQuantity < 1) {
        input.value = 1;
        toastr.warning("Minimum quantity is 1.");
      }

      WishlistService.updateQuantity(productId, input.value);
    });
  });
},

  clearWishlist: function () {
  if (!confirm("Are you sure you want to clear your wishlist?")) return;

  RestClient.delete("wishlist/clear", {}, function () {
    toastr.success("Wishlist cleared successfully.");
    WishlistService.getWishlist(); // refresh after clear
    WishlistService.loadSummary();
  }, function () {
    toastr.error("Failed to clear wishlist.");
  });
},

  removeItemFromWishlist: function (productId) {
  if (!productId) return;

  if (!confirm("Remove this item from your wishlist?")) return;

  RestClient.delete(`wishlist/remove/${productId}`, {}, function () {
    WishlistService.getWishlist();
  }, function () {
    toastr.error("Failed to remove item.");
  });
},



addToWishlist: function (productId, quantity = 1) {
  if (!productId) {
    toastr.error("No product selected.");
    return;
  }

  RestClient.post("wishlist/add", { product_id: productId, quantity: quantity }, function () {
    toastr.success("Product added to wishlist.");
  }, function () {
    toastr.error("Failed to add product to wishlist.");
  });
},

updateQuantity: function (productId, newQuantity) {
  if (!productId || isNaN(newQuantity) || newQuantity < 1) {
    toastr.error("Invalid quantity.");
    return;
  }

  RestClient.put("wishlist/update", {
    product_id: parseInt(productId),
    quantity: parseInt(newQuantity)
  }, function () {
    toastr.success("Quantity updated.");
    WishlistService.getWishlist(); // Refresh total and values
  }, function () {
    toastr.error("Failed to update quantity.");
  });
},
loadSummary: function () {
  RestClient.get("wishlist/summary", function (summary) {
    const value = summary.total_value ? Number(summary.total_value).toFixed(2) : "0.00";
    document.getElementById("wishlist-total-value").textContent = value;
    document.getElementById("wishlist-total-count").textContent = summary.total_count || 0;
  }, function () {
    document.getElementById("wishlist-total-value").textContent = "0.00";
    document.getElementById("wishlist-total-count").textContent = 0;
  });
},

addAllToCart: function () {
  if (!WishlistService.data || WishlistService.data.length === 0) {
    toastr.warning("Your wishlist is empty.");
    return;
  }

  WishlistService.data.forEach(item => {
    if (item.product_id && item.cart_quantity) {
      RestClient.post("cart/add", {
        product_id: item.product_id,
        quantity: item.cart_quantity
      }, function () {
        console.log(`✔ Added ${item.name} to cart`);
      }, function () {
        toastr.error(`❌ Failed to add ${item.name} to cart.`);
      });
    }
  });

  toastr.success("All wishlist items are being added to your cart.");
},









};
