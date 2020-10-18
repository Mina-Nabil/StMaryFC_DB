<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupsController extends Controller
{
    protected $data;
    protected $homeURL = 'groups/show';

    private function initDataArr()
    {
        $this->data['items'] = Group::all();
        $this->data['title'] = "Available Groups";
        $this->data['subTitle'] = "Manage all Available Groups";
        $this->data['cols'] = ['Group', 'Edit'];
        $this->data['atts'] = [
            'GRUP_NAME',
            ['edit' => ['url' => 'groups/edit/', 'att' => 'id']],
        ];
        $this->data['homeURL'] = $this->homeURL;
    }

    public function __construct()
    {
        $this->middleware("auth");
    }

    public function home()
    {
        $this->initDataArr();
        $this->data['formTitle'] = "Add Group";
        $this->data['formURL'] = "groups/insert";
        $this->data['isCancel'] = false;
        return view('users.groups', $this->data);
    }

    public function edit($id)
    {
        $this->initDataArr();
        $this->data['group'] = Group::findOrFail($id);
        $this->data['formTitle'] = "Edit Group ( " . $this->data['group']->GRUP_NAME . " )";
        $this->data['formURL'] = "groups/update";
        $this->data['isCancel'] = false;
        return view('users.groups', $this->data);
    }

    public function insert(Request $request)
    {

        $request->validate([
            "name"      => "required|unique:groups,GRUP_NAME",
        ]);

        $group = new Group();
        $group->GRUP_NAME = $request->name;
        $group->save();
        return redirect($this->homeURL);
    }

    public function update(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);
        $group = Group::findOrFail($request->id);

        $request->validate([
            "name" => ["required",  Rule::unique('groups', "GRUP_NAME")->ignore($group->GRUP_NAME, "GRUP_NAME"),],
            "id"        => "required",
        ]);

        $group->GRUP_NAME = $request->name;
        $group->save();

        return redirect($this->homeURL);
    }
}
