<?php

namespace App\Http\Controllers\Api;

use App\Utils\tripay;
use App\Models\Product;
use App\Utils\ApiAdapter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaksi;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PaymentGateway extends Controller
{
    protected $paymentGateway;
    protected ApiAdapter $api;
    protected tripay $tripay;

    public function __construct()
    {
        $this->paymentGateway = new Tripay();
        $this->api = new ApiAdapter();
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
                    // 'product_url' => 'https://tokokamu.com/product/' . $product->slug,
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
                'expired_time' => time() + 24 * 60 * 60, // 24 jam
                'signature' => $this->paymentGateway->getSignature(),
            ];

            $http = new \GuzzleHttp\Client();
            // $response = $http->post('https://tripay.co.id/api-sandbox/transaction/create', [
            //     'headers' => [
            //         'Authorization' => 'Bearer '. $tripayToken,
            //     ],
            //     'json' => $data,
            // ]);

            $response = $this->api->post('https://tripay.co.id/api-sandbox/transaction/create', $data, [
                'Authorization' => 'Bearer ' . $tripayToken,
            ]);

            if (!$response || !isset($response['success']) || $response['success'] !== true) {
                // throw new \Exception('Gagal membuat transaksi di Tripay.');
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Gagal membuat transaksi di Tripay.',
                        'data' => $response,
                    ],
                    500,
                );
            } else {
                Transaksi::create([
                    'kode_transaksi' => $invoice,
                    'kode_referensi' => $response['data']['reference'],
                    'user_id' => $user->id,
                    // 'user_id' => $user->id,
                    // 'total_harga' => $amount,
                    // 'metode_pembayaran' =>
                    // 'status' => 'PENDING',
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => $response,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function CekDetailTransaksi(Request $request)
    {
        try {
            // Validasi input
            $request->validate(
                [
                    'reference' => 'required|exists:transaksi,kode_referensi',
                ],
                [
                    'reference.required' => 'Kode Referensi diperlukan.',
                    'reference.exists' => 'Referensi tidak ditemukan dalam database.',
                ],
            );

            $reference = $request->input('reference');
            $tripayToken = env('TRIPAY_API_KEY');
            $endpoint = 'https://tripay.co.id/api-sandbox/transaction/detail';

            // Request ke Tripay
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tripayToken,
            ])->get($endpoint, [
                'reference' => $reference,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Status transaksi berhasil diambil',
                    'data' => $response->json(),
                ]);
            }

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Gagal mengambil status transaksi',
                    'data' => $response->json(),
                ],
                $response->status(),
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function CekStatusPembayaran(Request $request)
    {
        try {
            $reference = $request->input('reference');

            $request->validate(
                [
                    'reference' => 'required|exists:transaksi,kode_referensi',
                ],
                [
                    'reference.required' => 'Kode referensi diperlukan',
                    'reference.exists' => 'Kode referensi tidak valid',
                ],
            );

            // $reference_database = Transaksi::where('kode_referensi', operator: $reference)->first();

            // if (!$reference) {
            //     return response()->json(
            //         [
            //             'status' => false,
            //             'message' => 'Kode referensi invalid',
            //         ],
            //         422,
            //     );
            // }

            $tripayToken = env('TRIPAY_API_KEY');
            $endpoint = 'https://tripay.co.id/api-sandbox/transaction/detail';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tripayToken,
            ])->get($endpoint, [
                'reference' => $reference,
            ]);

            if ($response->successful()) {
                $status = $response->json('data.status');
                $status = $status === 'PAID' ? 'Lunas' : ($status === 'EXPIRED' ? 'Kedaluwarsa' : ($status === 'PENDING' ? 'Menunggu Pembayaran' : 'Belum Bayar'));
                return response()->json([
                    'status' => true,
                    'message' => 'Status pembayaran berhasil diambil',
                    'data' => $status,
                ]);
            }

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Gagal mengambil status pembayaran',
                    'data' => $response->json(),
                ],
                $response->status(),
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                ],
                500,
            );
        }
    }
}
