<?php

namespace App\Http\Controllers;

use App\shopping_cart;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;

class _FrontShoppingCart extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
            $data = shopping_cart::orderBy('shopping_carts.id', 'DESC')
                ->where('shopping_carts.user_id', $user->id)
                ->join('products', 'shopping_carts.product_id', '=', 'products.id')
                ->join('variation_products', 'variation_products.id', '=', 'shopping_carts.product_variation_id')
                ->select('shopping_carts.id', 'products.name', 'variation_products.label',
                    'variation_products.price_normal',
                    'variation_products.price_regular'
                )
                ->get();

            $data_total = shopping_cart::orderBy('shopping_carts.id', 'DESC')
                ->where('shopping_carts.user_id', $user->id)
                ->join('products', 'shopping_carts.product_id', '=', 'products.id')
                ->join('variation_products', 'variation_products.id', '=', 'shopping_carts.product_variation_id')
                ->select(
                    DB::raw('sum(variation_products.price_normal) as price_normal'),
                    DB::raw('sum(variation_products.price_regular) as price_regular')
                )
                ->groupBy('products.id')
                ->get();

            $response = array(
                'status' => 'success',
                'data' => [
                    'list' => $data,
                    'total' => $data_total
                ],
                'code' => 0
            );
            return response()->json($response);

        } catch (\Exception $e) {

            $response = array(
                'status' => 'fail',
                'msg' => $e->getMessage(),
                'code' => 1
            );

            return response()->json($response, 500);

        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fields = array();
        $request->request->remove('_location');


        foreach ($request->all() as $key => $value) {
            if ($key !== 'user_id') {
                $fields[$key] = $value;
            };
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $fields['user_id'] = $user->id;
            shopping_cart::insertGetId($fields);
            $data = shopping_cart::where('user_id', $user->id)
                ->get();

            $response = array(
                'status' => 'success',
                'msg' => 'Insertado',
                'data' => $data,
                'code' => 0
            );
            return response()->json($response);
        } catch (\Exception $e) {
            $response = array(
                'status' => 'fail',
                'code' => 5,
                'error' => $e->getMessage()
            );
            return response()->json($response);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {
            $request->request->remove('_location');
            $fields = array();
            foreach ($request->all() as $key => $value) {
                if ($key !== 'id') {
                    $fields[$key] = $value;
                };
            }

            shopping_cart::where('id', $id)
                ->update($fields);

            $data = shopping_cart::find($id);


            $response = array(
                'status' => 'success',
                'msg' => 'Actualizado',
                'data' => $data,
                'code' => 0
            );
            return response()->json($response);


        } catch (\Exception $e) {

            $response = array(
                'status' => 'fail',
                'msg' => $e->getMessage(),
                'code' => 1
            );

            return response()->json($response, 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {

            shopping_cart::where('id', $id)
                ->delete();

            $response = array(
                'status' => 'success',
                'msg' => 'Eliminado',
                'code' => 0
            );
            return response()->json($response);


        } catch (\Exception $e) {

            $response = array(
                'status' => 'fail',
                'msg' => $e->getMessage(),
                'code' => 1
            );

            return response()->json($response, 500);

        }
    }
}
