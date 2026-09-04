<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('role', 'users')) {
            $forge = \Config\Database::forge();
            $forge->addColumn('users', [
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'user',
                    'after'      => 'password',
                ],
            ]);
        }

        $search = trim($this->request->getGet('q') ?? '');
        $builder = $this->userModel->orderBy('id', 'DESC');

        if (!empty($search)) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('email', $search)
                    ->orLike('phone', $search)
                    ->groupEnd();
        }

        $users = $builder->findAll();

        $data = [
            'pageTitle'  => 'Kelola Pengguna & Hak Akses Role',
            'activeMenu' => 'users',
            'users'      => $users,
            'search'     => $search,
        ];

        return view('admin/users/index', $data);
    }

    public function updateRole(int $id)
    {
        $role = trim($this->request->getPost('role') ?? 'user');
        $validRoles = ['user', 'administrator'];

        if (!in_array($role, $validRoles, true)) {
            return redirect()->back()->with('error', 'Role tidak valid.');
        }

        // Hindari mengubah akun sendiri menjadi non-admin jika ini satu-satunya akun yang login
        $currentUserId = session()->get('user_id');
        if ($currentUserId === $id && $role !== 'administrator') {
            return redirect()->back()->with('error', 'Anda tidak dapat mencabut hak administrator pada akun Anda sendiri.');
        }

        $this->userModel->update($id, ['role' => $role]);

        return redirect()->to('/admin/users')->with('success', 'Role pengguna berhasil diperbarui!');
    }

    public function resetPassword(int $id)
    {
        $newPass = trim($this->request->getPost('new_password') ?? '');
        if (strlen($newPass) < 6) {
            return redirect()->back()->with('error', 'Password minimal 6 karakter.');
        }

        $this->userModel->update($id, [
            'password' => password_hash($newPass, PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/admin/users')->with('success', 'Password pengguna berhasil direset!');
    }

    public function delete(int $id)
    {
        $currentUserId = session()->get('user_id');
        if ($currentUserId === $id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->userModel->delete($id);
        return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil dihapus.');
    }
}
