var ProductService = {
  init: function () {
    ProductService.loadCategories();
    
    $('#addItemModal').on('show.bs.modal', function () {
      const form = document.getElementById('addItemForm');
      if (form) {
        form.reset();

        const fileInput = form.querySelector('input[type="file"]');
        if (fileInput) {
          fileInput.value = '';
        }

        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
          select.selectedIndex = 0;
        });
      }
    });
    FormValidation.validate(
      "#addItemForm",
      {
        name: "required",
        category_id: "required",
        quantity: {
          required: true,
          digits: true,
          min: 1
        },
        price_each: {
          required: true,
          number: true,
          min: 0.01
        }
      },
      {
        name: "Please enter the product name.",
        category_id: "Please enter the product category.",
        quantity: {
          required: "Please enter the quantity.",
          digits: "Quantity must be a whole number.",
          min: "Quantity must be at least 1."
        },
        price_each: {
          required: "Please enter the price.",
          number: "Price must be a valid number.",
          min: "Price must be at least 0.01."
        }
      },
      ProductService.addProduct
    );
  },

  addProduct: function (data) {
    ProductService.loadCategories()
    Utils.block_ui("#addItemForm");

    RestClient.post(
      "products/add",
      data,
      function (response) {
        const productId = response.id;

        const filesInput = document.getElementById("formFileMultiple");
        if (filesInput.files.length > 0) {
          let uploaded = 0;
          for (let i = 0; i < filesInput.files.length; i++) {
            const singleForm = new FormData();
            singleForm.append("product_image", filesInput.files[i]);

            RestClient.uploadFile(
              `products/upload_image/${productId}`,
              singleForm,
              function () {
                uploaded++;
                if (uploaded === filesInput.files.length) {
                  toastr.success("Product and all images uploaded.");
                  
                  $("#addItemModal").modal("hide");
                  ProductService.getAllProducts();
                  Utils.unblock_ui("#addItemForm");
                }
              },
              function () {
                toastr.error("One or more images failed to upload.");
                Utils.unblock_ui("#addItemForm");
              }
            );
          }
        } else {
          toastr.success("Product added without images.");
          $("#addItemModal").modal("hide");
          ProductService.getAllProducts();
          Utils.unblock_ui("#addItemForm");
        }
      },
      function (error) {
        toastr.error("Failed to add product.");
        Utils.unblock_ui("#addItemForm");
      }
    );
  },
  getAllProducts : function(){
    RestClient.get("products", function(data){
        Utils.datatable('itemsTable', [
            { data: 'name', title: 'Name' },
            { data: 'category_name', title: 'Category' },
            { data: 'quantity', title: 'Quantity' },
            { data: 'price_each', title: 'Price' },
            { data: 'description', title: 'Description' },
            {
            title: 'Actions',
                render: function (data, type, row, meta) {
                    const rowStr = encodeURIComponent(JSON.stringify(row));
                    return `<div class="d-flex justify-content-center gap-2 mt-1">
                        <button class="btn btn-sm btn-success save-order" data-bs-target="#editItemModal" onclick="ProductService.openEditModal('${row.id}')">Edit</button>
                        <button class="btn btn-danger" onclick="ProductService.openDeleteConfirmationDialog(decodeURIComponent('${rowStr}'))">Delete</button>
                    </div>
                    `;
                }
            }
        ], data, 10);
    }, function (xhr, status, error) {
        console.error('Error fetching data from file:', error);
    });
  },
