<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

trait CreateOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupCreateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/create', [
            'as'        => $routeName.'.create',
            'uses'      => $controller.'@create',
            'operation' => 'create',
        ]);

        Route::post($segment, [
            'as'        => $routeName.'.store',
            'uses'      => $controller.'@store',
            'operation' => 'create',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCreateDefaults()
    {
        $this->crud->allowAccess('create');

        $this->crud->operation('create', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
            $this->crud->setupDefaultSaveActions();
        });

        $this->crud->operation('list', function () {
            $this->crud->addButton('top', 'create', 'view', 'crud::buttons.create');
        });
    }

    /**
     * Show the form for creating inserting a new row.
     *
     * @return Response
     */
    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getCreateView(), $this->data);
    }
    
    /**
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        /**
         * Local timezone
         */
        date_default_timezone_set("Asia/Phnom_Penh");

        $this->crud->hasAccessOrFail('create');
        $url_type=$this->crud->entity_name;
        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        if ($url_type == "User") {
            $value = $this->crud->getStrippedSaveRequest();
            $updated_at = date('Y-m-d H:i:s');
            $name = $value['name'];
            $email = $value['email'];
            $password = $value['password'];
            $status = $value['status'];
            $role = $value['role'];
            $profile = $value['image'];
            $data = array('name'=>$name,'email'=>$email,'password'=>Hash::make($password),'status'=>$status,'role'=>$role,'image'=>$profile,'updated_at'=>$updated_at);
        
             // update the row in the db
             $item = $this->crud->create($data);
             $this->data['entry'] = $this->crud->entry = $item;

            // show a success message
            Alert::success(trans('backpack::crud.update_success'))->flash();

            // save the redirect choice for next time
            $this->crud->setSaveAction();
            return $this->crud->performSaveAction($item->getKey());
        }
        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
