<?php

namespace App\Http\Controllers\Api;

use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ScanController
{
    private $aiServiceUrl;

    public function __construct()
    {
        // AI service URL from environment
        $this->aiServiceUrl = env('AI_SERVICE_URL', 'http://localhost:5000');
    }

    /**
     * Upload scan image
     */
    public function upload(Request $request)
    {
        // Validation
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240', // Max 10MB
        ]);

        try {
            // Store image
            $path = $request->file('image')->store('scans', 'public');

            // Create scan record
            $scan = Scan::create([
                'user_id' => auth()->id(),
                'image_path' => $path,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'scan' => $scan
            ]);

        } catch (\Exception $e) {
            Log::error('Scan upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze scan with AI
     */
    public function analyze($id)
    {
        try {
            // Get scan (only user's own scans)
            $scan = Scan::where('user_id', auth()->id())->findOrFail($id);

            // Check if already analyzed
            if ($scan->status === 'completed') {
                return response()->json([
                    'success' => true,
                    'message' => 'Scan already analyzed',
                    'scan' => $scan
                ]);
            }

            // Update status to processing
            $scan->update(['status' => 'processing']);

            // Get image path
            $imagePath = storage_path('app/public/' . $scan->image_path);

            // Check if file exists
            if (!file_exists($imagePath)) {
                throw new \Exception('Image file not found');
            }

            // Send to AI service
            Log::info('Sending image to AI service: ' . $this->aiServiceUrl);
            
            $response = Http::timeout(30)
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post($this->aiServiceUrl . '/predict');

            // Check response
            if (!$response->successful()) {
                throw new \Exception('AI service error: ' . $response->status());
            }

            $aiResult = $response->json();

            // Validate AI response
            if (!isset($aiResult['success']) || !$aiResult['success']) {
                throw new \Exception('AI analysis failed: ' . ($aiResult['error'] ?? 'Unknown error'));
            }

            // Extract and save results
            $result = [
                'confidence' => $aiResult['confidence'] ?? 0,
                'detections' => $aiResult['detections'] ?? [],
                'primary_disease' => $aiResult['primary_disease'] ?? 'Unknown',
                'description' => $aiResult['description'] ?? '',
                'all_predictions' => $aiResult['all_predictions'] ?? [],
                'recommendations' => $aiResult['recommendations'] ?? [],
                'severity' => $aiResult['severity'] ?? 'Unknown',
                'analyzed_at' => now()->toISOString()
            ];

            // Update scan with results (will be encrypted automatically by model)
            $scan->update([
                'status' => 'completed',
                'result' => $result
            ]);

            Log::info('Scan analyzed successfully', [
                'scan_id' => $scan->id,
                'user_id' => auth()->id(),
                'disease' => $result['primary_disease'],
                'confidence' => $result['confidence']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Analysis completed successfully',
                'scan' => $scan
            ]);

        } catch (\Exception $e) {
            Log::error('Scan analysis error: ' . $e->getMessage());

            // Update scan status to failed
            if (isset($scan)) {
                $scan->update(['status' => 'failed']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Analysis failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's scan history
     */
    public function history(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $status = $request->get('status'); // Optional filter

            $query = auth()->user()->scans()->latest();

            // Filter by status if provided
            if ($status) {
                $query->where('status', $status);
            }

            $scans = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'scans' => $scans
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single scan details
     */
    public function show($id)
    {
        try {
            // Only user's own scans
            $scan = Scan::where('user_id', auth()->id())->findOrFail($id);

            return response()->json([
                'success' => true,
                'scan' => $scan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Scan not found'
            ], 404);
        }
    }

    /**
     * Delete scan
     */
    public function delete($id)
    {
        try {
            // Only user's own scans
            $scan = Scan::where('user_id', auth()->id())->findOrFail($id);

            // Delete image file from storage
            if ($scan->image_path) {
                Storage::disk('public')->delete($scan->image_path);
            }

            // Delete scan record
            $scan->delete();

            Log::info('Scan deleted', [
                'scan_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scan deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function myStats()
    {
        try {
            $user = auth()->user();

            // Basic stats
            $stats = [
                'total_scans' => $user->scans()->count(),
                'completed' => $user->scans()->where('status', 'completed')->count(),
                'pending' => $user->scans()->where('status', 'pending')->count(),
                'processing' => $user->scans()->where('status', 'processing')->count(),
                'failed' => $user->scans()->where('status', 'failed')->count(),
            ];

            // Get disease distribution (from completed scans)
            $completedScans = $user->scans()
                ->where('status', 'completed')
                ->get();

            $diseases = [];
            foreach ($completedScans as $scan) {
                if ($scan->result && isset($scan->result['primary_disease'])) {
                    $disease = $scan->result['primary_disease'];
                    $diseases[$disease] = ($diseases[$disease] ?? 0) + 1;
                }
            }

            $stats['disease_distribution'] = $diseases;

            // Recent scans
            $stats['recent_scans'] = $user->scans()
                ->latest()
                ->take(5)
                ->get();

            // Average confidence (for completed scans)
            $avgConfidence = 0;
            if ($completedScans->count() > 0) {
                $totalConfidence = 0;
                foreach ($completedScans as $scan) {
                    if ($scan->result && isset($scan->result['confidence'])) {
                        $totalConfidence += $scan->result['confidence'];
                    }
                }
                $avgConfidence = $totalConfidence / $completedScans->count();
            }
            $stats['average_confidence'] = round($avgConfidence, 2);

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