getProductById: function(id, callback) {
  RestClient.get('products/' + id, function(data) {
    console.log("=== PRODUCT DATA ===", data);

    localStorage.setItem('selected_product', JSON.stringify(data));

    $('input[name="name"]').val(data.name);
    $('input[name="quantity"]').val(data.quantity);
    $('input[name="price_each"]').val(data.price_each);
    $('input[name="description"]').val(data.description);
    const imageContainer = document.getElementById("existingImages");
imageContainer.innerHTML = ""; // očisti prethodne slike

if (data.images && data.images.length > 0) {
  data.images.forEach(img => {
    const imageWrapper = document.createElement("div");
    imageWrapper.classList.add("position-relative");

    const imageElement = document.createElement("img");

    let rawImageUrl = img.image || null;

    if (rawImageUrl.startsWith("https//")) {
      rawImageUrl = rawImageUrl.replace("https//", "https://");
    }

    imageElement.src = rawImageUrl || 'assets/images/earl_grey_tea.jpg';


    imageElement.classList.add("img-thumbnail");
    imageElement.style.height = "100px";

    const deleteBtn = document.createElement("button");
    deleteBtn.innerHTML = "&times;";
    deleteBtn.classList.add("btn", "btn-sm", "btn-danger", "position-absolute", "top-0", "end-0");
    deleteBtn.onclick = function () {
      imageWrapper.remove(); // samo vizualno, kasnije se filtrira
    };

    imageWrapper.dataset.imageId = img.id;
    imageWrapper.appendChild(imageElement);
    imageWrapper.appendChild(deleteBtn);
    imageContainer.appendChild(imageWrapper);
  });

  console.log("=== FULL PRODUCT DATA ===", data);
}


    RestClient.get('categories/category?name=' + encodeURIComponent(data.category), function (categoryData) {
      if (categoryData && categoryData.id) {
        $('select[name="category_id"]').val(categoryData.id).trigger('change');
      } else {
        console.error('Category ID not found for category:', data.category);
      }

      if (callback) callback(); // ✅ pozovi modal tek kad sve završi
    });

  }, function(xhr, status, error) {
    console.error('Error fetching product data:', error);
  });
},






  openEditModal: function (id) {
  Utils.block_ui("#editItemModal");

  ProductService.loadCategories().then(function () {
    ProductService.getProductById(id, function () {
      // Initialize validation when modal is ready
      ProductService.initEditFormValidation();
      $('#editItemModal').modal('show');
      Utils.unblock_ui("#editItemModal");
    });
  });
},


  loadCategories: function () {
  return new Promise(function (resolve, reject) {
    RestClient.get('categories', function (categories) {
      const categorySelect = $('select[name="category_id"]');
      categorySelect.empty(); // Clear existing options

      categories.forEach(function (category) {
        categorySelect.append(
          $('<option>', {
            value: category.id,
            text: category.name,
          })
        );
      });

      resolve(); // Sve prošlo dobro
    }, function (xhr, status, error) {
      console.error('Failed to load categories:', error);
      reject(error);
    });
  });
},

updateProduct: function () {
  const product = JSON.parse(localStorage.getItem("selected_product"));
  const productId = product.id;

  const updatedData = {
    name: $('#editItemForm input[name="name"]').val(),
    quantity: parseInt($('#editItemForm input[name="quantity"]').val()),
    price_each: parseFloat($('#editItemForm input[name="price_each"]').val()),
    description: $('#editItemForm input[name="description"]').val(),
    category_id: parseInt($('#editItemForm select[name="category_id"]').val())
  };

  console.log("=== UPDATE PRODUCT ===", updatedData);

  Utils.block_ui("#editItemModal");

  // Prvo ažuriraj osnovne podatke o proizvodu
  RestClient.put(
    "products/update/" + productId,
    updatedData,
    function () {
      // ✅ Nakon uspješnog update-a, ažuriraj slike
      const existingImageIds = Array.from(document.querySelectorAll("#existingImages div"))
        .map(div => parseInt(div.dataset.imageId));

      const newImagesInput = document.getElementById("formFileMultiple1");
      const formData = new FormData();
      formData.append("existingImageIds", JSON.stringify(existingImageIds));

      if (newImagesInput.files.length > 0) {
        for (let i = 0; i < newImagesInput.files.length; i++) {
          formData.append("new_images[]", newImagesInput.files[i]);
        }
      }

      RestClient.uploadFile(
        `products/product_images/${productId}`,
        formData,
        function () {
          toastr.success("Product and images updated.");
          document.getElementById("formFileMultiple1").value = "";
          $("#editItemModal").modal("hide");
          ProductService.getAllProducts();
          Utils.unblock_ui("#editItemModal");
        },
        function () {
          toastr.error("Failed to update images.");
          Utils.unblock_ui("#editItemModal");
        }
      );
    },
    function () {
      toastr.error("Failed to update product.");
      Utils.unblock_ui("#editItemModal");
    }
  );
},



openDeleteConfirmationDialog: function (productStr) {
  try {
    const product = JSON.parse(productStr);
    ProductService.deleteProduct(product.id);
  } catch (e) {
    console.error("Invalid product data for deletion:", e);
    toastr.error("Failed to parse product data.");
  }},

  deleteProduct: function (productId) {
  if (!productId) {
    toastr.error("Product ID not provided.");
    return;
  }

  if (!confirm("Are you sure you want to delete this product? This action cannot be undone.")) {
    return;
  }

  Utils.block_ui("body"); // You can change this selector to match your UI

  RestClient.delete(
    `products/delete/${productId}`,
    {},
    function (response) {
      toastr.success("Product has been deleted successfully.");
      ProductService.getAllProducts();
    },
    function (error) {
      toastr.error("Error deleting the product.");
    }
  );

  Utils.unblock_ui("body");
},







