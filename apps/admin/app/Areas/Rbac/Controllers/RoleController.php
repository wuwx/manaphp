<?php

namespace App\Areas\Rbac\Controllers;

use App\Areas\Rbac\Models\AdminRole;
use App\Areas\Rbac\Models\Role;
use ManaPHP\Mvc\Controller;

class RoleController extends Controller
{
    public function indexAction()
    {
        return $this->request->isAjax()
            ? Role::select()
                ->whereContains('role_name', input('keyword', ''))
                ->whereNotIn('role_name', ['guest', 'user', 'admin'])
                ->orderBy(['role_id' => SORT_DESC])
                ->paginate()
            : null;
    }

    public function listAction()
    {
        return Role::lists(['display_name', 'role_name']);
    }

    public function createAction()
    {
        if ($role_name = input('role_name', '')) {
            $permissions = ',' . implode(',', $this->authorization->buildAllowed($role_name)) . ',';
        } else {
            $permissions = '';
        }

        return Role::rCreate(['permissions' => $permissions]);
    }

    public function editAction()
    {
        return Role::rUpdate();
    }

    public function disableAction()
    {
        return Role::rUpdate(['enabled' => 0]);
    }

    public function enableAction()
    {
        return Role::rUpdate(['enabled' => 1]);
    }

    public function deleteAction()
    {
        if (!$this->request->isGet()) {
            $role = Role::get(input('role_id'));

            if (AdminRole::exists(['role_id' => $role->role_id])) {
                return '删除失败: 有用户绑定到此角色';
            }

            return $role->delete();
        }
    }
}