<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;

class ChienDich_Controller extends Controller
{
  public function store(Request $request)
    {
        // Định nghĩa các tham số bắt buộc
        $requiredFields = [
            'name', 'province', 'district', 'location', 
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
            'timeline' => 'required|array',
            'infoContact' => 'required|array',
            'infoOrganization' => 'required|array',
            'image' => 'required|base64_image',
            'description' => 'required|string',
            'status' => 'required|string|max:255',
        ]);

        // Kiểm tra nếu dữ liệu không hợp lệ
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        // Xử lý ảnh base64 và lưu vào thư mục storage
        $image = $request->image;
        $imageData = base64_decode($image);
        if (!$imageData) {
            return response()->json([
                'error' => 'Dữ liệu ảnh không hợp lệ'
            ], 400);
        }

        // Tạo tên file duy nhất
        $imageName = uniqid() . '.png';

        // Lưu file vào thư mục storage/app/public/images (thư mục images cần được tạo trước)
        Storage::disk('public')->put('images/' . $imageName, $imageData);

        // Đường dẫn tương đối của ảnh trong storage
        $imagePath = 'images/' . $imageName;

        try {
            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // Tạo một campaign mới và lưu vào cơ sở dữ liệu
            $campaign = new Campaign();
            $campaign->userid = auth()->user()->id; // Giả sử bạn có authentication, nếu không có thì sử dụng 1 giá trị user ID cố định hoặc lấy từ request.
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
