<?php

namespace App\Http\Resources\Profiling;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


        $data = json_decode($this->resource->data);

        $image = null;
        $idNumber = null;
        $passportNumber = null;
        $gender = null;
        $nationality = null;
        $race = null;
        $age = null;
        $address = null;
        $name = null;

        $information = array();

        // Is Agency
        if (isset($data[0]->agency)) {
            foreach ($data as $d) {
                if (isset($data[0]->agency) || isset($data[0]->organization_name)) {
                    if ($d->agency == 'pdrm') {
                        $information[] = $this->pdrmResource($d);
                    }

                    if ($d->agency == 'jim') {
                        $information[] = $this->jimResource($d);
                    }

                    if ($d->agency == 'jpn') {
                        $information[] = $this->jpnResource($d);
                    }

                    if ($d->agency == 'ssm') {
                        $information[] = $this->ssmResource($d);
                    }

                    if ($d->agency == 'skmm') {
                        $information[] = $this->skmmResource($d);
                    }

                    if ($d->agency == 'kpdn') {
                        $information[] = $this->kpdnResource($d);
                    }
                } else {
                    $information[] = $this->generalResource($d);
                }

                $image = $this->getData($image, $d->image ?? null);
                $name = $this->getData($name, $d->poi_name ?? $d->name ?? null);
                $idNumber = $this->getData($idNumber, $d->id_number ?? null);
                $passportNumber = $this->getData($passportNumber, $d->passport_number ?? null);
                $gender = $this->getData($gender, $d->gender ?? null);
                $nationality = $this->getData($nationality, $d->nationality ?? null);
                $race = $this->getData($race, $d->race ?? null);
                $age = $this->getData($age, $d->age ?? null);
                $address = $this->getData($address, $d->address ?? null);
            }
        } else {
            $socialMedias = json_decode($this->resource->data, true);
            foreach ($socialMedias as $d) {
                if (array_key_exists('social_media', $d)) {
                    if (strtolower($d['social_media']) == 'facebook') {
                        $information[] = $this->facebookResource($d);
                    } else if (strtolower($d['social_media']) == 'twitter') {
                        $information[] = $this->twitterResource($d);
                    } else if (strtolower($d['social_media']) == 'linkedin') {
                        $information[] = $this->linkedinResource($d);
                    } else if (strtolower($d['social_media']) == 'tiktok') {
                        $information[] = $this->tiktokResource($d);
                    } else if (strtolower($d['social_media']) == 'instagram') {
                        $information[] = $this->instagramResource($d);
                    }
                }
            }
        }

        if (empty($information)) {
            $information = null;
        }

        return [
            'id' => $this->resource->id,
//            'data' => json_decode($this->resource->data),
            'information' => $information ?? null,
            'image' => $image ?? null,
            'id_number' => $idNumber,
            'passport_number' => $passportNumber,
            'name' => $name, // 'name' => $this->resource->name ?? '
            'gender' => $gender,
            'nationality' => $nationality,
            'race' => $race,
            'age' => $age,
            'address' => $address,
            'created_at' => date_format(date_create($this->resource->created_at), 'd-m-Y'),
        ];
    }

    private function facebookResource($data): array
    {
        $intelligence = array();
        $intelligence['social_media'] = 'facebook';
        $intelligence['count_posts'] = $data['count_posts'] ?? null;
        $intelligence['count_user_profiles'] = $data['count_user_profiles']?? null;
        $intelligence['posts'] = $data['posts'] ?? null;
        $intelligence['user_profiles'] = $data['user_profiles'] ?? null;
        $intelligence['website_link'] = $data['website_link'];
        $intelligence['result'] = $data['result'] ?? null;
        return $intelligence;
    }

    private function instagramResource($data): array
    {
        $intelligence = array();
        $intelligence['social_media'] = 'instagram';
        $intelligence['type'] = $data['type'] ?? null;
        $intelligence['posts'] = $data['posts'] ?? null;
        $intelligence['user_profiles'] = $data['user_profiles'] ?? null;
        $intelligence['personal_infos'] = $data['personal_infos'] ?? null;
        $intelligence['website_link'] = $data['website_link'];
        $intelligence['result'] = $data['result'] ?? null;
        return $intelligence;
    }

    private function twitterResource($data): array
    {
        $intelligence = array();
        $intelligence['social_media'] = 'twitter';
//        $intelligence['count_posts'] = $data->count_posts ?? null;
//        $intelligence['count_user_profiles'] = $data->count_user_profiles ?? null;
        $intelligence['posts'] = $data['posts'] ?? null;
        $intelligence['user_profiles'] = $data['user_profiles'] ?? null;
        $intelligence['website_link'] = $data['website_link'];
        $intelligence['result'] = $data['result'] ?? null;
        return $intelligence;
    }

    private function linkedinResource($data): array
    {
        $intelligence = array();
        $intelligence['social_media'] = 'linkedin';
        $intelligence['type'] = $data['type'] ?? null;
        $intelligence['posts'] = $data['posts'] ?? null;
        $intelligence['user_profiles'] = $data['user_profiles'] ?? null;
        $intelligence['website_link'] = $data['website_link'];
        $intelligence['result'] = $data['result'] ?? null;
        return $intelligence;
    }

    private function tiktokResource($data): array
    {
        $intelligence = array();
        $intelligence['social_media'] = 'tiktok';
        $intelligence['type'] = $data['type'] ?? null;
        $intelligence['posts'] = $data['posts'] ?? null;
        $intelligence['user_profiles'] = $data['user_profiles'] ?? null;
        $intelligence['website_link'] = $data['website_link'];
        $intelligence['result'] = $data['result'] ?? null;
        return $intelligence;
    }

    private function generalResource($data): array
    {
        $intelligence = array();
        $information = array();

        $information[] = $this->detail('Nama', $data->name ?? null);
        $information[] = $this->detail('IC Number', $data->id_number ?? null);
        $information[] = $this->detail('Nombor Telefon', $data->phone_number ?? null);
        $information[] = $this->detail('Nombor Pendaftaran Syarikat', $data->business_registration ?? null);
        $information[] = $this->detail('Nombor Akaun Bank', $data->bank_account ?? null);
        $information[] = $this->detail('Lain-Lain', $data->other ?? null);

        $intelligence['agency'] = 'general information';
        $intelligence['information'] = $information;
        $intelligence['picture'] = $data->poi_pics ?? null;

        return $intelligence;
    }

    private function pdrmResource($data): array
    {
        $intelligence = array();
        $information = array();

        $information[] = $this->detail('Nama Suspek', $data->poi_name ?? null);
        $information[] = $this->detail('Nombor Kad Pengenalan Suspek', $data->poi_icno ?? null);
        $information[] = $this->detail('Nombor Telefon Suspek', $data->poi_phoneno ?? null);
//        $information[] = $this->detail('Gambar Suspek', $data->poi_pics ?? null);
        $information[] = $this->detail('Bilangan Laporan Polis ke atas No. KP Suspek', $data->poi_pdrm_report_sum ?? null);
        $information[] = $this->detail('Nombor Akaun Bank yang Terlibat dengan Transaksi', $data->poi_trans_acc_no ?? null);
        $information[] = $this->detail('Statistik Rekod Carian Nombor Telefon Suspek di Semak Mula', $data->poi_phoneno_stats ?? null);
        $information[] = $this->detail('Statistik Rekod Carian Nombor Akaun Bank di Semak Mula', $data->poi_accno_stats ?? null);
        $information[] = $this->detail('Maklumat Tangkapan', $data->poi_arrest_info ?? null);
        $information[] = $this->detail('Maklumat Ringkas Laporan Berkaitan', $data->poi_report_info ?? null);
        $information[] = $this->detail('Jumlah Kerugian', $data->poi_case_loss ?? null);

        $intelligence['agency'] = 'pdrm';
        $intelligence['information'] = $information;
        $intelligence['picture'] = $data->poi_pics ?? null;

        return $intelligence;
    }

    private function jimResource($data): array
    {
        $intelligence = array();
        $information = array();

        $information[] = $this->detail('Nama Suspek', $data->poi_name ?? null);
//        $information[] = $this->detail('Gambar Suspek', $data->poi_pics ?? null);
        $information[] = $this->detail('Nombor Passport Semasa Suspek', $data->poi_current_passport ?? null);
        $information[] = $this->detail('Nombor Passport Lama Suspek', $data->poi_old_passport ?? null);
        $information[] = $this->detail('Nombor Pas Sempadan/Border Pass', $data->poi_border_pass ?? null);
        $information[] = $this->detail('Negara Asal Suspek', $data->poi_origin_country ?? null);
        $information[] = $this->detail('Nama Syarikat Majikan Pekerja', $data->poi_employer ?? null);
        $information[] = $this->detail('Alamat Syarikat Majikan Pekerja', $data->poi_employer_add ?? null);
        $information[] = $this->detail('Cap Jari Suspek', $data->poi_thumbprint ?? null);
        $information[] = $this->detail('Tarikh Masuk Negara', $data->poi_arrival_date ?? null);
        $information[] = $this->detail('Tarikh Keluar Negara', $data->poi_departure_date ?? null);
        $information[] = $this->detail('Negara Destinasi', $data->poi_depart_country ?? null);
        $information[] = $this->detail('Maklumat Pelepasan (Boarding) Suspek', $data->poi_boardingpass_info ?? null);
        $information[] = $this->detail('Maklumat Pas (Lawatan/Kerja) Suspek', $data->poi_visitpass_info ?? null);

        $intelligence['agency'] = 'jim';
        $intelligence['information'] = $information;
        $intelligence['picture'] = $data->poi_pics ?? null;

        return $intelligence;
    }

    private function jpnResource($data): array
    {
        $intelligence = array();
        $information = array();

        $information[] = $this->detail('Nama Suspek', $data->poi_name ?? null);
//        $information[] = $this->detail('Gambar Suspek', $data->poi_pics ?? null);
        $information[] = $this->detail('No. Kad Pengenalan Lama Suspek (jika ada)', $data->poi_old_icno ?? null);
        $information[] = $this->detail('No. ID Tentera/Polis (jika ada)', $data->poi_id_others ?? null);
        $information[] = $this->detail('Tarikh Lahir Suspek', $data->poi_dob ?? null);
        $information[] = $this->detail('Jantina', $data->poi_sex ?? null);
        $information[] = $this->detail('Tarikh Mati Suspek (jika ada)', $data->poi_dod ?? null);
        $information[] = $this->detail('Taraf Penduduk', $data->poi_citizenship ?? null);
        $information[] = $this->detail('Alamat Tetap', $data->poi_address ?? null);
        $information[] = $this->detail('Maklumat Penama/Suami/Isteri/Anak', $data->poi_family ?? null);
        $information[] = $this->detail('Maklumat Ibu Bapa', $data->poi_parent ?? null);

        $intelligence['agency'] = 'jpn';
        $intelligence['information'] = $information;
        $intelligence['picture'] = $data->poi_pics ?? null;

        return $intelligence;
    }

    private function ssmResource($data): array
    {
        $intelligence = array();
        $information = array();

        $information[] = $this->detail('Nama Lama Syarikat (Jika ada)', $data->poi_oldcompany ?? null);
        $information[] = $this->detail('Nama Pemilik Syarikat', $data->poi_company_owner ?? null);
        $information[] = $this->detail('Nombor Kad Pengenalan Pemilik Syarikat', $data->poi_owner_icno ?? null);
        $information[] = $this->detail('Alamat Pemilik Syarikat', $data->poi_owner_add ?? null);
        $information[] = $this->detail('Nama Pengarah Syarikat', $data->poi_directors ?? null);
        $information[] = $this->detail('Nombor Kad Pengenalan Pengarah Syarikat', $data->poi_directors_icno ?? null);
        $information[] = $this->detail('Alamat Pengarah Syarikat', $data->poi_directors_add ?? null);
        $information[] = $this->detail('Tarikh Kemaskini Maklumat', $data->poi_update_as ?? null);
        $information[] = $this->detail('Nombor Pendaftaran', $data->poi_companyregno ?? null);
        $information[] = $this->detail('Tarikh Penubuhan', $data->poi_date_of_est ?? null);
        $information[] = $this->detail('Tarikh Pendaftaran', $data->poi_date_of_reg ?? null);
        $information[] = $this->detail('Jenis Syarikat', $data->poi_company_type ?? null);
        $information[] = $this->detail('Status Syarikat', $data->poi_company_status ?? null);
        $information[] = $this->detail('Alamat Pendaftaran', $data->poi_reg_add ?? null);
        $information[] = $this->detail('Alamat Perniagaan', $data->poi_biz_add ?? null);
        $information[] = $this->detail('Sifat Perniagaan', $data->poi_biz_nature ?? null);
        $information[] = $this->detail('Jenis Pemilikan', $data->poi_own_type ?? null);
        $information[] = $this->detail('Jenis Perniagaan', $data->poi_biz_type ?? null);

        $intelligence['agency'] = 'ssm';
        $intelligence['information'] = $information;

        return $intelligence;
    }

    private function skmmResource($data): array
    {
        $intelligence = array();
        $information = array();

        $information[] = $this->detail('Nama Pemilik Nombor Telefon', $data->poi_phone ?? null);
        $information[] = $this->detail('Nombor Kad Pengenalan Pemilik Nombor Telefon', $data->poi_phone_icno ?? null);
        $information[] = $this->detail('Alamat Pemilik Nombor Telefon', $data->poi_phone_add ?? null);
        $information[] = $this->detail('Alamat IP', $data->poi_phone_ip ?? null);
        $information[] = $this->detail('Log Panggilan', $data->poi_phone_log ?? null);
        $information[] = $this->detail('Nombor IMEI', $data->poi_imei ?? null);
        $information[] = $this->detail('Nombor ICCID', $data->poi_iccid ?? null);
        $information[] = $this->detail('Nombor MEID', $data->poi_meid ?? null);

        $intelligence['agency'] = 'skmm';
        $intelligence['information'] = $information;

        return $intelligence;
    }

    private function kpdnResource($data): array
    {
        $intelligence = array();
        $information = array();

        $information[] = $this->detail('Nama Syarikat/Premis/Penjual yang Terlibat', $data->poi_name ?? '-');
        $information[] = $this->detail('Nama Pembekal Perkhidmatan', $data->poi_service_prov ?? '-');
        $information[] = $this->detail('Nombor Akaun Bank yang Terlibat dengan Transaksi', $data->poi_trans_acc_no ?? '-');
        $information[] = $this->detail('Nombor Telefon', $data->poi_phoneno ?? '-');
        $information[] = $this->detail('Emel', $data->poi_email_add ?? '-');
        $information[] = $this->detail('Alamat Penuh Penjual/Pihat yand Diadu (jika ada)', $data->poi_address_report ?? '-');
        $information[] = $this->detail('Statistik Rekod Aduan (jika ada)', $data->poi_stats_report ?? '-');
        $information[] = $this->detail('Jumlah Kerugian', $data->poi_case_loss ?? '-');
        $information[] = $this->detail('Nama Pengadu', $data->poi_reporter_name ?? '-');
        $information[] = $this->detail('No. Kad Pengenalan Pengadu', $data->poi_reporter_icno ?? '-');
        $information[] = $this->detail('Kategori Aduan di eAduan', $data->report_category ?? '-');
        $information[] = $this->detail('Subkategori Aduan di eAduan', $data->report_subcategory ?? '-');
        $information[] = $this->detail('Keterangan Aduan di eAduan', $data->report_description ?? '-');

        $intelligence['agency'] = 'kpdn';
        $intelligence['information'] = $information;

        return $intelligence;
    }

    private function getData($variable, $data)
    {
        if (!$variable && isset($data)) {
            $variable = $data ?? null;
        }

        return $variable;
    }

    private function detail ($title, $data)
    {
        return [
            'title' => $title,
            'value' => $data,
        ];
    }
}
