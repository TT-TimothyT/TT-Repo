<?php

// Define the custom WC_Product_Old_Trip_Date class
class WC_Product_Old_Trip_Date extends WC_Product {
    public function __construct($product) {
        $this->product_type = 'old_trip_date';
        parent::__construct($product);
    }
}