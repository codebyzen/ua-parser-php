<?php

/////////////////////////////////////////////////////////////////////////////////
/* UAParser.php v0.0.1 
   PHP script based on UAParser.js v0.7.31
   Copyright © 2022 CodeByZen <dsda@dsda.ru>
   Copyright of original UAParser.js v0.7.31 belongs to Faisal Salman <f@faisalman.com> [https://github.com/faisalman/ua-parser-js]
   MIT License *//*
   Detect Browser, Engine, OS, CPU, and Device type/model from User-Agent data.
   Supports PHP 7.3.29 
   Source : https://github.com/faisalman/ua-parser-js */
/////////////////////////////////////////////////////////////////////////////////


class UAParserRegExp {

	///////////
	// Helper
	//////////

	function has($str1, $str2) {
		return is_string($str1) ? stripos($str1,$str2)!==false : false;
	}

	function lowerize($str) {
		return strtolower($str);
	}

	function majorize($version) {
		if (is_string($version)) {
			$ver = preg_replace("/[^\d\.]/", '', $version);
			$maj = explode(".",$ver)[0];
			return $maj;
		} else {
			return null;
		}
	}



	///////////////
	// Map helper
	//////////////

	function parse_matches($structure, $matches) {
		$result = [];
		// iterate structure
		foreach ($structure as $skey => $svalue) {
			
			// if difficult structure item
			if (is_array($svalue)) {
				// if second item is difficult structure
				if (count($svalue)>2) {
					// maybe call method
					$maybe_func = $svalue[1];
					if (method_exists($this, $maybe_func)) {
						$maybe_mapper = $svalue[2];
						$result[$svalue[0]] = $this->$maybe_func($matches[$skey+1][0], $this->$maybe_mapper);
						if (!$result[$svalue[0]]) {
							return $result;
						}
					} else {
						// maybe additional regexp
						// print_r($svalue);
						// print_r($matches);
						if (!isset($matches[$skey+1])) { continue; }
						$value = preg_replace($svalue[1], $svalue[2], $matches[$skey+1][0]);
						$result[$svalue[0]] = $value;
					}
				} else {
					// simple name - value
					$maybe_func = $svalue[1];
					if (method_exists($this, $maybe_func)) {
						$maybe_mapper = $svalue[1];
						$result[$svalue[0]] = $this->$maybe_func($matches[$skey+1][0]);
						if (!$result[$svalue[0]]) {
							return $result;
						}
					} else {
						$result[$svalue[0]] = $svalue[1];
					}
				}
			} 
			// if simple structure item
			elseif (is_string($svalue)) {
				$result[$svalue] = $matches[$skey+1][0];
			}
		}
		return $result;
	}


	function parse_ua($ua, $regexps) {

		// iterate lines
		foreach($regexps as $reg_line) {

			// iterate regexps
			foreach($reg_line[0] as $regexp) {
				preg_match_all($regexp, $ua, $matches);
				// count need be equal of regexp matches and structure items
				if ($matches[0]) {
					$info = $this->parse_matches($reg_line[1], $matches);
					return $info;
				}
			}

		}

		return [];
	}

	function strMapper($str, $map) {
		foreach ($map as $i=>$v) {
			// check if current value is array
			if (is_array($v)) {
				if ($this->strMapper($str,$v)!==null) {
					return ($i === '?') ? null : $i;
				}
			} elseif ($this->has($v, $str)) {
				return ($i === '?') ? null : $i;
			}
		}
		return null;
	}

	///////////////
	// String map
	//////////////

	// Safari < 3.0
	private $oldSafariMap = [
		'1.0'   => '/8',
		'1.2'   => '/1',
		'1.3'   => '/3',
		'2.0'   => '/412',
		'2.0.2' => '/416',
		'2.0.3' => '/417',
		'2.0.4' => '/419',
		'?'		=> '/'
	];
	
	private $windowsVersionMap = [
		'ME'		=> '4.90',
		'NT 3.11'   => 'NT3.51',
		'NT 4.0'	=> 'NT4.0',
		'2000'		=> 'NT 5.0',
		'XP'		=> ['NT 5.1', 'NT 5.2'],
		'Vista'		=> 'NT 6.0',
		'7'			=> 'NT 6.1',
		'8'			=> 'NT 6.2',
		'8.1'		=> 'NT 6.3',
		'10'		=> ['NT 6.4', 'NT 10.0'],
		'RT'		=> 'ARM'
	];

	//////////////
	// Regex map
	/////////////