loadProducts: function (filters = {}) {
  console.log("loadProducts() called with filters:", filters);
  const safeFilters = { ...filters, _: Date.now() }; // Copy + cache buster

  const params = new URLSearchParams(safeFilters).toString();
  const url = `products?${params}`;

  RestClient.get(
    url,
    function (products) {
      const container = document.getElementById("products-list");
      container.innerHTML = "";

      if (!products.length) {
        container.innerHTML = "<div class='col-12 text-center'>No products found.</div>";
        return;
      }

      products.forEach(product => {
        console.log("Product ID:", params[0]);

        // Get the first image URL or fallback
      let rawImageUrl = (product.images && product.images.length > 0)
        ? product.images[0].image
        : null;

      if (rawImageUrl && rawImageUrl.startsWith("https//")) {
        rawImageUrl = rawImageUrl.replace("https//", "https://");
      }

      const imageUrl = rawImageUrl
        ? rawImageUrl
        : 'assets/images/earl_grey_tea.jpg';

          container.innerHTML += `
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100">
              <a href="product.html" onclick="event.preventDefault(); localStorage.setItem('product_id', ${product.id}); window.location.href='#product';">
                <img src="${imageUrl}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Product Image">
                <div class="card-body">
                  <h5 class="card-title mb-3">${product.name}</h5>
                  </a>
                  <p class="mb-1"><strong>Category:</strong> ${product.category_name}</p>
                  <p class="mb-1"><strong>Price:</strong> $${product.price_each}</p>
                  <p class="mb-1"><strong>Quantity:</strong> ${product.quantity}</p>
                  <p class="mb-1">${product.description || ""}</p>
                </div>
              </div>
            </div>
          `;
      });

    },
    function () {
      document.getElementById("products-list").innerHTML =
        "<div class='col-12 text-center'>Failed to load products.</div>";
    }
  );
},
renderCategoryCheckboxes: function () {
    RestClient.get("categories", function (categories) {
      const container = document.getElementById("category-checkboxes");
      container.innerHTML = "";
      categories.forEach(cat => {
        container.innerHTML += `
          <div class="form-check category-item">
            <input class="form-check-input category-checkbox" type="checkbox" id="cat${cat.id}" value="${cat.id}">
            <label class="form-check-label p-2" for="cat${cat.id}">${cat.name}</label>
          </div>
        `;
      });
    });
  },

  loadProductDetailsWithRecommendations: function (productId) {
    RestClient.get(`products/${productId}`, function (product) {
      // Set main image
    const mainImage = document.getElementById('mainImage');

    let rawImageUrl = (product.images && product.images.length > 0)
      ? product.images[0].image
      : null;

    if (rawImageUrl && rawImageUrl.startsWith("https//")) {
      rawImageUrl = rawImageUrl.replace("https//", "https://");
    }

    mainImage.src = rawImageUrl || 'assets/images/cherry_blossom_tea.jpg';


      // Set thumbnails
      const thumbnailRow = document.querySelector('.thumbnail-row .d-flex');
      thumbnailRow.innerHTML = '';
(product.images && product.images.length > 0 ? product.images : []).forEach((img, i) => {
  let rawImageUrl = img.image || null;

  if (rawImageUrl && rawImageUrl.startsWith("https//")) {
    rawImageUrl = rawImageUrl.replace("https//", "https://");
  }

  const imgSrc = rawImageUrl || 'assets/images/earl_grey_tea.jpg';
        thumbnailRow.innerHTML += `
          <img src="${imgSrc}" alt="Thumbnail ${i+1}" class="thumbnail rounded ${i === 0 ? 'active' : ''}"
               onclick="changeImage(event, this.src)" style="width: 32%; height: 120px; object-fit: cover; cursor: pointer;">
        `;
      });

      // Product details
      const productTitle = document.querySelector('.container .row .col-md-6 h2.mb-3');
if (productTitle) productTitle.textContent = product.name;
      document.querySelector('.h4.me-2').textContent = `$${product.price_each}`;
      document.querySelector('p.mb-4').textContent = product.description || '';

      // Set categories, etc.
      const categoryList = document.getElementById('product-category-list');
      if (categoryList) {
        categoryList.innerHTML = `<li>${product.category}</li>`;
      }
      document.getElementById("quantity").value = 1;

      // Wait for recommendations container, then load recommendations
      const waitForRec = setInterval(() => {
        const recContainer = document.getElementById('recommendations-row');
        if (recContainer) {
          clearInterval(waitForRec);
          ProductService.loadRecommendations(product.category);
        }
      }, 50);
    });
  },
  loadRecommendations: function (categoryName) {
    RestClient.get(`products`, function (products) {
      const recContainer = document.getElementById('recommendations-row');
      if (!recContainer) return; // Only render if on product page

      recContainer.innerHTML = '';

      const matchingProducts = products.filter(p => p.category_name === categoryName).slice(0, 4);

matchingProducts.forEach(p => {
  let rawImageUrl = (p.images && p.images.length > 0)
    ? p.images[0].image
    : null;

  if (rawImageUrl && rawImageUrl.startsWith("https//")) {
    rawImageUrl = rawImageUrl.replace("https//", "https://");
  }

  const imageUrl = rawImageUrl || 'assets/images/earl_grey_tea.jpg';

        recContainer.innerHTML += `
          <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100 text-center" style="border: 1px solid #e0e0e0;">
              <a href="#product" onclick="localStorage.setItem('product_id', ${p.id}); ProductService.renderProductDetails();">
                <img src="${imageUrl}" class="card-img-top" alt="${p.name}" style="height: 200px; object-fit: cover; width: 100%;">
              </a>
              <div class="card-body">
                <a href="#product" onclick="localStorage.setItem('product_id', ${p.id}); ProductService.renderProductDetails();">
                  <h5 class="card-title">${p.name}</h5>
                </a>
                <p class="card-text">$${p.price_each}</p>
              </div>
            </div>
          </div>
        `;
      });
    });
  },

