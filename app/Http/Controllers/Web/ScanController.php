<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    // صفحة رفع صورة
    public function uploadPage()
    {
        return view('scans.upload');
    }

    // حفظ الصورة
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:8192'
        ]);

        $path = $request->file('image')->store('scans', 'public');

        $scan = Scan::create([
            'user_id' => auth()->id(),
            'image_path' => $path,
            'status' => 'pending'
        ]);

        return redirect()->route('scans.show', $scan->id);
    }

    // عرض نتيجة
    public function show($id)
    {
        $scan = Scan::where('id' , $id)
                    ->where('user_id' , auth()->id())
                    ->firstOrFail();

        return view('scans.show', compact('scan'));
    }

    // كل الفحوصات
    public function index()
    {
        $scans = Scan::where('user_id' , auth()->id())->latest()->get();
        return view('scans.index', compact('scans'));
    }

    // تحليل الصورة (مكان AI)
    public function analyze($id)
    {
        $scan = Scan::findOrFail($id);

        /*
        هنا تربط موديل الذكاء الاصطناعي بعدين
        */

        // بيانات تجريبية
        $scan->update([
            'status' => 'completed',
            'result' => [
                'confidence' => 85,
                'detections' => ['تسوس بسيط', 'التهاب لثة'],
                'description' => 'يرجى مراجعة طبيب الأسنان لإجراء فحص حقيقي'
            ]
        ]);

        return redirect()->route('scans.show', $scan->id);
    }
}
