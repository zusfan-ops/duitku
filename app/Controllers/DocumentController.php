<?php

namespace App\Controllers;

use App\Models\DigitalDocumentModel;
use App\Models\UserModel;

class DocumentController extends BaseController
{
    protected DigitalDocumentModel $docModel;
    protected UserModel            $userModel;

    public function __construct()
    {
        $this->docModel  = new DigitalDocumentModel();
        $this->userModel = new UserModel();
    }

    /**
     * Halaman daftar dokumen digital.
     * GET /documents
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        $category = trim((string)$this->request->getGet('category'));
        $list     = $this->docModel->getForUser($userId, $category);
        $summary  = $this->docModel->getSummary($userId);

        return view('document/index', [
            'pageTitle' => 'Dokumen Digital & Catatan Penting',
            'user'      => $user,
            'documents' => $list,
            'summary'   => $summary,
        ]);
    }

    /**
     * Detail satu dokumen.
     * GET /documents/(:num)
     */
    public function show(int $id)
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return redirect()->to('/documents')->with('error', 'Dokumen tidak ditemukan.');
        }

        return view('document/detail', [
            'pageTitle' => $doc['name'] . ' — Dokumen',
            'user'      => $user,
            'document'  => $doc,
        ]);
    }

    /**
     * Simpan dokumen baru.
     * POST /documents/store
     */
    public function store()
    {
        $userId = session()->get('user_id');
        $post   = $this->request->getPost();

        $name = trim($post['name'] ?? '');
        if (empty($name)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama dokumen wajib diisi.']);
        }

        $photoPath = $this->handleFile('photo_front');
        $photoBack = $this->handleFile('photo_back');

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
            'status'            => 'active',
            'is_favorite'       => !empty($post['is_favorite']) ? 1 : 0,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dokumen berhasil disimpan.',
            'id'      => $id,
        ]);
    }

    /**
     * Perbarui dokumen.
     * POST /documents/update/(:num)
     */
    public function update(int $id)
    {
        $userId = session()->get('user_id');
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dokumen tidak ditemukan.']);
        }

        $post = $this->request->getPost();
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

        $photoPath = $this->handleFile('photo_front');
        if ($photoPath) $data['photo_path'] = $photoPath;
        $photoBack = $this->handleFile('photo_back');
        if ($photoBack) $data['photo_back_path'] = $photoBack;

        $this->docModel->update($id, $data);

        return $this->response->setJSON(['success' => true, 'message' => 'Dokumen berhasil diperbarui.']);
    }

    /**
     * Toggle favorit dokumen.
     * POST /documents/favorite/(:num)
     */
    public function toggleFavorite(int $id)
    {
        $userId = session()->get('user_id');
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dokumen tidak ditemukan.']);
        }

        $newVal = (int)$doc['is_favorite'] ? 0 : 1;
        $this->docModel->update($id, ['is_favorite' => $newVal]);

        return $this->response->setJSON([
            'success'     => true,
            'is_favorite' => $newVal,
        ]);
    }

    /**
     * Hapus dokumen.
     * POST /documents/delete/(:num)
     */
    public function delete(int $id)
    {
        $userId = session()->get('user_id');
        $doc    = $this->docModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$doc) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dokumen tidak ditemukan.']);
        }

        $this->docModel->delete($id);

        return $this->response->setJSON(['success' => true, 'message' => 'Dokumen berhasil dihapus.']);
    }

    /**
     * Tangani upload foto dokumen, kembalikan path atau null.
     */
    private function handleFile(string $field): ?string
    {
        $file = $this->request->getFile($field);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadDir = FCPATH . 'uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = 'doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $file->getClientExtension();
        $file->move($uploadDir, $newName);

        return '/uploads/documents/' . $newName;
    }
}
