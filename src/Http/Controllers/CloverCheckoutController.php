<?php

namespace Supravatm\CloverPayment\Http\Controllers;

class CloverCheckoutController extends Controller
{
    protected $clover;

    public function test()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Clover Payment package is active!',
        ]);
    }
    public function index()
    {
        // Static cart example

        $items = [
            "id" => "VV3MDJZSK275Y",
            "hidden" => false,
            "available" => true,
            "autoManage" => false,
            "name" => "Black Coffee (Each)",
            "price" => 50,
            "priceType" => "PER_UNIT",
            "defaultTaxRates" => true,
            "unitName" => "each",
            "isRevenue" => true,
            "modifiedTime" => 1761838101000,
            "deleted" => false,
        ];

        $cart = [
            "id" => "VV3MDJZSK275Y",
            'product_name' => $items['name'],
            'quantity' => 1,
            "price" => $items['price'] / 100,
            'currency' => 'USD'
        ];
        return view('clover::checkout', compact('cart'));
    }
}
