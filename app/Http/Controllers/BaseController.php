<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App;

class BaseController extends Controller
{
    
    var $table;
    var $model;
    
    public function __construct()
    {
        $this->init();
    }
    
    private function init()
    {
        $className= get_class($this);
        $controller= substr($className, strripos($className, '\\') + 1);
//         $controller = pathinfo($class, PATHINFO_FILENAME);
        $name = str_replace('Controller', '', $controller);
        if (!$this->table)
        {
            $this->table = lcfirst($name);
        }
        if (!$this->model)
        {
            $class_name= "\App\\$name";
            if(class_exists($class_name = $class_name.'Model')) {
                $this->model = new $class_name();
            } elseif(class_exists($class_name)) {
                $this->model = new $class_name();
            }
        }
    }
    
    public function getLists($table, $extend=[], $return=false)
    {
        $model = DB::table($table);
        
        $default = [
            'join'      => '',
            'select'    =>  [ '*' ],
            'orderBy'   =>  ['id', 'desc'],
            'paginate'  => 10,
        ];
        $extend = array_merge($default, $extend);
        
        $multArray = [ 'orderBy', 'select', 'join' ];
        foreach ($extend as $k=>$v)
        {
            if (!$v) continue;
            if (in_array($k, $multArray)) {
                call_user_func_array(array($model, $k), $v);
            } else {
                call_user_func(array($model, $k), $v);
            }
        }
        $model->where('deleted_at', null);
        $data = $model->get()->toJson();
        $data = json_decode($data, true);
        if ($return)
        {
            return $data;
        } else {
            $this->response(['lists'=>$data]);
        }
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->getLists($this->table);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
//         $model = DB::table($this->table);
        $model = $this->model;
        $info = $model->where('id', $id)->first();
        $this->response(['info'=>$info]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function response($data=[])
    {
        $default = [
            'errcode' => 0
        ];
        $data = array_merge($default, $data);
        echo json_encode($data);die;
    }
}
