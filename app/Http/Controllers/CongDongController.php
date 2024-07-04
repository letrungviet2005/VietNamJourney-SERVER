<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateTimeZone;

class CongDongController extends Controller
{
    public function getSocialPosts(Request $request)
    {
        if ($request->isMethod('options')) {
            return response()->json(['message' => 'OK'], 200);
        }

        if ($request->isMethod('post')) {
            try {
                $posts = DB::table('post')
                    ->orderBy('Post_ID', 'desc')
                    ->get();

                $result = [];

                foreach ($posts as $post) {
                    $user = DB::table('user_information')
                        ->select('Name', 'Image')
                        ->where('User_ID', $post->User_ID)
                        ->first();

                    $likeCount = DB::table('IsLike')
                        ->where('Post_ID', $post->Post_ID)
                        ->count();

                    $commentCount = DB::table('comment')
                        ->where('Post_ID', $post->Post_ID)
                        ->count();

                    $result[] = [
                        'id' => $post->Post_ID,
                        'user_id' => $post->User_ID,
                        'content' => $post->Content,
                        'image' => $post->Image ? url($post->Image) : null,
                        'createdAt' => $this->timeElapsedString($post->CreateAt),
                        'likes' => $likeCount,
                        'comments' => $commentCount,
                        'name' => $user->Name,
                        'avatar' => $user->Image ? url($user->Image) : null,
                    ];
                }

                return response()->json(['posts' => $result], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Lỗi khi kết nối đến cơ sở dữ liệu'], 500);
            }
        }

        return response()->json(['error' => 'Method not allowed'], 405);
    }

    private function timeElapsedString($datetime, $full = false)
    {
        $timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $now = new DateTime('now', $timezone);
        $ago = new DateTime($datetime, $timezone);
        $diff = $now->diff($ago);

        $string = [
            'y' => 'năm',
            'm' => 'tháng',
            'd' => 'ngày',
            'h' => 'giờ',
            'i' => 'phút',
        ];

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v;
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' trước' : 'vừa xong';
    }
}
