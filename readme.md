# UAParser.php

PHP classes to detect Browser, Engine, OS, CPU, and Device type/model from User-Agent data.

* Author    : CodeByZen <<dsda@dsda.ru>>
* Source    : https://github.com/codebyzen/ua-parser-php

PHP script based on UAParser.js v0.7.31 [https://github.com/faisalman/ua-parser-js]

# Documentation

## Constructor

* `$parser = new UAParser($ua);`
    * returns new instance

* `$parser->getResult()`
    * returns result object `{ ua: '', browser: {}, cpu: {}, device: {}, engine: {}, os: {} }`

## Methods

* `getBrowser()`
    * returns `{ name: '', version: '' }`

```sh
# Possible 'browser.name':
2345Explorer, 360 Browser, Amaya, Android Browser, Arora, Avant, Avast, AVG,
BIDUBrowser, Baidu, Basilisk, Blazer, Bolt, Brave, Bowser, Camino, Chimera,
Chrome Headless, Chrome WebView, Chrome, Chromium, Comodo Dragon, Dillo,
Dolphin, Doris, DuckDuckGo, Edge, Electron, Epiphany, Facebook, Falkon, Fennec, 
Firebird, Firefox [Reality], Flock, Flow, GSA, GoBrowser, Huawei Browser, 
ICE Browser, IE, IEMobile, IceApe, IceCat, IceDragon, Iceweasel, Instagram, 
Iridium, Iron, Jasmine, K-Meleon, Kindle, Klar, Konqueror, LBBROWSER, Line, 
LinkedIn, Links, Lunascape, Lynx, MIUI Browser, Maemo Browser, Maemo, Maxthon, 
MetaSr Midori, Minimo, Mobile Safari, Mosaic, Mozilla, NetFront, NetSurf, Netfront, 
Netscape, NokiaBrowser, Obigo, Oculus Browser, OmniWeb, Opera Coast, 
Opera [Mini/Mobi/Tablet], PaleMoon, PhantomJS, Phoenix, Polaris, Puffin, QQ, 
QQBrowser, QQBrowserLite, Quark, QupZilla, RockMelt, Safari, Sailfish Browser, 
Samsung Browser, SeaMonkey, Silk, Skyfire, Sleipnir, Slim, SlimBrowser, Swiftfox, 
Tesla, Tizen Browser, UCBrowser, UP.Browser, Vivaldi, Waterfox, WeChat, Weibo, 
Yandex, baidu, iCab, w3m, Whale Browser...

# 'browser.version' determined dynamically
```

* `getDevice()`
    * returns `{ model: '', type: '', vendor: '' }`

```sh
# Possible 'device.type':
console, mobile, tablet, smarttv, wearable, embedded

# Possible 'device.vendor':
Acer, Alcatel, Amazon, Apple, Archos, ASUS, AT&T, BenQ, BlackBerry, Dell,
Essential, Fairphone, GeeksPhone, Google, HP, HTC, Huawei, Jolla, Lenovo, LG, 
Meizu, Microsoft, Motorola, Nexian, Nintendo, Nokia, Nvidia, OnePlus, OPPO, Ouya,
Palm, Panasonic, Pebble, Polytron, Realme, RIM, Roku, Samsung, Sharp, Siemens,
Sony[Ericsson], Sprint, Tesla, Vivo, Vodafone, Xbox, Xiaomi, Zebra, ZTE, ...

# 'device.model' determined dynamically
```

* `getEngine()`
    * returns `{ name: '', version: '' }`

```sh
# Possible 'engine.name'
Amaya, Blink, EdgeHTML, Flow, Gecko, Goanna, iCab, KHTML, Links, Lynx, NetFront,
NetSurf, Presto, Tasman, Trident, w3m, WebKit

# 'engine.version' determined dynamically
```

* `getOS()`
    * returns `{ name: '', version: '' }`

```sh
# Possible 'os.name'
AIX, Amiga OS, Android[-x86], Arch, Bada, BeOS, BlackBerry, CentOS, Chromium OS,
Contiki, Fedora, Firefox OS, FreeBSD, Debian, Deepin, DragonFly, elementary OS, 
Fuchsia, Gentoo, GhostBSD, GNU, Haiku, HarmonyOS, HP-UX, Hurd, iOS, Joli, KaiOS, 
Linpus, Linspire,Linux, Mac OS, Maemo, Mageia, Mandriva, Manjaro, MeeGo, Minix, 
Mint, Morph OS, NetBSD, Nintendo, OpenBSD, OpenVMS, OS/2, Palm, PC-BSD, PCLinuxOS, 
Plan9, PlayStation, QNX, Raspbian, RedHat, RIM Tablet OS, RISC OS, Sabayon, 
Sailfish, Series40, Slackware, Solaris, SUSE, Symbian, Tizen, Ubuntu, Unix, 
VectorLinux, WebOS, Windows [Phone/Mobile], Zenwalk, ...

# 'os.version' determined dynamically
```

* `getCPU()`
    * returns `{ architecture: '' }`

```sh
# Possible 'cpu.architecture'
68k, amd64, arm[64/hf], avr, ia[32/64], irix[64], mips[64], pa-risc, ppc, sparc[64]
```

* `getResult()`
    * returns `{ ua: '', browser: {}, cpu: {}, device: {}, engine: {}, os: {} }`

* `getUA()`
    * returns UA string of current instance

# Usage

## Using

```php
$parser = new UAParser('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36');
print_r($parser->getResult());
```


## How To Contribute

* Fork and clone this repository
* Make some changes as required
* Run the test suites to make sure it's not breaking anything
* Submit a pull request under `main` branch

# License

MIT License

Copyright (c) 2022 CodeByZen <<dsda@dsda.ru>>
Copyright (c) 2012-2021 Faisal Salman <<f@faisalman.com>>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.