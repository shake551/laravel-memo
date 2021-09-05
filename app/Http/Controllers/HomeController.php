<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // ここでメモのデータを取得
        $memos = Memo::select('memos.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC')
            ->get();

        return view('create', compact('memos'));
    }

    public function store(Request $request)
    {
        $posts = $request->all(); // リクエスト全てとる

        // トランザクション開始
        DB::transaction(function() use($posts) {
            // メモIDをインサートして取得
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id()]);
            $tag_exits = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])
                ->exists();
            // 新規タグが入力されているか
            // タグ名が既に登録されていないか
            if (!empty($posts['new_tag']) && !$tag_exits){
                // tagsテーブルにインサートしてIDを取得
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                // memo_tagsにインサートしてメモとタグを紐付ける
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }
        });
        // トランザクション終了

        return redirect( route('home') );
    }

    public function edit($id) 
    {
        // ここでメモのデータを取得
        $memos = Memo::select('memos.*')
            ->where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC')
            ->get();

        $edit_memo = Memo::find($id);

        return view('edit', compact('memos', 'edit_memo'));
    }

    public function update(Request $request)
    {
        $posts = $request->all(); // リクエスト全てとる

        Memo::where('id', $posts['memo_id'])
            ->update(['content' => $posts['content']]);

        return redirect( route('home') );
    }

    public function destroy(Request $request)
    {
        $posts = $request->all(); // リクエスト全てとる

        Memo::where('id', $posts['memo_id'])
            ->update(['deleted_at' => date('Y-m-d H:i:s', time())]);

        return redirect( route('home') );
    }
}
