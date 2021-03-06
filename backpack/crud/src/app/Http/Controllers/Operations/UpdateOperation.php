<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use App\User;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/**
 * @author
 */
trait UpdateOperation
{

    /**
     * Define which routes are needed for this operation.
     *
     * @param string $name       Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupUpdateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment . '/{id}/edit', [
            'as'        => $routeName . '.edit',
            'uses'      => $controller . '@edit',
            'operation' => 'update',
        ]);

        Route::put($segment . '/{id}', [
            'as'        => $routeName . '.update',
            'uses'      => $controller . '@update',
            'operation' => 'update',
        ]);

        Route::get($segment . '/{id}/translate/{lang}', [
            'as'        => $routeName . '.translateItem',
            'uses'      => $controller . '@translateItem',
            'operation' => 'update',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupUpdateDefaults()
    {
        $this->crud->allowAccess('update');

        $this->crud->operation('update', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();

            if ($this->crud->getModel()->translationEnabled()) {
                $this->crud->addField([
                    'name' => 'locale',
                    'type' => 'hidden',
                    'value' => request()->input('locale') ?? app()->getLocale(),
                ]);
            }

            $this->crud->setupDefaultSaveActions();
        });

        $this->crud->operation(['list', 'show'], function () {
            $this->crud->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
        });
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * Update the specified resource in the database.
     *
     * @return Response
     */
    public function update()
    {

        /**
         * Local timezone
         */
        date_default_timezone_set("Asia/Phnom_Penh");

        /**
         * Get entity_name
         */
        $url_type = $this->crud->entity_name;

        $this->crud->hasAccessOrFail('update');

        if ($url_type == "User") {
            $oldPassword = "";
            $id = $this->crud->getCurrentEntryId();
            $value = $this->crud->getStrippedSaveRequest();
            $user = User::find($id);

            $name = $value['name'];
            $email = $value['email'];
            $status = $value['status'];
            $role = $value['role'];
            $updatedAt = date('Y-m-d H:i:s');
            $profile = $value['image'];

            if ($value['password'] == "") {
                $oldPassword = $user->password;
            } else {
                request()->validate([
                    'password'  =>  'min:8|max:40|confirmed',
                ]);
                $oldPassword = Hash::make($value['password']);
            }
            $data = array('name' => $name, 'email' => $email, 'password' => $oldPassword, 'status' => $status, 'role' => $role, 'image' => $profile, 'updated_at' => $updatedAt);
            if ($email == $user->email) {

                // execute the FormRequest authorization and validation, if one is required
                request()->validate([
                    'name'      =>  'required|min:5|max:50',
                    'email'     =>  'required|email',
                ]);

                // update the row in the db
                $item = $this->crud->update($id, $data);
            } else {

                // execute the FormRequest authorization and validation, if one is required
                request()->validate([
                    'name'      =>  'required|min:5|max:50',
                    'email'     =>  'required|email|unique:users',
                ]);

                // update the row in the db
                $item = $this->crud->update($id, $data);
            }

            Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();
            return $this->crud->performSaveAction($item->getKey());
        } else {

            // execute the FormRequest authorization and validation, if one is required
            $request = $this->crud->validateRequest();

            // update the row in the db
            $item = $this->crud->update(
                $request->get($this->crud->model->getKeyName()),
                $this->crud->getStrippedSaveRequest()
            );
            $this->data['entry'] = $this->crud->entry = $item;

            // show a success message
            Alert::success(trans('backpack::crud.update_success'))->flash();

            // save the redirect choice for next time
            $this->crud->setSaveAction();

            return $this->crud->performSaveAction($item->getKey());
        }
    }
}
