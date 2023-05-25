<?php

namespace App\Http\Controllers;

use App\Models\PlayersCatogory;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index()
    {
        $data = self::getPageDataArray();
        return view('users.categories', $data);
    }

    public function show($id)
    {
        $data = self::getPageDataArray($id);
        return view('users.categories', $data);  
    }

    public function manage(Request $request)
    {
        $request->validate([
            "categoryID"        =>  "nullable|exists:players_categories",
            "title"     =>  "required",
        ]);
        if ($request->categoryID) {
            $pc = PlayersCatogory::findOrFail($request->categoryID);
            $pc->editCategory($request->title, null);
        } else {
            PlayersCatogory::newCategory($request->title, null);
        }

        return redirect('users/categories');
    }

    public function setDetails(Request $request)
    {
        $request->validate([
            "category_id"   =>  "required|exists:players_categories,id",
            "due"           =>  "present|array",
            "due.*"         =>  "numeric",
            "attendance"    =>  "present|array",
            "attendance.*"  =>  "numeric",
        ]);

        /** @var PlayersCategory */
        $pc = PlayersCatogory::findOrFail($request->category_id);
        $detArray = [];
        foreach($request->due as $i => $duea){
            array_push($detArray, [
                "attendance"    =>  $request->attendance[$i],
                "payment"       =>  $duea,
            ]);
        }
        $pc->setCategoryDetails($detArray);
        return redirect('users/categories');

    }

    public function delete($id)
    {
        $pc = PlayersCatogory::findOrFail($id);
        $pc->deleteCategory();

        return redirect('users/categories');
    }

    /////data functions
    private static function getPageDataArray($id = null)
    {
        $data['categories'] = PlayersCatogory::all();
        if ($id) {
            $data['category'] = PlayersCatogory::findOrFail($id);
        }
        return $data;
    }
}
