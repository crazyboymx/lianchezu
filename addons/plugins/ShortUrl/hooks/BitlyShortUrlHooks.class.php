<?php
class BitlyShortUrlHooks
{
	// 替换短网址
	public function getShortUrl($url,$login,$apikey)
	{
		if(!$login || !$apikey) return $url;
		return $this->request($url,$login,$apikey);
	}

	private function request($url,$login,$apikey){
		if(!$url) return '';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://api.bitly.com/v3/shorten');   //goo.gl api url
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'login='.$login.'&apiKey='.$apikey.'&format=txt&longUrl='.urlencode($url));
		$short = curl_exec($curl);
		curl_close($curl);
		if($short){
			return $short;
		}else{
			return $url;
		}
	}
}