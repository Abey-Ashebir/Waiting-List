<?php

namespace App\Http\Controllers;

use App\Models\WaitingList;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaitingListController extends Controller
{
    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = WaitingList::query();
            
            // Apply optional filters
            if ($request->has('source')) {
            $query->where('signup_source', $request->input('source'));
            }
            
            if ($request->has('date')) {
                $query->whereDate('created_at', $request->input('date'));
            }
            
            $waitingList = $query->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $waitingList,
                'message' => 'Waiting list retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching waiting list: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve waiting list',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:waiting_lists,email',
                'signup_source' => 'sometimes|string|in:referral,organic,social_media',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['signup_source'] = $data['signup_source'] ?? 'organic';

            $signup = WaitingList::create($data);

            return response()->json([
                'success' => true,
                'data' => $signup,
                'message' => 'Successfully added to waiting list'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing waiting list entry: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to waiting list',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $signup = WaitingList::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:waiting_lists,email,'.$id,
                'signup_source' => 'sometimes|string|in:referral,organic,social_media',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $signup->update($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $signup,
                'message' => 'Waiting list entry updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating waiting list entry: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update waiting list entry',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $signup = WaitingList::findOrFail($id);
            $signup->delete();

            return response()->json([
                'success' => true,
                'message' => 'Waiting list entry deleted successfully'
            ], 204);

        } catch (\Exception $e) {
            Log::error('Error deleting waiting list entry: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete waiting list entry',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get statistics about the waiting list.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $totalSignups = WaitingList::count();
            
            $signupsBySource = WaitingList::select('signup_source')
                ->selectRaw('count(*) as count')
                ->groupBy('signup_source')
                ->get()
                ->map(function ($item) use ($totalSignups) {
                    $percentage = round(($item->count / $totalSignups) * 100, 1);
                    return [
                        'source' => $item->signup_source,
                        'count' => $item->count,
                        'percentage' => $percentage,
                    ];
                });
                
            $last30Days = WaitingList::where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            $peakDay = WaitingList::selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderByDesc('count')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_signups' => $totalSignups,
                    'signups_by_source' => $signupsBySource,
                    'last_30_days_trend' => $last30Days,
                    'peak_signup_day' => $peakDay,
                ],
                'message' => 'Waiting list statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching waiting list statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve waiting list statistics',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /** 
     * @return StreamedResponse
     */
    public function export(): StreamedResponse
    {
        try {
            $fileName = 'waiting_list_stats_' . date('Y-m-d') . '.csv';
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() {
                $file = fopen('php://output', 'w');
                
                // Total signups
                fputcsv($file, ['Total Signups', WaitingList::count()]);
                fputcsv($file, []);
                
                // By source
                fputcsv($file, ['Sign Up Source', 'Count', 'Percentage']);
                $sources = WaitingList::select('signup_source')
                    ->selectRaw('count(*) as count')
                    ->groupBy('signup_source')
                    ->get();
                    
                $total = WaitingList::count();
                foreach ($sources as $source) {
                    $percentage = round(($source->count / $total) * 100, 1);
                    fputcsv($file, [
                        $source->signup_source, 
                        $source->count,
                        $percentage . '%'
                    ]);
                }
                
                fputcsv($file, []);
                
                // Last 30 days
                fputcsv($file, ['Date', 'Signups']);
                $trends = WaitingList::where('created_at', '>=', now()->subDays(30))
                    ->selectRaw('DATE(created_at) as date, count(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                    
                foreach ($trends as $trend) {
                    fputcsv($file, [$trend->date, $trend->count]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting waiting list data: ' . $e->getMessage());
            
            throw new \Exception('Failed to generate export. Please try again later.');
        }
    }
}