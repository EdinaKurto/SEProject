var CartService = {
  data: [],
  getCart: function () {
    RestClient.get("cart", function (cartItems) {
      CartService.data = cartItems;
      CartService.renderCart(cartItems);
    }, function (xhr, status, error) {
      toastr.error("Failed to load cart.");
      console.error(error);
    });
  },

renderCart: function (items) {
  const container = document.getElementById("cartItems");
  container.innerHTML = "";

  if (!items || items.length === 0) {
    container.innerHTML = `<div class="text-center">Your cart is empty.</div>`;
    return;
  }

  document.getElementById("cartItemCount").textContent = `You have ${items.length} items in your cart`;

  
items.forEach(item => {
  let rawImageUrl = (item.images && item.images.length > 0)
    ? item.images[0].image
    : null;

  if (rawImageUrl && rawImageUrl.startsWith("https//")) {
    rawImageUrl = rawImageUrl.replace("https//", "https://");
  }

  console.log("Raw Image URL:", rawImageUrl); // Debugging

  const imageUrl = rawImageUrl || 'assets/images/Red_Perfume.jpeg';

    const html = `
      <div class="card mb-3">
        <div class="card-body" style="background-color:rgb(255, 255, 255);">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-row align-items-center">
              <div>
                <img src="${imageUrl}" class="img-fluid rounded-3" alt="${item.name}" style="width: 100px; height: 75px;">
              </div>
              <div class="ms-3">
                <h5>${item.name}</h5>
                <p class="small mb-0">${item.description || "No description"}</p>
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 flex-shrink-0">
              <div class="d-flex align-items-center gap-1">
                <button class="btn btn-sm btn-outline-dark decrease-qty" data-product-id="${item.product_id}">-</button>
                <input type="number" class="form-control form-control-sm quantity-input text-center" 
                       value="${item.cart_quantity}" min="1"
                       data-product-id="${item.product_id}" style="width: 55px; background-color: #fff2dc;">
                <button class="btn btn-sm btn-outline-dark increase-qty" data-product-id="${item.product_id}">+</button>
              </div>

              <div>
                <h5 class="mb-0">$${item.price.toFixed(2)}</h5>
              </div>

              <div>
                <a class="remove-item" data-product-id="${item.product_id}" style="color: #cecece;">
                  <i class="fas fa-trash-alt"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>`;
    container.innerHTML += html;
  });

  CartService.attachEvents();
  CartService.loadTotalValue();
},



  attachEvents: function () {
    document.querySelectorAll('.increase-qty').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.getAttribute('data-product-id');
        const input = document.querySelector(`input.quantity-input[data-product-id="${productId}"]`);
        input.value = parseInt(input.value) + 1;
        CartService.updateQuantity(productId, input.value);
      });
    });

    document.querySelectorAll('.decrease-qty').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.getAttribute('data-product-id');
        const input = document.querySelector(`input.quantity-input[data-product-id="${productId}"]`);
        let currentValue = parseInt(input.value);
        if (currentValue > 1) {
          input.value = currentValue - 1;
          CartService.updateQuantity(productId, input.value);
        }
      });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
      input.addEventListener('change', function () {
        const productId = this.getAttribute('data-product-id');
        const newQuantity = parseInt(this.value);
        if (isNaN(newQuantity) || newQuantity < 1) {
          this.value = 1;
          toastr.warning("Minimum quantity is 1.");
        }
        CartService.updateQuantity(productId, this.value);
      });
    });

    document.querySelectorAll('.remove-item').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.getAttribute('data-product-id');
        CartService.removeFromCart(productId);
      });
    });
  },

  updateQuantity: function (productId, newQuantity) {
    RestClient.put("cart/update", {
      product_id: parseInt(productId),
      quantity: parseInt(newQuantity)
    }, function () {
      toastr.success("Quantity updated.");
      CartService.getCart();
    }, function () {
      toastr.error("Failed to update cart.");
    });
  },

  removeFromCart: function (productId) {
    RestClient.delete(`cart/remove/${productId}`, {}, function () {
      toastr.success("Item removed.");
      CartService.getCart();
    }, function () {
      toastr.error("Failed to remove item.");
    });
  },
  loadTotalValue: function () {
    RestClient.get("cart/summary", function (summary) {
      const value = summary.total_value ? Number(summary.total_value).toFixed(2) : "0.00";
      document.getElementById("cart-total-value").textContent = "$" + value;
    }, function () {
      document.getElementById("cart-total-value").textContent = "0.00";
    });
  },

  addToCart: function (productId, quantity = 1) {
  if (!productId) {
    toastr.error("Product ID is missing.");
    return;
  }

  if (!quantity || isNaN(quantity) || quantity < 1) {
    toastr.warning("Invalid quantity. Minimum is 1.");
    return;
  }

  RestClient.post("cart/add", {
    product_id: parseInt(productId),
    quantity: parseInt(quantity)
  }, function () {
    toastr.success("Item added to cart.");
  }, function () {
    toastr.error("Failed to add item to cart.");
  });
},

addToCartFromLocal: function (quantity = 1) {
  const productId = localStorage.getItem("product_id");

  if (!productId) {
    toastr.error("Product ID not found in localStorage.");
    return;
  }
  this.addToCart(productId, quantity);
}
};