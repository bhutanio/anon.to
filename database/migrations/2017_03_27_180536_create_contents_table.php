<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('title_slug')->unique();
            $table->mediumText('content');
            $table->timestamps();
        });

        $contents = [
            [
                'title'      => 'About',
                'title_slug' => 'about',
                'content'    => '<p>' . env('SITE_NAME') . ' is Anonymous URL Shortener and Redirection website.' . '</p>',
            ],

            [
                'title'      => 'Terms of Service ("Terms")',
                'title_slug' => 'terms',
                'content'    => "<h3>1. Terms</h3>
<p>By accessing the website at <a href=\"".url('/')."\">".url('/')."</a>, you are agreeing to be bound by these terms of service, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws. If you do not agree with any of these terms, you are prohibited from using or accessing this site. The materials contained in this website are protected by applicable copyright and trademark law.</p>
<h3>2. Use License</h3>
<ol type=\"a\">
<li>
  Permission is granted to temporarily download one copy of the materials (information or software) on <strong>".env('SITE_NAME')."'s</strong> website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
  <ol type=\"i\">
    <li>modify or copy the materials;</li>
    <li>use the materials for any commercial purpose, or for any public display (commercial or non-commercial);</li>
    <li>attempt to decompile or reverse engineer any software contained on <strong>".env('SITE_NAME')."'s</strong> website;</li>
    <li>remove any copyright or other proprietary notations from the materials; or</li>
    <li>transfer the materials to another person or \"mirror\" the materials on any other server.</li>
  </ol>
</li>
<li>This license shall automatically terminate if you violate any of these restrictions and may be terminated by <strong>".env('SITE_NAME')."</strong> at any time. Upon terminating your viewing of these materials or upon the termination of this license, you must destroy any downloaded materials in your possession whether in electronic or printed format.</li>
</ol>
<h3>3. Disclaimer</h3>
<ol type=\"a\">
<li>The materials on <strong>".env('SITE_NAME')."'s</strong> website are provided on an 'as is' basis. <strong>".env('SITE_NAME')."</strong> makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</li>
<li>Further, <strong>".env('SITE_NAME')."</strong> does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its website or otherwise relating to such materials or on any sites linked to this site.</li>
</ol>
<h3>4. Limitations</h3>
<p>In no event shall <strong>".env('SITE_NAME')."</strong> or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on <strong>".env('SITE_NAME')."'s</strong> website, even if <strong>".env('SITE_NAME')."</strong> or a <strong>".env('SITE_NAME')."</strong> authorized representative has been notified orally or in writing of the possibility of such damage. Because some jurisdictions do not allow limitations on implied warranties, or limitations of liability for consequential or incidental damages, these limitations may not apply to you.</p>
<h3>5. Accuracy of materials</h3>
<p>The materials appearing on <strong>".env('SITE_NAME')."'s</strong> website could include technical, typographical, or photographic errors. <strong>".env('SITE_NAME')."</strong> does not warrant that any of the materials on its website are accurate, complete or current. <strong>".env('SITE_NAME')."</strong> may make changes to the materials contained on its website at any time without notice. However <strong>".env('SITE_NAME')."</strong> does not make any commitment to update the materials.</p>
<h3>6. Links</h3>
<p><strong>".env('SITE_NAME')."</strong> has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by <strong>".env('SITE_NAME')."</strong> of the site. Use of any such linked website is at the user's own risk.</p>
<h3>7. Modifications</h3>
<p><strong>".env('SITE_NAME')."</strong> may revise these terms of service for its website at any time without notice. By using this website you are agreeing to be bound by the then current version of these terms of service.</p>
<h3>8. Governing Law</h3>
<p>These terms and conditions are governed by and construed in accordance with the laws of Bhutan and you irrevocably submit to the exclusive jurisdiction of the courts in that State or location.</p>
<h3>9. <strong>".env('SITE_NAME')."</strong> Services</h3>
<strong>".env('SITE_NAME')."</strong> allow you to shorten and redirect URLs using the <strong>".env('SITE_NAME')."'s</strong> domain as the link. As long as you comply with these Terms of Service, you may use the Services for your personal, non-commercial purposes. Please contact us if you would like to use the Services for your business.",
            ],

            [
                'title'      => 'Privacy Policy',
                'title_slug' => 'privacy-policy',
                'content'    => "<p>Your privacy is important to us.</p>
<p>It is <strong>".env('SITE_NAME')."'s</strong> policy to respect your privacy regarding any information we may collect while operating our website. Accordingly, we have developed this privacy policy in order for you to understand how we collect, use, communicate, disclose and otherwise make use of personal information. We have outlined our privacy policy below.</p>
<ul>
<li>We will collect personal information by lawful and fair means and, where appropriate, with the knowledge or consent of the individual concerned.</li>
<li>Before or at the time of collecting personal information, we will identify the purposes for which information is being collected.</li>
<li>We will collect and use personal information solely for fulfilling those purposes specified by us and for other ancillary purposes, unless we obtain the consent of the individual concerned or as required by law.</li>
<li>Personal data should be relevant to the purposes for which it is to be used, and, to the extent necessary for those purposes, should be accurate, complete, and up-to-date.</li>
<li>We will protect personal information by using reasonable security safeguards against loss or theft, as well as unauthorized access, disclosure, copying, use or modification.</li>
<li>We will make readily available to customers information about our policies and practices relating to the management of personal information.</li>
<li>We will only retain personal information for as long as necessary for the fulfilment of those purposes.</li>
</ul>
<p>We are committed to conducting our business in accordance with these principles in order to ensure that the confidentiality of personal information is protected and maintained. <strong>".env('SITE_NAME')."</strong> may change this privacy policy from time to time at <strong>".env('SITE_NAME')."'s</strong> sole discretion.</p>",
            ],

        ];

        DB::table('contents')->insert($contents);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
}
