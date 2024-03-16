<?php
namespace App\Controllers;

use App\Models\ProductModel;

class Products extends BaseController
{
    protected $helpers = ['form'];

    public function index(): string
    {
        return view('products/index');
    }    

    public function productList()
    {
        try {
            $filePath = FCPATH . '/productsData.json';
            
            if (file_exists($filePath)) {
                $jsonData = file_get_contents($filePath);
                $products = json_decode($jsonData, true);

                if ($products !== null) {
                    $responseData = [
                        'status' => true,
                        'message' => 'Products fetched successfully',
                        'products' => $products
                    ];
                } else {
                    $responseData = [
                        'status' => false,
                        'message' => 'Error decoding product data',
                        'products' => []
                    ];
                }
            } else {
                $responseData = [
                    'status' => true,
                    'message' => 'No products found',
                    'products' => []
                ];
            }

            echo json_encode($responseData);
            die;

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }


    public function productCreate()
    {
        try {
            // Define validation rules
            $validationRules = [
                'product_title' => [
                    'label' => 'Product Title',
                    'rules' => ['required', 'max_length[127]', 'min_length[3]', 'alpha_numeric_punct']
                ],
                'product_price' => [
                    'label' => 'Product Price',
                    'rules' => ['required', 'greater_than[0]']
                ],
                'product_image' => [
                    'label' => 'Product Image',
                    'rules' => [
                        'uploaded[product_image]',
                        'is_image[product_image]',
                        'mime_in[product_image,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                    ],
                ],
            ];

            // Validate input data
            if (! $this->validateData($this->request->getPost(), $validationRules)) {
                $data = [
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $this->validator->getErrors()
                ];
                echo json_encode($data);
                die;
            }

            // Handle product image upload
            $imageUrl = '';
            $productImage = $this->request->getFile('product_image');
            if ($productImage->getName() && !$productImage->hasMoved()) {
                $filePath = $productImage->store('products');
                $imageUrl = site_url('/uploads/') . $filePath;
            }

            // Generate product ID
            $productId = time();

            // Prepare product data
            $newProduct = [
                'product_id' => $productId,
                'product_price' => $this->request->getPost('product_price'),
                'product_title' => $this->request->getPost('product_title'),
                'product_image' => $imageUrl
            ];

            // Write product data to JSON file
            $filePath = FCPATH . '/productsData.json';
            if (file_exists($filePath)) {
                $jsonData = json_decode(file_get_contents($filePath), true);
                $jsonData[] = $newProduct;
            } else {
                $jsonData[] = $newProduct;
            }

            file_put_contents($filePath, json_encode($jsonData));

            // Prepare response data
            $data = [
                'status' => true,
                'message' => 'Product saved successfully',
                'product' => $newProduct
            ];

            // Send JSON response
            echo json_encode($data);
            die;

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function productEdit($productId)
    {
        try {
            $filePath = FCPATH . '/productsData.json';
            if (file_exists($filePath)) {
                $json = file_get_contents($filePath);
                $productsData = json_decode($json, true);
                $product = null;

                foreach ($productsData as $productData) {
                    if ($productData['product_id'] == $productId) {
                        $product = $productData;
                        break;
                    }
                }

                if ($product !== null) {
                    $data = [
                        'status' => true,
                        'message' => 'Product fetched successfully',
                        'product' => $product
                    ];
                } else {
                    $data = [
                        'status' => false,
                        'message' => 'Product not found',
                        'product' => null
                    ];
                }
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Product data file not found',
                    'product' => null
                ];
            }

            echo json_encode($data);
            die;
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }


    public function productUpdate()
    {
        try {
            // Validation rules
            $validationRules = [
                'product_id' => ['rules' => ['required']],
                'product_title' => [
                    'label' => 'Product Title',
                    'rules' => ['required', 'max_length[127]', 'min_length[3]', 'alpha_numeric_punct']
                ],
                'product_price' => [
                    'label' => 'Product Price',
                    'rules' => ['required', 'greater_than[0]']
                ]
            ];
    
            if (! $this->validateData($this->request->getPost(), $validationRules)) {
                $data = [
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $this->validator->getErrors()
                ];
                echo json_encode($data);
                die;
            }
    
            // Handle image upload
            $imageUrl = '';
            $productImage = $this->request->getFile('product_image');
            if ($productImage->getName() && !$productImage->hasMoved()) {
                $filePath = $productImage->store('products');
                $imageUrl = site_url('/uploads/') . $filePath;
            }
    
            $productId = $this->request->getPost('product_id');
            $filePath = FCPATH . '/productsData.json';
            if (file_exists($filePath)) {
                $jsonData = json_decode(file_get_contents($filePath), true);
    
                $productIndex = array_search($productId, array_column($jsonData, 'product_id'));
    
                if ($productIndex !== false) {
                    $jsonData[$productIndex]['product_title'] = $this->request->getPost('product_title');
                    $jsonData[$productIndex]['product_price'] = $this->request->getPost('product_price');
                    if (!empty($imageUrl)) {
                        $jsonData[$productIndex]['product_image'] = $imageUrl;
                    }
    
                    file_put_contents($filePath, json_encode($jsonData));
    
                    $data = [
                        'status' => true,
                        'message' => 'Product updated successfully',
                        'product' => $jsonData[$productIndex]
                    ];
                    echo json_encode($data);
                    die;
                } else {
                    $data = [
                        'status' => false,
                        'message' => 'Product not found',
                        'product' => null
                    ];
                    echo json_encode($data);
                    die;
                }
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Something went wrong. Please try again',
                    'product' => null
                ];
                echo json_encode($data);
                die;
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
    

    public function productDelete($productId)
    {
        try {
            $filePath = FCPATH . '/productsData.json';
            if (file_exists($filePath)) {
                $jsonData = json_decode(file_get_contents($filePath), true);
                $updatedData = [];

                foreach ($jsonData as $product) {
                    if ($product['product_id'] != $productId) {
                        $updatedData[] = $product;
                    } else {
                        // Optionally, you can uncomment the line below to delete associated product image
                        // unlink($product['product_image']);
                    }
                }

                file_put_contents($filePath, json_encode($updatedData));

                $data = [
                    'status' => true,
                    'message' => 'Product deleted successfully',
                    'products' => $updatedData
                ];
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Product not found',
                    'products' => []
                ];
            }
            echo json_encode($data);
            die;
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function saveToDatabase()
    {
        try {
            $filePath = FCPATH . '/productsData.json';
            if (file_exists($filePath)) {
                $jsonData = json_decode(file_get_contents($filePath), true);

                // Load the model
                $productModel = new ProductModel();

                foreach ($jsonData as $product) {
                    // Insert each product into the database
                    $productModel->insert($product);
                }

                unlink($filePath);

                $response = [
                    'status' => true,
                    'message' => 'Products saved to database successfully',
                    'products' => $jsonData
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'No product data found to save',
                    'products' => []
                ];
            }

            // Output JSON response
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            // Handle exceptions
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

}
