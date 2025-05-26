<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Utils\tripay;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PaymentGateway extends Controller
{
    protected $paymentGateway;

    public function __construct()
    {
        $this->paymentGateway = new Tripay();
    }

    //  $fill = $amount = 1000000;
    //         $invoice = 'INV-' . date('YmdHis');
    //         $product = product::all();
    //         $item = [];
    //         $tripayApiKey = env('TRIPAY_API_KEY');
    //         $this->paymentGateway->setSignature($invoice, $amount);
    // dd($this->paymentGateway->getSignature());
    // $http = new \GuzzleHttp\Client();    
   public function store(Request $request)
{
    try {
        // Validasi requestf
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'metode_pembayaran' => 'required|string|in:BRIVA,QRIS,OVO,DANA,LINKAJA,BCA_KLIKPAY,MANDIRI_CLICKPAY,ALFAMART,INDOMARET',
        ]);


        // Ambil user yang login via Sanctum
        $user = $request->user();

        // Opsional: Cek permission dengan Spatie
        // if (!$user->hasPermissionTo('create transaction')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Unauthorized action.',
        //     ], 403);
        // }

        // Generate invoice & amount
        $metode_pembayaran = $request->input('metode_pembayaran', 'BRIVA'); 
        $invoice = 'INV-' . date('YmdHis');
        $amount = 0;
        $orderItems = [];

        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $amount += $subtotal;

            $orderItems[] = [
                'sku' => 'PRD-' . $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'product_url' => 'https://tokokamu.com/product/' . $product->slug,
                // 'image_url' => $product->images->first()->image_url ?? 'https://tokokamu.com/default.jpg',
            ];
        }
        // dd($user);
        // Set signature Tripay
        $this->paymentGateway->setSignature($invoice, $amount);

        // Ambil Tripay token dari config
        $tripayToken = env('TRIPAY_API_KEY');

        $data = [
            'method' => $metode_pembayaran,
            'merchant_ref' => $invoice,
            'amount' => $amount,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'order_items' => $orderItems,
            'return_url' => 'https://domainanda.com/redirect',
            'expired_time' => time() + 24 * 60 * 60,
            'signature' => $this->paymentGateway->getSignature(),
        ];

        // Kirim ke Tripay API
        $http = new \GuzzleHttp\Client();    
        $response = $http->post('https://tripay.co.id/api-sandbox/transaction/create', [
            'headers' => [
                'Authorization' => 'Bearer '. $tripayToken,
            ],
            'json' => $data,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Transaction created successfully',
            'data' => json_decode($response->getBody()->getContents(), true),
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ]);
    }

    
}
Public function CekStatusTransaksi(Request $request){
        // Implementasi untuk cek status transaksi
        try {
            $reference_code = 
            
            $invoice = request()->input('invoice');
            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice is required',
                ], 422);
            }

            $tripayToken = env('TRIPAY_API_KEY');
            $http = new \GuzzleHttp\Client();
            $response = $http->get("https://tripay.co.id/api-sandbox/transaction/status/{$invoice}", [
                'headers' => [
                    'Authorization' => 'Bearer '. $tripayToken,
                ],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Transaction status retrieved successfully',
                'data' => json_decode($response->getBody()->getContents(), true),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
