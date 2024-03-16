<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'tbl_products';
    protected $primaryKey = 'product_id';
    protected $allowedFields = ['product_id', 'product_title', 'product_price', 'product_image'];
}

?>