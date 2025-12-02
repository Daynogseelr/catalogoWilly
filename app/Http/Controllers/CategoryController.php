<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\DataTables\CategoryDataTable;
class CategoryController extends Controller{

    public function indexCategory(CategoryDataTable $dataTable)
    {
         if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            return $dataTable->render('categories.category');
        } else {
            return redirect()->route('storeIndex');
        }
    }

    public function storeCategory(Request $request){  
        $request->validate([
            'name' => 'required|min:2|max:50|string',
        ]);   
        $categoryId = $request->id;
        $category   =   Category::updateOrCreate(
            [
                'id' => $categoryId
            ],
            [
                'name' => $request->name, 
                'description' => $request->description,
                'status' => '1',
            ]); 
        return Response()->json($category);
    }
    public function editCategory(Request $request){   
        $where = array('id' => $request->id);
        $category  = Category::where($where)->first();
        return Response()->json($category);
    }
    public function destroyCategory(Request $request){
        $category = Category::where('id',$request->id)->delete();
        return Response()->json($category);
    }
    public function statusCategory(Request $request){ 
        $category = Category::find($request-> id);
        if ( $category->status == '1') {
            $category->update(['status' => '0']); 
        } else {
            $category->update(['status' => '1']); 
        }
        return Response()->json($category);   
    }
}
