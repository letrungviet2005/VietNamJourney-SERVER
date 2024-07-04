<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChienDich_Controller extends Controller
{
    public function createCampaign(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'dateStart' => 'required|date',
            'dateEnd' => 'required|date',
            'totalMoney' => 'required|numeric',
            'moneyByVNJN' => 'required|numeric',
            'timeline' => 'required|string', // Tạm thời là string, sau sẽ decode JSON
            'infoContact' => 'required|string', // Tạm thời là string, sau sẽ decode JSON
            'infoOrganization' => 'required|string', // Tạm thời là string, sau sẽ decode JSON
            'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|string',
            'plan' => 'required|string',
        ]);

        // Kiểm tra nếu dữ liệu không hợp lệ
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        // Xử lý file ảnh và lưu vào thư mục storage
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('image', $imageName);
        } else {
            return response()->json([
                'error' => 'File ảnh không hợp lệ hoặc không tồn tại'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $campaign = new Campaign();
            $campaign->userid = $request->userid;
            $campaign->name = $request->name;
            $campaign->province = $request->province;
            $campaign->district = $request->district;
            $campaign->location = $request->location;
            $campaign->dateStart = $request->dateStart;
            $campaign->dateEnd = $request->dateEnd;
            $campaign->totalMoney = $request->totalMoney;
            $campaign->moneyByVNJN = $request->moneyByVNJN;
            $campaign->timeline = json_decode($request->timeline, true); // Giải mã JSON
            $campaign->infoContact = json_decode($request->infoContact, true); // Giải mã JSON
            $campaign->infoOrganization = json_decode($request->infoOrganization, true); // Giải mã JSON
            $campaign->image = $imagePath;
            $campaign->description = $request->description;
            $campaign->plan = $request->plan;
            $campaign->status = 0;

            $campaign->save();
            DB::commit();

            return response()->json([
                'success' => 'Thêm chiến dịch thành công',
                'campaign' => $campaign
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Không thể thêm chiến dịch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCampaignsIng($province)
    {
        try {
            // Truy vấn các chiến dịch theo tỉnh và ngày trong khoảng thời gian hợp lệ
            $campaigns = Campaign::where('province', $province)
                ->whereDate('dateStart', '<=', now())
                ->whereDate('dateEnd', '>=', now())
                ->get();

            // Nếu không tìm thấy chiến dịch nào
            if ($campaigns->isEmpty()) {
                return response()->json([
                    'error' => "Không tìm thấy chiến dịch thuộc tỉnh $province"
                ], 404);
            }

            // Xử lý dữ liệu trước khi trả về
            $campaigns->transform(function ($campaign) {
                return $campaign;
            });

            // Trả về dữ liệu dưới dạng JSON
            return response()->json($campaigns, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Không thể lấy thông tin chiến dịch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCampaignsByProvince($province)
    {
        try {
            // Truy vấn các chiến dịch theo tỉnh và ngày trong khoảng thời gian hợp lệ
            $campaigns = Campaign::where('province', $province)
                ->whereDate('dateStart', '<=', now())
                ->whereDate('dateEnd', '>=', now())
                ->get();

            // Nếu không tìm thấy chiến dịch nào
            if ($campaigns->isEmpty()) {
                return response()->json([
                    'error' => "Không tìm thấy chiến dịch thuộc tỉnh $province"
                ], 404);
            }

            // Xử lý dữ liệu trước khi trả về
            $campaigns->transform(function ($campaign) {
                return $campaign;
            });

            // Trả về dữ liệu dưới dạng JSON
            return response()->json($campaigns, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Không thể lấy thông tin chiến dịch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCampaignsByProvince($province)
    {
        try {
            // Truy vấn các chiến dịch theo tỉnh và ngày trong khoảng thời gian hợp lệ
            $campaigns = Campaign::where('province', $province)
                ->whereDate('dateStart', '<=', now())
                ->whereDate('dateEnd', '>=', now())
                ->get();

            // Nếu không tìm thấy chiến dịch nào
            if ($campaigns->isEmpty()) {
                return response()->json([
                    'error' => "Không tìm thấy chiến dịch thuộc tỉnh $province"
                ], 404);
            }

            // Xử lý dữ liệu trước khi trả về
            $campaigns->transform(function ($campaign) {
                return $campaign;
            });

            // Trả về dữ liệu dưới dạng JSON
            return response()->json($campaigns, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Không thể lấy thông tin chiến dịch: ' . $e->getMessage()
            ], 500);
        }
    }
}