	public $regexes = [
		"browser" => [
			[
				["/\b(?:crmo|crios)\/([\w\.]+)/i"],
				['version', ['name', 'Chrome']]
			],
			[
				["/edg(?:e|ios|a)?\/([\w\.]+)/i"],
				['version', ['name', 'Edge']]
			],
			[
				["/(opera mini)\/([-\w\.]+)/i","/(opera [mobiletab]{3,6})\b.+version\/([-\w\.]+)/i","/(opera)(?:.+version\/|[\/ ]+)([\w\.]+)/i"],
				['name', 'version']
			],
			[
				["/opios[\/ ]+([\w\.]+)/i"],
				['version', ['name', 'Opera Mini']]
			],
			[
				["/\bopr\/([\w\.]+)/i"],
				['version', ['name', 'Opera']]
			],
			[
				["/(kindle)\/([\w\.]+)/i","/(lunascape|maxthon|netfront|jasmine|blazer)[\/ ]?([\w\.]*)/i","/(avant |iemobile|slim)(?:browser)?[\/ ]?([\w\.]*)/i","/(ba?idubrowser)[\/ ]?([\w\.]+)/i","/(?:ms|\()(ie) ([\w\.]+)/i","/(flock|rockmelt|midori|epiphany|silk|skyfire|ovibrowser|bolt|iron|vivaldi|iridium|phantomjs|bowser|quark|qupzilla|falkon|rekonq|puffin|brave|whale|qqbrowserlite|qq|duckduckgo)\/([-\w\.]+)/i","/(weibo)__([\d\.]+)/i"],
				['name', 'version']
			],
			[
				["/(?:\buc? ?browser|(?:juc.+)ucweb)[\/ ]?([\w\.]+)/i"],
				['version', ['name', 'UC Browser']]
			],
			[
				["/microm.+\bqbcore\/([\w\.]+)/i","/\bqbcore\/([\w\.]+).+microm/i"],
				['version', ['name', 'WeChat(Win) Desktop']]
			],
			[
				["/micromessenger\/([\w\.]+)/i"],
				['version', ['name', 'WeChat']]
			],
			[
				["/konqueror\/([\w\.]+)/i"],
				['version', ['name', 'Konqueror']]
			],
			[
				["/trident.+rv[: ]([\w\.]{1,9})\b.+like gecko/i"],
				['version', ['name', 'IE']]
			],
			[
				["/yabrowser\/([\w\.]+)/i"],
				['version', ['name', 'Yandex']]
			],
			[
				["/(avast|avg)\/([\w\.]+)/i"],
				[['name', "/(.+)/", "$1 Secure Browser"], 'version']
			],
			[
				["/\bfocus\/([\w\.]+)/i"],
				['version', ['name', 'Firefox Focus']]
			],
			[
				["/\bopt\/([\w\.]+)/i"],
				['version', ['name', 'Opera Touch']]
			],
			[
				["/coc_coc\w+\/([\w\.]+)/i"],
				['version', ['name', 'Coc Coc']]
			],
			[
				["/dolfin\/([\w\.]+)/i"],
				['version', ['name', 'Dolphin']]
			],
			[
				["/coast\/([\w\.]+)/i"],
				['version', ['name', 'Opera Coast']]
			],
			[
				["/miuibrowser\/([\w\.]+)/i"],
				['version', ['name', 'MIUI Browser']]
			],
			[
				["/fxios\/([-\w\.]+)/i"],
				['version', ['name', 'Firefox']]
			],
			[
				["/\bqihu|(qi?ho?o?|360)browser/i"],
				[['name', '360 Browser']]
			],
			[
				["/(oculus|samsung|sailfish|huawei)browser\/([\w\.]+)/i"],
				[['name', "/(.+)/", "$1 Browser"], 'version']
			],
			[
				["/(comodo_dragon)\/([\w\.]+)/i"],
				[['name', "/_/", ' '], 'version']
			],
			[
				["/(electron)\/([\w\.]+) safari/i","/(tesla)(?: qtcarbrowser|\/(20\d\d\.[-\w\.]+))/i","/m?(qqbrowser|baiduboxapp|2345Explorer)[\/ ]?([\w\.]+)/i"],
				['name', 'version']
			],
			[
				["/(metasr)[\/ ]?([\w\.]+)/i","/(lbbrowser)/i","/\[(linkedin)app\]/i"],
				['name']
			],
			[
				["/((?:fban\/fbios|fb_iab\/fb4a)(?!.+fbav)|;fbav\/([\w\.]+);)/i"],
				[['name', 'Facebook'], 'version']
			],
			[
				["/safari (line)\/([\w\.]+)/i","/\b(line)\/([\w\.]+)\/iab/i","/(chromium|instagram)[\/ ]([-\w\.]+)/i"],
				['name', 'version']
			],
			[
				["/\bgsa\/([\w\.]+) .*safari\//i"],
				['version', ['name', 'GSA']]
			],
			[
				["/headlesschrome(?:\/([\w\.]+)| )/i"],
				['version', ['name', 'Chrome Headless']]
			],
			[
				["/ wv\).+(chrome)\/([\w\.]+)/i"],
				[['name', 'Chrome WebView'], 'version']
			],
			[
				["/droid.+ version\/([\w\.]+)\b.+(?:mobile safari|safari)/i"],
				['version', ['name', 'Android Browser']]
			],
			[
				["/(chrome|omniweb|arora|[tizenoka]{5} ?browser)\/v?([\w\.]+)/i"],
				['name', 'version']
			],
			[
				["/version\/([\w\.]+) .*mobile\/\w+ (safari)/i"],
				['version', ['name', 'Mobile Safari']]
			],
			[
				["/version\/([\w\.]+) .*(mobile ?safari|safari)/i"],
				['version', 'name']
			],
			[
				["/webkit.+?(mobile ?safari|safari)(\/[\w\.]+)/i"],
				['name', ['version', 'strMapper', 'oldSafariMap']]
			],
			[
				["/(webkit|khtml)\/([\w\.]+)/i"],
				['name', 'version']
			],
			[
				["/(navigator|netscape\d?)\/([-\w\.]+)/i"],
				[['name', 'Netscape'], 'version']
			],
			[
				["/mobile vr; rv:([\w\.]+)\).+firefox/i"],
				['version', ['name', 'Firefox Reality']]
			],
			[
				["/ekiohf.+(flow)\/([\w\.]+)/i","/(swiftfox)/i","/(icedragon|iceweasel|camino|chimera|fennec|maemo browser|minimo|conkeror|klar)[\/ ]?([\w\.\+]+)/i","/(seamonkey|k-meleon|icecat|iceape|firebird|phoenix|palemoon|basilisk|waterfox)\/([-\w\.]+)$/i","/(firefox)\/([\w\.]+)/i","/(mozilla)\/([\w\.]+) .+rv\:.+gecko\/\d+/i","/(polaris|lynx|dillo|icab|doris|amaya|w3m|netsurf|sleipnir|obigo|mosaic|(?:go|ice|up)[\. ]?browser)[-\/ ]?v?([\w\.]+)/i","/(links) \(([\w\.]+)/i"],
				['name', 'version']
			]
		],
	
		"cpu" => [
			[
				["/(?:(amd|x(?:(?:86|64)[-_])?|wow|win)64)[;\)]/i"],
				[['architecture', 'amd64']]
			],
			[
				["/(ia32(?=;))/i"],
				[['architecture', 'lowerize']]
			],
			[
				["/((?:i[346]|x)86)[;\)]/i"],
				[['architecture', 'ia32']]
			],
			[
				["/\b(aarch64|arm(v?8e?l?|_?64))\b/i"],
				[['architecture', 'arm64']]
			],
			[
				["/\b(arm(?:v[67])?ht?n?[fl]p?)\b/i"],
				[['architecture', 'armhf']]
			],
			[
				["/windows (ce|mobile); ppc;/i"],
				[['architecture', 'arm']]
			],
			[
				["/((?:ppc|powerpc)(?:64)?)(?: mac|;|\))/i"],
				[['architecture', "/ower/", '', 'lowerize']]
			],
			[
				["/(sun4\w)[;\)]/i"],
				[['architecture', 'sparc']]
			],
			[
				["/((?:avr32|ia64(?=;))|68k(?=\))|\barm(?=v(?:[1-7]|[5-7]1)l?|;|eabi)|(?=atmel )avr|(?:irix|mips|sparc)(?:64)?\b|pa-risc)/i"],
				[['architecture', 'lowerize']]
			]
		],
	
		'device' => [
			[
				["/\b(sch-i[89]0\d|shw-m380s|sm-[pt]\w{2,4}|gt-[pn]\d{2,4}|sgh-t8[56]9|nexus 10)/i"],
				['model', ['vendor', 'Samsung'], ['type', 'Tablet']]
			],
			[
				["/\b((?:s[cgp]h|gt|sm)-\w+|galaxy nexus)/i","/samsung[- ]([-\w]+)/i","/sec-(sgh\w+)/i"],
				['model', ['vendor', 'Samsung'], ['type', 'Mobile']]
			],
			[
				["/\((ip(?:hone|od)[\w ]*);/i"],
				['model', ['vendor', 'Apple'], ['type', 'Mobile']]
			],
			[
				["/\((ipad);[-\w\),; ]+apple/i","/applecoremedia\/[\w\.]+ \((ipad)/i","/\b(ipad)\d\d?,\d\d?[;\]].+ios/i"],
				['model', ['vendor', 'Apple'], ['type', 'Tablet']]
			],
			[
				["/\b((?:ag[rs][23]?|bah2?|sht?|btv)-a?[lw]\d{2})\b(?!.+d\/s)/i"],
				['model', ['vendor', 'Huawei'], ['type', 'Tablet']]
			],
			[
				["/(?:huawei|honor)([-\w ]+)[;\)]/i","/\b(nexus 6p|\w{2,4}e?-[atu]?[ln][\dx][012359c][adn]?)\b(?!.+d\/s)/i"],
				['model', ['vendor', 'Huawei'], ['type', 'Mobile']]
			],
			[
				["/\b(poco[\w ]+)(?: bui|\))/i", "/\b; (\w+) build\/hm\1/i","/\b(hm[-_ ]?note?[_ ]?(?:\d\w)?) bui/i","/\b(redmi[\-_ ]?(?:note|k)?[\w_ ]+)(?: bui|\))/i","/\b(mi[-_ ]?(?:a\d|one|one[_ ]plus|note lte|max|cc)?[_ ]?(?:\d?\w?)[_ ]?(?:plus|se|lite)?)(?: bui|\))/i"],
				[['model', "/_/", ' '], ['vendor', 'Xiaomi'], ['type', 'Mobile']]
			],
			[
				["/\b(mi[-_ ]?(?:pad)(?:[\w_ ]+))(?: bui|\))/i"],
				[['model', "/_/", ' '], ['vendor', 'Xiaomi'], ['type', 'Tablet']]
			],
			[
				["/; (\w+) bui.+ oppo/i","/\b(cph[12]\d{3}|p(?:af|c[al]|d\w|e[ar])[mt]\d0|x9007|a101op)\b/i"],
				['model', ['vendor', 'OPPO'], ['type', 'Mobile']]
			],
			[
				["/vivo (\w+)(?: bui|\))/i","/\b(v[12]\d{3}\w?[at])(?: bui|;)/i"],
				['model', ['vendor', 'Vivo'], ['type', 'Mobile']]
			],
			[
				["/\b(rmx[12]\d{3})(?: bui|;|\))/i"],
				['model', ['vendor', 'Realme'], ['type', 'Mobile']]
			],
			[
				["/\b(milestone|droid(?:[2-4x]| (?:bionic|x2|pro|razr))?:?( 4g)?)\b[\w ]+build\//i","/\bmot(?:orola)?[- ](\w*)/i","/((?:moto[\w\(\) ]+|xt\d{3,4}|nexus 6)(?= bui|\)))/i"],
				['model', ['vendor', 'Motorola'], ['type', 'Mobile']]
			],
			[
				["/\b(mz60\d|xoom[2 ]{0,2}) build\//i"],
				['model', ['vendor', 'Motorola'], ['type', 'Tablet']]
			],
			[
				["/((?=lg)?[vl]k\-?\d{3}) bui| 3\.[-\w; ]{10}lg?-([06cv9]{3,4})/i"],
				['model', ['vendor', 'LG'], ['type', 'Tablet']]
			],
			[
				["/(lm(?:-?f100[nv]?|-[\w\.]+)(?= bui|\))|nexus [45])/i", "/\blg[-e;\/ ]+((?!browser|netcast|android tv)\w+)/i", "/\blg-?([\d\w]+) bui/i"],
				['model', ['vendor', 'LG'], ['type', 'Mobile']]
			],
			[
				["/(ideatab[-\w ]+)/i", "/lenovo ?(s[56]000[-\w]+|tab(?:[\w ]+)|yt[-\d\w]{6}|tb[-\d\w]{6})/i"],
				['model', ['vendor', 'Lenovo'], ['type', 'Tablet']]
			],
			[
				["/(?:maemo|nokia).*(n900|lumia \d+)/i", "/nokia[-_ ]?([-\w\.]*)/i"],
				[['model', "/_/", ' '], ['vendor', 'Nokia'], ['type', 'Mobile']]
			],
			[
				["/(pixel c)\b/i"],
				['model', ['vendor', 'Google'], ['type', 'Tablet']]
			],
			[
				["/droid.+; (pixel[\daxl ]{0,6})(?: bui|\))/i"],
				['model', ['vendor', 'Google'], ['type', 'Mobile']]
			],
			[
				["/droid.+ (a?\d[0-2]{2}so|[c-g]\d{4}|so[-gl]\w+|xq-a\w[4-7][12])(?= bui|\).+chrome\/(?![1-6]{0,1}\d\.))/i"],
				['model', ['vendor', 'Sony'], ['type', 'Mobile']]
			],
			[
				["/sony tablet [ps]/i", "/\b(?:sony)?sgp\w+(?: bui|\))/i"],
				[['model', 'Xperia Tablet'], ['vendor', 'Sony'], ['type', 'Tablet']]
			],
			[
				["/ (kb2005|in20[12]5|be20[12][59])\b/i", "/(?:one)?(?:plus)? (a\d0\d\d)(?: b|\))/i"],
				['model', ['vendor', 'OnePlus'], ['type', 'Mobile']]
			],
			[
				["/(alexa)webm/i", "/(kf[a-z]{2}wi)( bui|\))/i", "/(kf[a-z]+)( bui|\)).+silk\//i"],
				['model', ['vendor', 'Amazon'], ['type', 'Tablet']]
			],
			[
				["/((?:sd|kf)[0349hijorstuw]+)( bui|\)).+silk\//i"],
				[['model', "/(.+)/", 'Fire Phone $1'], ['vendor', 'Amazon'], ['type', 'Mobile']]
			],
			[
				["/(playbook);[-\w\),; ]+(rim)/i"],
				['model', 'vendor', ['type', 'Tablet']]
			],
			[
				["/\b((?:bb[a-f]|st[hv])100-\d)/i", "/\(bb10; (\w+)/i"],
				['model', ['vendor', 'BlackBerry'], ['type', 'Mobile']]
			],
			[
				["/(?:\b|asus_)(transfo[prime ]{4,10} \w+|eeepc|slider \w+|nexus 7|padfone|p00[cj])/i"],
				['model', ['vendor', 'Asus'], ['type', 'Tablet']]
			],
			[
				["/ (z[bes]6[027][012][km][ls]|zenfone \d\w?)\b/i"],
				['model', ['vendor', 'Asus'], ['type', 'Mobile']]
			],
			[
				["/(nexus 9)/i"],
				['model', ['vendor', 'HTC'], ['type', 'Tablet']]
			],
			[
				["/(htc)[-;_ ]{1,2}([\w ]+(?=\)| bui)|\w+)/i", "/(zte)[- ]([\w ]+?)(?: bui|\/|\))/i", "/(alcatel|geeksphone|nexian|panasonic|sony(?!-bra))[-_ ]?([-\w]*)/i"],
				['vendor', ['model', "/_/", ' '], ['type', 'Mobile']]
			],
			[
				["/droid.+; ([ab][1-7]-?[0178a]\d\d?)/i"],
				['model', ['vendor', 'Acer'], ['type', 'Tablet']]
			],
			[
				["/droid.+; (m[1-5] note) bui/i", "/\bmz-([-\w]{2,})/i"],
				['model', ['vendor', 'Meizu'], ['type', 'Mobile']]
			],
			[
				["/\b(sh-?[altvz]?\d\d[a-ekm]?)/i"],
				['model', ['vendor', 'Sharp'], ['type', 'Mobile']]
			],
			[
				["/(blackberry|benq|palm(?=\-)|sonyericsson|acer|asus|dell|meizu|motorola|polytron)[-_ ]?([-\w]*)/i", "/(hp) ([\w ]+\w)/i", "/(asus)-?(\w+)/i", "/(microsoft); (lumia[\w ]+)/i", "/(lenovo)[-_ ]?([-\w]+)/i", "/(jolla)/i", "/(oppo) ?([\w ]+) bui/i"],
				['vendor', 'model', ['type', 'Mobile']]
			],
			[
				["/(archos) (gamepad2?)/i", "/(hp).+(touchpad(?!.+tablet)|tablet)/i", "/(kindle)\/([\w\.]+)/i", "/(nook)[\w ]+build\/(\w+)/i", "/(dell) (strea[kpr\d ]*[\dko])/i", "/(le[- ]+pan)[- ]+(\w{1,9}) bui/i", "/(trinity)[- ]*(t\d{3}) bui/i", "/(gigaset)[- ]+(q\w{1,9}) bui/i", "/(vodafone) ([\w ]+)(?:\)| bui)/i"],
				['vendor', 'model', ['type', 'Tablet']]
			],
			[
				["/(surface duo)/i"],
				['model', ['vendor', 'Microsoft'], ['type', 'Tablet']]
			],
			[
				["/droid [\d\.]+; (fp\du?)(?: b|\))/i"],
				['model', ['vendor', 'Fairphone'], ['type', 'Mobile']]
			],
			[
				["/(u304aa)/i"],
				['model', ['vendor', 'AT&T'], ['type', 'Mobile']]
			],
			[
				["/\bsie-(\w*)/i"],
				['model', ['vendor', 'Siemens'], ['type', 'Mobile']]
			],
			[
				["/\b(rct\w+) b/i"],
				['model', ['vendor', 'RCA'], ['type', 'Tablet']]
			],
			[
				["/\b(venue[\d ]{2,7}) b/i"],
				['model', ['vendor', 'Dell'], ['type', 'Tablet']]
			],
			[
				["/\b(q(?:mv|ta)\w+) b/i"],
				['model', ['vendor', 'Verizon'], ['type', 'Tablet']]
			],
			[
				["/\b(?:barnes[& ]+noble |bn[rt])([\w\+ ]*) b/i"],
				['model', ['vendor', 'Barnes & Noble'], ['type', 'Tablet']]
			],
			[
				["/\b(tm\d{3}\w+) b/i"],
				['model', ['vendor', 'NuVision'], ['type', 'Tablet']]
			],
			[
				["/\b(k88) b/i"],
				['model', ['vendor', 'ZTE'], ['type', 'Tablet']]
			],
			[
				["/\b(nx\d{3}j) b/i"],
				['model', ['vendor', 'ZTE'], ['type', 'Mobile']]
			],
			[
				["/\b(gen\d{3}) b.+49h/i"],
				['model', ['vendor', 'Swiss'], ['type', 'Mobile']]
			],
			[
				["/\b(zur\d{3}) b/i"],
				['model', ['vendor', 'Swiss'], ['type', 'Tablet']]
			],
			[
				["/\b((zeki)?tb.*\b) b/i"],
				['model', ['vendor', 'Zeki'], ['type', 'Tablet']]
			],
			[
				["/\b([yr]\d{2}) b/i", "/\b(dragon[- ]+touch |dt)(\w{5}) b/i"],
				[['vendor', 'Dragon Touch'], 'model', ['type', 'Tablet']]
			],
			[
				["/\b(ns-?\w{0,9}) b/i"],
				['model', ['vendor', 'Insignia'], ['type', 'Tablet']]
			],
			[
				["/\b((nxa|next)-?\w{0,9}) b/i"],
				['model', ['vendor', 'NextBook'], ['type', 'Tablet']]
			],
			[
				["/\b(xtreme\_)?(v(1[045]|2[015]|[3469]0|7[05])) b/i"],
				[['vendor', 'Voice'], 'model', ['type', 'Mobile']]
			],
			[
				["/\b(lvtel\-)?(v1[12]) b/i"],
				[['vendor', 'LvTel'], 'model', ['type', 'Mobile']]
			],
			[
				["/\b(ph-1) /i"],
				['model', ['vendor', 'Essential'], ['type', 'Mobile']]
			],
			[
				["/\b(v(100md|700na|7011|917g).*\b) b/i"],
				['model', ['vendor', 'Envizen'], ['type', 'Tablet']]
			],
			[
				["/\b(trio[-\w\. ]+) b/i"],
				['model', ['vendor', 'MachSpeed'], ['type', 'Tablet']]
			],
			[
				["/\btu_(1491) b/i"],
				['model', ['vendor', 'Rotor'], ['type', 'Tablet']]
			],
			[
				["/(shield[\w ]+) b/i"],
				['model', ['vendor', 'Nvidia'], ['type', 'Tablet']]
			],
			[
				["/(sprint) (\w+)/i"],
				['vendor', 'model', ['type', 'Mobile']]
			],
			[
				["/(kin\.[onetw]{3})/i"],
				[['model', "/\./", ' '], ['vendor', 'Microsoft'], ['type', 'Mobile']]
			],
			[
				["/droid.+; (cc6666?|et5[16]|mc[239][23]x?|vc8[03]x?)\)/i"],
				['model', ['vendor', 'Zebra'], ['type', 'Tablet']]
			],
			[
				["/droid.+; (ec30|ps20|tc[2-8]\d[kx])\)/i"],
				['model', ['vendor', 'Zebra'], ['type', 'Mobile']]
			],
			[
				["/(ouya)/i", "/(nintendo) ([wids3utch]+)/i"],
				['vendor', 'model', ['type', 'Console']]
			],
			[
				["/droid.+; (shield) bui/i"],
				['model', ['vendor', 'Nvidia'], ['type', 'Console']]
			],
			[
				["/(playstation [345portablevi]+)/i"],
				['model', ['vendor', 'Sony'], ['type', 'Console']]
			],
			[
				["/\b(xbox(?: one)?(?!; xbox))[\); ]/i"],
				['model', ['vendor', 'Microsoft'], ['type', 'Console']]
			],
			[
				["/smart-tv.+(samsung)/i"],
				['vendor', ['type', 'SmartTV']]
			],
			[
				["/hbbtv.+maple;(\d+)/i"],
				[['model', "/^/", 'SmartTV'], ['vendor', 'Samsung'], ['type', 'SmartTV']]
			],
			[
				["/(nux; netcast.+smarttv|lg (netcast\.tv-201\d|android tv))/i"],
				[['vendor', 'LG'], ['type', 'SmartTV']]
			],
			[
				["/(apple) ?tv/i"],
				['vendor', ['model', 'Apple TV'], ['type', 'SmartTV']]
			],
			[
				["/crkey/i"],
				[['model', 'Chromecast'], ['vendor', 'Google'], ['type', 'SmartTV']]
			],
			[
				["/droid.+aft(\w)( bui|\))/i"],
				['model', ['vendor', 'Amazon'], ['type', 'SmartTV']]
			],
			[
				["/\(dtv[\);].+(aquos)/i"],
				['model', ['vendor', 'Sharp'], ['type', 'SmartTV']]
			],
			[
				["/(bravia[\w-]+) bui/i"],
				['model', ['vendor', 'Sony'], ['type', 'SmartTV']]
			],
			[
				["/(mitv-\w{5}) bui/i"],
				['model', ['vendor', 'Xiaomi'], ['type', 'SmartTV']]
			],
			[
				["/\b(roku)[\dx]*[\)\/]((?:dvp-)?[\d\.]*)/i", "/hbbtv\/\d+\.\d+\.\d+ +\([\w ]*; *(\w[^;]*);([^;]*)/i"],
				[['vendor', 'trim'], ['model', 'trim'], ['type', 'SmartTV']]
			],
			[
				["/\b(android tv|smart[- ]?tv|opera tv|tv; rv:)\b/i"],
				[['type', 'SmartTV']]
			],
			[
				["/((pebble))app/i"],
				['vendor', 'model', ['type', 'Wearable']]
			],
			[
				["/droid.+; (glass) \d/i"],
				['model', ['vendor', 'Google'], ['type', 'Wearable']]
			],
			[
				["/droid.+; (wt63?0{2,3})\)/i"],
				['model', ['vendor', 'Zebra'], ['type', 'Wearable']]
			],
			[
				["/(quest( 2)?)/i"],
				['model', ['vendor', 'FaceBook'], ['type', 'Wearable']]
			],
			[
				["/(tesla)(?: qtcarbrowser|\/[-\w\.]+)/i"],
				['vendor', ['type', 'Embeded']]
			],
			[
				["/droid .+?; ([^;]+?)(?: bui|\) applew).+? mobile safari/i"],
				['model', ['type', 'Mobile']]
			],
			[
				["/droid .+?; ([^;]+?)(?: bui|\) applew).+?(?! mobile) safari/i"],
				['model', ['type', 'Tablet']]
			],
			[
				["/\b((tablet|tab)[;\/]|focus\/\d(?!.+mobile))/i"],
				[['type', 'Tablet']]
			],
			[
				["/(phone|mobile(?:[;\/]| [ \w\/\.]*safari)|pda(?=.+windows ce))/i"],
				[['type', 'Mobile']]
			],
			[
				["/(android[-\w\. ]{0,9});.+buil/i"],
				['model', ['vendor', 'Generic']]
			]
		],
	
		'engine' => [
			[
				["/windows.+ edge\/([\w\.]+)/i"],
				['version', ['name', 'Edge HTML']]
			],
			[
				["/webkit\/537\.36.+chrome\/(?!27)([\w\.]+)/i"],
				['version', ['name', 'Blink']]
			],
			[
				["/(presto)\/([\w\.]+)/i", "/(webkit|trident|netfront|netsurf|amaya|lynx|w3m|goanna)\/([\w\.]+)/i", "/ekioh(flow)\/([\w\.]+)/i", "/(khtml|tasman|links)[\/ ]\(?([\w\.]+)/i", "/(icab)[\/ ]([23]\.[\d\.]+)/i"],
				['name', 'version']
			],
			[
				["/rv\:([\w\.]{1,9})\b.+(gecko)/i"],
				['version', 'name']
			]
		],
	
		'os' => [
			[
				["/microsoft (windows) (vista|xp)/i"],
				['name', 'version']
			],
			[
				["/(windows) nt 6\.2; (arm)/i", "/(windows (?:phone(?: os)?|mobile))[\/ ]?([\d\.\w ]*)/i", "/(windows)[\/ ]?([ntce\d\. ]+\w)(?!.+xbox)/i"],
				['name', ['version', 'strMapper', 'windowsVersionMap']]
			],
			[
				["/(win(?=3|9|n)|win 9x )([nt\d\.]+)/i"],
				[['name', 'Windows'], ['version', 'strMapper', 'windowsVersionMap']]
			],
			[
				["/ip[honead]{2,4}\b(?:.*os ([\w]+) like mac|; opera)/i", "/cfnetwork\/.+darwin/i"],
				[['version', "/_/", '.'], ['name', 'iOS']]
			],
			[
				["/(mac os x) ?([\w\. ]*)/i", "/(macintosh|mac_powerpc\b)(?!.+haiku)/i"],
				[['name', 'Mac OS'], ['version', "/_/", '.']]
			],
			[
				["/droid ([\w\.]+)\b.+(android[- ]x86|harmonyos)/i"],
				['version', 'name'],],
			[
				["/(android|webos|qnx|bada|rim tablet os|maemo|meego|sailfish)[-\/ ]?([\w\.]*)/i", "/(blackberry)\w*\/([\w\.]*)/i", "/(tizen|kaios)[\/ ]([\w\.]+)/i", "/\((series40);/i"],
				['name', 'version']
			],
			[
				["/\(bb(10);/i"],
				['version', ['name', 'BlackBerry']]
			],
			[
				["/(?:symbian ?os|symbos|s60(?=;)|series60)[-\/ ]?([\w\.]*)/i"],
				['version', ['name', 'Symbian']]
			],
			[
				["/mozilla\/[\d\.]+ \((?:mobile|tablet|tv|mobile; [\w ]+); rv:.+ gecko\/([\w\.]+)/i"],
				['version', ['name', 'Firefox OS']]
			],
			[
				["/web0s;.+rt(tv)/i", "/\b(?:hp)?wos(?:browser)?\/([\w\.]+)/i"],
				['version', ['name', 'webOS']]
			],
			[
				["/crkey\/([\d\.]+)/i"],
				['version', ['name', 'Chromecast']]
			],
			[
				["/(cros) [\w]+ ([\w\.]+\w)/i"],
				[['name', 'Chromium OS'], 'version']
			],
			[
				["/(nintendo|playstation) ([wids345portablevuch]+)/i", "/(xbox); +xbox ([^\);]+)/i", "/\b(joli|palm)\b ?(?:os)?\/?([\w\.]*)/i", "/(mint)[\/\(\) ]?(\w*)/i", "/(mageia|vectorlinux)[; ]/i", "/([kxln]?ubuntu|debian|suse|opensuse|gentoo|arch(?= linux)|slackware|fedora|mandriva|centos|pclinuxos|red ?hat|zenwalk|linpus|raspbian|plan 9|minix|risc os|contiki|deepin|manjaro|elementary os|sabayon|linspire)(?: gnu\/linux)?(?: enterprise)?(?:[- ]linux)?(?:-gnu)?[-\/ ]?(?!chrom|package)([-\w\.]*)/i", "/(hurd|linux) ?([\w\.]*)/i", "/(gnu) ?([\w\.]*)/i", "/\b([-frentopcghs]{0,5}bsd|dragonfly)[\/ ]?(?!amd|[ix346]{1,2}86)([\w\.]*)/i", "/(haiku) (\w+)/i"],
				['name', 'version']
			],
			[
				["/(sunos) ?([\w\.\d]*)/i"],
				[['name', 'Solaris'], 'version']
			],
			[
				["/((?:open)?solaris)[-\/ ]?([\w\.]*)/i", "/(aix) ((\d)(?=\.|\)| )[\w\.])*/i", "/\b(beos|os\/2|amigaos|morphos|openvms|fuchsia|hp-ux)/i", "/(unix) ?([\w\.]*)/i"],
				['name', 'version']
			]
		]
	];


}


