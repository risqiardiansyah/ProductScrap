<?php

namespace App\Http\Repositories;

use App\Http\Resources\Friends;
use App\Http\Resources\ProdukTani;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\User;

class UserRepository
{
    public function __construct()
    {
    }

    public function getProduk()
    {
        $html = file_get_contents('https://hargapangan.id/tabel-harga/produsen/daerah');

        $start = stripos($html, 'id="report"');

        $end = stripos($html, '</table>', $offset = $start);

        $length = $end - $start;

        $htmlSection = substr($html, $start, $length);

        preg_match_all('@<div class="text-right">(.+)</div>@', $htmlSection, $matches);
        $listItems = $matches[1];

        preg_match_all('@<span>(.+)</span>@', $htmlSection, $matchesProduk);
        $listProduk = $matchesProduk[1];

        $harga = [];
        for ($p = 0; $p < count($listItems); $p++) {
            if ((($p + 1) % 6) == 0 && !strpos($listItems[$p], "strong")) {
                array_push($harga, $listItems[$p]);
            }
        }

        $produk = [];
        for ($h = 0; $h < count($listProduk); $h++) {
            if (!strpos($listProduk[$h], "strong") && !is_numeric($listProduk[$h])) {
                array_push($produk, $listProduk[$h]);
            }
        }

        $data = [];

        $first = true;
        for ($i = 0; $i < count($harga); $i++) {
            if ($i == 9 && $first == true) {
                $obj = [
                    "produk_nama" => "Telur Ayam Ras Segar (kg)",
                    "produk_info" => "Telur Ayam Ras Segar (kg)",
                    "produk_satuan" => 'Kg',
                    "produk_picture" => 'telur_ayam_ras_segar_(kg).png',
                    "produk_code" => 'telur_ayam_ras_segar_(kg)',
                    "produk_status" => 1,
                    "produk_satuan" => 'Kg',
                    "produk_harga" => str_replace('.', '', $harga[$i])
                ];
                array_push($data, (object)$obj);
                $first = false;
                // DB::table('produk_tani')->updateOrInsert(['produk_code' => $obj['produk_code']], $obj);
            } else if ($first == true) {
                $obj = [
                    "produk_nama" => $produk[$i],
                    "produk_info" => $produk[$i],
                    "produk_satuan" => 'Kg',
                    "produk_picture" => strtolower(str_replace(' ', '_', $produk[$i])) . '.png',
                    "produk_code" => strtolower(str_replace(' ', '_', $produk[$i])),
                    "produk_status" => 1,
                    "produk_satuan" => 'Kg',
                    "produk_harga" => str_replace('.', '', $harga[$i])
                ];
                array_push($data, (object)$obj);
                // DB::table('produk_tani')->updateOrInsert(['produk_code' => $obj['produk_code']], $obj);
            } else {
                $obj = [
                    "produk_nama" => $produk[$i - 1],
                    "produk_info" => $produk[$i - 1],
                    "produk_satuan" => 'Kg',
                    "produk_picture" => strtolower(str_replace(' ', '_', $produk[$i - 1])) . '.png',
                    "produk_code" => strtolower(str_replace(' ', '_', $produk[$i - 1])),
                    "produk_status" => 1,
                    "produk_satuan" => 'Kg',
                    "produk_harga" => str_replace('.', '', $harga[$i])
                ];
                array_push($data, (object)$obj);
                // DB::table('produk_tani')->updateOrInsert(['produk_code' => $obj['produk_code']], $obj);
            }
        }

        $data = ProdukTani::collection($data);
        return $data;
    }
}
