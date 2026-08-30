<?php

namespace App\Controllers;

class EmergencyController extends BaseController
{
    /**
     * Preloaded directory of verified National & Public Emergency Contacts
     */
    public static function getEmergencyDirectory(): array
    {
        return [
            [
                'id'          => 'emergency_112',
                'name'        => 'Panggilan Darurat Terpadu Nasional',
                'category'    => 'Umum',
                'number'      => '112',
                'description' => 'Layanan darurat terintegrasi (Polisi, Medis, Damkar, Bencana) bebas pulsa 24 jam.',
                'icon'        => '🚨',
                'color'       => '#EF4444',
                'is_toll_free'=> true,
                'priority'    => 1,
            ],
            [
                'id'          => 'police_110',
                'name'        => 'Kepolisian RI (Polri)',
                'category'    => 'Keamanan',
                'number'      => '110',
                'description' => 'Bantuan keamanan, tindak kriminal, kecelakaan lalu lintas, dan posko polisi darurat.',
                'icon'        => '🚓',
                'color'       => '#3B82F6',
                'is_toll_free'=> true,
                'priority'    => 2,
            ],
            [
                'id'          => 'fire_113',
                'name'        => 'Pemadam Kebakaran & Penyelamatan (Damkar)',
                'category'    => 'Penyelamatan',
                'number'      => '113',
                'description' => 'Penanganan kebakaran, evakuasi binatang berbisa, pelepasan cincin, dan penyelamatan darurat.',
                'icon'        => '🚒',
                'color'       => '#F97316',
                'is_toll_free'=> true,
                'priority'    => 3,
            ],
            [
                'id'          => 'ambulance_118',
                'name'        => 'Ambulans & Gawat Darurat Medis',
                'category'    => 'Medis',
                'number'      => '118',
                'description' => 'Layanan ambulans gawat darurat dan penanganan pertolongan pertama Kemenkes.',
                'icon'        => '🚑',
                'color'       => '#10B981',
                'is_toll_free'=> true,
                'priority'    => 4,
            ],
            [
                'id'          => 'kemenkes_119',
                'name'        => 'National Command Center Medis (NCC 119)',
                'category'    => 'Medis',
                'number'      => '119',
                'description' => 'Sistem penanggulangan gawat darurat terpadu (SPGDT) Kementerian Kesehatan.',
                'icon'        => '🏥',
                'color'       => '#059669',
                'is_toll_free'=> true,
                'priority'    => 5,
            ],
            [
                'id'          => 'toll_jasamarga',
                'name'        => 'Derek & Bantuan Jalan Tol Jasa Marga',
                'category'    => 'Derek Tol',
                'number'      => '14080',
                'description' => 'Bantuan derek resmi jalan tol Jasa Marga, patroli tol, dan informasi jalan tol 24 jam.',
                'icon'        => '🚗',
                'color'       => '#8B5CF6',
                'is_toll_free'=> false,
                'priority'    => 6,
            ],
            [
                'id'          => 'toll_astrainfra',
                'name'        => 'Derek & Bantuan Tol Astra Infra',
                'category'    => 'Derek Tol',
                'number'      => '02189840000',
                'description' => 'Call center bantuan & derek jalan tol ruas Astra Infra (Cikopo-Palimanan, Tangerang-Merak, dll).',
                'icon'        => '🛣️',
                'color'       => '#6366F1',
                'is_toll_free'=> false,
                'priority'    => 7,
            ],
            [
                'id'          => 'pertamina_135',
                'name'        => 'Pertamina Delivery BBM Darurat (PDS)',
                'category'    => 'Derek Tol',
                'number'      => '135',
                'description' => 'Layanan antar BBM darurat saat mogok/kehabisan bensin di jalan tol dan non-tol.',
                'icon'        => '⛽',
                'color'       => '#D97706',
                'is_toll_free'=> false,
                'priority'    => 8,
            ],
            [
                'id'          => 'basarnas_115',
                'name'        => 'SAR & Basarnas (Pencarian & Pertolongan)',
                'category'    => 'Penyelamatan',
                'number'      => '115',
                'description' => 'Operasi pencarian dan pertolongan korban bencana alam, musibah pelayaran, dan penerbangan.',
                'icon'        => '⛑️',
                'color'       => '#EC4899',
                'is_toll_free'=> true,
                'priority'    => 9,
            ],
            [
                'id'          => 'pln_123',
                'name'        => 'PLN Gangguan Listrik & Korsleting',
                'category'    => 'Utilitas',
                'number'      => '123',
                'description' => 'Laporan korsleting listrik, tiang roboh, trafo meledak, dan padam darurat 24 jam.',
                'icon'        => '⚡',
                'color'       => '#0284C7',
                'is_toll_free'=> false,
                'priority'    => 10,
            ],
            [
                'id'          => 'pmi_darurat',
                'name'        => 'Palang Merah Indonesia (PMI Pusat)',
                'category'    => 'Medis',
                'number'      => '0217992325',
                'description' => 'Bantuan donor darah darurat, posko bencana alam, dan ambulans PMI.',
                'icon'        => '🩸',
                'color'       => '#E11D48',
                'is_toll_free'=> false,
                'priority'    => 11,
            ],
            [
                'id'          => 'bpjs_165',
                'name'        => 'BPJS Kesehatan Care Center',
                'category'    => 'Medis',
                'number'      => '165',
                'description' => 'Informasi faskes rujukan darurat, pelayanan administrasi dan kepesertaan 24 jam.',
                'icon'        => '🩺',
                'color'       => '#0D9488',
                'is_toll_free'=> false,
                'priority'    => 12,
            ],
        ];
    }

    /**
     * Web View: /emergency
     */
    public function index()
    {
        $directory = self::getEmergencyDirectory();
        $categories = array_values(array_unique(array_column($directory, 'category')));
        array_unshift($categories, 'Semua');

        $data = [
            'pageTitle'  => 'Layanan Darurat',
            'directory'  => $directory,
            'categories' => $categories,
        ];

        return view('emergency/index', $data);
    }

    /**
     * API Endpoint: GET /api/emergency
     */
    public function apiList()
    {
        $directory = self::getEmergencyDirectory();
        $categories = array_values(array_unique(array_column($directory, 'category')));
        array_unshift($categories, 'Semua');

        return $this->response->setJSON([
            'success'    => true,
            'directory'  => $directory,
            'categories' => $categories,
        ]);
    }
}
