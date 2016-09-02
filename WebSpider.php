<?php
/**
 * Web spider, Crawling whole website for links.
 *
 * no long description
 *
 * @author     kadekjayak <kadekjayak@yahoo.co.id>
 * @copyright  2016 kadekjayak
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version    0.1
 */
namespace Kadekjayak;

class WebSpider
{

    public $links = [];
    public $domain;
    protected $curlHandle;

    public $_defaultHeaders = array(
        'Connection: keep-alive',
        'Cache-Control: max-age=0',
        'Upgrade-Insecure-Requests: 1',
        'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.76 Mobile Safari/537.36',
        'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Encoding: gzip, deflate, sdch, br',
        'Accept-Language: en-US,en;q=0.8,id;q=0.6,fr;q=0.4'
    );


    public function __construct(){
        $this->curlHandle = curl_init();
        $this->setupCurl();
    }

    public function scrape($url = null, $depth = 2, $parentUrl = null){
        if( $depth <= 0) return;

        if($url == null) return false;

        if($parentUrl !== null) {
            if( !$this->isSameDomain($parentUrl, $url) ) {
                return false;
            }
        }

        if ( $this->isSeen($url) ) {
            return false;
        }

        $this->links[] = $url;

        $dom = new \DOMDocument('1.0');

        @$dom->loadHTML( $this->get($url) );


        $anchors = $dom->getElementsByTagName('a');
        $i = 0;

        foreach ($anchors as $element) {
            $href = $element->getAttribute('href');
            if ( 0 !== strpos($href, 'http') ) {
                $path = '/' . ltrim($href, '/');
                if (extension_loaded('http')) {
                    $href = http_build_url($url, array('path' => $path));
                } else {
                    $parts = parse_url($url);
                    $href = $parts['scheme'] . '://';
                    if (isset($parts['user']) && isset($parts['pass'])) {
                        $href .= $parts['user'] . ':' . $parts['pass'] . '@';
                    }
                    $href .= $parts['host'];
                    if (isset($parts['port'])) {
                        $href .= ':' . $parts['port'];
                    }
                    $href .= $path;
                }
            }
            if( $this->isSameDomain($url, $href) ) {
                $this->scrape($href, $depth - 1, $url);  
            }
        }
        return $this->links;
    }

    private function isSameDomain($url, $href){
        $host = parse_url($url, PHP_URL_HOST);
        $host2 = parse_url($href, PHP_URL_HOST);
        return strtolower($host) == strtolower($host2);
    }

    private function isSeen($url){
        return in_array($url, $this->links);
    }



    /*** CURL **/
    /**
    * Register default CURL parameters
    */
    protected function setupCurl()
    {
        curl_setopt( $this->curlHandle, CURLOPT_POST, 0 );
        curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 1 );
        //curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, $this->_defaultHeaders);
        curl_setopt( $this->curlHandle, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->curlHandle, CURLOPT_COOKIEFILE,'cookie' );
        curl_setopt( $this->curlHandle, CURLOPT_COOKIEJAR, 'cookiejar' );
    }

    /**
    * Execute the CURL and return result
    *
    * @return curl result
    */
    public function get($url)
    {
        curl_setopt( $this->curlHandle, CURLOPT_URL, $url );
        $result = curl_exec($this->curlHandle);
        return $result;
    }
}
