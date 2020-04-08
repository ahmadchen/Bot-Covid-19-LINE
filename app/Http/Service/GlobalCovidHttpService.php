<?php

namespace App\Http\Service;

use App\Http\Service\Base\HttpService;
use Illuminate\Support\Facades\Http;

class GlobalCovidHttpService extends HttpService
{
    protected $BASE_URL = 'https://covid-19.mathdro.id/api';

    public function getCovidGlobalData()
    {
        $response = $this->getReq("/");
        $data = $response->json();

        return [
            "confirmed" => $data['confirmed']['value'],
            "recovered" => $data['recovered']['value'],
            "deaths" => $data['deaths']['value']
        ];
    }

    public function getCovidCountryData($query)
    {
        $response = $this->getReq("/countries");
        $countries = $response->json()['countries'];

        $foundCountry = null;
        foreach ($countries as $country)
        {
            $country = $country['name'];
            if (strtolower($country) == $query)
            {
                $foundCountry = $country;
                break;
            }
            if (strpos(strtolower($country), $query) !== false && $foundCountry == null)
            {
                $foundCountry = $country;
            }
        }

        if ($foundCountry == null)
        {
            return null;
        }

        $response = $this->getReq("/countries/" . $foundCountry);
        $countryData = $response->json();

        return [
            "country" => $foundCountry,
            "confirmed" => $countryData['confirmed']['value'],
            "recovered" => $countryData['recovered']['value'],
            "deaths" => $countryData['deaths']['value']
        ];
    }
}
