<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task; 



class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function index()
    {
      $data = [];
        if (\Auth::check()) { // 認証済みの場合
            // 認証済みユーザを取得
            $user = \Auth::user();
            
            // ユーザの投稿の一覧を作成日時の降順で取得
            // （後のChapterで他ユーザの投稿も取得するように変更しますが、現時点ではこのユーザの投稿のみ取得します）
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);
            $data = [
                'user' => $user,
                'tasks' => $tasks,
            ];
        }
        
        // dashboardビューでそれらを表示
        return view('dashboard', $data);
        
         // トップページへリダイレクトさせる
         return redirect('/');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    
         $task = new Task;

        // メッセージ作成ビューを表示
        return view('tasks.create', [
            'task' => $task,]);
       
            
            
       // トップページへリダイレクトさせる
         return redirect('/');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:255',
        ]);
        
        
          // 認証済みユーザ（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->tasks()->create([
            'content' => $request->content,
            'status' => $request->status,
        ]);
        

        // トップページへリダイレクトさせる
         return redirect('/');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
      // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        
        if (\Auth::id() === $task->user_id) {    

        // メッセージ詳細ビューでそれを表示
        return view('tasks.show', [
            'task' => $task,
        ]);
        }
        
         // トップページへリダイレクトさせる
         return redirect('/');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
          // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        
        if (\Auth::id() === $task->user_id) {
        

        // メッセージ編集ビューでそれを表示
        return view('tasks.edit', [
            'task' => $task,
        ]);
        }
        
         // トップページへリダイレクトさせる
         return redirect('/');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:255',
        ]);
        
    
        // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
      

    // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は投稿を更新
        if (\Auth::id() === $task->user_id) {
        $task->status = $request->status;
        $task->content = $request->content;
        $task->save();

             return redirect('/');
                
        }

        // トップページへリダイレクトさせる
        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
         // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        
        
       // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は投稿を削除
        if (\Auth::id() === $task->user_id) {
            $task->delete();
            return redirect('/')
                ->with('success','Delete Successful');
        }

        return redirect('/')
            ->with('Delete Failed');
    }
}
