<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    /**
     * Flask AI Server URL
     */
    private $aiServerUrl = 'http://localhost:5000';

    /**
     * Health check for AI server
     */
    public function health()
    {
        try {
            $response = Http::timeout(5)->get($this->aiServerUrl . '/health');
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'ai_server' => $response->json()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'AI server not responding'
            ], 503);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to AI server',
                'details' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Analyze dental X-ray image
     */
    public function analyze(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|string',
                'model' => 'nullable|in:1,2'
            ]);

            Log::info('AI Analysis requested');

            $data = [
                'image' => $request->input('image'),
                'model' => $request->input('model', '1')
            ];

            $response = Http::timeout(30)
                ->post($this->aiServerUrl . '/analyze', $data);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('AI Analysis completed', [
                    'detections' => count($result['detections'] ?? [])
                ]);

                return response()->json($result);
            }

            $error = $response->json();
            Log::error('AI Analysis failed', ['error' => $error]);

            return response()->json([
                'success' => false,
                'error' => $error['error'] ?? 'Analysis failed'
            ], $response->status());

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request data',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('AI Analysis exception', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Medical advisor chatbot
     */
    public function chat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            Log::info('Chat request received', [
                'message' => substr($request->input('message'), 0, 50)
            ]);

            $data = [
                'message' => $request->input('message'),
                'history' => $request->input('history', [])
            ];

            $response = Http::timeout(30)
                ->post($this->aiServerUrl . '/chat', $data);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('Chat response generated');

                return response()->json($result);
            }

            $error = $response->json();
            Log::error('Chat failed', ['error' => $error]);

            return response()->json([
                'success' => false,
                'error' => $error['error'] ?? 'Chat failed'
            ], $response->status());

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid message',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Chat exception', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get models status
     */
    public function modelsStatus()
    {
        try {
            $response = Http::timeout(5)
                ->get($this->aiServerUrl . '/models/status');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'models' => $response->json()
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Cannot get models status'
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to AI server'
            ], 503);
        }
    }
}