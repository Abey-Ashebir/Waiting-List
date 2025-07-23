<?php

namespace App\Console\Commands;

use App\Models\WaitingList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReport extends Command
{
    protected $signature = 'report:weekly';
    protected $description = 'Send weekly waiting list report to admin';

    public function handle()
    {
        $totalSignups = WaitingList::count();
        $weeklySignups = WaitingList::where('created_at', '>=', now()->subWeek())->count();
        
        $signupsBySource = WaitingList::select('signup_source')
            ->selectRaw('count(*) as count')
            ->groupBy('signup_source')
            ->get();
            
        $data = [
            'totalSignups' => $totalSignups,
            'weeklySignups' => $weeklySignups,
            'signupsBySource' => $signupsBySource,
        ];
        
        Mail::send('emails.weekly_report', $data, function($message) {
            $message->to('admin@tenamart.com')
                    ->subject('Weekly Waiting List Report');
        });
        
        $this->info('Weekly report sent successfully!');
    }
}