renderProductDetails: function () {
  const productId = localStorage.getItem('product_id');

  if (productId) {
    ProductService.loadProductDetailsWithRecommendations(productId);

    const user = JSON.parse(localStorage.getItem("user"));
    if (user && user.id) {
      const payload = {
        customer_id: user.id,
        product_id: parseInt(productId)
      };

      RestClient.post("product_views/add", payload, function () {
        console.log("✔ Product view added.");
      }, function () {
        console.warn("⚠ Failed to log product view.");
      });
    }
  } else {
    console.warn("No product ID found in localStorage.");
  }

  // 🔽 MORA biti ovdje, kada je DOM već učitan
  const wishlistBtn = document.getElementById("addToWishlistBtn");

/*   if (wishlistBtn) {
    wishlistBtn.onclick = function () {
  const productId = localStorage.getItem("product_id");
  const quantityInput = document.getElementById("quantity");
  const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
  WishlistService.addToWishlist(productId, quantity);
};

  } */
},



renderDashboardProducts: function(containerSelector = '#dashboard-products-row') {
  RestClient.get('products', function(products) {
    const row = document.querySelector(containerSelector);
    if (!row) return;

    row.innerHTML = ''; // Clear only the product cards

    products.slice(0, 3).forEach(product => {
let rawImageUrl = (product.images && product.images.length > 0)
  ? product.images[0].image
  : null;

if (rawImageUrl && rawImageUrl.startsWith("https//")) {
  rawImageUrl = rawImageUrl.replace("https//", "https://");
}

const imageUrl = rawImageUrl || 'assets/images/earl_grey_tea.jpg';


      row.innerHTML += `
        <div class="col-md-6 col-lg-4 mb-4 mb-lg-0">
          <div class="card" style="background: linear-gradient(135deg, #53342A, #3E241B);">
            <a href="#product" onclick="localStorage.setItem('product_id', ${product.id})">
              <img src="${imageUrl}" class="card-img-top" alt="${product.name}" />
            </a>
            <div class="card-body">
              <div class="d-flex justify-content-between mb-3" style="color: #eee">
                <a href="#product" style="text-decoration: none; color: inherit;" onclick="localStorage.setItem('product_id', ${product.id})">
                  <h5 class="mb-0">${product.name}</h5>
                </a>
                <h5 class="text-white mb-0">$${product.price_each}</h5>
              </div>
              <p class="small text-white mb-0">${product.category_name || ''}</p>
            </div>
          </div>
        </div>
      `;
    });
  });
},

