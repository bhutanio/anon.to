<?php

namespace Tests\Feature;

use Tests\TestCase;

class UrlFormatTest extends TestCase
{
    /**
     * String constant for homepage (failed redirection)
     */
    const noRedirectTitle = '<title>Anonymous URL Shortener and Redirect Service - Anon.to</title>';

    /**
     * Create custom HTML title from URL
     * @param String $url
     * @return String
     */
    private function redirectionTitle($url)
    {
        return '<title>Redirecting to '.urldecode($url).'</title>';
    }

    /**
     * Shortcut function to test if URL gives a correct redirection
     * @param String $url
     * @return void
     */
    private function assertRedirection($url)
    {
        $response = $this->get('/?'.$url);
        $response->assertSee($this->redirectionTitle($url));
    }

    /**
     * Shortcut function to test if URL does not give a correct redirection
     * @param String|null $url
     * @return void
     */
    private function assertNoRedirection($url = null)
    {
        if (!empty($url))
            $url = '?' . $url;

        $response = $this->get('/'.$url);
        $response->assertSee(self::noRedirectTitle);
    }

    /**
     * Test invalid URLs format
     * @return void
     */
    public function testBadUrl()
    {
        $this->assertNoRedirection('test');
    }

    /**
     * Test when no param is given
     * @return void
     */
    public function testNoParam()
    {
        $this->assertNoRedirection();
    }

    /**
     * Test regular URLs
     * @return void
     */
    public function testUrlRegular()
    {
        $this->assertRedirection('https://local.dev/test');
    }

    /**
     * Test URLs with an hash ('#')
     * It should redirect WITHOUT the hash
     * @return void
     */
    public function testUrlWithHashAndNoEncoding()
    {
        $response = $this->get('/?https://local.dev/test#withHash');
        $response->assertSee($this->redirectionTitle('https://local.dev/test'));
    }

    /**
     * Test URLs with an hash ('#') where only the hash is percent-encoded
     * It should redirect WITH the hash
     * @return void
     */
    public function testUrlWithHashAndPartialEncoding()
    {
        $this->assertRedirection('https://local.dev/test%23withHash');
    }

    /**
     * Test URLs with an hash ('#') that are partially percent-encoded
     * e.g. with usual encoding functions such as PHP's urlencode
     * @return void
     */
    public function testUrlWithHashAndPhpEncoding()
    {
        $this->assertRedirection(urlencode('https://local.dev/test#withHash'));
    }

    /**
     * Test URLs that are fully percent-encoded
     * @return void
     */
    public function testUrlWithFullEncoding()
    {
        // Real URL is https://local.dev/test#withHash
        $this->assertRedirection('%68%74%74%70%73%3a%2f%2f%6c%6f%63%61%6c%2e%64%65%76%2f%74%65%73%74%23%77%69%74%68%48%61%73%68');
    }
}
