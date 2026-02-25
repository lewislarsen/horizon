<?php

namespace Laravel\Horizon\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\RedisQueue;

class QueueController extends Controller
{
    /**
     * Clear all jobs from the specified queue.
     *
     * @param Request $request
     * @return array
     */
    public function clear(Request $request)
    {
        $request->validate([
            'connection' => 'required|string',
            'queue' => 'required|string',
        ]);

        $connection = $request->input('connection');
        $queue = $request->input('queue');

        if (! method_exists(RedisQueue::class, 'clear')) {
            abort(500, 'Clearing queues is not supported on this version of Laravel.');
        }

        $jobRepository = app(JobRepository::class);

        if (method_exists($jobRepository, 'purge')) {
            $jobRepository->purge($queue);
        }

        $count = app('queue')->connection($connection)->clear($queue);

        return [
            'cleared' => $count,
            'queue' => $queue,
            'connection' => $connection,
        ];
    }

    /**
     * Get all queue connections and their queues.
     *
     * @return array
     */
    public function index()
    {
        $connections = config('queue.connections', []);
        $result = [];

        foreach ($connections as $connectionName => $config) {
            if (!isset($config['driver']) || $config['driver'] !== 'redis') {
                continue;
            }

            $result[] = [
                'name' => $connectionName,
                'queue' => $config['queue'] ?? 'default',
            ];
        }

        return $result;
    }
}
