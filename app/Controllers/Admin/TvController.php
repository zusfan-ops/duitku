<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TvChannelModel;

class TvController extends BaseController
{
    protected TvChannelModel $tvModel;

    public function __construct()
    {
        $this->tvModel = new TvChannelModel();
    }

    public function index()
    {
        $this->tvModel->ensureTable();
        $channels = $this->tvModel->orderBy('sort_order', 'ASC')
                                  ->orderBy('name', 'ASC')
                                  ->findAll();

        $categories = $this->tvModel->getCategories();

        $data = [
            'pageTitle'  => 'Kelola TV Streaming & M3U Channels',
            'activeMenu' => 'tv',
            'channels'   => $channels,
            'categories' => $categories,
        ];

        return view('admin/tv/index', $data);
    }

    public function store()
    {
        $name        = trim($this->request->getPost('name') ?? '');
        $category    = trim($this->request->getPost('category') ?? 'Nasional');
        $streamUrl   = trim($this->request->getPost('stream_url') ?? '');
        $logoUrl     = trim($this->request->getPost('logo_url') ?? '');
        $description = trim($this->request->getPost('description') ?? '');
        $sortOrder   = (int) ($this->request->getPost('sort_order') ?? 0);
        $isActive    = $this->request->getPost('is_active') ? 1 : 0;

        if (empty($name) || empty($streamUrl)) {
            return redirect()->back()->withInput()->with('error', 'Nama channel dan Alamat Streaming (URL M3U/M3U8) wajib diisi.');
        }

        // Handle logo file upload if provided
        $logoFile = $this->request->getFile('logo_file');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/tv_logos';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $logoFile->getRandomName();
            $logoFile->move($uploadPath, $newName);
            $logoUrl = '/uploads/tv_logos/' . $newName;
        }

        $this->tvModel->insert([
            'name'        => $name,
            'category'    => $category ?: 'Nasional',
            'stream_url'  => $streamUrl,
            'logo_url'    => $logoUrl ?: null,
            'description' => $description ?: null,
            'sort_order'  => $sortOrder,
            'is_active'   => $isActive,
        ]);

        return redirect()->to('/admin/tv')->with('success', 'Channel TV Streaming berhasil ditambahkan!');
    }

    public function update(int $id)
    {
        $channel = $this->tvModel->find($id);
        if (!$channel) {
            return redirect()->to('/admin/tv')->with('error', 'Channel TV tidak ditemukan.');
        }

        $name        = trim($this->request->getPost('name') ?? '');
        $category    = trim($this->request->getPost('category') ?? 'Nasional');
        $streamUrl   = trim($this->request->getPost('stream_url') ?? '');
        $logoUrl     = trim($this->request->getPost('logo_url') ?? '');
        $description = trim($this->request->getPost('description') ?? '');
        $sortOrder   = (int) ($this->request->getPost('sort_order') ?? 0);
        $isActive    = $this->request->getPost('is_active') ? 1 : 0;

        if (empty($name) || empty($streamUrl)) {
            return redirect()->back()->withInput()->with('error', 'Nama channel dan Alamat Streaming (URL M3U/M3U8) wajib diisi.');
        }

        // Handle logo file upload if provided
        $logoFile = $this->request->getFile('logo_file');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/tv_logos';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $logoFile->getRandomName();
            $logoFile->move($uploadPath, $newName);
            $logoUrl = '/uploads/tv_logos/' . $newName;
        } elseif (empty($logoUrl)) {
            $logoUrl = $channel['logo_url'];
        }

        $this->tvModel->update($id, [
            'name'        => $name,
            'category'    => $category ?: 'Nasional',
            'stream_url'  => $streamUrl,
            'logo_url'    => $logoUrl ?: null,
            'description' => $description ?: null,
            'sort_order'  => $sortOrder,
            'is_active'   => $isActive,
        ]);

        return redirect()->to('/admin/tv')->with('success', 'Channel TV Streaming berhasil diperbarui!');
    }

    public function toggle(int $id)
    {
        $channel = $this->tvModel->find($id);
        if ($channel) {
            $newActive = $channel['is_active'] ? 0 : 1;
            $this->tvModel->update($id, ['is_active' => $newActive]);
            return redirect()->to('/admin/tv')->with('success', 'Status channel berhasil diubah.');
        }
        return redirect()->to('/admin/tv')->with('error', 'Channel tidak ditemukan.');
    }

    public function delete(int $id)
    {
        $this->tvModel->delete($id);
        return redirect()->to('/admin/tv')->with('success', 'Channel TV berhasil dihapus.');
    }

    /**
     * Batch import M3U playlist (Upload file .m3u atau paste raw M3U text)
     */
    public function importM3u()
    {
        $m3uContent = '';
        $m3uFile = $this->request->getFile('m3u_file');
        if ($m3uFile && $m3uFile->isValid() && !$m3uFile->hasMoved()) {
            $m3uContent = file_get_contents($m3uFile->getTempName());
        } else {
            $m3uContent = trim($this->request->getPost('m3u_text') ?? '');
        }

        $defaultCategory = trim($this->request->getPost('default_category') ?? 'Nasional');

        if (empty($m3uContent)) {
            return redirect()->back()->with('error', 'Silakan upload file .m3u atau tempel teks M3U playlist.');
        }

        $lines = preg_split('/\r\n|\r|\n/', $m3uContent);
        $importedCount = 0;
        $currentName = '';
        $currentLogo = '';
        $currentGroup = $defaultCategory;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (str_starts_with($line, '#EXTINF')) {
                // Parse EXTINF metadata
                // e.g., #EXTINF:-1 tvg-logo="https://example.com/logo.png" group-title="Berita",Kompas TV
                if (preg_match('/tvg-logo="([^"]+)"/i', $line, $mLogo)) {
                    $currentLogo = $mLogo[1];
                } else {
                    $currentLogo = '';
                }

                if (preg_match('/group-title="([^"]+)"/i', $line, $mGroup)) {
                    $currentGroup = $mGroup[1];
                } else {
                    $currentGroup = $defaultCategory;
                }

                // Name is after last comma
                if (preg_match('/,(.+)$/', $line, $mName)) {
                    $currentName = trim($mName[1]);
                } else {
                    $currentName = 'Channel ' . ($importedCount + 1);
                }
            } elseif (!str_starts_with($line, '#') && (str_starts_with($line, 'http://') || str_starts_with($line, 'https://') || str_starts_with($line, 'rtmp://'))) {
                // This is the stream URL
                $streamUrl = $line;
                $name = $currentName ?: ('Channel ' . ($importedCount + 1));

                $this->tvModel->insert([
                    'name'        => $name,
                    'category'    => $currentGroup ?: 'Nasional',
                    'stream_url'  => $streamUrl,
                    'logo_url'    => $currentLogo ?: null,
                    'description' => 'Imported via M3U playlist',
                    'is_active'   => 1,
                    'sort_order'  => $importedCount,
                ]);

                $importedCount++;
                $currentName = '';
                $currentLogo = '';
                $currentGroup = $defaultCategory;
            }
        }

        if ($importedCount > 0) {
            return redirect()->to('/admin/tv')->with('success', "Berhasil mengimpor {$importedCount} channel TV dari playlist M3U!");
        } else {
            return redirect()->back()->with('error', 'Tidak ada channel TV valid yang ditemukan dalam teks/file M3U.');
        }
    }
}
