<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data = [];
    protected $template = '';
    protected $to = '';
    protected $subject = '';
    protected $optionService;

    /**
     * Create a new job instance.
     *
     * @param string $template
     * @param string $subject
     * @param string $to
     * @param array $data
     */
    public function __construct($template, $subject, $to, $data)
    {
        $this->data = $data;
        $this->template = $template;
        $this->to = $to;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send($this->template, $this->data, function ($m) {
            $m->from('contactus@example.org', $this->subject);
            $m->to($this->to)->subject($this->subject);
        });
    }
}
