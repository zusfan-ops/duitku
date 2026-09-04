<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceListingModel extends Model
{
    protected $table            = 'marketplace_listings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'title',
        'slug',
        'type',             // 'sale', 'rent', 'service'
        'category',         // Motor, Mobil, Properti, Elektronik, Pijat, Kebersihan, dll
        'condition',        // 'new', 'like_new', 'used_good', 'used_fair', or null for service
        'price',
        'rent_period',      // 'hari', 'bulan', 'tahun'
        'service_type',     // 'panggilan', 'di_tempat', 'keduanya', 'online'
        'service_area',     // Jangkauan radius/area, misal: Semarang & Sekitarnya
        'service_hours',    // Jam kerja / operasional, misal: 24 Jam, 08:00 - 21:00
        'rate_unit',        // 'per_sesi', 'per_panggilan', 'per_jam', 'per_unit', 'per_pekerjaan', 'mulai_dari'
        'experience_years', // Pengalaman, garansi, keahlian
        'description',
        'location',
        'whatsapp',
        'third_party_url',  // Shopee, Tokopedia, Bukalapak
        'status',           // 'active', 'sold', 'rented', 'inactive'
        'views_count',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil daftar iklan dengan filter & relasi gambar utama, seller, dan jumlah komentar
     */
    public function getListings(array $filters = [], int $limit = 30, int $offset = 0): array
    {
        $builder = $this->db->table($this->table . ' l');
        $builder->select('
            l.*,
            u.name AS seller_name,
            u.username AS seller_username,
            u.avatar AS seller_avatar,
            u.phone AS seller_phone,
            (SELECT mi.image_url FROM marketplace_images mi WHERE mi.listing_id = l.id ORDER BY mi.is_primary DESC, mi.sort_order ASC, mi.id ASC LIMIT 1) AS primary_image,
            (SELECT COUNT(*) FROM marketplace_images mi2 WHERE mi2.listing_id = l.id) AS image_count,
            (SELECT COUNT(*) FROM marketplace_comments mc WHERE mc.listing_id = l.id) AS comment_count
        ', false);
        $builder->join('users u', 'u.id = l.user_id', 'inner');

        // Status filter: default active unless requested otherwise
        if (!empty($filters['status'])) {
            $builder->where('l.status', $filters['status']);
        } else {
            $builder->where('l.status', 'active');
        }

        // Tipe filter: sale, rent, atau service
        if (!empty($filters['type']) && in_array($filters['type'], ['sale', 'rent', 'service'])) {
            $builder->where('l.type', $filters['type']);
        }

        // Kategori filter
        if (!empty($filters['category']) && $filters['category'] !== 'Semua') {
            $builder->where('l.category', $filters['category']);
        }

        // Kondisi filter
        if (!empty($filters['condition']) && $filters['condition'] !== 'Semua') {
            $builder->where('l.condition', $filters['condition']);
        }

        // Lokasi / Kota filter
        if (!empty($filters['location'])) {
            $builder->like('l.location', $filters['location']);
        }

        // User ID filter (iklan milik user tertentu)
        if (!empty($filters['user_id'])) {
            $builder->where('l.user_id', (int)$filters['user_id']);
        }

        // Search query (keyword pada judul, deskripsi, atau kategori)
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $builder->groupStart()
                    ->like('l.title', $s)
                    ->orLike('l.description', $s)
                    ->orLike('l.category', $s)
                    ->orLike('l.location', $s)
                    ->groupEnd();
        }

        // Sorting
        $sort = $filters['sort'] ?? 'latest';
        switch ($sort) {
            case 'price_low':
                $builder->orderBy('l.price', 'ASC');
                break;
            case 'price_high':
                $builder->orderBy('l.price', 'DESC');
                break;
            case 'popular':
                $builder->orderBy('l.views_count', 'DESC');
                break;
            case 'latest':
            default:
                $builder->orderBy('l.created_at', 'DESC');
                break;
        }

        $builder->limit($limit, $offset);
        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$r) {
            $r['id'] = (int) $r['id'];
            $r['user_id'] = (int) $r['user_id'];
            $r['price'] = (float) $r['price'];
            $r['views_count'] = (int) ($r['views_count'] ?? 0);
            $r['image_count'] = (int) ($r['image_count'] ?? 0);
            $r['comment_count'] = (int) ($r['comment_count'] ?? 0);
        }
        unset($r);

        return $rows;
    }

    /**
     * Ambil detail listing lengkap beserta images, seller, dan comments
     */
    public function getListingDetail(int $id): ?array
    {
        $builder = $this->db->table($this->table . ' l');
        $builder->select('
            l.*,
            u.name AS seller_name,
            u.username AS seller_username,
            u.email AS seller_email,
            u.avatar AS seller_avatar,
            u.phone AS seller_phone_registered,
            u.created_at AS seller_joined_at
        ');
        $builder->join('users u', 'u.id = l.user_id', 'inner');
        $builder->where('l.id', $id);

        $listing = $builder->get()->getRowArray();
        if (!$listing) {
            return null;
        }

        $listing['id'] = (int) $listing['id'];
        $listing['user_id'] = (int) $listing['user_id'];
        $listing['price'] = (float) $listing['price'];
        $listing['views_count'] = (int) ($listing['views_count'] ?? 0);

        // Ambil semua foto produk
        $imgBuilder = $this->db->table('marketplace_images');
        $imgBuilder->where('listing_id', $id)
                   ->orderBy('is_primary', 'DESC')
                   ->orderBy('sort_order', 'ASC')
                   ->orderBy('id', 'ASC');
        $listing['images'] = $imgBuilder->get()->getResultArray();

        // Ambil komentar produk beserta nama & avatar pengomentar
        $cmtBuilder = $this->db->table('marketplace_comments mc');
        $cmtBuilder->select('
            mc.*,
            u.name AS user_name,
            u.username AS user_username,
            u.avatar AS user_avatar
        ');
        $cmtBuilder->join('users u', 'u.id = mc.user_id', 'inner');
        $cmtBuilder->where('mc.listing_id', $id);
        $cmtBuilder->orderBy('mc.created_at', 'DESC');
        $listing['comments'] = $cmtBuilder->get()->getResultArray();

        return $listing;
    }

    /**
     * Tambah jumlah tayangan produk (view count)
     */
    public function incrementViews(int $id): void
    {
        $this->db->table($this->table)
                 ->where('id', $id)
                 ->set('views_count', 'views_count + 1', false)
                 ->update();
    }

    /**
     * Ambil daftar kategori produk barang (jual / sewa)
     */
    public static function getProductCategoriesList(): array
    {
        return [
            'Motor & Skuter',
            'Mobil & Truk',
            'Rumah & Properti',
            'Elektronik & Gadget',
            'Komputer & Laptop',
            'Perabotan & Rumah Tangga',
            'Pakaian & Aksesoris',
            'Hobi, Musik & Olahraga',
            'Peralatan Usaha / Bisnis',
            'Lainnya',
        ];
    }

    /**
     * Ambil daftar kategori khusus layanan jasa (pijat, cleaning, tambal ban, service, dll)
     */
    public static function getServiceCategoriesList(): array
    {
        return [
            'Pijat & Refleksi Panggilan',
            'Kebersihan Rumah & Kantor (Cleaning)',
            'Bengkel & Tambal Ban Panggilan',
            'Service AC & Pendingin',
            'Service Elektronik & Kelistrikan',
            'Pertukangan & Renovasi Rumah',
            'Angkutan Barang & Pindahan',
            'Guru Les & Kursus Privat',
            'Salon & Barbershop Panggilan',
            'Servis Komputer & Gadget Panggilan',
            'Laundry & Cuci Kasur / Sepatu',
            'Jasa Desain, Foto & Video',
            'Jasa Lainnya',
        ];
    }

    /**
     * Ambil semua kategori (barang + jasa)
     */
    public static function getCategoriesList(): array
    {
        return array_merge(self::getProductCategoriesList(), self::getServiceCategoriesList());
    }

    /**
     * Ambil daftar satuan tarif jasa
     */
    public static function getRateUnitsList(): array
    {
        return [
            'per_sesi'       => 'Per Sesi (Pijat / Terapi)',
            'per_panggilan'  => 'Per Panggilan / Kunjungan',
            'per_jam'        => 'Per Jam',
            'per_unit'       => 'Per Unit / Titik (AC / Ban / Alat)',
            'per_pekerjaan'  => 'Per Pekerjaan / Borongan',
            'mulai_dari'     => 'Tarif Mulai Dari (Bisa Nego)',
        ];
    }

    /**
     * Ambil daftar sistem layanan jasa
     */
    public static function getServiceTypesList(): array
    {
        return [
            'panggilan' => '🛵 Panggilan ke Rumah / Lokasi Konsumen (Home Service)',
            'di_tempat' => '🏢 Datang ke Bengkel / Tempat Penyedia',
            'keduanya'  => '🔄 Bisa Panggilan & Datang ke Tempat',
            'online'    => '💻 Layanan Online / Jarak Jauh',
        ];
    }
}
