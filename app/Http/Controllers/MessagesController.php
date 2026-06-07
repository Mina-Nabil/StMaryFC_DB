<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    protected $data;
    protected $homeURL = 'messages/show';

    private function initDataArr()
    {
        $this->data['items'] = MessageTemplate::orderByDesc('is_system')->orderBy('name')->get();
        $this->data['title'] = "Message Templates";
        $this->data['subTitle'] = "Manage every message the system sends";
        $this->data['keys'] = MessageTemplate::keys();
        $this->data['cols'] = ['Name', 'Type', 'Active', 'Edit', 'Delete'];
        $this->data['atts'] = [
            'name',
            'type_label',
            [
                'toggle' => [
                    "att"   =>  "is_active",
                    "url"   =>  "messages/toggle/",
                    "states" => [
                        "1" => "Active",
                        "0" => "Inactive",
                    ],
                    "actions" => [
                        "1" => "Disable",
                        "0" => "Activate",
                    ],
                    "classes" => [
                        "1" => "label-success",
                        "0" => "label-danger",
                    ],
                ]
            ],
            ['edit' => ['url' => 'messages/edit/', 'att' => 'id']],
            ['del' => ['url' => 'messages/delete/', 'att' => 'id']],
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
        $this->data['formTitle'] = "Add Template";
        $this->data['formURL'] = "messages/insert";
        $this->data['template'] = null;
        return view('messages.index', $this->data);
    }

    public function edit($id)
    {
        $this->initDataArr();
        $this->data['template'] = MessageTemplate::findOrFail($id);
        $this->data['formTitle'] = "Edit Template ( " . $this->data['template']->name . " )";
        $this->data['formURL'] = "messages/update";
        return view('messages.index', $this->data);
    }

    public function insert(Request $request)
    {
        $request->validate([
            "name" => "required",
            "body" => "required",
        ]);

        $template = new MessageTemplate();
        $template->name = $request->name;
        $template->body = $request->body;
        $template->is_system = false;
        $template->is_active = $request->has('is_active');
        $template->save();

        return redirect($this->homeURL);
    }

    public function update(Request $request)
    {
        $request->validate([
            "id"   => "required",
            "name" => "required",
            "body" => "required",
        ]);

        $template = MessageTemplate::findOrFail($request->id);
        $template->name = $request->name;
        $template->body = $request->body;
        $template->is_active = $request->has('is_active');
        // is_system and key are immutable from the form (system rows keep their stable slug).
        $template->save();

        return redirect($this->homeURL);
    }

    public function delete($id)
    {
        $template = MessageTemplate::findOrFail($id);
        if ($template->is_system) {
            return redirect($this->homeURL)->with('error', 'System templates cannot be deleted.');
        }
        $template->delete();
        return redirect($this->homeURL);
    }

    public function toggle($id)
    {
        $template = MessageTemplate::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();
        return back();
    }
}
