<?php

namespace App\Controllers\Api;

use App\Models\DigitalDocumentModel;

class DocumentController extends ApiController
{
    protected DigitalDocumentModel $docModel;

    public function __construct()
    {
        $this->docModel = new DigitalDocumentModel();
    }

    /**
     * Simpan foto base64 ke folder uploads/documents, kembalikan path.
     */
    private function storePhoto(?string $base64): ?string
    {
        if (empty($base64)) return null;

        $rawB64 = $base64;
        $ext = 'jpg';
        if (preg_match('/^data:image\/(\w+);base64,/', $rawB64, $type)) {
            $rawB64 = substr($rawB64, strpos($rawB64, ',') + 1);
            $ext = strtolower($type[1]);
            if ($ext === 'jpeg') $ext = 'jpg';
            if (!in_array($ext, ['jpg', 'png', 'webp', 'gif'], true)) $ext = 'jpg';
        }
        $decoded = base64_decode($rawB64);
        if ($decoded === false) return null;

        $uploadDir = FCPATH . 'uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = 'doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        file_put_contents($uploadDir . $filename, $decoded);

        return '/uploads/documents/' . $filename;
    }

    /**
     * Daftar dokumen + ringkasan.
     * GET /api/documents?category=Identitas
     */
    public function index()
    {
        $userId = $this->uid();
        $category = trim((string)$this->request->getGet('category'));

        $list    = $this->docModel->getForUser($userId, $category);
        $summary = $this->docModel->getSummary($userId);
        $expiring = $this->docModel->getExpiring($userId);

        return $this->ok([
            'documents' => $list,
            'summary'   => $summary,
            'expiring'  => $expiring,
        ]);
    }

    /**
     * Detail satu dokumen.
     * GET /api/documents/(:num)
     */
    public function show(int $id)
    {
        $userId = $this->uid();
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return $this->fail('Dokumen tidak ditemukan.');
        }

        return $this->ok(['document' => $doc]);
    }

    /**
     * Simpan dokumen baru (termasuk upload foto depan + belakang).
     * POST /api/documents/store
     */
    public function store()
    {
        $userId = $this->uid();
        $post   = $this->request->getPost();
        if (empty($post)) {
            $post = $this->request->getJSON(true) ?? [];
        }

        $name = trim($post['name'] ?? '');
        if (empty($name)) {
            return $this->fail('Nama dokumen wajib diisi.');
        }

        $photoPath = $this->storePhoto($post['photo_base64'] ?? ($post['photo_path'] ?? null));
        $photoBack = $this->storePhoto($post['photo_back_base64'] ?? ($post['photo_back_path'] ?? null));

        $id = $this->docModel->insert([
            'user_id'           => $userId,
            'name'              => $name,
            'document_type'     => trim($post['document_type'] ?? 'Lainnya'),
            'category'          => trim($post['category'] ?? 'Identitas'),
            'document_number'   => $post['document_number'] ?? null,
            'issued_date'       => $post['issued_date'] ?? null,
            'expiry_date'       => $post['expiry_date'] ?? null,
            'issuing_authority' => $post['issuing_authority'] ?? null,
            'owner_name'        => $post['owner_name'] ?? null,
            'notes'             => trim($post['notes'] ?? ''),
            'photo_path'        => $photoPath,
            'photo_back_path'   => $photoBack,
            'storage_location'  => $post['storage_location'] ?? null,
            'notify_before_days'=> (int)($post['notify_before_days'] ?? 30),
            'status'            => $post['status'] ?? 'active',
            'is_favorite'       => !empty($post['is_favorite']) ? 1 : 0,
        ]);

        return $this->ok([
            'document_id' => $id,
            'message'     => 'Dokumen berhasil disimpan.',
            'document'    => $this->docModel->find($id),
        ]);
    }

    /**
     * Perbarui dokumen.
     * POST /api/documents/update/(:num)
     */
    public function update(int $id)
    {
        $userId = $this->uid();
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return $this->fail('Dokumen tidak ditemukan.');
        }

        $post = $this->request->getPost();
        if (empty($post)) {
            $post = $this->request->getJSON(true) ?? [];
        }

        $data = [];
        foreach ([
            'name', 'document_type', 'category', 'document_number',
            'issued_date', 'expiry_date', 'issuing_authority', 'owner_name',
            'notes', 'storage_location', 'notify_before_days', 'status',
        ] as $field) {
            if (array_key_exists($field, $post)) {
                $data[$field] = $post[$field];
            }
        }
        if (array_key_exists('is_favorite', $post)) {
            $data['is_favorite'] = $post['is_favorite'] ? 1 : 0;
        }

        // Foto baru (opsional — hanya jika dikirim)
        if (!empty($post['photo_base64'])) {
            $path = $this->storePhoto($post['photo_base64']);
            if ($path) $data['photo_path'] = $path;
        }
        if (!empty($post['photo_back_base64'])) {
            $path = $this->storePhoto($post['photo_back_base64']);
            if ($path) $data['photo_back_path'] = $path;
        }

        $this->docModel->update($id, $data);

        return $this->ok([
            'message'  => 'Dokumen berhasil diperbarui.',
            'document' => $this->docModel->find($id),
        ]);
    }

    /**
     * Tandai favorit / hapus favorit.
     * POST /api/documents/favorite/(:num)
     */
    public function toggleFavorite(int $id)
    {
        $userId = $this->uid();
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return $this->fail('Dokumen tidak ditemukan.');
        }

        $newVal = (int)$doc['is_favorite'] ? 0 : 1;
        $this->docModel->update($id, ['is_favorite' => $newVal]);

        return $this->ok([
            'message' => $newVal ? 'Dokumen ditandai sebagai favorit.' : 'Favorit dibatalkan.',
            'is_favorite' => $newVal,
        ]);
    }

    /**
     * Hapus dokumen.
     * POST /api/documents/delete/(:num)
     */
    public function delete(int $id)
    {
        $userId = $this->uid();
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return $this->fail('Dokumen tidak ditemukan.');
        }

        $this->docModel->delete($id);

        return $this->ok(['message' => 'Dokumen berhasil dihapus.']);
    }
}
