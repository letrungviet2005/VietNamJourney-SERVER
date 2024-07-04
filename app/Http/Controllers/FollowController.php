<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{
    public function updateFollowStatus(Request $request)
    {
        $userId = $request->input('User_ID');
        $followedUserId = $request->input('Followed_User_ID');
        $status = $request->input('Status');

        if (!$userId || !$followedUserId || !$status) {
            return response()->json(['success' => false, 'error' => 'Missing parameters'], 400);
        }

        try {
            if ($status === 'follow') {
                // Thêm vào bảng follow
                Follow::create([
                    'Follower_ID' => $userId,
                    'Following_ID' => $followedUserId
                ]);
            } elseif ($status === 'unfollow') {
                // Xóa khỏi bảng follow
                Follow::where('Follower_ID', $userId)
                    ->where('Following_ID', $followedUserId)
                    ->delete();
            } else {
                return response()->json(['success' => false, 'error' => 'Invalid status'], 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function getUnFollowedUsers(Request $request)
    {
        // Lấy User_ID từ request
        $userId = $request->input('User_ID');

        if (!$userId) {
            return response()->json(['error' => 'Invalid User ID'], 400);
        }

        try {
            // Lấy danh sách following
            $followingIds = DB::table('follow')
                ->where('follower_id', $userId)
                ->pluck('following_id')
                ->toArray();

            if (count($followingIds) > 0) {
                // Lấy danh sách các user không được theo dõi
                $unfollowedIds = DB::table('follow')
                    ->whereNotIn('following_id', $followingIds)
                    ->where('following_id', '!=', $userId)
                    ->distinct()
                    ->limit(5)
                    ->pluck('following_id')
                    ->toArray();

                if (count($unfollowedIds) > 0) {
                    // Lấy thông tin người dùng cho các user không được theo dõi
                    $users = DB::table('user_information')
                        ->whereIn('User_ID', $unfollowedIds)
                        ->select('User_ID', 'Username', 'Image')
                        ->get();

                    $formattedUsers = $users->map(function ($user) {
                        return [
                            'User_ID' => $user->User_ID,
                            'Username' => $user->Username,
                            'Image' => $user->Image,
                        ];
                    });

                    $response = ['users' => $formattedUsers];
                } else {
                    $response = ['users' => []];
                }
            } else {
                // Trường hợp người dùng không theo dõi ai
                $users = DB::table('user_information')
                    ->where('User_ID', '!=', $userId)
                    ->limit(5)
                    ->select('User_ID', 'Username', 'Image')
                    ->get();

                $formattedUsers = $users->map(function ($user) {
                    return [
                        'User_ID' => $user->User_ID,
                        'Username' => $user->Username,
                        'Image' => $user->Image,
                    ];
                });

                $response = ['users' => $formattedUsers];
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve data'], 500);
        }
    }
    public function updateFollower(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'User_ID' => 'required|integer',
            'Followed_User_ID' => 'required|integer',
            'Status' => 'required|string|in:follow,unfollow',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => 'Missing parameters or invalid status'], 400);
        }

        $userId = $request->input('User_ID');
        $followedUserId = $request->input('Followed_User_ID');
        $status = $request->input('Status');

        try {
            if ($status === 'follow') {
                // Thêm vào bảng follow
                DB::table('follow')->insert([
                    'Follower_ID' => $userId,
                    'Following_ID' => $followedUserId,
                ]);
            } elseif ($status === 'unfollow') {
                // Xóa khỏi bảng follow
                DB::table('follow')
                    ->where('Follower_ID', $userId)
                    ->where('Following_ID', $followedUserId)
                    ->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Database error'], 500);
        }
    }
}
