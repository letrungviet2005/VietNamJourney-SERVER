<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follow;
use App\Models\Link;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use DateTime;
use DateTimeZone;

class UserController extends Controller
{

    public function getUserInformation(Request $request)
    {
        // Xử lý yêu cầu preflight
        if ($request->isMethod('options')) {
            return response('', 200);
        }

        $userId = $request->input('userId');
        $currentUserId = $request->input('currentUserId');

        // Kiểm tra nếu thiếu thông tin userId
        if (!$userId) {
            return response()->json(['error' => 'User ID không hợp lệ'], 400);
        }

        try {
            $user = DB::table('user_information as u')
                ->select(
                    'u.*',
                    DB::raw('(SELECT COUNT(*) FROM follow WHERE Following_ID = u.User_ID) AS followers'),
                    DB::raw('(SELECT COUNT(*) FROM follow WHERE Follower_ID = u.User_ID) AS following'),
                    'l.Link as facebookLink'
                )
                ->leftJoin('link as l', function ($join) {
                    $join->on('u.User_ID', '=', 'l.User_ID')
                        ->where('l.Social', 'Facebook');
                })
                ->where('u.User_ID', $userId)
                ->first();

            // Nếu không tìm thấy người dùng
            if (!$user) {
                return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
            }

            // Kiểm tra trạng thái theo dõi
            $isFollowing = false;
            if ($currentUserId) {
                $isFollowing = DB::table('follow')
                    ->where('Follower_ID', $currentUserId)
                    ->where('Following_ID', $userId)
                    ->exists();
            }

            // Tạo response JSON
            $response = [
                'user' => [
                    'avatar' => $user->Image ? url($user->Image) : null,
                    'name' => $user->Name,
                    'username' => $user->Username,
                    'followers' => $user->followers,
                    'following' => $user->following,
                    'role' => $user->Role,
                    'location' => $user->LiveAt,
                    'facebookLink' => $user->facebookLink,
                    'isFollowing' => $isFollowing
                ]
            ];

            // Trả về response thành công
            return response()->json($response, 200);
        } catch (\Exception $e) {
            // Bắt lỗi và trả về thông báo lỗi
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function updateUserInfo(Request $request)
    {
        Log::info('Received request to update user information', $request->all());

        $userId = $request->input('userId');
        $name = $request->input('name');
        $location = $request->input('location');
        $facebookLink = $request->input('facebookLink');
        $role = $request->input('role');
        $avatar = $request->file('avatar');

        try {
            // Initialize avatarUrl to null
            $avatarUrl = null;

            // Check if avatar is provided
            if ($avatar) {
                $avatarName = uniqid() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = $avatar->storeAs('image', $avatarName, 'public');
                $avatarUrl = Storage::url($avatarPath);
                Log::info('Avatar uploaded successfully', ['avatarUrl' => $avatarUrl]);
            } elseif ($request->has('avatar')) {
                // Handle case where avatar is explicitly set to null in the request
                $avatarUrl = 'null'; // Replace with your desired representation of null
                Log::info('Avatar explicitly set to null');
            }

            // Update the user information
            DB::table('user_information')
                ->where('UserLogin_ID', $userId)
                ->update([
                    'Name' => $name,
                    'LiveAt' => $location,
                    'Role' => $role,
                    'Image' => $avatarUrl // Updated to use $avatarUrl instead of $avatarPath
                ]);
            Log::info('User information updated successfully', ['userId' => $userId]);

            // Update or insert Facebook link
            DB::table('link')
                ->updateOrInsert(
                    ['User_ID' => $userId, 'Social' => 'Facebook'],
                    ['Link' => $facebookLink]
                );
            Log::info('Facebook link updated or inserted successfully', ['userId' => $userId, 'facebookLink' => $facebookLink]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error updating user information', ['error' => $e->getMessage()]);
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

    public function getPosts(Request $request)
    {
        // Lấy userId từ request
        $userId = $request->input('userId');

        if (!$userId) {
            return response()->json(['error' => 'User ID không hợp lệ'], 400);
        }

        try {
            // Lấy thông tin người dùng từ bảng user_information
            $user = DB::table('user_information')->where('User_ID', $userId)->first();

            if (!$user) {
                return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
            }

            // Lấy danh sách bài viết của người dùng và định dạng thời gian
            $posts = DB::table('post')->where('User_ID', $userId)
                ->orderBy('Post_ID', 'DESC')
                ->get();

            $formattedPosts = [];
            foreach ($posts as $post) {
                // Định dạng thời gian của bài viết
                $createdAt = $this->timeElapsedString($post->CreateAt);

                // Đếm tổng số lượt like và comment
                $likeCount = DB::table('islike')
                    ->where('Post_ID', $post->Post_ID)
                    ->count();

                $commentCount = DB::table('comment')
                    ->where('Post_ID', $post->Post_ID)
                    ->count();

                // Thêm vào mảng kết quả
                $formattedPosts[] = [
                    'id' => $post->Post_ID,
                    'user_id' => $post->User_ID,
                    'user_name' => $user->Name,
                    'user_avatar' => $user->Image, // Không chuyển đổi base64
                    'content' => $post->Content,
                    'image' => $post->Image, // Không chuyển đổi base64
                    'createdAt' => $createdAt,
                    'likes' => $likeCount,
                    'comments' => $commentCount,
                    // Thêm các thông tin khác của bài viết tại đây
                ];
            }

            // Trả về response thành công
            return response()->json($formattedPosts, 200);
        } catch (\Exception $e) {
            // Bắt lỗi và trả về thông báo lỗi
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
