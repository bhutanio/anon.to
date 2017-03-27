<?php

namespace App\Http\Controllers;

use App\Mail\LinkReported;
use App\Models\Reports;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ReportLinkController extends LinksController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function report()
    {
        meta()->setMeta('Report a Link');

        return view('report');
    }

    public function postReport()
    {
        Validator::extend('anon_url', function ($attribute, $value, $parameters, $validator) {
            if ($this->linkFromUrl($value)) {
                return true;
            }

            return false;
        });

        $this->validate($this->request, [
            'url'                  => 'required|url|anon_url',
            'email'                => 'required|email',
            'comment'              => 'required',
            'g-recaptcha-response' => 'required|recaptcha',
        ], [
            'anon_url'                      => 'This URL is not found in our database',
            'g-recaptcha-response.required' => 'Verification required',
            'recaptcha'                     => 'Verification failed. You might be a robot!',
        ]);

        $link = $this->linkFromUrl($this->request->get('url'));
        if ($link) {
            $dupe = Reports::where('link_id', $link->id)->first();
            if ($dupe) {
                flash('This URL has already been reported.', 'error');
            } else {
                $report = Reports::create([
                    'link_id'    => $link->id,
                    'url'        => $this->url_service->unParseUrlFromDb($link),
                    'email'      => $this->request->get('email'),
                    'comment'    => $this->request->get('comment'),
                    'ip_address' => get_ip(),
                    'created_by' => Auth::check() ? Auth::id() : 1,
                ]);

                Mail::send(new LinkReported($report->id));

                flash('Report submitted successfully.', 'success');

                return redirect('report');
            }
        } else {
            flash('Error submitting the report.', 'error');
        }

        return redirect()->back()->withInput($this->request->except('_token'));
    }

    protected function linkFromUrl($url)
    {
        $parsed_url = $this->url_service->parseUrl($url);

        if ($parsed_url['host'] == parse_url(env('APP_URL'), PHP_URL_HOST)) {
            $path = parse_url($url, PHP_URL_PATH);
            if (empty($path)) {
                return null;
            }

            $path = str_replace('/', '', $path);
            if ($link = $this->hashExists($path)) {
                return $link;
            }
        } elseif ($link = $this->urlExists($parsed_url)) {
            return $link;
        }

        return null;
    }
}