<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MemoController extends Controller
{
    //
    public function index()
    {
        $memos = Memo::where('user_id', Auth::id())->orderBy('updated_at', 'desc')->get();

        return view('memo', [
            'name' => $this->getLoginUserName(),
            'memos' => $memos,
            // sessionの中身を取得する
            'select_memo' => session()->get('select_memo'),
        ]);
    }

    public function add()
    {
        Memo::create([
            'user_id' => Auth::id(),
            'title' => '新規メモ',
            'memo' => '',
        ]);

        return redirect()->route('memo.index');
    }

    public function update(Request $request)
    {
        // memosテーブルの指定したカラムを書き換える
        $memo = Memo::find($request->edit_id);
        $memo->title = $request->edit_title;
        $memo->content = $request->edit_content;

        // updateでtrueが帰ってくる
        if ($memo->update()) {
            session()->put('select_memo', $memo);
        } else {
            session()->remove('select_memo');
        }

        return redirect()->route('memo.index');
    }

    /**
     * メモを削除
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        Memo::find($request->edit_id)->delete();
        session()->remove('select_memo');

        return redirect()->route('memo.index');
    }

    /**
     * メモを選択
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function select(Request $request)
    {
        $memo = Memo::find($request->id);
        session()->put('select_memo', $memo);

        return redirect()->route('memo.index');
    }

    public function search(Request $request)
    {
        $keyword_title = $request->search_title;
        
        $user_name = $this->getLoginUserName();
        $select_memo = $request->session()->get('select_memo');
        $search_result = Memo::where('user_id', Auth::id())
                                ->where('title', 'like' , '%' . $keyword_title . '%')
                                ->orderBy('updated_at', 'desc')
                                ->get();
        $message = "「" . $keyword_title . "」を含む名前の検索が完了しました。";
        
        return view('memo')->with([
            'name' => $user_name,
            'select_memo' => $select_memo,
            'memos' => $search_result,
            'message' => $message,
        ]);
    }

    /**
     * ログインユーザー名取得
     * @return string
     */
    public function getLoginUserName()
    {
        $user = Auth::user();

        $name = '';
        if ($user) {
            if (7 < mb_strlen($user->name)) {
                $name = mb_substr($user->name, 0, 7) . "...";
            } else {
                $name = $user->name;
            }
        }

        return $name;
    }
}
