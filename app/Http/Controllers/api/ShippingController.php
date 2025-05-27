<?php

namespace App\Http\Controllers\Api;

use App\Utils\ApiAdapter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShippingController extends Controller
{

    protected ApiAdapter $ApiAdapter;
    public function __construct()
    {
        $this->ApiAdapter = new ApiAdapter();
    }

    public function get_destinations(Request $request)
    {
        
        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 0);

        $response = $this->ApiAdapter->get('https::/', [
            'query' => [
                'key' => env('RAJA_ONGKIR_API_KEY'),
                'q' => $search,
                'limit' => $limit,
                'offset' => $offset,
            ]
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mendapatkan data kota',
            'data' => $response['rajaongkir']['results'] ?? [],
        ]);

    }
}
