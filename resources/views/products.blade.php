<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: sans-serif;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: .5rem;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background: #2563eb;
        }

        .product-card {
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-top: 10px;
            border-radius: 8px;
            background: #f9fafb;
        }

        .error {
            color: red;
            font-size: 0.875rem;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <h1>Product Manager</h1>

    <div x-data="productApp()">

        <div style="background: #eee; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h2>Add New Product</h2>
            <form @submit.prevent="saveProduct">

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" x-model="form.name" placeholder="Enter product name">
                    <p class="error" x-text="errors.name"></p>
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" x-model="form.price" placeholder="0.00">
                    <p class="error" x-text="errors.price"></p>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea x-model="form.description"></textarea>
                </div>

                <button type="submit" x-text="isSaving ? 'Saving...' : 'Save Product'" :disabled="isSaving"></button>
            </form>
        </div>

        <h2>Current Inventory</h2>
        <p x-show="isLoading">Loading products...</p>

        <template x-for="product in products" :key="product.id">
            <div class="product-card">
                <div style="display: flex; justify-content: space-between;">
                    <h3 style="margin:0;" x-text="product.name"></h3>
                    <strong style="color: green;">$<span x-text="product.price"></span></strong>
                </div>
                <p x-text="product.description || 'No description provided.'"></p>
            </div>
        </template>

    </div>

    <script>
        function productApp() {
            return {
                products: [],
                isLoading: true,
                isSaving: false,
                form: {
                    name: '',
                    price: '',
                    description: ''
                },
                errors: {},

                init() {
                    // Fetch initial data
                    fetch('/api/products')
                        .then(res => res.json())
                        .then(data => {
                            this.products = data;
                            this.isLoading = false;
                        });
                },

                saveProduct() {
                    this.isSaving = true;
                    this.errors = {}; // Clear previous errors

                    fetch('/api/products', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(async response => {
                        const data = await response.json();

                        // Handle Validation Errors (Status 422)
                        if (response.status === 422) {
                            // Laravel returns errors in data.errors
                            this.isSaving = false;
                            // Map Laravel errors to our errors object
                            if (data.errors.name) this.errors.name = data.errors.name[0];
                            if (data.errors.price) this.errors.price = data.errors.price[0];
                            return; 
                        }

                        // Handle Success (Status 201)
                        if (response.ok) {
                            // Add the new product to the list instantly!
                            this.products.unshift(data); 
                            
                            // Reset Form
                            this.form.name = '';
                            this.form.price = '';
                            this.form.description = '';
                            this.isSaving = false;
                            alert('Product Created!');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.isSaving = false;
                    });
                }
            }
        }
    </script>
</body>

</html>