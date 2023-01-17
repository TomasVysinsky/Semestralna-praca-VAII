<?php

namespace App\Controllers;

use App\Core\AControllerBase;
use App\Core\Responses\Response;
use App\Models\Adminaccount;
use App\Models\Title;

class AdminaccountController extends AControllerBase
{
    /**
     *
     * @return \App\Core\Responses\RedirectResponse|\App\Core\Responses\Response
     */
    public function index(): Response
    {
        return $this->html();
    }

    public function logging(): Response
    {
        return $this->html(viewName: 'login.form');
    }

    /**
     * Login a user
     * @return \App\Core\Responses\RedirectResponse|\App\Core\Responses\ViewResponse
     */
    public function login(): Response
    {
        $admins = Adminaccount::getAll();
        $username = $this->request()->getValue('login');
        $password = $this->request()->getValue('password');
        foreach ($admins as $admin) {
            /*$user = User::getOne($login);*/
            if ($admin->getUsername() == $username) {
                if ($this->app->getAuth()->login($username, $password, true))
                    return $this->redirect("?c=adminaccount");
            }
        }

        $data = ['message' => 'Zlý login alebo heslo!'];
        return $this->html($data);
    }
    public function edit(): Response {
        $id = $this->request()->getValue('id');

        $postToEdit = Title::getOne($id);
        return $this->html($postToEdit, viewName: 'edit.form');
    }

    public function store(): Response {
        $id = $this->request()->getValue('id');

        if ($id == null)
            return $this->redirect("?c=adminaccount");
        $post = Title::getOne($id);
        $post->setTitle($this->request()->getValue('nadpis'));
        $post->setText($this->request()->getValue('obsah'));
        $post->save();

        return $this->redirect("?c=adminaccount");
    }

    public function delete() {
        $id = $this->request()->getValue('id');
        $user = Adminaccount::getOne($id);
        $current = null;
        if ($user->getUsername() == $_SESSION['user']) {
            $current = $user;
        }

        if ($current) {
            $this->app->getAuth()->logout();
            $current->delete();
            return $this->redirect("?c=home");
        }
        $user->delete();
        return $this->redirect("?c=adminaccount&a=adminmanagement");
    }

    public function adminmanagement(): Response {
        return $this->html();
    }

    public function create() {
        return $this->html(viewName: 'register.form');
    }

    public function register()
    {
        $usersToCheck = Adminaccount::getAll();
        $username = $this->request()->getValue('login');
        foreach ($usersToCheck as $user) {
            /*$user = User::getOne($login);*/
            if ($user->getUsername() == $username) {
                return $this->html(['message' => 'Username already exists. Try another one'], viewName: 'register.form');
            }
        }

        $password = $this->request()->getValue('password');
        if ($password != $this->request()->getValue('confPassword'))
        {
            return $this->html(['message' => "Passwords don't match!"], viewName: 'register.form');
        }

        $user = new Adminaccount();
        $user->setUsername($username);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user->setPassword($hash);
        $user->save();
        return $this->redirect("?c=adminaccount&a=adminmanagement");
    }
}