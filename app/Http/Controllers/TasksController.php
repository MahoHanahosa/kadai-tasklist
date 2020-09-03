<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Task;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //getでtasks/にアクセスされた場合の「一覧表示処理」
    public function index()
    {
        
        
        $data = [];
        if (\Auth::check()) { // 認証済みの場合
            // 認証済みユーザを取得
            $user = \Auth::user();
            // ユーザの投稿の一覧を作成日時の降順で取得
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);

            $data = [
                'user' => $user,
                'tasks' => $tasks,
            ];
        }
        
        //タスク一覧表示
        return view('tasks.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    //getでtasks/createにアクセスされた場合の「新規登録画面処理」（フォーム）
    public function create()
    {
        //インスタンス生成
        $task = new Task;

        // タスク作成ビューを表示
        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //postでtasks/にアクセスされた場合の「新規登録処理」（データベースへ保存）
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'content' => 'required|max:255',
            'status' => 'required|max:10',
        ]);
        
        //認証済みユーザのタスク作成としてデータ作成、保存
        $request->user()->tasks()->create([
            'status' => $request->status,
            'content'=> $request->content,
        ]);
        
        //トップページへリダイレクト
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
        //idの値でタスクを検索して取得
        $task = Task::findOrFail($id);

        //タスク詳細ビューでそれを表示
        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //getでtasks/id/editにアクセスされた場合の「更新画面表示処理」（フォーム）
    public function edit($id)
    {
        //idの値でタスクを検索して取得
        $task = Task::findOrFail($id);

        //タスク編集ビューでそれを表示
        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //putまたはpatchでtasks/idにアクセスされた場合の「更新処理」（データベースへ上書き保存）
    public function update(Request $request, $id)
    {
        // バリデーション
        $request->validate([
            'content' => 'required|max:255',
            'status' => 'required|max:10',
        ]);
        
        //idの値でタスクを検索して取得
        $task = Task::findOrFail($id);
        
        //認証済みユーザがそのタスク所有者である場合、そのタスクを削除
        if (\Auth::id() === $task->user_id) {
            $request->user()->tasks()->update([
                'status' => $request->status,
                'content'=> $request->content,
            ]);
            
        }

        //トップページへリダイレクト
        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //deleteでtasks/idにアクセスされた場合の「削除処理」
    public function destroy($id)
    {
        //idの値でタスクを検索して取得
        $task = Task::findOrFail($id);
        
        //認証済みユーザがそのタスク所有者である場合、そのタスクを削除
        if (\Auth::id() === $task->user_id) {
            $task->delete();
        }

        // トップページへリダイレクトさせる
        return redirect('/');
    }
}
