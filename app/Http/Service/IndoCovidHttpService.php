<?php

namespace App\Http\Service;

use App\Http\Service\Base\HttpService;
use Illuminate\Support\Facades\Http;

class IndoCovidHttpService extends HttpService
{
    protected $BASE_URL = 'https://indonesia-covid-19.mathdro.id/api';

    public function getAllProvinceData()
    {
        $response = $this->getReq("/provinsi");
        $provinces = $response->json()['data'];

        $data = [];
        foreach($provinces as $province)
        {
            $data[] = [
                "name" => $province['provinsi'],
                "positive" => $province['kasusPosi'],
                "recovered" => $province['kasusSemb'],
                "deaths" => $province['kasusMeni']
            ];
        }

        return $data;
    }

    public function getProvinceData($query)
    {
        $provinces = $this->getAllProvinceData();

        $foundProvince = null;
        foreach ($provinces as $province)
        {
            $name = strtolower($province['name']);
            if ($name == $query)
            {
                return $province;
            }
            if (strpos($name, $query) !== false && $foundProvince == null)
            {
                $foundProvince = $province;
            }
        }

        return $foundProvince;
    }

    public function getIndonesiaData()
    {
        $response = $this->getReq("/");
        $data = $response->json();

        return [
            "total" => $data['jumlahKasus'],
            "recovered" => $data['sembuh'],
            "deaths" => $data['meninggal']
        ];
    }
}
