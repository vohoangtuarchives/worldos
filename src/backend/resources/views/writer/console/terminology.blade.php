@extends('layouts.writer')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div class="pb-5 border-b border-gray-700">
        <h2 class="text-2xl font-bold leading-7 text-white sm:truncate sm:text-3xl sm:tracking-tight">
            Hướng dẫn Thuật ngữ Nhà văn
        </h2>
        <p class="mt-2 text-sm text-gray-400">Dịch cơ chế mô phỏng sang các khái niệm kể chuyện.</p>
    </div>

    <div class="space-y-6">
        <!-- Archetype Weights -->
        <!-- Archetype Weights -->
        <div class="bg-gray-800 shadow overflow-hidden sm:rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:px-6 bg-gray-900/50">
                <h3 class="text-lg font-medium leading-6 text-white">Tâm trạng Thế giới (Trọng số Nguyên mẫu)</h3>
                <p class="mt-1 text-sm text-gray-500">Mức độ ảnh hưởng của một chủ đề đối với thế giới.</p>
            </div>
            <dl>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b border-gray-700">
                    <dt class="text-sm font-medium text-white">Áp đảo (> 0.8)</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Chủ đề định nghĩa thực tại. Sự bất đồng là không thể.</dd>
                </div>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b border-gray-700">
                    <dt class="text-sm font-medium text-white">Thống trị (0.6 - 0.8)</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Chủ đề là hiện trạng. Nó định hình luật pháp và phong tục.</dd>
                </div>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b border-gray-700">
                    <dt class="text-sm font-medium text-white">Hiện diện (0.4 - 0.6)</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Một lực lượng hữu hình, nhưng chỉ là một trong số nhiều.</dd>
                </div>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-white">Phai nhạt (< 0.2)</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Một ký ức hoặc huyền thoại. Không có quyền lực thực sự.</dd>
                </div>
            </dl>
        </div>

        <!-- Stability -->
        <div class="bg-gray-800 shadow overflow-hidden sm:rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:px-6 bg-gray-900/50">
                <h3 class="text-lg font-medium leading-6 text-white">Sự ổn định (Tính chính danh)</h3>
                <p class="mt-1 text-sm text-gray-500">Sự gắn kết xã hội của trật tự hiện tại.</p>
            </div>
            <dl>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b border-gray-700">
                    <dt class="text-sm font-medium text-green-400">Thời Hoàng kim</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Sự hài hòa hoàn hảo. Sự trì trệ là rủi ro duy nhất.</dd>
                </div>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b border-gray-700">
                    <dt class="text-sm font-medium text-yellow-400">Thời Đại Rắc Rối</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Các phe phái đang bồn chồn. Trung tâm không thể giữ vững.</dd>
                </div>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-red-400">Sụp đổ Sắp xảy ra</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Thế giới cũ đang chết dần. Sự hỗn loạn ngự trị.</dd>
                </div>
            </dl>
        </div>
        
        <!-- Concepts -->
         <div class="bg-gray-800 shadow overflow-hidden sm:rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:px-6 bg-gray-900/50">
                <h3 class="text-lg font-medium leading-6 text-white">Các Khái niệm Cốt lõi</h3>
            </div>
            <dl>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b border-gray-700">
                    <dt class="text-sm font-medium text-indigo-400">Trôi dạt (Drift)</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Sự thay đổi chậm chạp, vô hình về các giá trị theo thời gian. Giống như các mảng kiến tạo di chuyển trước một trận động đất.</dd>
                </div>
                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-indigo-400">Đột biến (Phân nhánh)</dt>
                    <dd class="mt-1 text-sm text-gray-400 sm:col-span-2 sm:mt-0">Một sự chia tách đột ngột, không thể đảo ngược trong một nguyên mẫu. Ví dụ: "Công lý" tách thành "Báo thù" và "Luật pháp".</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
