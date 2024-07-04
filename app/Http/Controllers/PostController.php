<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Models\User;
use DateTime;
use DateTimeZone;

class PostController extends Controller
{
    public function checkLikeStatus(Request $request)
    {
        $postId = $request->input('Post_ID');
        $userId = $request->input('user_id');

        if (!$postId || !$userId) {
            return response()->json(["error" => "Invalid parameters"], 400);
        }

        try {
            $isLiked = DB::table('islike')
                ->where('Post_ID', $postId)
                ->where('User_ID', $userId)
                ->exists();

            return response()->json(["isLiked" => $isLiked]);
        } catch (\Exception $e) {
            return response()->json(["error" => "Database connection failed"], 500);
        }
    }
    public function getComment(Request $request)
    {
        // Xử lý yêu cầu preflight
        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        // Kiểm tra phương thức yêu cầu
        if (!$request->isMethod('post')) {
            return response()->json(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        // Lấy dữ liệu từ request
        $input = $request->all();

        $Post_ID = isset($input['Post_ID']) ? (int)$input['Post_ID'] : null;
        if (!$Post_ID) {
            return response()->json(['success' => false, 'error' => 'Missing Post_ID'], 400);
        }

        try {
            // Lấy thông tin comment mới nhất
            $comment = DB::table('comment as c')
                ->select('c.User_ID', 'c.Content', 'c.ImageComment', 'c.CreateAt', 'u.Name', 'u.Image')
                ->join('user_information as u', 'c.User_ID', '=', 'u.User_ID')
                ->where('c.Post_ID', $Post_ID)
                ->orderByDesc('c.CreateAt')
                ->first();

            if ($comment) {
                // Định dạng thời gian
                $comment->CreateAt = $this->timeElapsedString($comment->CreateAt);

                // Tạo đường dẫn cho ảnh
                $comment->Image = $comment->Image ? url($comment->Image) : null;
                $comment->ImageComment = $comment->ImageComment ? url($comment->ImageComment) : null;

                return response()->json(['success' => true, 'comment' => $comment]);
            } else {
                return response()->json(['success' => true, 'comment' => null]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function toogleLike(Request $request)
    {
        // Xử lý yêu cầu preflight
        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        // Kiểm tra phương thức yêu cầu
        if (!$request->isMethod('post')) {
            return response()->json(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        // Lấy dữ liệu từ request
        $data = $request->all();
        $postId = $data['Post_ID'] ?? null;
        $userId = $data['user_id'] ?? null;
        $isLike = $data['isLike'] ?? null;

        // Kiểm tra các tham số
        if (!$postId || $userId === null || $isLike === null) {
            return response()->json(['success' => false, 'error' => 'Invalid parameters'], 400);
        }

        try {
            // Thực hiện truy vấn vào cơ sở dữ liệu
            if ($isLike) {
                DB::table('islike')->insert([
                    'Post_ID' => $postId,
                    'User_ID' => $userId,
                ]);
            } else {
                DB::table('islike')
                    ->where('Post_ID', $postId)
                    ->where('User_ID', $userId)
                    ->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deletePost(Request $request)
    {
        // Xử lý yêu cầu preflight
        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        // Kiểm tra phương thức yêu cầu
        if (!$request->isMethod('post')) {
            return response()->json(['success' => false, 'error' => 'Invalid request method'], 405);
        }

        // Lấy dữ liệu từ request
        $data = $request->all();
        $postId = isset($data['Post_ID']) ? $data['Post_ID'] : null;

        // Kiểm tra tham số Post_ID
        if (!$postId) {
            return response()->json(['success' => false, 'error' => 'Missing Post_ID'], 400);
        }

        try {
            // Bắt đầu transaction
            DB::beginTransaction();

            // Xóa bình luận liên quan đến bài viết
            DB::table('comment')->where('Post_ID', $postId)->delete();

            // Xóa lượt thích liên quan đến bài viết
            DB::table('islike')->where('Post_ID', $postId)->delete();

            // Xóa bài viết
            DB::table('post')->where('Post_ID', $postId)->delete();

            // Commit transaction
            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollBack();

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
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

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' trước' : 'vừa xong';
    }
    public function getComments(Request $request)
    {
        $postId = $request->input('post_ID');

        if (!$postId) {
            return response()->json(['error' => 'Post ID không hợp lệ'], 400);
        }

        try {
            $comments = DB::table('comment')
                ->where('Post_ID', $postId)
                ->orderBy('Comment_ID', 'desc')
                ->get();

            $formattedComments = [];

            foreach ($comments as $comment) {
                $user = DB::table('User_Information')
                    ->where('User_ID', $comment->User_ID)
                    ->first();

                if ($user) {
                    $formattedComments[] = [
                        'user_ID' => $comment->User_ID,
                        'username' => $user->Name,
                        'avatar' => $user->Image ? url('storage/' . $user->Image) : null,
                        'content' => $comment->Content,
                        'imageComment' => $comment->ImageComment ? url($comment->ImageComment) : null,
                        'time' => $this->timeElapsedString($comment->CreateAt),
                    ];
                }
            }

            if ($formattedComments) {
                return response()->json(['comments' => $formattedComments], 200);
            } else {
                return response()->json(['error' => 'Không tìm thấy bình luận cho bài đăng này'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi truy vấn cơ sở dữ liệu'], 500);
        }
    }
    public function addComment(Request $request)
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'User_ID' => 'required|integer',
            'Post_ID' => 'required|integer',
            'Content' => 'nullable|string',
            'ImageComment' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], 400);
        }

        // Get the validated inputs
        $user_ID = $request->input('User_ID');
        $post_ID = $request->input('Post_ID');
        $content = $request->input('Content');
        $imageComment = $request->file('ImageComment');

        $imagePath = null;
        if ($imageComment) {
            // $imagePath = $imageComment->store('comments', 'public');
            $imageName = uniqid() . '.' . $imageComment->getClientOriginalExtension();
            // $imagePath = $imageComment->store('image', $imageName);
            $imagePath = $imageComment->store('image');
        }

        try {
            // Insert the new comment into the database
            DB::table('comment')->insert([
                'User_ID' => $user_ID,
                'Post_ID' => $post_ID,
                'Content' => $content,
                'ImageComment' => $imagePath,
                'CreateAt' => now(),
            ]);

            // Get user information for response
            $user = DB::table('user_information')->where('User_ID', $user_ID)->first();

            if (!$user) {
                return response()->json(['success' => false, 'error' => 'User not found'], 404);
            }

            $commentData = [
                'user_ID' => $user_ID,
                'avatar' => $user->Image ? Storage::url($user->Image) : null,
                'username' => $user->Name,
                'content' => $content,
                'imageComment' => $imagePath ? Storage::url($imagePath) : null,
                'time' => 'vừa xong'
            ];

            return response()->json(['success' => true, 'comment' => $commentData]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}
