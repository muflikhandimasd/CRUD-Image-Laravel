<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    public function show(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Data not found'
                ],
                404
            );
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $rules = [
            'name' => 'required',
            'detail' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ];

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        if ($image = $request->file('image')) {
            $destinationPath = 'image/';
            $fileName = $image->getClientOriginalName();
            $image->move($destinationPath, $fileName);
            $input['image'] = "$fileName";
        }

        $product = Product::create($input);
        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Data not found'
                ],
                404
            );
        }
        $input = $request->all();
        if ($request->hasFile('image')) {
            $pathImage = 'image/' . $product->image;
            if (File::exists($pathImage)) {
                File::delete($pathImage);
            }

            $file = $request->file('image');
            $fileName = $file->getClientOriginalName();
            $file->move('image/', $fileName);
            $input['image'] = $fileName;
        }
        $product->update($input);
        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Data not found'
                ],
                404
            );
        }
        $pathImage = 'image/' . $product->image;
        if (File::exists($pathImage)) {
            File::delete($pathImage);
        }

        $product->delete();
        return response()->json([
            'status' => true,
            'message' => 'Product successfully deleted'
        ]);
    }
}