/////////////////
// Constructor
////////////////

class UAParser {

	private $_ua = null;
	private $_browser = null;
	private $_cpu = null;
	private $_device = null;
	private $_os = null;

	private $parser_reg_class = null;

	function __construct($ua){
		$this->parser_reg_class = new UAParserRegExp();
		$this->_rgxmap = $this->parser_reg_class->regexes;
		$this->_ua = $ua;
	}

	function getBrowser() {
		$this->_browser = [];
		$this->_browser['name'] = null;
		$this->_browser['version'] = null;
		$this->_browser = array_merge($this->_browser, $this->parser_reg_class->parse_ua($this->_ua, $this->_rgxmap['browser']));
		$this->_browser['major'] = $this->parser_reg_class->majorize($this->_browser['version']);
		return $this->_browser;
	}
	
	function getCPU() {
		$this->_cpu = [];
		$this->_cpu['architecture'] = null;
		$this->_cpu = array_merge($this->_cpu, $this->parser_reg_class->parse_ua($this->_ua, $this->_rgxmap['cpu']));
		return $this->_cpu;
	}
	
	function getDevice() {
		$this->_device = [];
		$this->_device['vendor'] = null;
		$this->_device['model'] = null;
		$this->_device['type'] = null;
		$this->_device = array_merge($this->_device, $this->parser_reg_class->parse_ua($this->_ua, $this->_rgxmap['device']));
		return $this->_device;
	}
	
	function getEngine() {
		$this->_engine = [];
		$this->_engine['name'] = null;
		$this->_engine['version'] = null;
		$this->_engine = array_merge($this->_engine, $this->parser_reg_class->parse_ua($this->_ua, $this->_rgxmap['engine']));
		return $this->_engine;
	}
	
	function getOS() {
		$this->_os = [];
		$this->_os['name'] = null;
		$this->_os['version'] = null;
		$this->_os = array_merge($this->_os, $this->parser_reg_class->parse_ua($this->_ua, $this->_rgxmap['os']));
		return $this->_os;
	}
	
	function getResult() {
		return [
			'ua'	=> $this->getUA(),
			'browser' => $this->getBrowser(),
			'engine' => $this->getEngine(),
			'os'	=> $this->getOS(),
			'device' => $this->getDevice(),
			'cpu'	=> $this->getCPU()
		];
	}
	
	function getUA() {
		return $this->_ua;
	}

};