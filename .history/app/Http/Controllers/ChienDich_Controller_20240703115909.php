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
        // Định nghĩa các tham số bắt buộc
        $requiredFields = [
            'userid', 'name', 'province', 'district', 'location', 
            'dateStart', 'dateEnd', 'totalMoney', 'moneyByVNJN', 
            'timeline', 'infoContact', 'infoOrganization', 
            'image', 'description', 'status'
        ];

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
            // 'timeline' => 'required|array',
            // 'infoContact' => 'required|array',
            // 'infoOrganization' => 'required|array',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048', // Yêu cầu file ảnh với các định dạng cụ thể và giới hạn kích thước
            'description' => 'required|string',
            'status' => 'required|string|max:255',
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

            // Tạo tên file duy nhất
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();

            // Lưu file vào thư mục storage/app/public/images
            $image->storeAs('public/image', $imageName);

            // Đường dẫn tương đối của ảnh trong storage
            $imagePath = 'image/' . $imageName;
        
        } else {
            return response()->json([
                'error' => 'File ảnh không hợp lệ hoặc không tồn tại'
            ], 400);
        }

        try {
            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // Tạo một campaign mới và lưu vào cơ sở dữ liệu
            $campaign = new Campaign();
            $campaign->userid = $request->userid; // Giả sử bạn có authentication, nếu không có thì sử dụng 1 giá trị user ID cố định hoặc lấy từ request.
            $campaign->name = $request->name;
            $campaign->province = $request->province;
            $campaign->district = $request->district;
            $campaign->location = $request->location;
            $campaign->dateStart = $request->dateStart;
            $campaign->dateEnd = $request->dateEnd;
            $campaign->totalMoney = $request->totalMoney;
            $campaign->moneyByVNJN = $request->moneyByVNJN;
            $campaign->timeline = json_encode($request->timeline);
            $campaign->infoContact = json_encode($request->infoContact);
            $campaign->infoOrganization = json_encode($request->infoOrganization);
            $campaign->image = $imagePath; // Lưu đường dẫn ảnh vào cơ sở dữ liệu
            $campaign->description = $request->description;
            $campaign->status = $request->status;

            $campaign->save();

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => 'Thêm chiến dịch thành công',
                'campaign' => $campaign
            ], 201);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi xảy ra
            DB::rollBack();

            return response()->json([
                'error' => 'Không thể thêm chiến dịch: ' . $e->getMessage()
            ], 500);
        }
    }
}
