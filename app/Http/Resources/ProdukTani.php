<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProdukTani extends JsonResource
{
    public function toArray($request)
    {
        return [
            'produk_code' => $this->produk_code,
            'produk_nama' => $this->produk_nama,
            'produk_info' => $this->produk_info,
            'produk_picture_ori' => $this->produk_picture,
            // 'produk_picture' => secure_asset('storage/tani/' . $this->produk_picture),
            'produk_picture' => 'https://api.temanpasar.com/storage/tani/' . $this->produk_picture,
            'produk_harga' => $this->produk_harga,
            'produk_harga_satuan' => 'Rp ' . number_format($this->produk_harga, 0, '.', '.'),
            'produk_status' => $this->produk_status,
            'produk_satuan' => $this->produk_satuan
        ];
    }
}
