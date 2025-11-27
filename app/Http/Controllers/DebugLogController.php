<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DebugLogController extends Controller
{
    /**
     * Save a debug log message to a file
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function log(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'type' => 'nullable|string|in:info,error,warning,success',
            'context' => 'nullable|array'
        ]);

        $type = $validated['type'] ?? 'info';
        $timestamp = now()->toDateTimeString();
        
        $logMessage = "[$timestamp] [$type] " . $validated['message'] . "\n";
        
        if (!empty($validated['context'])) {
            $logMessage .= 'Context: ' . json_encode($validated['context'], JSON_PRETTY_PRINT) . "\n";
        }
        
        $logMessage .= str_repeat('-', 80) . "\n";
        
        // Create logs directory if it doesn't exist
        $logDir = storage_path('logs/debug');
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // Use daily log files
        $logFile = $logDir . '/debug-' . now()->format('Y-m-d') . '.log';
        
        // Write to file
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        return response()->json([
            'success' => true,
            'message' => 'Log saved successfully',
            'log_file' => $logFile
        ]);
    }
    
    /**
     * Get the latest debug logs
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogs(Request $request)
    {
        $logDir = storage_path('logs/debug');
        $logFile = $logDir . '/debug-' . now()->format('Y-m-d') . '.log';
        
        if (!file_exists($logFile)) {
            return response()->json([
                'success' => true,
                'logs' => 'No logs found for today.'
            ]);
        }
        
        $logs = file_get_contents($logFile);
        
        return response()->json([
            'success' => true,
            'logs' => $logs,
            'log_file' => $logFile
        ]);
    }
}
