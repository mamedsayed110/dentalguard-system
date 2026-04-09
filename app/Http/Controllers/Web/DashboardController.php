<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class DashboardController extends Controller
{
    public function index()
    {
        $scans = Scan::where('user_id', auth()->id())->latest()->limit(6)->get();

        return view('dashboard.index', compact('scans'));
    }

    public function chat()
    {
        return view('chat.index');
    }
public function sendMessage(Request $request)
{
    $request->validate([
        'message' => 'required|string|max:500'
    ]);

    $badWords = ['انتحار','سم','مخدر','قتل'];
    foreach($badWords as $word){
        if(str_contains($request->message, $word)){
            return response()->json(['reply' => '⚠️ لا يمكنني المساعدة في هذا النوع من الطلبات']);
        }
    }

    $response = Http::post(env('OLLAMA_URL').'/api/generate', [
        'model' => env('OLLAMA_MODEL'),
        'prompt' => "أنت مساعد طبيب أسنان محترف. أجب بلغة بسيطة بدون تشخيص نهائي:\n\n" . $request->message,
        'stream' => false
    ]);

    if (!$response->ok()) {
        return response()->json(['reply' => '⚠️ الذكاء الاصطناعي غير متاح حالياً']);
    }

    return response()->json([
        'reply' => $response->json()['response'] ?? 'لم يتم توليد رد'
    ]);
}

}