loadUserProductViews: function () {
  RestClient.get("product_views", function (data) {
    Utils.datatable(
      "productViewsTable",
      [
        { data: 'product_name', title: 'Product' },
/*         { data: 'customer_name', title: 'Customer' }, // optional if admin only */
        { data: 'time', title: 'Viewed At' },
        {
          title: 'Actions',
          render: function (data, type, row) {
            return `
              <a href="#product" class="btn btn-sm btn-outline-dark"
                 onclick="localStorage.setItem('product_id', ${row.product_id})">
                View Product
              </a>`;
          }
        }
      ],
      data,
      5
    );
  }, function (xhr, status, error) {
    console.error("Error loading product views:", error);
    toastr.error("Failed to load product views.");
  });
},

loadDashboardSummary: function () {
  const user = JSON.parse(localStorage.getItem("user"));
  if (!user || !user.id) return;

  // Total Orders
  RestClient.get("order/count_all", function (res) {
    document.getElementById("total-orders-count").textContent = res || 0;
  }, function () {
    console.warn("Failed to load total orders");
  });

  // Wishlist Items
  RestClient.get("wishlist/summary", function (res) {
    document.getElementById("wishlist-count").textContent = res.total_count || 0;
  }, function () {
    console.warn("Failed to load wishlist summary");
  });

  // Pending Orders
  RestClient.get("order/count_pending", function (res) {
    document.getElementById("pending-count").textContent = res || 0;
  }, function () {
    console.warn("Failed to load pending orders");
  });

  // Delivered Orders
  RestClient.get("order/count_delivered", function (res) {
    document.getElementById("delivered-count").textContent = res || 0;
  }, function () {
    console.warn("Failed to load delivered orders");
  });
},


handleNavbarSearch: function () {
    const searchInput = document.getElementById("navbar-search-input");
    const searchBtn = document.getElementById("navbar-search-btn");
    if (!searchInput || !searchBtn) return;

    // Remove previous listeners to avoid duplicates
    searchBtn.onclick = null;
    searchInput.onkeydown = null;

    function doSearch() {
      const searchTerm = searchInput.value.trim();

      if (window.location.hash === "#browse") {
        ProductService.renderCategoryCheckboxes();
        ProductService.loadProducts(searchTerm ? { search: searchTerm } : {});
      } else {
        localStorage.setItem("products_search_term", searchTerm);
        window.location.hash = "#browse";
      }
    }

    searchBtn.onclick = doSearch;
    searchInput.onkeydown = function (e) {
      if (e.key === "Enter") {
        doSearch();
      }
    };
  },

  applyStoredSearch: function () {
    const searchTerm = localStorage.getItem("products_search_term") || "";
    if (searchTerm) {
      localStorage.removeItem("products_search_term");
      ProductService.loadProducts({ search: searchTerm });
      // Optionally, set the search box value if on products page
      const searchInput = document.getElementById("navbar-search-input");
      if (searchInput) searchInput.value = searchTerm;
    } else {
      ProductService.loadProducts();
      // Optionally clear the search box if not searching
      const searchInput = document.getElementById("navbar-search-input");
      if (searchInput) searchInput.value = "";
    }
  },

  reloadProductsView: function(searchTerm) {
    ProductService.renderCategoryCheckboxes();
    ProductService.loadProducts(searchTerm ? { search: searchTerm } : {});
    const searchInput = document.getElementById("navbar-search-input");
    if (searchInput) searchInput.value = searchTerm || "";
  },

  initEditFormValidation: function () {
    $("#editItemForm").validate({
      rules: {
        name: "required",
        category_id: "required",
        quantity: {
          required: true,
          digits: true,
          min: 1
        },
        price_each: {
          required: true,
          number: true,
          min: 0.01
        },
        description: "required"
      },
      messages: {
        name: "Please enter the product name.",
        category_id: "Please enter the product category.",
        quantity: {
          required: "Please enter the quantity.",
          digits: "Quantity must be a whole number.",
          min: "Quantity must be at least 1."
        },
        price_each: {
          required: "Please enter the price.",
          number: "Price must be a valid number.",
          min: "Price must be at least 0.01."
        },
        description: "Please enter the description."
      },
      onfocusout: false,
      onkeyup: false,
      submitHandler: function(form, event) {
        event.preventDefault();
        ProductService.updateProduct();
      }
    });
  },
};